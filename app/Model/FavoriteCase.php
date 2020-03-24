<?php
namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class FavoriteCase extends Model
{
    protected $table = 'favorite_case';

    protected $fillable = [
        'case_id',
        'user_id'
    ];
     
    public function getCountByCaseId(int $case_id)
    {
        return self::where('case_id', $case_id)->count();
    }

    /**
     * 是否收藏
     *
     * @user_id 用户id
     */
    public function isFavorite($case_id, $user_id)
    {
        $Favorites = self::where('case_id',  $case_id) 
            ->where('user_id', $user_id)
            ->limit(1)
            ->get();
        return $Favorites->isNotEmpty();
    }

    /**
     * 收藏
     *
     */
    public function favorite($case_id, $user_id)
    {
        $this->case_id = $case_id;
        $this->user_id = $user_id;
        return $this->save() ? true : false;
    }

    /**
     * 取消收藏
     *
     */
    public function destroyFavorite($case_id, $user_id)
    {
        $is_delete = self::where('user_id', $user_id)
            ->where('case_id', $case_id)
            ->delete();
        return $is_delete ? true: false;
    }

    public function case()
    {
        return $this->hasOne(Cases::class, 'id', 'case_id');
    }

}
