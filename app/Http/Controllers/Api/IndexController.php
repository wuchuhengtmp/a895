<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Model\{
    Slide as SlideModel
};
use Illuminate\Support\Facades\Storage;

class IndexController extends Controller
{
    /**
     * 首页杂七杂八的配置
     *
     */
    public function index()
    {
        //  公告
        $notice = get_config('notice');
        // 轮播图
        $Slides = SlideModel::select(['path', 'url'])
            ->orderBy('order_num', 'ASC')
            ->get();
        $Slides->each(function($item, $key){
            $item->path = Storage::disk('admin')->url($item->path);
        });
        $slides = [
            'list'  => $Slides->toArray(),
            'total' => $Slides->count()
        ];
        return $this->responseSuccessData([
            'notice'  => $notice,
            'slides' => $slides
        ]);
    }
}
