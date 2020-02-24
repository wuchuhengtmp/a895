<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Validate\CheckGoods;

class TestController extends Controller
{
    public function index()
    {
        (new CheckGoods())->scene('A_and_B')->gocheck();
        (new CheckGoods())->scene('A')->gocheck();
    }
}
