<?php
namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Model\{
    CaseCategory,
    CaseLikes,
    CaseComments,
    FavoriteCase,
    Cases,
    Cases as CaseModel,
    FavoriteCase as FavoriteCaseModel
};
use App\Http\Validate\{
    CheckLocation,
    CheckCityCode,
    CheckKeyWordsMustBeExists,
    CheckCaseMustBeExists
};
use App\Http\Service\{
    Cases as CasesService
};
use Illuminate\Support\Facades\Validator;
use App\Exceptions\Api\Base as BaseException;
use Illuminate\Support\Facades\Storage;

class CasesController extends Controller
{
    /**
     *  分类列表
     *
     */
    public function categoryIndex()
    {
        $categores = (new CaseCategory())->getListForApi();
        return $this->responseSuccessData($categores);
    }

    /**
     * 列表
     *
     */
    public function index(Request $Request)
    {
        (new CheckLocation())->gocheck();

        list($longitude, $latitude) = explode(',', $Request->location);
        $Page = CaseModel::OrderBy('id', 'DESC')
            ->select(['id', 'clickes', 'title', 'longitude', 'latitude', 'designer_id', 'thumb_url']);
        if ($Request->case_category_id) {
            $Page = $Page->where('case_category_id', '=', $Request->case_category_id);
        }
        if ($Request->city_code){
            $Page = $Page->where('city_code', '=', $Request->city_code);
        }
        if ($Request->keyword){
            $Page = $Page->where('title', 'like', "%".$Request->keyword."%");
        }
        $Page = $Page->paginate(10);

        $Page->each(function($item, $key) use ($longitude, $latitude){
            $item->designer_name = $item->title;
            $item->avatar = Storage::disk('admin')->url($item->designer->avatar);
            $item->distance = get_distance(
                $longitude,
                $latitude,
                $item->longitude, 
                $item->latitude,
                2
            ) . 'Km';
            $Favorite = FavoriteCaseModel::where('user_id', $this->user()->id)
                ->where('case_id', $item->id)
                ->get();
            $item->is_favorite = $Favorite->isNotEmpty() ? 1 : 0;
            unset($item->designer,
                $item->designer_id,
                $item->longitude,
                $item->latitude
            );
        });
        
        $page_data = $Page->toArray();
        return $this->responseSuccessData(
            [
                'list'     => $page_data['data'],
                'total'    => $page_data['total'],
                'lastpage' => $Page->lastPage()
            ]
        );
    }

    /**
     *  城市案例查询
     *
     */
    public function searchByCityCode($city_code, Request $Request)
    {
        (new CheckCityCode())->goCheck();
        (new CheckKeyWordsMustBeExists())->gocheck();
        $page_list = (new CasesService())->query([
            'location'  => $Request->location,
            'keyword'   => $Request->keyword,
            'city_code' => $Request->city_code
        ]);
        return $this->responseSuccessData($page_list);
    }
    
    /**
     * 案例详情
     *  
     */
    public function show(
        $case_id,
        Request $Request,
        CaseLikes $CaseLikes,
        CaseComments $CaseComments,
        FavoriteCase $FavoriteCase
    )
    {
        (new CheckCaseMustBeExists())->goCheck();
        (new CheckLocation())->goCheck();
        $Case = (new CasesService())->getDetailById($Request->id);
        list($longitude, $latitude) = explode(',', $Request->location);
        $Case->distance = get_distance(
                $longitude,
                $latitude,
                $Case->longitude, 
                $Case->latitude,
                2
            );
        $Case->makeHidden(['longitude', 'latitude']);
        // 点赞量
        $Case->total_likes = $CaseLikes->getCountByCaseId($Case->id);
        // 是否点赞
        $Case->is_like = $CaseLikes->isLikeCase($Case->id, $this->user()->id);
        // 评论量 
        $Case->total_comments = $CaseComments->getCountByCaseId($Case->id);
        // 收藏量
        $Case->total_favorites = $FavoriteCase->getCountByCaseId($Case->id);
        // 是否收藏
        $Case->is_favorite = $FavoriteCase->isFavorite($case_id, $this->user()->id);
        $Case->distance .= 'Km';
        return $this->responseSuccessData($Case->toArray());
    }

