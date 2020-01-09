<?php

use Illuminate\Routing\Router;

Admin::routes();

Route::group([
    'prefix'        => config('admin.route.prefix'),
    'namespace'     => config('admin.route.namespace'),
    'middleware'    => config('admin.route.middleware'),
], function (Router $router) {

    $router->get('/', 'HomeController@index')->name('admin.home');

    $router->resource('users', UsersController::class);
    $router->resource('setting/slides', SlideController::class);
    $router->resource('setting/configs', ConfigController::class);

    $router->resource('goods', GoodsController::class);
    $router->resource('configes/user', UserConfigController::class);
    $router->resource('configes/sign', SignConfigController::class);
});
