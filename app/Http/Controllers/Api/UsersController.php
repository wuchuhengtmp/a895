<?php

namespace App\Http\Controllers\Api;

use function Couchbase\defaultDecoder;
use Illuminate\Http\Request;
use App\Http\Validate\{
    CheckPhoneRegister,
    CheckResetPassword 
};
use App\Model\{
    Address as AddressModel,
    User as UserModel,
    Share,
    CaseComments,
    CaseLikes,
    CaseOrder,
    CreditLog,
    SignLog,
    FavoriteCase,
    GoodsComment,
    Order,
    UserEvaluate
};
use App\Http\Validate\{
    CheckUserExists,
    CheckLocationParams,
    CheckUser
};
use App\Http\Service\{
    MeCollection as MeCollectionService,
    User         as UserService
};
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use App\Exceptions\Api\Base as BaseException;
use Endroid\QrCode\QrCode;
use App\Model\User;
use Illuminate\Support\Facades\Storage;

class UsersController extends Controller
{
    /**
     * 用户注册
     */
    public function store(Request $Request)
    {
        (new CheckPhoneRegister())->gocheck();
       ( new UserService())->registerStore();
        \Cache::forget(request()->validate_key); 
        return $this->responseSuccess();
    }

    /**
     * 重置密码 
     *
     */
    public function updatePassword(Request $Request)
    {
        (new CheckResetPassword())->gocheck();
        (new UserService())->resetPassword();
        \Cache::forget(request()->validate_key); 
        return $this->responseSuccess();
    }

    /**
     * 座标信息
     *
     */
    public function location(Request $Request)
    {
        (new CheckLocationParams())->gocheck();
	$location_info = get_location($Request->input('longitude'), $Request->input('latitude'));
	return $this->responseSuccessData([
            'city'              => $location_info['regeocode']['addressComponent']['city'],
            'province'          => $location_info['regeocode']['addressComponent']['province'],
            'code'              => $location_info['regeocode']['addressComponent']['adcode'],
            'formatted_address' => $location_info['regeocode']['formatted_address']
	]);
    }

    /**
     * 获取收藏列表
     *
     */
    public function getCollectionList()
    {
        (new CheckUserExists())->gocheck();
        $collectionInfo = (new MeCollectionService())->getCollectionList($this->user()->id);
        return $collectionInfo ? $this->responseSuccessData($collectionInfo) : $this->responseFail('暂无收藏');
    }

    /**
     
     *
     */
    public function getCollectionInfo($id)
    {
        (new CheckUserExists())->gocheck();
        $collectionInfo = (new MeCollectionService())->getCollectionInfo($id);
        return $this->responseSuccessData($collectionInfo);
    }

    /**
     * 删除收藏
     *
     */
    public function collectionDelete(Request $Request)
    {
        (new CheckUserExists())->gocheck();
        $ids = array_filter(explode(',', $Request->ids));
        $ids = array_unique($ids);

        foreach($ids as $id) {
            (new MeCollectionService())->collectionDelete($this->user()->id,$id);
        }
        
        return $this->responseSuccess();
    }

    /**
     * 用户默认地址
     *
     */
    public function showDefefaultAddress(UserService $UserService, AddressModel $AddressModel)
    {
        $Address = $AddressModel->where('user_id', $this->user()->id)
            ->where('is_default', 1)
            ->get();
        if ($Address->isEmpty()) {
            /* return $this->responseFail('没有收货地址，请添加'); */
            throw new BaseException([
                'code' =>  402,
                'msg' => '没有收货地址，请添加'
            ]);
        } else {
            $Address = $Address->first();
            return $this->responseSuccessData([
                'id' => $Address->id,
                'phone' => $Address->phone,
                'name' => $Address->name,
                'city_code' => $Address->city_code,
                'city_name' => $Address->city->name,
                'address' => $Address->address
            ]);
            
        }
    }

    /**
     * 添加地址
     *
     */
    public function addressSave(Request $Request, AddressModel $AddressModel)
    {
        (new CheckUser())->scene('add_address')->gocheck();
        $AddressModel->name      = $Request->name;
        $AddressModel->address   = $Request->address;
        $AddressModel->city_code = $Request->city_code;
        $AddressModel->phone     = $Request->phone;
        $AddressModel->user_id   = $this->user()->id;
        $count_addr = $AddressModel->where('user_id', $this->user()->id)->count();
        if ($count_addr === 0 ) {
            $AddressModel->is_default = 1;
        }
        if ($AddressModel->save()) {
            return $this->responseSuccess();
        } else {
            return $this->responseFail();
        }
    }

    /**
     * 地址列表
     *
     * @http get
     */
    public function addressinde(AddressModel $AddressModel)
    {
        $return_arr = [
            'list' => [],
            'total' => 0
        ];
        $Addresses = $AddressModel->where('user_id', $this->user()->id)
            ->select('id', 'name', 'phone', 'address', 'is_default', 'city_code')
            ->get();
        $Addresses->each(function($el) {
            $el->city_name = $el->city->name;
            unset($el->city);
        });
        return $this->responseSuccessData($Addresses->toArray());
    }


