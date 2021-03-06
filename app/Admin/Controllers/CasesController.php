<?php

namespace App\Admin\Controllers;

use App\Model\{
    Cases,
    Designer,
    CaseCategory
};
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Show;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class CasesController extends AdminController
{
    public $times = [
            1 => '一期',
            2 => '二期',
            3 => '三期',
            4 => '四期',
            5 => '五期',
            6 => '六期',
            7 => '七期',
            8 => '八期',
            9 => '九期',
            10 => '十期',
            11 => '十一期',
            12 => '十二期',
            13 => '十三期',
            14 => '十四期',
        ];
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '案例';

    protected $is_store = false;

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Cases());
        $grid->filter(function($filter){
            $filter->disableIdFilter();
            $filter->like('title', __('title'));
            $filter->between('created_at', __('created_at'))->datetime();
        });
        $grid->column('id', __('Id'));
        $grid->column('title', __('Title'));
        $grid->column('thumb_url', __('Thumb url'))->lightbox();
        $grid->column('designer.name', __('Designer'));
        $grid->column('category.name', __('Case Category'));
        $grid->column('clickes', __('Clickes'));
        $grid->column('summary', __('Summary'))->display(function(){
            return mb_substr($this->summary, 0, 15);
        });
        $grid->column('apartment', __('Apartment'));
        $grid->column('style', __('Style'));
        $grid->column('area', __('Area'));
        $grid->column('price', __('price'))->display(function(){
            return $this->min_price . '-' . $this->max_price;
        });
        $grid->column('is_ecdemic_errand', __('Is ecdemic errand'))->display(function(){
            switch($this->is_ecdemic_errand) {
                case 0: return '否'; break;
                case 1: return '是'; break;
            }
        });
        $grid->column('city.name', __('Service city'));
        $grid->column('is_to_build', __('Is to build'))->display(function(){
            switch($this->is_ecdemic_errand) {
                case 0: return '否'; break;
                case 1: return '是'; break;
            }
        });
        $grid->column('community', __('Community'));
        $states = [
            'on'  => ['value' => 0, 'text' => '关', 'color' => 'primary'],
            'off' => ['value' => 1, 'text' => '开', 'color' => 'default'],
        ];
        $grid->column('is_commend', __('Is commend'))->switch($states);
        $grid->column('tags', __('Tags'))
            ->display(function(){
                return explode(',', $this->tags);
            })
            ->label();
        $grid->column('created_at', __('Created at'));

        return $grid;
    }

    /**
     * Make a show builder.
     *
     * @param mixed $id
     * @return Show
     */
    protected function detail($id)
    {
        $show = new Show(Cases::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('designer_id', __('Designer id'));
        $show->field('clickes', __('Clickes'));
        $show->field('title', __('Title'));
        $show->field('content', __('Content'));
        $show->field('apartment', __('Apartment'));
        $show->field('style', __('Style'));
        $show->field('area', __('Area'));
        $show->field('is_local_errand', __('Is local errand'));
        $show->field('service_city', __('Service city'));
        $show->field('min_price', __('Min price'));
        $show->field('max_price', __('Max price'));
        $show->field('is_to_build', __('Is to build'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new Cases());

        $states = [
            'on'  => ['value' => 1, 'text' => '是', 'color' => 'success'],
            'off' => ['value' => 0, 'text' => '否', 'color' => 'danger'],
        ];
        $form->text('title', __('Title'))->rules('required');
        $form->textarea('summary', __('Summary'))->rules('required');
        $form->simditor('content', __('Content'))->rules('required');
        $form->select('designer_id', __('Designer'))
            ->options((new Designer())->getList())
            ->rules('required');
        $form->select('case_category_id', __('Case Category'))
            ->options((new CaseCategory())->getList())
            ->rules('required');
        $form->text('apartment', __('Apartment'))
            ->rules('required');
        $form->text('style', __('Style'))
            ->rules('required');
        $form->number('area', __('Area'))
            ->rules('required');
        $form->image('thumb_url', __('thumb_url'))->uniqueName()->rules('required');
        $form->distpicker([ 'province_code', 'city_code', 'district_code'], '服务城市')->autoselect(2);

        $form->switch('is_ecdemic_errand', __('Is ecdemic errand'))->states($states);
        $form->decimal('min_price', __('Min price'))->default(0)->rules('required');
        $form->decimal('max_price', __('Max price'))->default(0)->rules('required');
        $form->switch('is_to_build', __('Is to build'))->states($states);
        $form->tags('tags', __('Tags'))->rules('required');
        $location_info = file_get_contents("https://restapi.amap.com/v3/ip?ip=" . $_SERVER['REMOTE_ADDR'] . "&output=json&key=" . env('AMAP_KEY'));
        $location_info = json_decode($location_info);
        list($target, $tmp) = explode(';', $location_info->rectangle);
        list($lng, $lat) = explode(',', $target);
        
        $form->latlong('latitude', 'longitude', '地址坐标')
            ->default(['lat' => $lat, 'lng' =>$lng ])
            ->rules('required');
        $form->text('community', __('Community'))->rules('required');

        $form->multipleSelect('period', '分期')->options($this->times)->rules('required', ['required' =>'分期数不能为空']);

        $form->saving(function(Form $form){
            unset($form->province_code, $form->district_code);
        });
        return $form;
    }

    public function store()
    {
        $this->is_store = true;
        return $this->form()->store();
    }

}
