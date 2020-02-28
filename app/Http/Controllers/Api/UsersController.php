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
    CheckLocationParams
};
use App\Http\Service\{
    MeCollection as MeCollectionService,
    User         as UserService
};

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
}
