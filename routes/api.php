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
        /* 'middleware' => 'api.throttle', */
        /* 'limit'      => 1, */
        /* 'expires'    => 1, */
    ], function($api) {
        // 注册短信验证码
        $api->post('verificationCodes', 'VerificationCodesController@store');
        $api->post('resetPasswordCodes', 'VerificationCodesController@passwordStore');
    });

    $api->post('authorizations', 'AuthorizationsController@store');

    $api->post('users', 'UsersController@store');
    $api->post('users/updatepassword', 'UsersController@updatePassword');

    $api->get('index', 'IndexController@index');

    $api->get('location', 'UsersController@location');

    $api->get('articles/categores', 'ArticleController@categoresIndex');
    $api->get('articles', 'ArticleController@index');
    $api->get('articles/categores/{category}', 'ArticleController@categoryIndex');
    $api->get('articles/{article}', 'ArticleController@show');


    $api->post('avatar','MemberPictureController@uploadAvatar');
    
    $api->get('pays/wechat/natify', 'PayController@wxNotify');
    $api->post('pays/wechat/natify', 'PayController@wxNotify');

    $api->get('pays/wechat/case_order/natify', 'PayController@wxCaseOrderNotify');
    $api->post('pays/wechat/case_order/natify', 'PayController@wxCaseOrderNotify');


    $api->get('cases/categores', 'CasesController@categoryIndex');

    $api->get('calculate', 'CalculateController@index');
    $api->get('about_us/categores', 'MeConfigController@aboutUs');
    $api->get('about_us/{type}', 'MeConfigController@aboutUsList');
    $api->get('designeres/{id}', 'DesignerController@show');

       // 需要 token 验证的接口
    $api->group(['middleware' => 'api.auth'], function($api) {
        $api->get('cases', 'CasesController@index');
        $api->get('cases/recomments', 'CasesController@recommentShow');
        $api->get('cases/city_codes/{city_code}', 'CasesController@searchByCityCode');
        $api->get('cases/{id}', 'CasesController@show')->where('id', '[0-9]+');
        $api->post('cases/{id}/like', 'CasesController@like');
        /* $api->post('cases/{id}/del_like', 'CasesController@destroyLike'); */
        $api->post('cases/{id}/favorite', 'CasesController@favorite');
        /* $api->post('cases/{id}/del_favorite', 'CasesController@destroyFavorite'); */
        $api->post('cases/{id}/comments', 'CasesController@saveComment');
        $api->get('cases/orders/{id}', 'CaseOrderController@show');
        $api->post('cases/orders/{id}/application', 'CaseOrderController@update');
        $api->get('cases/orders/{order_id}/pay_times', 'PaytimesController@index');
        $api->post('cases/orders/{order_id}/pay_times/{id}/pay', 'PaytimesController@update');
        $api->post('cases/orders/{id}/pay', 'CaseOrderController@pay');
        $api->post('cases/orders/{id}/patch_comment', 'CaseOrderController@saveComment');
        $api->get('cases/orders', 'CaseOrderController@index');
        $api->post('del_cases/orders/{id}', 'CaseOrderController@destroy');

        $api->get('signes', 'SignesController@index');
        $api->post('signes', 'SignesController@store') ;

        // 配置
        $api->post('feedback', 'MeConfigController@feedback');
        $api->post('patch_userpwd', 'MeConfigController@userPwdUpdate');
        $api->post('patch_transfer_pwd', 'MeConfigController@transferPwd');

        // 个人主页
        $api->get('userinfo', 'MeUserInfoController@getUserInfo');
        $api->post('patch_avatar', 'MeUserInfoController@avatarUpdate');
        $api->post('patch_user_nickname', 'MeUserInfoController@nickNameUpdate');
        $api->get('address', 'UsersController@addressinde');
        $api->post('address', 'UsersController@addressSave');
        $api->post('patch_address/{address_id}', 'UsersController@addressUpdate');
        $api->post('del_address/{address_id}', 'UsersController@addressDestroy');
        
        // 我的收藏
        $api->get('favorite_cases', 'UsersController@getCollectionList');
        $api->get('favorite_cases/{id}', 'UsersController@getCollectionInfo');
        $api->post('cases/del_favorite', 'UsersController@collectionDelete');

        // 积分
        $api->get('credits', 'MeCreditController@meCreditList');
        $api->post('patch_credits', 'MeCreditController@transferAccounts');
        $api->get('meCredit', 'MeCreditController@meCreditList');

        $api->post('phone', 'UsersController@resetPhone');

        // 商城 
        $api->get('goods', 'MallController@getGoodsList');
        $api->get('goods/{id}', 'MallController@show')
            ->where('id', '[0-9]+');
        $api->post('goods/{id}/orders', 'MallController@addOrder')
            ->where('id', '[0-9]+');
        $api->get('goods/orders', 'GoodsOrderController@index');
        $api->get('goods/orders/{id}/express', 'GoodsOrderController@expressShow');
        $api->get('goods/orders/{id}', 'GoodsOrderController@show');
        $api->get('goods/orders/{id}/status/4', 'GoodsOrderController@show');
        $api->post('goods/orders/{id}/comment', 'GoodsOrderController@saveComment');
        $api->post('goods/orders/{id}/receive', 'GoodsOrderController@receive');
        $api->post('del_goods/orders/{id}', 'GoodsOrderController@destroy')->where('id', '[0-9]+');
        $api->post('orders/refund/{id}', 'GoodsOrderController@refundSave')->where('id', '[0-9]+');
        $api->post('goods/orders/{id}/repay', 'GoodsOrderController@repay')->where('id', '[0-9]+');

        // 我的订单
        $api->get('me/address/default', 'UsersController@showDefefaultAddress');

        $api->get('share', 'UsersController@share');
    });

    $api->get('cases/{id}/comments', 'CasesController@contentIndex');
    $api->post('cases/orders', 'CaseOrderController@save');

    // 商城幻灯片
    $api->get('goods/ad', 'MallController@getAd');

    $api->get('test', 'TestController@test');

    // 商品评论
    $api->get('goods/{id}/comments', 'MallController@showComments');

    // 第三方登录
    $api->post('socials/{social_type}/authorizations', 
        'AuthorizationsController@socialStore');

});

