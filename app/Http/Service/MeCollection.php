<?php

namespace App\Http\Service;

use App\Model\{
    User as UserModel,
    Cases as CasesModel,
    FavoriteCase as FavoriteCaseModel
};
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use App\Exceptions\Api\{
    Base as BaseException,
    SystemErrorException
};

class MeCollection extends Base
{
    //  我的->我的收藏
    /*
     *  获取收藏列表
     *
     */
    public function getCollectionList(int $user_id)
    {
        $ids = FavoriteCaseModel::select('case_id')->where('user_id', $user_id)->get()->toArray();
        if(!$ids){
            return false;
        }
        $user_ids = [];
        foreach ($ids as $v){
            $user_ids[] = $v['case_id'];
        }
        $list = CasesModel::select('id','title','thumb_type','thumb_url','thumb_video_url')->whereIn('id',$user_ids)->orderBy('id','desc')->get()->toArray();
        for($i=0;$i<count($list);$i++){
            if($list[$i]['thumb_type'] == 'image'){
                unset($list[$i]['thumb_video_url']);
                $list[$i]['thumb_url'] = env('APP_URL').'/uploads/'.$list[$i]['thumb_url'];
            }else{
                unset($list[$i]['thumb_url']);
                $list[$i]['thumb_video_url'] = env('APP_URL').'/uploads/'.$list[$i]['thumb_video_url'];
            }
        }

        return $list;
    }

    /*
     *  获取收藏列表详情信息
     *
     */
    public function getCollectionInfo(int $id)
    {
        $Info= CasesModel::select('clickes','title','content','thumb_type','thumb_video_url','thumb_url','clickes')
            ->where('id', $id)->first();
        if(!$Info){
            throw new SystemErrorException([
                'msg' => '该案例不存在'
            ]);
        }
        if($Info['thumb_type'] == 'image'){
            unset($Info['thumb_video_url']);
            $Info['thumb_url'] = env('APP_URL').'/uploads/'.$Info['thumb_url'];
        }else{
            unset($Info['thumb_url']);
            $Info['thumb_video_url'] = env('APP_URL').'/uploads/'.$Info['thumb_video_url'];
        }

        return $Info->toArray();
    }

    /*
     *  删除收藏
     *
     */
    public function collectionDelete(int $user_id,int $id)
    {
        $bool= FavoriteCaseModel::where(['user_id'=>$user_id,'case_id'=>$id])->delete();
        if(!$bool){
            throw new SystemErrorException([
                'msg' => '删除收藏失败'
            ]);
        }
    }

}
