<?php

namespace App\Admin\Controllers;

use App\Model\Config;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\{
    Form,
    Grid,
    Show
};
use Encore\Admin\Layout\{
    Column,
    Content,
    Row
};
use Encore\Admin\Widgets;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;

class ConfigController extends Controller
{
    public function index(Content $content, Config $Config)
    {
        $content->title('系统设置');
        $content->description('选项');

        $tab = new Widgets\Tab();
        $this->showFormParameters($content);
        $form = $this->formTrait();
        $form->text('notice', __('notice'))->default(get_config('notice'));
        $tab->add('系统公告', $form);

        $form = $this->formTrait();
        $form->text('amap_key', __('amap_key'))->default(get_config('amap_key'));
        $tab->add('地图配置', $form);

        $form = $this->formTrait();
        $form->text('EXPRESS_APP_CODE', __('快递平台APP_CODE码'))->default(get_config('EXPRESS_APP_CODE'));
        $tab->add('物流配置', $form);

        $form = $this->formTrait();
        $form->text('CUSTOMER_URL', __('客服'))->default(get_config('CUSTOMER_URL'));
        $tab->add('阿里客服', $form);

        /* $form = new Widgets\Form(); */
        /* $form->method('post'); */
        /* $form->image('DEFAULT_AVATOR', __('DEFAULT_AVATOR'))->move('public/upload/image1/'); */
        /* $tab->add('用户配置', $form); */


        $content->row($tab);

        return $content;
    }

    protected function showFormParameters($content)
    {
        $parameters = request()->except(['_pjax', '_token']);
        if (!empty($parameters)) {

            ob_start();
            
            $contents = ob_get_contents();
            foreach ($parameters as $key=>$val) {
                $RowConfig = Config::where('name', $key)->first();
                if ($RowConfig) {
                    $RowConfig->value = $val;
                    $RowConfig->save();
                }
            }
            ob_end_clean();

            $content->row($contents);
        }
    }

    protected function formTrait()
    {
        $form = new Widgets\Form();
        $form->method('get');
        return $form;
    }

    /**
     * 保存
     *
     */
    public function store($content)
    {
        $is_save = request()->file('DEFAULT_AVATOR')->store('admin');
        ob_start();
        $contents = ob_get_contents();
        ob_end_clean();
        $content->row($contents);
    }
}
