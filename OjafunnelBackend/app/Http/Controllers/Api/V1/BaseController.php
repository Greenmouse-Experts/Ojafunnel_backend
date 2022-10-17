<?php

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\MessageBag;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller as Controller;

/**
 * @OA\Info(
 *    title="PayBuyMax API DOCUMENTATION",
 *    version="2.0.0",
 * )
 */
class BaseController extends Controller
{
    /**
     * success response method.
     * @result mixed the returned result
     * @message string
     * @responseCode \Illuminate\Http\Response
     *
     * @param $result
     * @param $message
     * @param int $responseCode
     * @return \Illuminate\Http\JsonResponse
     */
    public function sendResponse($result, $message, $responseCode = Response::HTTP_OK)
    {
        $misc['hasBvn'] = false;
        $misc['hasVerifiedBVN'] = false;
        $misc['isLiving'] = false;
        $misc['hasValidatedIsLiving'] = false;
        $misc['hasValidatedDocument'] = false;
        if (Auth::user() != null) {
            if (Auth::user()->userDetail != null) {
                $userDetail = Auth::user()->userDetail;
                $misc['hasBvn'] = $userDetail->bvn != null ?? false;
                $misc['hasVerifiedBVN'] = $userDetail->has_validate_phone_no == 'yes' || $misc['hasBvn'];
                $misc['isLiving'] = $userDetail->is_living == 'yes';
                $misc['hasValidatedIsLiving'] = $userDetail->has_validate_is_living == 'yes';
                $misc['hasValidatedDocument'] = $userDetail->has_validate_document == 'yes';
            }
        }
        $response = [
            'success' => true,
            'data' => $result,
            'message' => $message,
            // 'misc' => $misc
        ];


        return response()->json($response, $responseCode);
    }


    /**
     * return error response.
     * @param string $error error message
     * @param array $errorMessages array of messages
     *
     * @param int $code
     * @return \Illuminate\Http\JsonResponse
     */
    public function sendError($error, $errorMessages = [], $code = Response::HTTP_BAD_REQUEST)
    {
        $response = [
            'success' => false,
            'message' => $error,
        ];
        if (!empty($errorMessages)) {
            if ($errorMessages instanceof MessageBag) {

                $errorMessages->add('error', $errorMessages->first());
                $response['message'] = $errorMessages->first(); //put the first error inside message
            }
            $response['data'] = $errorMessages;
        } else {
            $response['data'] = ['error' => "$error"];
        }


        return response()->json($response, $code);
    }

    
    public function sendResponsePublic($result, $message, $responseCode = Response::HTTP_OK)
    {
        $response = [
            'success' => true,
            'data' => $result,
            'message' => $message,
        ];

        return response()->json($response, $responseCode);
    }

}
