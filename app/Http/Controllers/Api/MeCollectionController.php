<?php

namespace App\Http\Controllers\Api;

use function Couchbase\defaultDecoder;
use Illuminate\Http\Request;
use App\Http\Validate\{
    CheckUserExists
};
use App\Http\Service\{
    MeCollection as MeCollectionService
};

class MeCollectionController extends Controller
{
    /**
     * 获取收藏列表
     *
     */
    public function getCollectionList()
    {
        (new CheckUserExists())->gocheck();
        $collectionInfo = (new MeCollectionService())->getCollectionList($this->user()->id);
        return $collectionInfo ? $this->responseSuccessData($collectionInfo) : $this->responseFail('暂无收藏');
    }

    /**
     * 获取收藏列表详情信息
     *
     */
    public function getCollectionInfo($id)
    {
        (new CheckUserExists())->gocheck();
        $collectionInfo = (new MeCollectionService())->getCollectionInfo($id);
        return $this->responseSuccessData($collectionInfo);
    }

    /**
     * 删除收藏
     *
     */
    public function collectionDelete($id)
    {
        (new CheckUserExists())->gocheck();
        (new MeCollectionService())->collectionDelete($this->user()->id,$id);
        return $this->responseSuccess();
    }

}
