<?php

namespace App\Http\Controllers\Api\V1\User;

use App\Models\UserDetail;
use Illuminate\Http\Request;
use App\Http\Controllers\Api\V1\BaseController;

class UserProfileSettingsController extends BaseController
{
    public function __construct()
    {
        $this->userDetail = new UserDetail();
    }

    public function getMyProfile()
    {
        $data = [];
        $data['email'] = auth()->user()->email;
        $data['detail'] = $this->userDetail->where('user_id',auth()->user()->id)->get()->first();
        return $this->sendResponse($data, 'User Profile');
    }

    public function updateMyProfile(Request $request)
    {
        if($request){
            $data = $this->userDetail->where('id',auth()->user()->userDetail->id)->update([
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'other_names' => $request->other_names,
                'profile_photo' => $request->profile_photo,
            ]);

            if($data){
                return $this->sendResponse($request->all(), 'User Profile Updated');
            }else{
                return $this->sendError('Error', ['error' => ['An error occurred']]);
            }
        }else{
            return $this->sendError('Error', ['error' => ['An error occurred']]);
        }
    }
}
