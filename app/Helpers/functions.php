<?php

use App\Model\{
    Config,
    Calculates
};
use GuzzleHttp\Client;

/**
 *  获取自定义配置
 *
 */
function get_config(string $config_name) : string
{
    $Config = Config::where('name', $config_name)->first();
    return $Config->value;
}

/**
 * 
 *
 */
function get_calculate(string $key)
{
    $Config = Calculates::where('key', $key)->first();
    return $Config->value;
}

/**
 *  是否json
 *
 * @return boolean
 */
function is_json($string) {
    return ((is_string($string) &&
            (is_object(json_decode($string)) ||
            is_array(json_decode($string))))) ? true : false;
}

/**
 * 复制图片文件
 *
 * @return mix
 */
function image_copy(string $file, string $to_file = '', string $disk = 'admin')
{
    $path_info = pathinfo($file);
    if (strlen($to_file) === 0) {
        $to_file = $path_info['dirname'] . '/' .uniqid() . '.' . $path_info['extension'];
    }
    Storage::disk($disk)->copy($file, $to_file);
    return $to_file;
}

/**
 * 用坐标获取城市编号
 */
function get_city_code_by_location($lng, $lat)
{
    $location_info = getLocation($lng, $lat);
    $code = $location_info['regeocode']['addressComponent']['adcode'];
    return DB::table('region')->where('id', $code)->first();
}

/**
 * 获取座标信息
 *
 * @longitude float 经度
 * @latitude  float 纬度
 * @reutrn array
 */
function get_location($longitude, $latitude)
{
    $url = 'https://restapi.amap.com/v3/geocode/regeo';
    $query = array_filter([
        'key' => env('AMAP_KEY'),
        'output' => 'json',
        'location' => $longitude. ',' . $latitude,
        'extensions' => 'base'
    ]);
    $response = (new Client())
        ->get($url, [
            'query' => $query,
        ])
        ->getBody()
        ->getContents();
    $response = json_decode($response, true);
    return $response;
}

/**
 * 格式化密钥
 *
 * @private_key 密钥
 *
 * @return 格式化后的密钥
 */
function format_private_key(string $private_key) : string
{
    $private_key = "-----BEGIN RSA PRIVATE KEY-----\n" .
        wordwrap($private_key, 64, "\n", true) .
        "\n-----END RSA PRIVATE KEY-----";
    return $private_key;
}

/**
 * 格式化公钥
 *
 * @public_key 无头尾公钥
 */
function format_public_key(string $public_key) : string
{
    $public_key = "-----BEGIN PUBLIC KEY-----\n" .
        wordwrap($public_key, 64, "\n", true) .
        "\n-----END PUBLIC KEY-----";
    return $public_key;
}

/**
 *  地址查找
 *
 *  @address 地址
 *  @city_code 城市编码
 *
 *  @return  地址数据
 */
function search_address(string $address, int $city_code) :array
{
    $url = 'https://restapi.amap.com/v3/place/text';
    $query = array_filter([
        'key'        => env('AMAP_KEY'),
        'output'     => 'json',
        'keywords'    => $address,
        'city'       => $city_code,
        'offset' => 20,
        'extensions' => 'base'
    ]);
    $response = (new Client())
        ->get($url, [
            'query' => $query,
        ])
        ->getBody()
        ->getContents();
    $response = json_decode($response, true);
    return $response;
}


/**
    * 计算两点地理坐标之间的距离
    * @param  Decimal $longitude1 起点经度
    * @param  Decimal $latitude1  起点纬度
    * @param  Decimal $longitude2 终点经度
    * @param  Decimal $latitude2  终点纬度
    * @param  Int     $unit       单位 1:米 2:公里
    * @param  Int     $decimal    精度 保留小数位数
    * @return Decimal
    */
function get_distance($longitude1, $latitude1, $longitude2, $latitude2, $unit=2, $decimal=2)
{

    $EARTH_RADIUS = 6370.996; // 地球半径系数
    $PI = 3.1415926;

    $radLat1 = $latitude1 * $PI / 180.0;
    $radLat2 = $latitude2 * $PI / 180.0;

    $radLng1 = $longitude1 * $PI / 180.0;
    $radLng2 = $longitude2 * $PI /180.0;

    $a = $radLat1 - $radLat2;
    $b = $radLng1 - $radLng2;

    $distance = 2 * asin(sqrt(pow(sin($a/2),2) + cos($radLat1) * cos($radLat2) * pow(sin($b/2),2)));
    $distance = $distance * $EARTH_RADIUS * 1000;

    if($unit==2){
        $distance = $distance / 1000;
    }

    return round($distance, $decimal);

}


/**
 *计算某个经纬度的周围某段距离的正方形的四个点
 *@param lng float 经度
 *@param lat float 纬度
 *@param distance float 该点所在圆的半径，该圆与此正方形内切，默认值为0.5千米
 *@return array 正方形的四个点的经纬度坐标
 */
