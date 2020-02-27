<?php

use Illuminate\Routing\Router;

Admin::routes();

Route::group([
    'prefix'        => config('admin.route.prefix'),
    'namespace'     => config('admin.route.namespace'),
    'middleware'    => config('admin.route.middleware'),
], function (Router $router) {

    $router->get('/', 'HomeController@index')->name('admin.home');

    $router->resource('users/order', UsersOrderController::class);
    $router->resource('users', UsersController::class);
    $router->resource('setting/slides', SlideController::class);
    $router->resource('setting/configs', ConfigController::class);

    $router->resource('goods', GoodsController::class);
    $router->resource('configes/user', UserConfigController::class);
    $router->resource('configes/sign', SignConfigController::class);
    $router->resource('configes/abouts', MeConfigController::class);
    $router->resource('configes/task', TaskController::class);
    $router->resource('configes/share', ShareController::class);
    $router->resource('articles', ArticlesController::class);

    $router->resource('cases/designer', DesignerController::class);
    $router->resource('cases/all', CasesController::class);
    $router->resource('cases/orders', CaseOrderController::class);

    $router->resource('calculates', CalculateController::class);
    $router->resource('configes/pay', PayConfigController::class);

    $router->resource('cases/pay-times', PayTimesController::class);
});
