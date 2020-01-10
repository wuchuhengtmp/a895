<?php

namespace App\Http\Logic;

use App\Model\ArticleCategory as ArticleCategoryModel;

class ArticleCategory
{
    /**
     * 获取新闻资讯分类
     *
     */
    public function getArticleCategores()
    {
        $Categres = ArticleCategoryModel::get();
        $categres = [];
        foreach($Categres as $Categry) {
            $categres[$Categry->id] = $Categry->name;
        }
        return $categres;
    }
}

