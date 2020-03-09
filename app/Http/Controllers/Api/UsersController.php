<?php

namespace App\Http\Controllers\Api;

use function Couchbase\defaultDecoder;
use Illuminate\Http\Request;
use App\Http\Validate\{
    CheckPhoneRegister,
    CheckResetPassword 
};
use App\Model\{
    Address as AddressModel
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

class UsersController extends Controller
{
    /**
     * 用户注册
     */
    public function store(Request $Request)
    {
        (new CheckPhoneRegister())->gocheck();
        (new UserService())->registerStore();
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
    public function collectionDelete($id)
    {
        (new CheckUserExists())->gocheck();
        (new MeCollectionService())->collectionDelete($this->user()->id,$id);
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
            return $this->responseFail('没有收货地址，请添加');
        } else {
            $Address = $Address->first();
            return $this->responseSuccessData([
                'id' => $Address->id,
                'phone' => $Address->phone,
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
        DB::beginTransaction();
        try{
            if ($Request->is_default) {
                $Address->is_default = 1;
                $AddressModel->where('user_id', $this->user()->id)
                    ->update(['is_default' => 0]);
            }
            $Address->save();
            DB::commit();
            return $this->responseSuccess();
        } catch(\Exception $E) {
            DB::rollBack();
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
}

