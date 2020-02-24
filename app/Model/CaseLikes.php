<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class CaseLikes extends Model
{
    protected $table = 'case_likes';

    protected $fillable = [
        'case_id',
        'user_id'
    ];

    /**
     * 获取案例点赞量
     *
     */
    public function getCountByCaseId(int $case_id): int
    {
        $count = self::where('case_id', $case_id)->count();
        return $count;
    }

    /**
     * 是否点赞过
     */
    public function isLikeCase($case_id, $user_id)
    {
        $Cases = self::where('user_id', $user_id)
            ->where('case_id', $case_id)
            ->limit(1)->get();
        return $Cases->isEmpty() ? false : true;
    }

    /**
     * 点赞
     *
     */
    public function like($case_id, $user_id)
    {
        $this->user_id = $user_id;
        $this->case_id  = $case_id;
        return $this->save() ? true : false;
    }

    /**
     * 取消点赞
     *
     */
    public function destroyLike($case_id, $user_id)
    {
        $is_delete = self::where('case_id', $case_id)
            ->where('user_id', $user_id)
            ->delete();
        return $is_delete ? true : false;
    }
    
}
