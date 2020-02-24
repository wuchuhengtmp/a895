<?php

namespace App\Http\Service;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use App\Exceptions\Api\{
    Base as BaseException,
    SystemErrorException
};

class MemberPicture extends Base
{
    /*
     *  获取用户上传头像链接
     *
     */
    public function avatarUrlGet()
    {
        $Request = request();
        if ($Request->hasFile('avatar')) {
            $file = $Request->file('avatar');
            if ($file->isValid()) {
                $allowed_extensions = ['gif', 'jpg', 'jpeg', 'bmp', 'png', 'ico'];
                if ($file->getClientOriginalExtension() && !in_array($file->getClientOriginalExtension(), $allowed_extensions)) {
                    throw new SystemErrorException([
                        'msg' => '图片格式有误！'
                    ]);
                }
                //获取扩展名
                $ext = $file->getClientOriginalExtension();
                //获取文件的绝对路径
                $path = $file->getRealPath();
                //定义文件名
                $name = time() . rand(1, 99999) . '.' . $ext;
                $data['avatar'] = env('APP_URL') . Storage::url($name);
                $data['avatar'] = str_replace(env('APP_URL') .'/storage', 'images', $data['avatar']);

                if (Storage::disk('img')->put($name, file_get_contents($path))) {
                    return $data;
                } else {
                    throw new SystemErrorException([
                        'msg' => '上传失败！'
                    ]);
                }
            } else {
                throw new SystemErrorException([
                    'msg' => '上传信息有误！'
                ]);
            }
        } else {
            throw new SystemErrorException([
                'msg' => '请上传图片！'
            ]);
        }
    }

}
