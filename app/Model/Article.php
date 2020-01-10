<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Article extends Model
{
    protected $table = 'articles';

    protected $fillable = [
        'title',
        'category_id',
        'content',
        'thumb_type',
        'thumb_video_url',
        'thumb_url',
        'clickes'
    ];

    /**
     *  关联文章分类 
     */
    public function category()
    {
        return $this->hasOne(ArticleCategory::class, 'id', 'category_id');
    }
}
