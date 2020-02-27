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

        $form = $this->formTrait();
        $form->select('PAY_DAY', __('PAY_DAY'))->default(get_config('PAY_DAY'))->options([
            1 => 1,
            2 => 2,
            3 => 3,
            4 => 4,
            5 => 5,
            6 => 6,
            7 => 7,
            8 => 8,
            9 => 9,
            10 => 10,
            11 => 11,
            12 => 12,
            13 => 13,
            14 => 14,
            15 => 15,
            16 => 16,
            17 => 17,
            18 => 18,
            19 => 19,
            20 => 20,
            21 => 21,
            22 => 22,
            23 => 23,
            24 => 24,
            25 => 25,
            26 => 26,
            27 => 27,
        ]);
        $tab->add('分期付款', $form);
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