function get_square_point($lng, $lat,$distance = 2)
{
    $earth_radius =  6371;//地球半径，平均半径为6371km
    $dlng =  2 * asin(sin($distance / (2 * $earth_radius)) / cos(deg2rad($lat)));
    $dlng = rad2deg($dlng);
    $dlat = $distance/$earth_radius;
    $dlat = rad2deg($dlat);
    $squares= array(
        'left-top'     => array('latitude'=>$lat + $dlat, 'longitude'=>$lng-$dlng),
        'right-top'    => array('latitude'=>$lat + $dlat, 'longitude'=>$lng + $dlng),
        'left-bottom'  => array('latitude'=>$lat - $dlat, 'longitude'=>$lng - $dlng),
        'right-bottom' => array('latitude'=>$lat - $dlat, 'longitude'=>$lng + $dlng)
    );
    $info_sql = "
        select
        *
        from
        `admin_users`
        where
        latitude<>0
        and
        latitude>{$squares['right-bottom']['latitude']}
        and
        latitude<{$squares['left-top']['latitude']}
        and
        longitude>{$squares['left-top']['longitude']}
        and
        longitude<{$squares['right-bottom']['longitude']}
        ";
    $locations = DB::select($info_sql);
    return $locations;
}

/**
 * 解析token
 *
 * @rturn object
 */
function parse_token(string $token)
{
   list($header, $body, $sign) = explode('.', $token);
   $user_info_token = base64_decode($body);
   $UserToken = json_decode($user_info_token);
   return $UserToken;
}

/**
 * 解密
 *
 * return mix
 */
function decryptData(string $encrypted_data)
{
    $key = env('EAS');
    $aesKey=base64_decode($key);
    $iv = 0;
    $aesIV=base64_decode($iv);
    $aesCipher=base64_decode($encrypted_data);
    $result=openssl_decrypt( $aesCipher, "AES-128-CBC", $aesKey, 1, $aesIV);
    return $result;
}

/**
 * 是否时间格式
 *
 * @time_str 时间格式 如 2019-04-04 00:01:00
 */
function is_time_string(string $time_str) : bool
{
    return (date('Y-m-d H:i:s', strtotime($time_str)) == $time_str);
}

/**
 *  是否是日期格式
 *
 */
function is_dateformat(string $time_str) : bool
{
    return (date('Y-m-d', strtotime($time_str)) == $time_str);
}

/**
 * 是否是假期
 *
 *  @date 时间格式
 */
function is_vacation(string $date) :bool
{
    $date = strtotime($date);
    $week = date('w', $date);
    if (in_array($week, ['0', '6'])) {
        return true;
    }

    $date_format = date('Y-m-d H:i:s', $date);
    $Holiday = Holiday::where('date', $date_format)->get();
    if ($Holiday->isEmpty()) {
        return false;
    } else {
        return true;
    }
}

/**
 *  租金周期价格
 *
 */
function period_prices($goods_id, $start_date, $end_date)
{
        $Request = new Request();
        $Goods = Goods::where('id', $goods_id)
            ->where('status', 1)
            ->get();

        $Goods = Goods::where('id', $goods_id)->first();

        $start_date = strtotime($start_date);
        $end_date = strtotime($end_date);
        $days = ($end_date - $start_date) / (60 * 60 * 24);
        $days += $days === 0 ? 1 : 0;
        $days = ceil($days);
        $result_list = [];
        $total_amount = 0.00;
        for($i=1; $i <= $days; $i++){
            $tmp = [];
            $day = $day ?? $start_date;
            $tmp['date'] = date('Y-m-d', $day);
            $tmp['day']  = date('d', $day);
            $day_format  = date('Y-m-d', $day);
            if (is_vacation($day_format)) {
                $tmp['is_vacation'] = 1;
                $holiday_rate  = (new Holiday())->getHolidayRate();
                $tmp['price']       = number_format($Goods->price * $holiday_rate, 2);
            } else {
                $tmp['is_vacation'] = 0;
                $tmp['price']       = $Goods->price;
            }
            $total_amount += $tmp['price'];
            $result_list[] = $tmp;
            $day += 60 * 60 * 24;
        }
        return [
            'list' => $result_list,
            'total_amount' => $total_amount
        ];
}

/**
 * 转换为中文时间格式
 *
 */
function time_to_chiness(string $time_format) : string
{
    $time_format = trim($time_format);
    $result_date = '';
    $time = strtotime($time_format);
    $chinese_week = [
        0 => '周日',
        1 => '周一',
        2 => '周二',
        3 => '周三',
        4 => '周四',
        5 => '周五',
        6 => '周六'
    ];
    $result_date = date('m', $time) . '月' . date('d', $time) . '日';
    $result_date = $result_date . ' ' . $chinese_week[date('w', $time)];
    $result_date = $result_date .  ' ' . date('H:i', $time);

    return $result_date;
}

/**
 *  发送http请求，并返回数据
 *
 */
function https_request($url, $data = null)
{
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
    if (!empty($data)) {
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
    }
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    $output = curl_exec($curl);
    curl_close($curl);
    return $output;
}

/**
 *  把jsonp转为php数组
 *
 */
function jsonp_decode($jsonp, $assoc = false)
{
    $jsonp = trim($jsonp);
    if(isset($jsonp[0]) && $jsonp[0] !== '[' && $jsonp[0] !== '{') {
        $begin = strpos($jsonp, '(');
        if(false !== $begin)
        {
            $end = strrpos($jsonp, ')');
            if(false !== $end)
            {
                $jsonp = substr($jsonp, $begin + 1, $end - $begin - 1);
            }
        }
    }
    return json_decode($jsonp, $assoc);
}

