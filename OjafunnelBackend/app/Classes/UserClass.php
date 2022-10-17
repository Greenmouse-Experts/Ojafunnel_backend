<?php

namespace App\Classes;

use App\Models\User;
use App\Models\UserDetail;
use Illuminate\Support\Str;

class UserClass
{
    /**
     * @var User
     */
    private $user;
    /**
     * @var UserDetail
     */
    private $userDetail;

    public function __construct()
    {
        $this->user = new User();
        $this->userDetail = new UserDetail();
    }

    public function generateReferralCode($userName)
    {
        $userNameLength = Str::length($userName);
        $referralCodeLength = 8;
        $randomUpperCase = "BCDFGHJKLMNPQRSTVWXYZ";

        if ($userNameLength >= 5) {
            $randomLetters = substr(str_shuffle(str_repeat($randomUpperCase, 5)), 0, 3);
            $getUserFirstFiveCharacter = substr($userName, 0, 5);
            $code = Str::upper('' . $randomLetters . '' . $getUserFirstFiveCharacter . '');
            $referral_code = $code;
        } else {
            $amountOfRandomStringToGen = $referralCodeLength - $userNameLength;
            $randomLetters = substr(str_shuffle(str_repeat($randomUpperCase, 5)), 0, $amountOfRandomStringToGen);
            $getUserFirstFiveCharacter = $userName;
            $code = Str::upper('' . $randomLetters . '' . $getUserFirstFiveCharacter . '');
            $referral_code = $code;
        }
        //fetch referral from database
        $checkReferral = $this->validateReferralCode($referral_code);
        //check if referral code is unique
        if ($checkReferral[0] === false) {
            return $referral_code;
        }

        return $this->generateReferralCode($userName);

    }

    public function validateReferralCode($referralCode)
    {

        $checkIfCodeExist = $this->userDetail::where('referral_code', $referralCode)->first();
        if ($checkIfCodeExist != null) {
            return [true, $checkIfCodeExist];
        }
        return [false, 'Referral code does not exist'];
    }
}
