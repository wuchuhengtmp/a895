<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

$api = app('Dingo\Api\Routing\Router');

$api->version('v1', [
    'namespace' => 'App\Http\Controllers\Api'
], function($api) {
    $api->group([
        'middleware' => 'api.throttle',
        'limit'      => 1,
        'expires'    => 1,
    ], function($api) {
        // 注册短信验证码
        $api->post('verificationCodes', 'VerificationCodesController@store');
        $api->post('resetPasswordCodes', 'VerificationCodesController@passwordStore');
    });

    $api->post('authorizations', 'AuthorizationsController@store');

    $api->post('users', 'UsersController@store');
    $api->put('users/password', 'UsersController@updatePassword');

    $api->get('index', 'IndexController@index');

    $api->get('location', 'UsersController@location');

    $api->get('articles/categores', 'ArticleController@categoresIndex');
    $api->get('articles', 'ArticleController@index');
    $api->get('articles/categores/{category}', 'ArticleController@categoryIndex');
    $api->get('articles/{article}', 'ArticleController@show');


    $api->post('avatar','MemberPictureController@uploadAvatar');

    $api->patch('pay/wx_notify', 'PayController@wxNotify');
    $api->patch('pay/alipay_notify', 'PayController@aliPayNotify');

    $api->get('cases/categores', 'CasesController@categoryIndex');

    $api->get('calculate', 'CalculateController@index');

       // 需要 token 验证的接口
    $api->group(['middleware' => 'api.auth'], function($api) {
        $api->get('cases', 'CasesController@index');
        $api->get('cases/city_codes/{city_code}', 'CasesController@searchByCityCode');
        $api->get('cases/{id}', 'CasesController@show');
        $api->put('cases/{id}/like', 'CasesController@like');
        $api->delete('cases/{id}/like', 'CasesController@destroyLike');
        $api->put('cases/{id}/favorite', 'CasesController@favorite');
        $api->delete('cases/{id}/favorite', 'CasesController@destroyFavorite');
        $api->post('cases/{id}/comments', 'CasesController@saveComment');
        $api->get('cases/orders/{id}', 'CaseOrderController@show');
        $api->put('cases/orders/{id}/application', 'CaseOrderController@update');

        $api->get('signes', 'SignesController@index');
        $api->post('signes', 'SignesController@store') ;

        // 配置
        $api->get('about_us/categores', 'MeConfigController@aboutUs');
        $api->get('about_us/{type}', 'MeConfigController@aboutUsList');
        $api->post('feedback', 'MeConfigController@feedback');
        $api->patch('userpwd', 'MeConfigController@userPwdUpdate');
        $api->patch('transfer_pwd', 'MeConfigController@transferPwd');

        // 个人主页
        $api->get('userinfo', 'MeUserInfoController@getUserInfo');
        $api->patch('avatar', 'MeUserInfoController@avatarUpdate');
        $api->patch('user_nickname', 'MeUserInfoController@nickNameUpdate');
        $api->get('address', 'MeUserInfoController@receivingAddressList');
        $api->post('address', 'MeUserInfoController@receivingAddressAdd');
        $api->patch('address', 'MeUserInfoController@receivingAddressUpdate');
        $api->delete('address/{id}', 'MeUserInfoController@receivingAddressDelete');
        
        // 我的收藏
        $api->get('favorite_cases', 'MeCollectionController@getCollectionList');
        $api->get('favorite_cases/{id}', 'MeCollectionController@getCollectionInfo');
        $api->delete('favorite_cases/{id}', 'MeCollectionController@collectionDelete');

        // 积分
        $api->get('credits', 'MeCreditController@getTaskList');
        $api->patch('credits', 'MeCreditController@transferAccounts');
        $api->get('credits', 'MeCreditController@meCreditList');

        // 商城 
        $api->get('goods', 'MallController@getGoodsList');
        $api->get('goods/{id}', 'MallController@getGoodsInfo');
        $api->post('pay/{type}', 'PayController@payAdd');

        // 我的订单
        $api->get('me_orders/{type}', 'MeOrderController@getOrderList');
        $api->delete('me_orders/{id}', 'MeOrderController@orderDelete');
        $api->patch('me_orders/{id}', 'MeOrderController@orderConfirm');
        $api->post('evaluate_good', 'MeOrderController@evaluateGood');
        $api->get('order_logistics/{id}', 'MeOrderController@orderLogistics');
        $api->get('order_info/{id}', 'MeOrderController@orderInfo');
    });

    $api->get('cases/{id}/comments', 'CasesController@contentIndex');
    $api->post('cases/orders', 'CaseOrderController@save');

    // 第三方登录
    $api->post('socials/{social_type}/authorizations', 
        'AuthorizationsController@socialStore');
});

