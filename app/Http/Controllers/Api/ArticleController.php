<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Model\{
    ArticleCategory,
    Article
};
use Illuminate\Support\Facades\Storage;
use App\Exceptions\Api\Base as BaseException;

class ArticleController extends Controller
{
    /**
     * 分类
     *
     */
    public function categoresIndex()
    {
        $Categores = ArticleCategory::select(['id', 'name'])->get();
        return $this->responseSuccessData(
            $Categores->toArray()
        );
    }

    /**
     * 资讯列表
     *
     */
    public function index()
    {
        $Articles = Article::orderBy('id', 'DESC')
            ->select(['id', 'title', 'thumb_type', 'thumb_url', 'clickes'])
            ->paginate(10);
        $Articles->each(function($item, $key){
            if ($item->thumb_type === 'image') {
                $item->thumb_url  = Storage::disk('admin')->url($item->thumb_url);
            }
        });
        $articles = $Articles->toArray();
        return $this->responseSuccessData([
            'list' => $articles['data'],
            'total' => $articles['total']
        ]);
    }

    /**
     * 分类资讯列表
     *
     */
    public function categoryIndex($category)
    {
        $Categores = ArticleCategory::where('id', $category)
            ->get();
        if ($Categores->isEmpty()) {
            throw new BaseException([
                'msg' => '没有这个分类'
            ]);
        }

        $Articles = Article::orderBy('id', 'DESC')
            ->where('category_id', $category)
            ->select(['id', 'title', 'thumb_type', 'thumb_url', 'clickes'])
            ->paginate(10);
        $Articles->each(function($item, $key){
            if ($item->thumb_type === 'image') {
                $item->thumb_url  = Storage::disk('admin')->url($item->thumb_url);
            }
        });
        $articles = $Articles->toArray();
        return $this->responseSuccessData([
            'list' => $articles['data'],
            'total' => $articles['total']
        ]);
    }

    /**
     * 文章详情
     *
     */
    public function show($article_id)
    {
        $Articles = Article::where('id', $article_id)
            ->limit(1)
            ->get();
        if ($Articles->isEmpty()) {
            throw new BaseException([
                'msg' => '没有这个文章'
            ]);
        }
        $Article = $Articles->first();
        if ($Article->thumb_type === 'image') {
            $Article->thumb_url = Storage::disk('admin')->url($Article->thumb_url);
        } else if ($Article->thumb_type === 'video')  {
            $Article->thumb_video_url = Storage::disk('admin')->url($Article->thumb_video_url);
        }
        return $this->responseSuccessData([
            'title'           => $Article->title,
            'content'         => $Article->content,
            'thumb_url'       => $Article->thumb_url,
            'thumb_type'      => $Article->thumb_type,
            'thumb_video_url' => $Article->thumb_video_url
        ]); 
    }
}
