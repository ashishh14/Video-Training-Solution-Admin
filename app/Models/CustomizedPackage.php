<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomizedPackage extends Model
{
    protected $guarded = ['id'];

    public function package()
    {
        return $this->belongsTo(Package::class);
    }

    public function selectedPackage()
    {
        return $this->belongsTo(Package::class, 'selected_package_id');
    }
}
