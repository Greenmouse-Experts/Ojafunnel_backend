<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Models\User;
use Illuminate\Support\Str;
use App\Classes\CommonClass;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Notifications\LoginNotification;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Notification;
use App\Notifications\FailedLoginNotification;
use App\Http\Controllers\Api\V1\BaseController;

class LoginController extends BaseController
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */


    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/home';
    /**
     * @var CommonClass
     */
    private $common;


    public function __construct()
    {
        $this->middleware('guest')->except('logout');
        $this->common = new CommonClass();
    }

    public function failedLogin($request)
    {
        $email_exist = User::where('email',$request->email)->get()->first();
        if($email_exist){
            $requestArr = ['is_mobile'=>$request->is_mobile,'os'=>$request->os,'version'=>$request->version,
            'browser_name'=>$request->browser_name,'macAddress' => $request->macAddress,
            'channel'=>$request->channel,'ip'=>$request->ip()];

            Notification::route('mail', $email_exist->email)
                    ->notify((new FailedLoginNotification($email_exist->subdomain, now(), $requestArr))
                        ->delay(now()->addSeconds(5)));
        }
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required',
        ]);
        if ($validator->fails()){
            $this->failedLogin($request);
            return $this->sendError('Validation Error.', $validator->messages());
        }

        $validated = $validator->validated();

        $requestArr = ['is_mobile'=>$request->is_mobile,'os'=>$request->os,'version'=>$request->version,
            'browser_name'=>$request->browser_name,'macAddress' => $request->macAddress,
            'channel'=>$request->channel,'ip'=>$request->ip()];

        if (Auth::attempt(['email' => $validated['email'], 'password' => $validated['password']])) {
            $user = Auth::user();
            if ($user->status != "active") {
                return ['success' => false, 'message' => 'Your account is inactive'];
            }
            $loginToken = $user->createToken('user');
            //return $loginToken->accessToken->id;
            if ($user->email_verified_at !== NULL) {
                $data['hasVerifiedEmail'] = true;
                $userTokens = $request->user()->tokens()->get();
                foreach ($userTokens as $key => $token) {
                    if ($token->id != $loginToken->accessToken->id) {
                        $token->delete();
                    }
                }
                Notification::route('mail', $user->email)
                    ->notify((new LoginNotification($user->subdomain, now(), $requestArr))
                        ->delay(now()->addSeconds(5)));
                $data['accessToken'] = $loginToken->plainTextToken;
                $data['subdomain'] = $user->subdomain;
                $data['email'] = $user->email;
                $data['tier'] = $user->tier;
                $message = 'Login successful';

                return $this->sendResponse($data, $message, Response::HTTP_CREATED);
            }
            $data['hasVerifiedEmail'] = false;
            $data['email'] = $user->email;
            $message = 'Please verify your email';

            return $this->sendResponse($data, $message, Response::HTTP_CREATED);
        }

        $this->failedLogin($request);

        return $this->sendError('Your email or password is incorrect', ['error' => ['Your email or password is incorrect']]);


    }

}