    /**
     * 点赞
     * 
     * @http put
     *
     */
    public function like(Request $Request, CaseLikes $CaseLikes)
    {
        if ($Request->is_check == 1) {
            $case_id= $Request->route('id');
            $that  = $this;
            $CheckResult = Validator::make(['case_id' => $case_id], [
                'case_id' => [
                    'required',
                    'exists:cases,id',
                    function ($attribute, $value, $fail) use ($CaseLikes, $that) {
                        if ($CaseLikes->where('case_id', $value)
                            ->where('user_id', $that->user()->id)
                            ->get()->isNotEmpty()) {
                              return $fail('您已经点赞过了');
                        }
                    }
                ]
            ], [
                'case_id.required' => '项目不能为空',
                'case_id.exists' => '没有这个项目'
            ]);
            if ($CheckResult->fails()) {
                throw new BaseException([
                    'msg' => $CheckResult->errors()->first()
                ]);
            }
            // 点赞
            $is_like_ok = $CaseLikes->like($case_id, $this->user()->id);
            return $is_like_ok ? $this->responseSuccess() : $this->responseFail();
        } else if($Request->is_check == 0) {
            $case_id= $Request->route('id');
            $that  = $this;
            $CheckResult = Validator::make(['case_id' => $case_id], [
                'case_id' => [
                    'required',
                    'exists:cases,id',
                    function ($attribute, $value, $fail) use ($CaseLikes, $that) {
                        if ($CaseLikes->where('case_id', $value)
                            ->where('user_id', $that->user()->id)
                            ->get()->isEmpty()) {
                              return $fail('您并没有点赞该项目');
                        }
                    }
                ]
            ], [
                'case_id.required' => '项目不能为空',
                'case_id.exists' => '没有这个项目'
            ]);
            if ($CheckResult->fails()) {
                throw new BaseException([
                    'msg' => $CheckResult->errors()->first()
                ]);
            }
            // 取消点赞
            $is_delete = $CaseLikes->destroyLike($case_id, $this->user()->id);
            return $is_delete ? $this->responseSuccess() : $this->responseFail();
        }
    } 

    public function destroyLike(Request $Request, CaseLikes $CaseLikes)
    {
        $case_id= $Request->route('id');
        $that  = $this;
        $CheckResult = Validator::make(['case_id' => $case_id], [
            'case_id' => [
                'required',
                'exists:cases,id',
                function ($attribute, $value, $fail) use ($CaseLikes, $that) {
                    if ($CaseLikes->where('case_id', $value)
                        ->where('user_id', $that->user()->id)
                        ->get()->isEmpty()) {
                          return $fail('您并没有点赞该项目');
                    }
                }
            ]
        ], [
            'case_id.required' => '项目不能为空',
            'case_id.exists' => '没有这个项目'
        ]);
        if ($CheckResult->fails()) {
            throw new BaseException([
                'msg' => $CheckResult->errors()->first()
            ]);
        }
        // 取消点赞
        $is_delete = $CaseLikes->destroyLike($case_id, $this->user()->id);
        return $is_delete ? $this->responseSuccess() : $this->responseFail();
    }
    
    /**
     *  收藏
     *
     * @http put
     */
    public function favorite(Request $Request, FavoriteCase $FavoriteCase)
    {
        if ($Request->is_check == 1) {
            $case_id= $Request->route('id');
            $that  = $this;
            $CheckResult = Validator::make(['case_id' => $case_id], [
                'case_id' => [
                    'required',
                    'exists:cases,id',
                    function ($attribute, $value, $fail) use ($FavoriteCase, $that) {
                        $is_favorite = $FavoriteCase->where('case_id', $value)
                            ->where('user_id', $that->user()->id)
                            ->get()
                            ->isNotEmpty();
                        if ( $is_favorite) {
                            return $fail('您已经收藏过了');
                        }
                    }
            ]
            ], [
                'case_id.required' => '项目不能为空',
                'case_id.exists' => '没有这个项目'
            ]);
            if ($CheckResult->fails()) {
                throw new BaseException([
                    'msg' => $CheckResult->errors()->first()
                ]);
            }
            // 收藏
            $is_favorite = $FavoriteCase->favorite($case_id, $this->user()->id);
            return $is_favorite ? $this->responseSuccess() : $this->responseFail();
        } else {
            $case_id= $Request->route('id');
            $that  = $this;
            $CheckResult = Validator::make(['case_id' => $case_id], [
                'case_id' => [
                    'required',
                    'exists:cases,id',
                    function ($attribute, $value, $fail) use ($FavoriteCase, $that) {
                        if ($FavoriteCase->where('case_id', $value)
                            ->where('user_id', $that->user()->id)
                            ->get()->isEmpty()) {
                                return $fail('您并没有收藏该项目');
                            }
                    }
            ]
            ], [
                'case_id.required' => '项目不能为空',
                'case_id.exists' => '没有这个项目'
            ]);
            if ($CheckResult->fails()) {
                throw new BaseException([
                    'msg' => $CheckResult->errors()->first()
                ]);
            }
            // 取消收藏
            $is_delete = $FavoriteCase->destroyFavorite($case_id, $this->user()->id);
            return $is_delete ? $this->responseSuccess() : $this->responseFail();
        }
    }

