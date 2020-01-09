<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Service\User as UserService;

class SignesController extends Controller
{
    /**
     *  签到积分列表
     *
     */
    public function index()
    {
        $signes = (new UserService())->getSignListByUserId($this->user()->id);
        return $this->responseSuccessData($signes);
    }

    /**
     * 签到
     *
     */
    public function store()
    {
        $signes = (new UserService())->signByUserId($this->user()->id);
        return $this->responseSuccess();
    }
}
