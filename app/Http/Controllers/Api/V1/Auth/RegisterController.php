<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Models\User;
use App\Classes\UserClass;
use App\Models\UserDetail;
use Illuminate\Support\Str;
use App\Classes\CommonClass;
use Illuminate\Http\Request;
use App\Models\TokenActivity;
use App\Events\UserEmailVerified;
use App\Models\EmailVerification;
//use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\Verified;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Events\UserRegistrationSuccessful;
use Symfony\Component\HttpFoundation\Response;
use App\Http\Controllers\Api\V1\BaseController;

class RegisterController extends BaseController
{
    /*
    |--------------------------------------------------------------------------
    | Register Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users as well as their
    | validation and creation. By default this controller uses a trait to
    | provide this functionality without requiring any additional code.
    |
    */

    //use RegistersUsers;

    /**
     * Where to redirect users after registration.
     *
     * @var string
     */
    protected $redirectTo = '/home';
    /**
     * @var CommonClass
     */
    private $commonClass;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        //$this->middleware('guest');
        $this->commonClass = new CommonClass();
        $this->userClass = new UserClass();
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param array $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {

        $messages = [
            'password.regex' => 'Password must be more than 8 characters long, should contain at least 1 Uppercase, 1 Lowercase and  1 number',
        ];
        return Validator::make($data, [
            'name' => ['required', 'string', 'max:255'],
            'surname' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'phone_no' => ['required', 'digits:11', 'unique:users,phone_no'],
            'password' => ['required', 'string', 'min:8', 'confirmed', 'regex:/^(?=.*?[A-Z])(?=.*?[a-z])(?=.*?[0-9]).{8,}$/'],
        ], $messages);
    }


    protected function create(Request $request)
    {

        $messages = [
            'password.regex' => 'Password must be more than 8 characters long, should contain at least 1 Uppercase, 1 Lowercase and  1 number',
        ];
        $validator = Validator::make($request->all(), [
            'subdomain' => ['required', 'string', 'max:24', 'unique:users'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'phone' => ['required', 'string', 'max:20', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed', 'regex:/^(?=.*?[A-Z])(?=.*?[a-z])(?=.*?[0-9]).{8,}$/'],
            'referral_code' => ['nullable', 'string'],
        ], $messages);

        if ($validator->fails()) return $this->sendError('Validation Error.', $validator->messages());
        $validated = $validator->validated();
        $emailExist = DB::table('users')->where(['email'=>$request->email])->exists();
        if ($emailExist){
            return $this->sendError('Email has already been taken', ['error' => ['Email has already been taken']]);
        }
        //check if username already exists
        $userTagExist = DB::table('users')->where(['subdomain'=>$request->subdomain])->exists();
        if ($userTagExist){
            return $this->sendError('Sub - domain has already been taken', ['error' => ['Sub - domain has already been taken']]);
        }


        $message = "Successfully Registered";

        //Verify referral Code
        $referred_by_verify = $this->userClass->validateReferralCode($request->referral_code);

        $user = User::create([
            'email' => $validated['email'],
            'subdomain' => $validated['subdomain'],
            'phone' => $request->phone,
            'referred_by' => $referred_by_verify,
            'password' => Hash::make($validated['password']),
        ]);
        $data['email'] = $user->email;
        $data['subdomain'] = $user->subdomain;
        $data['hasVerifiedEmail'] = false;
        $misc['tier'] = 0;

        event(new UserRegistrationSuccessful($user->id, $validated));
        

        return $this->sendResponse($data, $message, Response::HTTP_CREATED);

    }

    public function verifyEmail(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => ['required', 'string', 'email', 'max:255'],
            'token' => ['required', 'string', 'min:6', 'max:6'],
        ]);
        if ($validator->fails()) return $this->sendError('Validation Error.', $validator->messages());

        $validated = $validator->validated();

        $user = User::where('email', $validated['email'])->first();
        if (!$user) {
            return $this->sendError('User not found', ['error' => ['User not found']]);
        }

        if($user->email_verified_at != null) {
            return $this->sendError('Email has already been verified', ['error' => ['Email has already been verified']]);
        }

        $emailVerification = EmailVerification::where(['email' => $validated['email']])->orderBy('created_at', 'desc')->first();
        if ($emailVerification == null) {
            return $this->sendError('Invalid email verification token', ['error' => ['Invalid email verification token']]);
        }

        $this->commonClass::isEmailTokenValid($emailVerification, $validated['token']);
        if (!$this->commonClass::isEmailTokenValid($emailVerification, $validated['token'])) {;
            return $this->sendError('Invalid email verification token', ['error' => ['Invalid email verification token']]);
        }



        $emailVerification->update(['used' => 'yes']);
        $user->update(['email_verified_at' => now(), 'tier' => '1']);

        event(new UserEmailVerified($user->id));


        $data['email'] = $user->email;
        $data['subdomain'] = $user->subdomain.".".config('settings.customer_support.domain');
        $data['site'] = "https://".$user->subdomain.".".config('settings.customer_support.domain');
        $data['hasVerifiedEmail'] = true;
        $misc['tier'] = 1;
        $message = 'Email verified successfully';

        return $this->sendResponse($data, $message, Response::HTTP_CREATED);
    }

