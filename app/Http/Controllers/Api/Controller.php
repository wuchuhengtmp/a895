<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller as BaseController;
use Dingo\Api\Routing\Helpers;

class Controller extends BaseController
{
    use Helpers;

    public function responseSuccessData(array $data): object
    {
        return response()->json([
            'msg' => 'success',
            'data' => $data,
            'code' => 200
        ]);
    }

    public function responseSuccess(): object
    {
        return response()->json([
            'msg' => 'success',
            'code' => 200
        ]);
    }
}
