<?php
namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class CaseComments extends Model
{
    protected $table = 'case_comments';

    protected $fillable =  [
        'content',
        'user_id',
        'case_id'
    ];

    public function getCountByCaseId(int $case_id) : int
    {
        return self::where('case_id', $case_id)->count();
    }

    /**
     *　添加评论
     *
     */
    public function addRow($content, $case_id, $user_id)
    {
        $this->content = $content;
        $this->user_id = $user_id;
        $this->case_id = $case_id;
        return $this->save() ? true : false;
    }

    public  function getPageByCaseId($case_id)
    {
        $page = self::where('case_id', $case_id)
            ->orderBy('id', 'desc')
            ->paginate(10);
        return $page;
    }

    public function user()
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }
}
