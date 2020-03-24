<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Model\{
    ArticleCategory,
    Article,
    Designer,
    ChinaArea
};
use Illuminate\Support\Facades\Storage;
use App\Exceptions\Api\Base as BaseException;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Validator;

class DesignerController extends Controller
{
    public function show(Request $request, Designer $designer, ChinaArea $ChinaArea)
    {
        $id = $request->route()->id;
        $CheckResult = Validator::make(['id' => $id], [
            'id' =>[
                'required',
                'exists:designer'
            ]
        ], [
            'id.exists' => '没有这个设计者'
        ]);
        if ($CheckResult->fails() ) {
            throw new BaseException([
                'msg' => $CheckResult->errors()->first()
            ]);
        }

        $Designer = $designer->where('id', $id)->first();
        $Designer->makeHidden(['created_at', 'updated_at']);
        $Designer->avatar = Storage::disk('admin')->url($Designer->avatar);
        $City = $ChinaArea->where('code', $Designer->service_city_code)->first();
        $Designer->city = $City->name;
        return $this->responseSuccessData($Designer->toArray());
    }
}
