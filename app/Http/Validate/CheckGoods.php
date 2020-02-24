<?php
namespace App\Http\Validate;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Exceptions\Api\Base as BaseException;
use App\Model\User as UserModel;

class CheckGoods extends Base
{
    public $rules = [
        'A' => 'required',
        'B' => 'required|nit'
    ];

    public $messages = [
        'A.required' => 'a 参数要有',
        'B.required' => 'a 参数要有',
        'B.int'      => 'B 参数为int类型'
    ];
    
    public $scene = [
        'A_and_B'  => [
            'A',
            'B'
        ],
        'A' => [
            'A'
        ],
        'B' => [
            'B'
        ]
    ];
    
}
