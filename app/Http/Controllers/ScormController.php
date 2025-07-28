<?php

namespace App\Http\Controllers;

use App\CourseProgress;
use App\ScormPackage as AppScormPackage;
use ZipArchive;
use Illuminate\Http\Request;
use App\CourseProgress as AppCourseProgress;

class ScormController extends Controller
{
     public function showForm()
    {
        return view('upload');
    }

   public function upload(Request $request)
    {
        $request->validate([
            'title' => 'required|string',
            'zip_file' => 'required|mimes:zip|max:1024000', 
        ]);

        $zip = $request->file('zip_file');
        $folderName = 'scorm_' . time();
        $extractPath = public_path('scorm_packages/' . $folderName);

        mkdir($extractPath, 0775, true);
        $zipPath = $extractPath . '/' . $zip->getClientOriginalName();
        $zip->move($extractPath, $zip->getClientOriginalName());

        $zipArchive = new ZipArchive;
        if ($zipArchive->open($zipPath)) {
            $zipArchive->extractTo($extractPath);
            $zipArchive->close();
            unlink($zipPath);
        }

        $manifestPath = $extractPath . '/imsmanifest.xml';
        $launchFile = null;

        if (file_exists($manifestPath)) {
            $xml = simplexml_load_file($manifestPath);
            $xml->registerXPathNamespace('ns', 'http://www.imsproject.org/xsd/imscp_rootv1p1p2');
            $resource = $xml->xpath('//ns:resource')[0] ?? null;

            if ($resource) {
                $base = (string) $resource['base'] ?? '';
                $href = (string) $resource['href'];
                $launchFile = $base ? ($base . '/' . $href) : $href;
            }
        }

        if (!$launchFile || !file_exists($extractPath . '/' . $launchFile)) {
            return back()->with('error', 'Launch file not found.');
        }

        AppScormPackage::create([
            'title' => $request->title,
            'folder_name' => $folderName,
            'launch_file' => $launchFile,
        ]);

    return back()->with('success', 'SCORM course uploaded ');    }

public function view($id)
{
    $package = AppScormPackage::findOrFail($id);
    $userId = auth()->id();

    $progress = AppCourseProgress::where('user_id', $userId)
                ->where('course_id', $id)
                ->first();

    // Default launch file
    $defaultLaunchFile = 'scorm_packages/' . $package->folder_name . '/' . $package->launch_file;

    // Use saved lesson location if exists
    if ($progress && $progress->cmi_core_lesson_location) {
        $launchPath = public_path('scorm_packages/' . $package->folder_name . '/' . $progress->cmi_core_lesson_location);
        if (file_exists($launchPath)) {
            $launchUrl = asset('scorm_packages/' . $package->folder_name . '/' . $progress->cmi_core_lesson_location);
        } else {
            // fallback to default if saved file not found
            $launchUrl = asset($defaultLaunchFile);
        }
    } else {
        $launchUrl = asset($defaultLaunchFile);
    }

    return view('view', [
        'launchUrl' => $launchUrl,
        'title' => $package->title,
        'courseId' => $id,
        'lastLocation' => optional($progress)->cmi_core_lesson_location,
        'sessionTime' => optional($progress)->session_time ?? 0,
        'lessonStatus' => optional($progress)->cmi_core_lesson_status,
    ]);
}



    public function saveProgress(Request $request)
{
    $user = auth()->user();

    CourseProgress::updateOrCreate(
        [
            'user_id' => $user->id,
            'course_id' => $request->course_id
        ],
        [
            $request->key => $request->value, // will save either lesson_location or lesson_status
        ]
    );

    return response()->json(['status' => 'saved']);
}

}
