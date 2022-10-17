<?php

namespace App\Classes;



use App\Models\EmailVerification;
use App\Models\WithdrawalVerification;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;

class CommonClass
{
    public static function isEmailTokenValid(EmailVerification $emailVerification, string $token): bool
    {

        if ($emailVerification->used == 'yes') {
            return false;
        }
        $now = Carbon::now();
        if (Carbon::parse($emailVerification->created_at)->diffInMinutes($now) > config('settings.auth.email_verification_token_expiration')) {
            return false;
        }
        if (!Hash::check($token, $emailVerification->token)) {
            return false;
        }
        return true;
    }

    public static function isWithdrawalTokenValid(WithdrawalVerification $withdrawalVerification, string $token): bool
    {

        if ($withdrawalVerification->used == 'yes') {
            return false;
        }
        $now = Carbon::now();
        if (Carbon::parse($withdrawalVerification->created_at)->diffInMinutes($now) > config('settings.auth.withdrawal_verification_token_expiration')) {
            return false;
        }
        if (!Hash::check($token, $withdrawalVerification->token)) {
            return false;
        }
        return true;
    }

    public function getBvnDataFromJson(string $bvnData, $gateway): array
    {

        if ($gateway == 'smile_identity')$thisData = $this->resolveSmileIdentityRawBvnData($bvnData);
        else return ['success' => false, 'message' => 'Operation gateway not available'];

        if($thisData['success'] == false) return ['success' => false, 'message' => 'Operation not successful'];

       return ['success' => true,  'data' => $thisData['data']];
    }

    private function resolveSmileIdentityRawBvnData(string $bvn): array
    {
        $response = json_decode($bvn, true);
        if ($response['ResultCode'] != "1012") {
            return ['success' => false, 'message' => $response['ResultText']];
        }
        if (!array_key_exists('FullData', $response)) {
            return ['success' => false, 'message' => 'Operation could not be successful'];
        }
        if (array_key_exists('PhoneNumber1', $response['FullData'])) {
            $phoneNo = $response['FullData']['PhoneNumber1'];
        } elseif (array_key_exists('PhoneNumber', $response['FullData'])) {
            $phoneNo = $response['FullData']['PhoneNumber'];
        } else {
            return ['success' => false, 'message' => 'Operation could not be successful'];
        }
        if (substr($phoneNo, 0, 3) == '234') {
            $str1 = substr($phoneNo, 0, 7);
            $str2 = substr($phoneNo, 8, 15);
            $remove234 = substr($str1, 3, 7);
            $phoneNo = '0' . $remove234 . $str2;
        }
        //$imagePath = $this->storeImage($response['FullData']['ImageBase64'], 'bvn-images',  Str::random(5).'_'.$response['FullData']['FirstName'] . '_' . $userId);
        $smileDob = date('Y-m-d', strtotime($response['FullData']['DateOfBirth']));

        $data = [
                    'birth_date' => $smileDob,
                    'bvn' => $response['IDNumber'],
                    'gender' => $response['FullData']['Gender'],
                    'phone_no' => $phoneNo,
                    'full_name' => $response['FullName'],
                    'first_name' => $response['FullData']['FirstName'],
                    'last_name' => $response['FullData']['LastName'],
                    'other_names' => $response['FullData']['MiddleName'],
                    'address' => $response['Address'],
                    'imageBase64' => $response['FullData']['ImageBase64'],

                ];
        return ['success' => true, 'message' => 'Valid BVN', 'data' => $data];
    }


}
