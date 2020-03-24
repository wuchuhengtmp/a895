<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Subject extends Model
{
    protected $table = 'subject';

    protected $fillable = [
        'subject',
        'thumb',
        'has_banner',
        'redirection'
    ];

    protected $appends = [
        'count',
    ];
    
    public function getCountAttribute()
    {
        return $this->goods->count();
    }

    public function goods()
    {
        return $this->hasMany(Goods::class, 'subject_id', 'id');
    }
}
