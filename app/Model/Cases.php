<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Cases extends Model
{
    protected $table = 'cases';

    protected $fillable = [
        'designer_id',
        'clickes',
        'title',
        'content',
        'apartment',
        'style',
        'area',
        'prepay',
        'is_ecdemic_errand',
        'service_city',
        'min_price',
        'max_price',
        'is_to_build',
        'tags',
        'thumb_url',
        'thumb_type',
        'city_code',
        'community',
        'longitude',
        'latitude',
        'introduction',
        'period'
    ];

    /**
     * 关联设计者
     */
    public function designer()
    {
        return $this->hasOne(Designer::class, 'id', 'designer_id');
    }

    /**
     * 关联城市
     *
     */
    public function city()
    {
        return $this->hasOne(ChinaArea::class, 'code', 'city_code');
    }

    /**
     * 关联分类
     *
     */
    public function category()
    {
        return $this->hasOne(CaseCategory::class, 'id', 'case_category_id');
    }

    public function favorites()
    {
        return $this->hasMany(FavoriteCase::class, 'case_id', 'id');
    }

    public function likes()
    {
        return $this->hasMany(CaseLikes::class, 'case_id', 'id');
    }

    public function getPeriodAttribute($value)
    {
        return json_decode($value, true);
    }

    public function setPeriodAttribute($value)
    {
        $value = json_encode($value);
        $this->attributes['period'] = $value;
    }

    public function getThumbUrlAttribute($value)
    {
        return get_absolute_url($value);
    }
}

