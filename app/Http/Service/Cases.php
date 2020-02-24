<?php

namespace App\Http\Service;

use App\Model\{
    Cases    as CaseModel,
    Designer as DesignerModel,
    FavoriteCase as FavoriteCaseModel
};
use Illuminate\Support\Facades\Storage;

class Cases extends Base
{
    /**
     * 案例分页列表
     *
     */
    public function getPageList(arary $params, ...$where_map): array
    {
        list($longitude, $latitude) = explode(',', $params['location']);
        $Page = CaseModel::OrderBy('id', 'DESC')
            ->select(['id', 'clickes', 'longitude', 'latitude', 'designer_id']);
        if (count($where_map) !== []) {
            foreach($where_map as $map) {
                if ($map) {
                    list($column, $value) = array_map(function($el) {
                        return trim($el);
                    }, explode('=', $map));

                    $Page = $Page->where($column, '=', $value);
                }
            }
        }
        $Page = $Page->paginate(10);

        $Page->each(function($item, $key) use ($longitude, $latitude){
            $item->designer_name = $item->designer->name;
            $item->avatar = Storage::disk('admin')->url($item->designer->avatar);
            $item->distance = get_distance(
                $longitude,
                $latitude,
                $item->longitude, 
                $item->latitude,
                2
            );
            $Favorite = FavoriteCaseModel::where('user_id', $this->user()->id)->get();
            $item->is_favorite = $Favorite->isNotEmpty() ? 1 : 0;
            unset($item->designer,
                $item->designer_id,
                $item->longitude,
                $item->latitude
            );
        });
        
        $page_data = $Page->toArray();
         
        return [
            'list' => $page_data['data'],
            'total' => $page_data['total']
        ];
    }

    /**
     * 案例查找
     *
     * @params['location']  string  经纬
     * @params['keyword']   string  搜索词
     * @params['city_code'] string  城市编码
     * 
     * @return 查找的查询结果
     */
    public function query(array $params)
    {
        list($longitude, $latitude) = explode(',', $params['location']);
        $Page = CaseModel::where('city_code', $params['city_code'])
            ->where('community', 'like', "%" . $params['keyword'] . "%")
            ->OrderBy('id', 'DESC')
            ->select(['id', 'clickes', 'longitude', 'latitude', 'designer_id'])
            ->paginate(10);
        $page_list = $this->_format($Page, $longitude, $latitude);
        return $page_list;
    }

    /**
     * 格式化分页数据
     *
     */
    protected function _format(object $Page, $longitude, $latitude): array
    {
        $Page->each(function($item, $key) use ($longitude, $latitude){
            $item->designer_name = $item->designer->name;
            $item->avatar = Storage::disk('admin')->url($item->designer->avatar);
            $item->distance = get_distance(
                $longitude,
                $latitude,
                $item->longitude, 
                $item->latitude,
                2
            );
            $Favorite = FavoriteCaseModel::where('user_id', $this->user()->id)->get();
            $item->is_favorite = $Favorite->isNotEmpty() ? 1 : 0;
            unset($item->designer,
                $item->designer_id,
                $item->longitude,
                $item->latitude
            );
        });
        
        $page_data = $Page->toArray();
         
        return [
            'list' => $page_data['data'],
            'total' => $page_data['total']
        ];
    }
 
    /**  
     * 案例详情
     *
     */
    public function getDetailById(?int $case_id): Object
    {
        $return_result = [];
        $Case = CaseModel::where('id', $case_id)
            ->select([
                'id',
                'content',
                'title',
                'tags',
                'apartment',
                'area',
                'style',
                'min_price',
                'max_price',
                'designer_id',
                'latitude',
                'longitude'
            ])
            ->first();
        if ($Case->thumb_type === 'image') {
            $Case->thumb_url = Storage::disk('admin')->url($Case->thumb_url);
        }
        $Case->tags = explode(',', $Case->tags);
        $Case->designer_name = $Case->designer->name;
        $Case->avatar = Storage::disk('admin')->url($Case->designer->avatar);
        $Case->evaluator =  $Case->min_price . '-' . $Case->max_price . 'm²';
        $Case->makeHidden(['designer', 'max_price', 'min_price']);
        return $Case;
    }
}
