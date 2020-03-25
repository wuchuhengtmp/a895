<?php

namespace App\Http\Service;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use App\Exceptions\Api\{
    Base as BaseException,
    SystemErrorException
};
use Monolog\Logger;
use Illuminate\Support\Facades\Log;

class MemberPicture extends Base
{
    /*
     *  获取用户上传头像链接
     *
     */
    public function avatarUrlGet()
    {
        $Request = request();
        if ($Request->has('avatar')) {
            $file = $Request->avatar;
            $file_content = base64_decode($file);
            $fc = iconv('windows-1250', 'utf-8', $file);
            $handle=fopen("php://temp", "rw");
            fwrite($handle, $file_content );
            fseek($handle, 0);
            $mime_type  =  mime_content_type($handle);
            if (in_array($mime_type, ['image/jpeg', 'image/gif', 'image/jpeg', 'image/x-icon', 'image/png'])) {
                $mime_list = [
                    'image/jpeg' => 'jpeg',
                    'image/gif' => 'gif',
                    'image/jpeg' => 'jpeg',
                    'image/x-icon' => 'ico',
                    'image/png' => 'png'
                ];
                $relative_path  = time() . rand(1, 99999) . '.' . $mime_list[$mime_type];
                $path = Storage::disk('img')->path($relative_path);
                is_dir(dirname($path)) || mkdir(dirname($path), 0700, true);
                Storage::disk('img')->put($relative_path, $file_content);
                return ['avatar' =>  $relative_path];
            } else {
                throw new SystemErrorException([
                    'msg' => '图片格式有误！'
                ]);
            }
            if ($file->isValid()) {
                $allowed_extensions = ['gif', 'jpg', 'jpeg', 'bmp', 'png', 'ico'];
                if ($file->getClientOriginalExtension() && !in_array($file->getClientOriginalExtension(), $allowed_extensions)) {
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
