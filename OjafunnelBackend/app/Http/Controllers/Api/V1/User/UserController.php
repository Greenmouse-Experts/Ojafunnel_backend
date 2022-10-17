<?php

namespace App\Http\Controllers\Api\V1\User;

use App\Http\Controllers\Api\V1\BaseController;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;


class UserController extends BaseController
{
    /**
     * @var User
     */
    private $user;

    public function __construct()
    {
        $this->user = new User;
    }
    //
    public function dashboard(){
        $data = [];
        $userDetailsSummary = $this->user::with('accounts')->find(Auth::user()->id);
        $userWalletAccount = $userDetailsSummary->accounts->where('account_type_id', config('settings.accounts.wallet'))->first();

        $usdExchangeRate = getExchangeRate('USD');

        $data['walletBalance'] = $userWalletAccount->getAccountBalance();
        return $this->sendResponse($data, "User Dashboard");
    }

    public function userCheckList(): \Illuminate\Http\JsonResponse
    {
        $userDetailsSummary = $this->user::with('cards','userDetail', 'withdrawalAccount')->find(Auth::user()->id);

        $userHasPendingCheckList = true;
        $checkList = [];
        $i = 0;
        if(!$userDetailsSummary->userDetail || !$userDetailsSummary->userDetail->bvn || $userDetailsSummary->userDetail->has_validate_bvn == 'no'){
            $i++;
            $data_array = array(
                "title" => "Validate your BVN",
                "link" => "01"
            );
            $checkList[] = $data_array;
        }
        if ($userDetailsSummary->cards->where('status', 'active')->count() == 0) {
            $i++;
            $data_array = array(
                "title" => "Add your card to top-up easily",
                "link" => "02"
            );
            $checkList[] = $data_array;
        }
        if ($userDetailsSummary->withdrawalAccount->where('valid_status', '1')->count() == 0) {
            $i++;
            $data_array = array(
                "title" => "You have not added a withdrawal bank",
                "link" => "03"
            );
            $checkList[] = $data_array;
        }
        if(!$userDetailsSummary->userDetail || !$userDetailsSummary->userDetail->document_number){
            $i++;
            $data_array = array(
                "title" => "Validate your identity check",
                "link" => "04"
            );
            $checkList[] = $data_array;
        }



        if ($i == 0) $userHasPendingCheckList = false;
        $data['userHasPendingCheckList'] = $userHasPendingCheckList;
        $data['checkList'] = $checkList;

        return $this->sendResponse($data, "User Dashboard check list");
    }


}
