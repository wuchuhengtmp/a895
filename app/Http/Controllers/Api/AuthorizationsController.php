<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Validate\CheckAuthorizations;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request as RequestClient;
use App\Exceptions\Api\Base as BaseException;
use App\Model\User;
use Illuminate\Support\Facades\Storage;

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

    /**
     *   微信登录
     * 
     */
    public function socialStore(Request $Request, User $UserModel)
    {
        $Request->validate([
            'code' => 'required'
        ], [
            'code.required' => 'code不能为空'
        ]);

        $client = new Client([
            // Base URI is used with relative requests
            'base_uri' => 'http://baidu.com',
            // You can set any number of default request options.
            'timeout'  => 2.0,
        ]);
        
        $client->request('GET', '/get', ['query' => ['foo' => 'bar']]);
        $appid    = get_config('WX_APPID');
        $secret   = get_config('WX_APPSECRET');
        $code     = $Request->code;
        $response = file_get_contents("https://api.weixin.qq.com/sns/oauth2/access_token?appid={$appid}&secret={$secret}&code={$code}&grant_type=authorization_code");
        $response = json_decode($response, true);
        if (array_key_exists('errcode', $response)) {
            throw new BaseException([
                'msg' => $response['errmsg']
            ]);
        }
	$access_token = $response['access_token'];
        $openid = $response['openid'];
        $userinfo_response = file_get_contents("https://api.weixin.qq.com/sns/userinfo?access_token={$access_token}&openid={$openid}");
        $Userinfo_response = json_decode($userinfo_response);
        if (array_key_exists('errcode', $response)) {
            throw new BaseException([
                'msg' => $response['errmsg']
            ]);
        }
        $User = $UserModel->where('openid', $openid)->get();
        // 新建用
        if ($User->isEmpty()) {
            $UserModel->openid   = $Userinfo_response->openid;
            $UserModel->nickname = $Userinfo_response->nickname;
            $UserModel->avatar   = $Userinfo_response->headimgurl;
            $file_name  = 'avatar' . '/' .time() . rand(1, 9999999) . ".png";
            Storage::disk('qiniu')->put($file_name, file_get_contents($UserModel->avatar));
            $UserModel->avatar = $file_name;
            $UserModel->save();
        }
        $User = $UserModel->where('openid', $openid)->first();
        $token=\Auth::guard('api')->fromUser($User);
        $has_phone = $User->phone ? 1 : 0;
        return $this->responseSuccessData([
            'access_token' => $token,
            'token_type'   => 'Bearer',
            'expires_in'   => \Auth::guard('api')->factory()->getTTL() * 60,
            'has_phone'   => $has_phone

        ]);
    }
}