    public function resendEmailVerification(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => ['required', 'string', 'email', 'max:255'],
        ]);
        if ($validator->fails()) return $this->sendError('Validation Error.', $validator->messages());
        $validated = $validator->validated();

        $user = User::where(['email' => $validated['email']])->whereNull('email_verified_at')->first();
        if ($user == null) {
            return ['success' => false, 'message' => 'Invalid operation'];
        }
        $data['email'] = $user->email;
        $data['hasVerifiedEmail'] = false;

        $user->sendApiEmailVerificationTokenNotification();
        $message = 'Email verification has been resent successfully';

        return $this->sendResponse($data, $message, Response::HTTP_CREATED);
    }

    public function showRegistrationForm()
    {

        return view('auth.register');
    }



    public function verifyReferral(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'referral_code' => 'required',
        ]);

        if ($validator->fails()) return $this->sendError('Validation Error.', $validator->messages());

        $validated = $validator->validated();
        $validateReferral = $this->validateReferralCode($validated['referral_code']);

        if ($validateReferral[0] == false) {
            return $this->sendError('Error', ['error' => [$validateReferral[1]]]);
        }
        $data['name'] = $validateReferral[1]->name;
        $data['surname'] = $validateReferral[1]->surname;
        $data['referral_code'] = $validateReferral[1]->referral_code;
        return $this->sendResponse($data, 'Retrieved successfully');

    }


    /**
     * @OA\Post(
     * path="/api/v2/verify-email",
     * summary="Verify User",
     * description="Verify the user by checking if there email is registered on fundbae",
     * operationId="authLogin",
     * tags={"user"},
     * security={ {"bearer": {} }},
     * @OA\RequestBody(
     *    required=true,
     *    description="Pass user email",
     *    @OA\JsonContent(
     *       required={"email"},
     *       @OA\Property(property="email", type="string", format="email", example="user1@mail.com"),
     *    ),
     * ),
     * @OA\Response(
     *   response=400,
     *   description="Returns if no record for the currencies exist",
     *      @OA\JsonContent(
     *         @OA\Property(property="message", type="string", example="Error"),
     *         @OA\Property(property="success", type="boolean", example="false"),
     *         @OA\Property(property="data", type="object",
     *            @OA\Property(property="error", type="array",
     *               @OA\Items( type="string", example="User is not registered ")
     *            ),
     *            @OA\Property(property="is_registered", type="boolean", example="false"),
     *         ),
     *     ),
     *  ),
     *  @OA\Response(
     *    response=200,
     *    description="User has registered",
     *    @OA\JsonContent(
     *       @OA\Property(property="message", type="string", example="Invalid username and/or password"),
     *       @OA\Property(property="success", type="boolean", example="false"),
     *       @OA\Property(property="data", type="object",
     *          @OA\Property(property="is_registered", type="boolean", example="true"),
     *        ),
     *    )
     *  ),
     * )
     */

    public function validateUser(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
        ]);
        if ($validator->fails()) return $this->sendError('Validation Error.', $validator->messages());
        $doesUserExists = $this->checkIfEmailExist($request->email);
        if (!$doesUserExists[0]) {
            return $this->sendError('Email does not exist', ['error' => ['User is not registered '], 'is_registered' => false]);
        }
        return $this->sendResponse(['is_registered' => true], 'User has registered');
    }




    public function checkIfEmailExist($email)
    {

        $user = User::where('email', $email)->first();
        if ($user != null) {
            return [true, $user->email];
        }
        return [false, 'Email does not exist'];

    }


}