<?php

namespace App\Http\Service;

use App\Model\{
    User as UserModel,
    Task as TaskModel,
    CreditLog  as CreditLogModel
};
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Exceptions\Api\{
    Base as BaseException,
    SystemErrorException
};
use App\Http\Logic\{
    CreaditRecord as CreaditRecordLogic
};

class MeCredit extends Base
{
    //  我的->积分

    /*
     *  获取任务列表
     *
     */
    public function getTaskList()
    {
        $List= TaskModel::select('title','credit','url')->get();
        if(!$List){
            throw new SystemErrorException([
                'msg' => '未设置任务'
            ]);
        }
        return $List->toArray();
    }

    /*
     *  积分转账
     *
     */
    public function transferAccounts(int $user_id)
    {
        $Request = request();
        $user_info= UserModel::select('credit','transfer_pwd')->where('id',$user_id)->first();
        $get_user_info= UserModel::select('id','credit')->where('id',$Request->to_id)->first();
        if(!$get_user_info){
            throw new SystemErrorException([
                'msg' => '该用户不存在'
            ]);
        }elseif(!Hash::check($Request->transfer_pwd, $user_info['transfer_pwd'])){
            throw new SystemErrorException([
                'msg' => '转账密码错误'
            ]);
        }elseif($user_info['credit'] < $Request->credit){
            throw new SystemErrorException([
                'msg' => '积分不足，转账失败'
            ]);
        }

        DB::beginTransaction();
        try{
            UserModel::where('id',$user_id)->update(['credit'=>$user_info['credit']-$Request->credit]);
            UserModel::where('phone',$Request->phone)->update(['credit'=>$get_user_info['credit']+$Request->credit]);
            (new CreaditRecordLogic())->creaditRecordAdd($user_id,'积分转账',$Request->credit,0);
            (new CreaditRecordLogic())->creaditRecordAdd($get_user_info['id'],'积分转账',$Request->credit,1);
            DB::commit();
        } catch(\Exception $E) {
            DB::rollBack();
            throw new SystemErrorException([
                'msg' => '转账失败'
            ]);
        }
    }

    /*
     *  获取积分明细记录
     *
     */
    public function meCreditList(int $user_id)
    {
        $List= CreditLogModel::select('title','total','status', 'created_at')->where('user_id',$user_id)->orderBy('id','desc')
            ->paginate(10);
        return [
            'list'     => $List->items(),
            'total'    => $List->total(),
            'lastpage' => $List->lastpage() 
        ];
    }

}
