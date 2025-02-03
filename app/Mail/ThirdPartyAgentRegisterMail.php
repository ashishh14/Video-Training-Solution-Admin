<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ThirdPartyAgentRegisterMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($attributes)
    {
        $this->attributes = $attributes;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        // $this->attributes['logo'] = env('WEB_URL') . '/assets/images/logo.png';
        $this->attributes['logo']=env('APP_ENV')=='production'?env('WEB_URL') . '/assets/images/logo.png':public_path('logo.png');
        $this->attributes['web'] = env('WEB_URL').'/admin';
        
        return $this->to($this->attributes['email'])
            ->subject('JKSHAH ONLINE - THIRD PARTY AGENT REGISTRATION')
            ->view('emails.registration_mail')
            ->with('attributes', $this->attributes);
    }
}
