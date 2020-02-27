<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class CaseOrderComment extends Model
{
    protected $table = 'case_order_comments';

    protected $fillable = [
        'order_id',
        'business_stars',
        'service_stars',
        'design_stars',
        'material_stars',
        'content',
        'img',
    ];
}
