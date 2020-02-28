<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

use App\Exceptions\Api\{
    Base as BaseException,
    SystemErrorException
};
use Illuminate\Support\Facades\Log;
use App\Http\Service\{
    CaseOrder  as CaseOrderService,
    PayTimes   as PayTimesService,
    Mall       as MallService,
    user       as UserService,
    PayService as PayService
};

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        \API::error(function (\Exception $E){
                // 内部异常
            if ($E instanceof SystemErrorException) {
                $Request = request();
                $error_info = [
                    'line'      => $E->getLine(),
                    'file_name' => $E->getFile(),
                    'error_msg' => $E->msg,
                    'url'       => $Request->url(),
                    'method'    => $Request->method(),
                    'params'    => $Request->input()
                ];
                Log::channel('systemlog')->info($error_info);
                if (env('APP_DEBUG')) {
                    return response()->json([
                        'msg'      => $E->msg,
                        'code' => $E->code
                    ], 200);
                } else {
                    return response()->json([
                        'msg' => '系统内部错误',
                        'code' => $E->code
                    ], 200);
                }

            } else if ($E instanceof BaseException) {
                // 常规异常 
                return response()->json([
                    'msg'      => $E->msg,
                    'code' => $E->code
                ], 200);
            }
            // 限流异常
            if ($E instanceof \Dingo\Api\Exception\RateLimitExceededException) {
                return response()->json([
                    'msg'      => '请不要频繁访问',
                    'code'     => 404
                ], 200);
            }
            // token 异常
            if ($E instanceof \Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException) {
                return response()->json([
                    'msg'      => '无效请求头部或者无效token,请检查请求的token',
                    'code'     => 401
                ], 200);
            }
        });
        // 契约注册
        $this->app->bind(CaseOrderService::class, CaseOrderService::class);
        $this->app->bind(CaseOrderService::class, CaseOrderService::class);
        $this->app->bind(MallService::class, MallService::class);
        $this->app->bind(UserService::class, UserService::class);
        $this->app->bind(PayService::class, PayService::class);
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
