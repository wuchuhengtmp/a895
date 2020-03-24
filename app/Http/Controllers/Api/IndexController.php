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
            ->where('type', 'index')
            ->get();
        $Slides->each(function($item, $key){
            $item->path = Storage::disk('admin')->url($item->path);
        });
        $slides = [
            'list'  => $Slides->toArray(),
            'total' => $Slides->count()
        ];
        // 分享
        $share_url = get_config('share_url');
        $user_credit = get_config('user_credit');
        $get_credit = get_config('get_credit');
        $share = [
            'share_url' => $share_url,
            'user_credit' => $user_credit,
            'get_credit' => $get_credit,
        ];
        return $this->responseSuccessData([
            'notice'  => $notice,
            'slides' => $slides,
            'service' => get_config('CUSTOMER_URL')
        ]);
    }
}