    /**
     * 删除收藏
     *
     * @http delete
     * 
     */
    public function destroyFavorite(Request $Request, FavoriteCase $FavoriteCase)
    {
    }

    /**
     *  增加评论 
     *
     */
    public function saveComment(Request $Request, CaseComments $CaseComments)
    {
        $case_id= $Request->route('id');
        $that  = $this;
        $request_list = array_merge($Request->input(), ['case_id' => $case_id]);
            $CheckResult = Validator::make($request_list, [
            'case_id' => [
                'required',
                'exists:cases,id'
            ],
            'content' =>[
                'required'
            ]
        ], [
            'case_id.required' => '项目不能为空',
            'case_id.exists' => '没有这个项目',
            'content.required' => '评论内容不能为空'
        ]);
        if ($CheckResult->fails()) {
            throw new BaseException([
                'msg' => $CheckResult->errors()->first()
            ]);
        }
        // 添加评论 
        $is_add = $CaseComments->addRow($Request->input('content'), $case_id, $this->user()->id);
        return $is_add ? $this->responseSuccess() : $this->responseSuccessData();
    }

    /**
     *  评论列表
     *
     */
    public function contentIndex(
        Request $Request,
        CaseComments $CaseComments,
        Storage $Storage
    ) 
    {
        $return_result   = [
            'list'      => array(),
                'total' => 0
            ];
        $case_id= $Request->route('id');
        $that  = $this;
        $CheckResult = Validator::make(['case_id' => $case_id], [
            'case_id' => [
                'required',
                'exists:cases,id'
            ]
        ], [
            'case_id.required' => '项目不能为空',
            'case_id.exists' => '没有这个项目'
        ]);
        $Page_list = $CaseComments->getPageByCaseId($case_id);
        foreach($Page_list as $Comment) { 
            $tm = [];
            $timestamp = $Comment->created_at->timestamp;
            $result = '';
            if (($timestamp + 60 ) > time()) {
                $result = time() - $timestamp  . ' second';
            } else if(($timestamp + 60 * 60) > time()){
                $time_len = intval((time() - $timestamp) /60);
                $result = $time_len . ' minute';
            } else if (($timestamp + 60 * 60 * 24 ) > time()) {
                $time_len = intval((time() - $timestamp) / (60 * 60));
                $result = $time_len . ' hour';
            } else if(($timestamp + 60 * 60 * 24 * 31) > time()) {
                $time_len = intval((time() - $timestamp) / (60 * 60 * 24));
                $result = $time_len . ' day';
            } else if (($timestamp + 60 * 60 * 24 * 365) > time()) {
                $time_len = (time() - $timestamp ) / (60 * 60 * 24 * 31);
                $result = $time_len . ' month';
            }
            $result .= ' ago';
            $tmp['created_at'] = $result;
            $tmp['content'] = $Comment->content;
            $tmp['nickname'] = $Comment->user->nickname;
            $tmp['avatar'] = Storage::disk('img')->url($Comment->user->avatar);
            $tmp['user_id'] = $Comment->user->id;
            $return_result['list'][] = $tmp;
        }
        $return_result['total'] = $Page_list->total();
        $return_result['lastpage'] = $Page_list->lastPage();
        return $this->responseSuccessData($return_result);
    }

    /**
     * 推荐
     */
    public function recommentShow(Request $Request, Cases $CaseModel, FavoriteCase $FavoriteCase)
    {
        $return_data = [
            'list' => [],
            'total' =>0
        ];
        $Cases = $CaseModel->where('is_commend', 1)
            ->with(['designer', 'likes'])
            ->select(['id', 'designer_id', 'thumb_url', 'title', 'clickes', 'thumb_type'])
            ->paginate(10);
        if (!$Cases->isEmpty()) {
            $Cases->each(function(&$el) use(&$return_data){
                if ($el->thumb_type === 'image') {
                    $el->thumb_url = get_absolute_url($el->thumb_url);
                }
                $el->avatar = Storage::disk('img')->url($el->designer->avatar);
                $el->name = $el->designer->name;
                $el->likes_count = $el->likes->count();
                $has_favorite = FavoriteCase::where('user_id', $this->user()->id)
                    ->where('case_id', $el->id)->first();
                $has_likes = CaseLikes::where('user_id', $this->user()->id)
                    ->where('case_id', $el->id)->first();
                $el->is_favorite = $has_favorite ? true : false;
                $el->is_like = $has_likes ? true : false;
                unset($el->designer, $el->likes);
                $return_data['list'][] = $el->toArray();
            });
        }
        $return_data['total']  = $Cases->total();
        $return_data['lastpage']  = $Cases->lastPage();
        return $this->responseSuccessData( $return_data);
    }
}

