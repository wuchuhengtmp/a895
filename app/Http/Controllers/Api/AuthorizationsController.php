<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Validate\CheckAuthorizations;

class AuthorizationsController extends Controller
{
    public function store(Request $Request)
    {
        (new CheckAuthorizations())->goCheck();
        $credentials['password'] = $Request->password;
        $credentials['phone']    = $Request->phone;
        if (!$token = \Auth::guard('api')->attempt($credentials)) {
            return $this->responseFail('用户名或密码错误');
        }
        return $this->responseSuccessData([
            'access_token' => $token,
            'token_type'   => 'Bearer',
            'expires_in'   => \Auth::guard('api')->factory()->getTTL() * 60
        ]);
    }
}
