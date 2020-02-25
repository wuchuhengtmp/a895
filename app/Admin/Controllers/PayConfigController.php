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

class PayConfigController extends Controller
{
    public function index(Content $content, Config $Config)
    {
        $content->title('支付配置');
        $content->description('选项');

        $tab = new Widgets\Tab();
        $this->showFormParameters($content);
        $form = $this->formTrait();
        $form->text('WX_APPID', __('WX_APPID'))->default(get_config('WX_APPID'));
        $form->text('WX_APPSECRET', __('WX_APPSECRET'))->default(get_config('WX_APPSECRET'));
        $form->text('WX_PAY_KEY', __('WX_PAY_KEY'))->default(get_config('WX_PAY_KEY'));
        $form->text('WX_MCH_ID', __('WX_MCH_ID'))->default(get_config('WX_MCH_ID'));
        $tab->add('微信支付', $form);

        $form = $this->formTrait();
        $form->text('amap_key', __('amap_key'))->default(get_config('amap_key'));
        $tab->add('支付宝', $form);

        $form = $this->formTrait();
        $form->text('PAY_ACCOUNT', __('PAY_ACCOUNT'))->default(get_config('PAY_ACCOUNT'));
        $tab->add('平台收款账号', $form);

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
    public function store()
    {
        $is_save = request()->file('DEFAULT_AVATOR')->store('admin');
        dd($is_save);
        echo asset($is_save); exit;
    }
}
