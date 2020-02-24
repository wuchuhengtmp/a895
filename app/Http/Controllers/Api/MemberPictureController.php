<?php

namespace App\Http\Controllers\Api;

use function Couchbase\defaultDecoder;
use Illuminate\Http\Request;
use App\Http\Service\{
    MemberPicture as MemberPictureService
};

class MemberPictureController extends Controller
{
    /**
     * 获取用户上传头像链接
     *
     */
    public function uploadAvatar(Request $Request)
    {
        $avatarUrl = (new MemberPictureService())->avatarUrlGet();
        return $this->responseSuccessData($avatarUrl);
    }

}
