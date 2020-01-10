<?php
/**
 * Created by PhpStorm.
 * User: 猫巷
 * Email:catlane@foxmail.com
 * Date: 2019/5/29
 * Time: 9:26 AM
 */
return [
    'disks' => [

        'local' => [
            'driver' => 'local' ,
            'root' => storage_path ( 'app' ) ,
        ] ,

        'public' => [
            'driver' => 'local' ,
            'root' => storage_path ( 'app/public' ) ,
        ] ,
        'qiniu_live' => [//七牛云
            'driver' => 'qiniu' ,//如果是七牛云空间，必填qiniu
            'domains' => [
                'default' => env('QINIU_DOMAIN') , //你的七牛域名
                'https' => '' , //你的HTTPS域名
                'custom'    => '****',                //你的自定义域名
            ] ,
            'access_key' => env('QINIU_ACCESS_KEY' ),  //AccessKey
            'secret_key' => env('QINIU_SECRET_KEY') ,  //SecretKey
            'bucket' => env('QINIU_BUCKET') ,  //Bucket名字
            'url' => '/' ,  // 填写文件访问根url
            'qn_area'=> 'http://up.qiniu.com',
        ]
    ] ,
    'default' => [
        'disk' => 'public' ,//默认磁盘
        'extensions' => 'mp4' ,//后缀
        'mimeTypes' => 'video/*' ,//类型
        'fileSizeLimit' => 10737418240 ,//上传文件限制总大小，默认10G,默认单位为b
        'fileNumLimit' => '5' ,//文件上传总数量
        'saveType' => 'json', //单文件默认为字符串，多文件上传存储格式，json:['a.jpg','b.jpg']
    ]



];
