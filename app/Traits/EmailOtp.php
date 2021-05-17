<?php

namespace App\Traits;

use App\Models\User;
use App\Notifications\SendEmailOtp;
use Illuminate\Validation\ValidationException;

trait EmailOtp
{
    /**
     * Send otp
     *
     * @param User|Contact $model
     */
    public function sendOtp($model) : void
    {   
        $type = $model instanceof User ? 'user' : 'contact';

        $otp = rand(100000, 999999);

        (new User)->forceFill([
            'email' => $model->email,
        ])->notify(new SendEmailOtp($otp));

        cache(['otp_email_' . $type . '_'.$model->email => $otp], config('config.auth.otp_lifetime') * 60);
    }

    /**
     * Validate cache
     *
     * @param User|Contact $model
     */
    public function validateCache($model) : void
    {
        $type = $model instanceof User ? 'user' : 'contact';

        if (! \Cache::has('otp_email_'. $type .'_'.$model->email)) {
            throw ValidationException::withMessages(['otp' => __('auth.login.invalid_otp')]);
        }

        if (\Cache::get('otp_email_'. $type .'_'.$model->email) != request('otp')) {
            throw ValidationException::withMessages(['otp' => __('auth.login.invalid_otp')]);
        }

        \Cache::forget('otp_email_'. $type .'_'.$model->email);
    }
}