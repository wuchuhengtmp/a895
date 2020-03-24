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
        $return_data = [
            'list' => [],
            'total' => 0,
            'lastpage' => 0
        ];
        $Favorites = FavoriteCaseModel::where('user_id', $user_id)->paginate(10);

        $return_data['total'] = $Favorites->total();
        $return_data['lastpage'] = $Favorites->lastpage();
        foreach($Favorites as $Favorite) {
            $tmp               = [];
            $tmp['id']         = $Favorite->id;
            $tmp['case_id']         = $Favorite->case_id;
            $tmp['title']      = $Favorite->case->title;
            $tmp['thumb_type'] = $Favorite->case->thumb_type;
            $tmp['thumb_url']  = get_absolute_url($Favorite->case->thumb_url);
            $return_data['list'][] = $tmp;
        }
        return $return_data;
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
        $bool= FavoriteCaseModel::where(['user_id'=>$user_id,'id'=>$id])->delete();
        if(!$bool){
            throw new SystemErrorException([
                'msg' => '删除收藏失败'
            ]);
        }
    }

}
