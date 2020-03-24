<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Model\{
    ArticleCategory,
    Article
};
use Illuminate\Support\Facades\Storage;
use App\Exceptions\Api\Base as BaseException;
use Illuminate\Support\Facades\View;

class ArticleController extends Controller
{
    /**
     * 分类
     *
     */
    public function categoresIndex()
    {
        $Categores = ArticleCategory::select(['id', 'name'])->get();
        $categores_list = $Categores->toArray();
        $a = asort($categores_list);
        $categores_list = array_merge($categores_list, [['id' => 0, 'name' => '全部']]);
        asort($categores_list);
        $categores_list = array_values($categores_list);
        return $this->responseSuccessData(
            $categores_list
        );
    }

    /**
     * 资讯列表
     *
     */
    public function index(Request $Request)
    {
        if ($Request->has('category_id') && $Request->category_id != 0) {
            $Articles = Article::orderBy('id', 'DESC')
                ->select(['id', 'title', 'thumb_type', 'thumb_url', 'clickes'])
                ->where('category_id', $Request->category_id)
                ->paginate(10);
        } else {
            $Articles = Article::orderBy('id', 'DESC')
                ->select(['id', 'title', 'thumb_type', 'thumb_url', 'clickes'])
                ->paginate(10);
        }
        $Articles->each(function($item, $key){
            if ($item->thumb_type === 'image') {
                $item->thumb_url  = Storage::disk('admin')->url($item->thumb_url);
            }
        });
        foreach($Articles as $Article) {
            $Article->url = env("APP_URL")  . '/api/articles/'  . $Article->id;
        }
        $articles = $Articles->toArray();
        return $this->responseSuccessData([
            'list'  => $articles['data'],
            'total' => $Articles->lastPage()
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
        $Article->clickes += 1;
        $Article->save();
        return view('article', ['content' => $Article->content]);
    }
}
