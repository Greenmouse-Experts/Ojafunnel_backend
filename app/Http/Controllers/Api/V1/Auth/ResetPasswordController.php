<?php

namespace App\Http\Controllers\Api\V1\Auth;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Notification;
use App\Http\Controllers\Api\V1\BaseController;
use App\Notifications\ResetPasswordNotification;

class ResetPasswordController extends BaseController
{

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        //$this->middleware('guest');
    }

    public function reset_password(Request $request)
    {
        $messages = [
            'password.regex' => 'Password must be more than 8 characters long, should contain at least 1 Uppercase, 1 Lowercase and  1 number',
        ];
        $validator = Validator::make($request->all(), [
            'password' => ['required', 'string', 'min:8', 'confirmed', 'regex:/^(?=.*?[A-Z])(?=.*?[a-z])(?=.*?[0-9]).{8,}$/'],
        ], $messages);

        if ($validator->fails()) return $this->sendError('Validation Error.', $validator->messages());
        $validated = $validator->validated();

        $user = Auth::user();
        //compare hash password with old password
        if (Hash::check($validated['password'], $user->password)) {
            return $this->sendError('Password already exists, please use another password', ['password' => 'Password already exists']);
        }
        $user->update(['password' => Hash::make($validated['password'])]);
        //todo: make notification to alert user that password has been changed

         $requestArr = ['is_mobile'=>$request->is_mobile,'os'=>$request->os,'version'=>$request->version,
            'browser_name'=>$request->browser_name,'macAddress' => $request->macAddress,
            'channel'=>$request->channel,'ip'=>$request->ip()];

        Notification::route('mail', $user->email)
                    ->notify((new ResetPasswordNotification($user->subdomain, now(), $requestArr))
                        ->delay(now()->addSeconds(5)));
        $data = [];

        return $this->sendResponse($data, "Password reset successful");

    }
}
