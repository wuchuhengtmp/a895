<?php
/** 
 * 快递服务
 *
 */
namespace App\Http\Service;

use App\Exceptions\Api\Base as BaseException;
use GuzzleHttp\Client;

class  Express extends Base
{
    /**
     * 获取物流信息
     *
     * @No 单号
     * @type 物流公司缩写
     */
    public function getExpressInfoByNo($No, $type = '')
    {
        $Client = new Client([
            // Base URI is used with relative requests
            'base_uri' => 'http://wuliu.market.alicloudapi.com/kdi',
            // You can set any number of default request options.
            'timeout'  => 2.0,
        ]);
        $query['no'] = $No;
        if ($type)  $query['type'] = $type;
        $Response = $Client->request('GET', '', [
            'query' => $query,
           'headers' => [
                'Authorization' => 'APPCODE ' . get_config('EXPRESS_APP_CODE')
            ]]
        );
        $Body = ($Response->getBody());
        $Body = json_decode((string)($Body));
        if ($Body->status !== '0') {
            throw new BaseException([
                'msg' => $Body->msg
            ]);
        } else {
            return $Body;
        }
    }
}

