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

       // 需要 token 验证的接口
    $api->group(['middleware' => 'api.auth'], function($api) {
        $api->get('signes', 'SignesController@index');
        $api->post('signes', 'SignesController@store');
    });

});

