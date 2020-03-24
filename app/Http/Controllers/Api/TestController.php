<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Model\Designer;
use App\Model\Goods;
use Illuminate\Support\Facades\Storage;

class TestController extends Controller
{
    public function test()
    {
        $Avatars = Goods::select('thumb')->get();
        foreach($Avatars as $Avatar) {
            $Avatar->avatar = $Avatar->thumb;
            if ($Avatar) {
                $avatar_info = parse_url($Avatar->avatar);
                if (array_key_exists('host', $avatar_info) && $avatar_info['host']) {
                    
                } else {
                    if ($Avatar->avatar) {
                        $path = "/www/wwwroot/a895.mxnt.net/public/uploads/" . $Avatar->avatar;
                        $is_upload = Storage::disk('qiniu')->put($Avatar->avatar, file_get_contents($path));
                        dump($is_upload);
                    }
                }
            }
        }
    }
    
}
