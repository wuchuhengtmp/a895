<?php

namespace App\Admin\Controllers;

use App\Model\{
    Config,
    Calculates,
    ChinaArea
};
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

class CalculateController extends Controller
{
    public function index(Content $content, Calculates $Calculates, ChinaArea $ChinaArea)
    {
        $content->title('在线评估');
        $content->description('选项');

        $tab = new Widgets\Tab();
        $this->showFormParameters($content);
        $form = $this->formTrait();
        $form->text('room', __('room'))->default(get_calculate('room'));
        $form->text('toilet', __('toilet'))->default(get_calculate('toilet'));
        $form->text('kitchen', __('kitchen'))->default(get_calculate('kitchen'));
        $form->text('parlour', __('parlour'))->default(get_calculate('parlour'));
        $tab->add('房屋类型(元/每平米)', $form);

        $form = $this->formTrait();
        $cities = $Calculates->where('type', 'city')->get();
        foreach($cities as $city) {
            $form->text('city_code_' . $city->key, $city->city->name . '(单位:%)')->default($city->value);
        }
        $be_select_cities = array_column($cities->toArray(), 'key');
        $tmp = $ChinaArea->whereNotIn('code', $be_select_cities)->select(['code', 'name'])->get()->toArray();
        foreach($tmp as $k => $v) {
            $area[$v['code']] = $v['name'];
        }
        $form->hidden('type')->default('city');
        $form->hasMany('code', '添加', function(Form\NestedForm $form) use ($area){
            $form->select('name', '城市')->options($area);
            $form->text('value', '涨价(单位:%)')->options($area);
        });
        $tab->add('所在城市', $form);

        $form = $this->formTrait();
        $form->hidden('type')->default('vip');
        $form->text('vip1', '简装')->default(get_calculate('vip1'));
        $form->text('vip2', '精装')->default(get_calculate('vip2'));
        $form->text('vip3', '豪华装')->default(get_calculate('vip3'));
        $tab->add('套餐(附加价(%))', $form);

        $form = $this->formTrait();
        $form->hidden('type')->default('proportion');
        $form->text('labor', '人工费占比')->default(get_calculate('labor'));
        $form->text('material', '材料占比')->default(get_calculate('material'));
        $tab->add('价格占比(单位:%)', $form);

        $form = $this->formTrait();
        $form->hidden('type')->default('valuation');
        $form->text('valuation', '上限价多于基础价格的:')->default(get_calculate('valuation'));
        $tab->add('估价范围(单位:%)', $form);

        $content->row($tab);

        return $content;
    }

    protected function showFormParameters($content)
    {
        $parameters = request()->except(['_pjax', '_token']);
        if (!empty($parameters)) {

            ob_start();
            
            $contents = ob_get_contents();
            // add optiones
            if (array_key_exists('code', $parameters) && $parameters['type'] === 'city' && $parameters['code']) {
                foreach($parameters['code'] as $new_option) {
                    if ($new_option['name']) {
                        if ((new Calculates())->where('key', $new_option['name'])->first()) {
                            continue;
                        }
                        $Calculates = new Calculates();
                        $Calculates->key = $new_option['name'];
                        $Calculates->value = $new_option['value'];
                        $Calculates->type = 'city';
                        $Calculates->save();
                    }
                }
            }
            if (array_key_exists('type', $parameters) && $parameters['type'] === 'vip') {
                foreach($parameters as $key=>$value) {
                    if (preg_match('/^vip.+/', $key) && $value) {
                        $Row = Calculates::where('key', $key)->first();
                        $Row->value = $value;
                        $Row->save();
                    }
                }
            }
            // 人工费用
            if (array_key_exists('type', $parameters) && $parameters['type'] === 'proportion') {
                foreach($parameters as $key=>$value) {
                    if (in_array($key, ['labor', 'material']) && $value) {
                        $Row = Calculates::where('key', $key)->first();
                        $Row->value = $value;
                        $Row->save();
                    }
                }
            }

            // 估值上限
            if (array_key_exists('type', $parameters) && $parameters['type'] === 'valuation') {
                foreach($parameters as $key=>$value) {
                    if ($key === 'valuation') {
                        $Row = Calculates::where('key', $key)->first();
                        $Row->value = $value;
                        $Row->save();
                    }
                }
            }
            // edit options
            foreach($parameters as $key=>$value) {
                if (preg_match('/city_code_.?/', $key) && $value){
                    $Row = Calculates::where('key', str_replace('city_code_', '', $key))->first();
                    $Row->value = $value;
                    $Row->save();
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