    /**
     * 部分更新 地址
     */
    public function addressUpdate(Request $Request, AddressModel $AddressModel)
    {
        (new CheckUser())->scene('patch_address')->gocheck();
        $Address = $AddressModel->where('id', $Request->address_id)->first();
        $Request->name      && $Address->name      = $Request->name;
        $Request->phone     && $Address->phone     = $Request->phone;
        $Request->city_code && $Address->city_code = $Request->city_code;
        $Request->address   && $Address->address   = $Request->address;
        if ($Request->is_default == 1) {
            DB::table('address')->where('user_id', $this->user()->id)
                ->update(['is_default' => 0]);
            $Address = $AddressModel->where('id', $Request->address_id)->first();
            $Address->is_default = 1;
        }
        if ($Address->save()) {
            return $this->responseSuccess();
        } else {
            return $this->responseFail();
        }
    }

    public function addressDestroy(Request $Request, AddressModel $AddressModel)
    {
        (new CheckUser())->scene('del_addr')->gocheck();
        if ($AddressModel->where('id', $Request->address_id)->delete()) {
            return $this->responseSuccess();
        } else {
            return $this->responseFail();
        }
    }

    public function resetPhone(Request $Request)
    {
        $Request->validate([
            'code'         => 'required',
            'validate_key' => 'required'
        ], [
            'code.required'         => 'code不能为空',
            'validate_key.required' => '验证码'
        ]);
        $validate = Cache::get($Request->validate_key);
        if ($validate['code'] !== $Request->code) {
            throw new BaseException([
                'msg' => '验证码不正确'
            ]);
        }
        // 微信合并已有的账号
        $PhoneUser = User::where('phone', $validate['phone'])->first();
        if ($this->user()->openid && $PhoneUser && $PhoneUser->id !== $this->user()->id) {
            DB::beginTransaction();
            try{
                CaseComments::where('user_id', $PhoneUser->id)
                    ->update(['user_id' => $this->user()->id]);
                CaseLikes::where('user_id', $PhoneUser->id)
                    ->update(['user_id' => $this->user()->id]);
                CaseOrder::where('user_id', $PhoneUser->id)
                    ->update(['user_id' => $this->user()->id]);
                SignLog::where('user_id', $PhoneUser->id)
                    ->update(['user_id' => $this->user()->id]);
                CreditLog::where('user_id', $PhoneUser->id)
                    ->update(['user_id' => $this->user()->id]);
                favoritecase::where('user_id', $PhoneUser->id)
                    ->update(['user_id' => $this->user()->id]);
                GoodsComment::where('user_id', $PhoneUser->id)
                    ->update(['user_id' => $this->user()->id]);
                Order::where('user_id', $PhoneUser->id)
                    ->update(['user_id' => $this->user()->id]);
                Share::where('user_id', $PhoneUser->id)
                    ->update(['user_id' => $this->user()->id]);
                UserEvaluate::where('user_id', $PhoneUser->id)
                    ->update(['user_id' => $this->user()->id]);
                $PhoneUser->destroy($PhoneUser->id);
                DB::commit();
            } catch(\Exceptions  $E) {
                DB::rollBack();
            }
        }
        $User = User::where('id', $this->user()->id)->first();
        $User->phone = $validate['phone'];
        if($User->save()){
            return $this->responseSuccess();
            Cache::forget($Request->validate_key);
        } else {
            return $this->responseFail();
        }
    }

    public function share()
    {
        $Shares = Share::where('user_id', $this->user()->id)
            ->with('shareUser')
            ->select('id', 'share_id', 'user_id')
            ->get();
        $share_users = [];
        foreach($Shares as $Share) {
            $tmp = [];
            $tmp['credites'] = $Share->shareUser->credit;
            $tmp['nickname']     = $Share->shareUser->nickname;
            $share_users[] = $tmp;
        }
        return $this->responseSuccessData([
            'title'        => get_config('SHARE_TITLE'),
            'img'          => get_config('SHARE_IMG'),
            'content'      => get_config('SHARE_CONTENT'),
            'sub_credit'   => get_config('get_credit'),
            'share_credit' => get_config('user_credit'),
            'my_share_url' => env('APP_SHARE_URL') . '/?invite=' . $this->user()->id,
            'my_credites'  => User::where('id', $this->user()->id)->first()->credit,
            'list'         => $share_users
        ]); 
    }

    public function qrShow()
    {
        $Me = $this->user();
        $Disk = Storage::disk('admin');
        if ($Me->qr && $Disk->has($Me->qr))  {
            $qr = $Me->qr;
        } else {
            $qr = 'images/' . microtime(true) . 'png';
            $qrCode = new QrCode(env('APP_SHARE_URL') . '?invite=' . $this->user()->id);
            Storage::disk('admin')->put($qr, $qrCode->writeString());
            $Me->qr = $qr;
            $Me->save();
        }
        
        return $this->responseSuccessData([
            'qr' => $Disk->url($Me->qr),
            'nickname' => $Me->nickname,
            'avatar'   => $Disk->url($Me->avatar),
            'invite'   => $Me->id
        ]);
    }

    public function teamShow(UserModel $UserModel)
    {
        $return_arr = [
            'list'     => [],
            'lastpage' => 1,
            'total'    => 0
        ];
        $Teams = $UserModel->where('invite', $this->user()->id)->paginate(10);
        if ($Teams->isNotEmpty()) {
            foreach($Teams->items() as $Item) {
                $tmp = [];
                $tmp['nickname'] = $Item->nickname;
                $tmp['avatar'] = Storage::disk('admin')->url($Item->avatar);
                $tmp['created_at'] = $Item->created_at->toDateString();
                $tmp['invite'] = $this->user()->id;
                $return_arr['list'][] = $tmp;
            }
            $return_arr['lastpage'] = $Teams->lastPage();
            $return_arr['total'] = $Teams->total();
        }
        return $this->responseSuccessData($return_arr);
    }
}

