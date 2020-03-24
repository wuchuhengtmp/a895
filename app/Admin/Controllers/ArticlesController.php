<?php

namespace App\Admin\Controllers;

use App\Model\Article;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use App\Http\Logic\{
    ArticleCategory as ArticleCategoryLogic
};
use Illuminate\Support\Facades\Storage;

class ArticlesController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '新闻资讯';

    protected $is_store = false;

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Article());

        $grid->column('id', __('Id'));
        $grid->column('title', __('Title'));
        $grid->column('category.name', __('Category id'));
        $grid->column('thumb_type', __('Thumb type'))
            ->display(function() {
                switch($this->thumb_type) {
                    case 'image' : 
                        return '图片';
                        break;
                    case 'video' :
                        return '视频';
                        break;
                }
            });
        $grid->column('thumb_url', __('Thumb url'))
            ->display(function(){
                if ($this->thumb_type === 'image') {
                    $this->thumb_url = Storage::disk('admin')->url($this->thumb_url);
                }
                return "<img src='" . $this->thumb_url . "' style='width:100px'/>";
            });
        $grid->column('clickes', __('Clickers'));
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
        $show = new Show(Article::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('title', __('Title'));
        $show->field('category_id', __('Category id'));
        $show->field('content', __('Content'));
        $show->field('thumb_type', __('Thumb type'));
        $show->field('thumb_video_url', __('Thumb video url'));
        $show->field('thumb_url', __('Thumb url'));
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
        $form = new Form(new Article());
        $form->text('title', __('Title'))->rules('required');
        $form->select('category_id', __('Category id'))
            ->options((new ArticleCategoryLogic)->getArticleCategores())
            ->default(1)
            ->rules('required');
        $form->simditor('content', __('Content'))->rules('required');
        /* $form->select('thumb_type', __('Thumb type')) */
        /*     ->options(['image' => '图片', 'video' => '视频']) */
        /*     ->default('image') */
        /*     ->rules('required'); */
        /* if ($this->is_store) { */
        /*     if (request()->thumb_type === 'image') { */
        /*         $form->image('thumb_url', __('thumb_url'))->uniqueName()->rules('required'); */
        /*     } else if (request()->thumb_type === 'video') { */
        /*         $form->file('thumb_video_url', __('Thumb video url'))->uniqueName()->rules('required'); */
        /*     } */
        /* } else { */
        /*     $form->image('thumb_url', __('thumb_url'))->uniqueName(); */
        /*     $form->file('thumb_video_url', __('Thumb video url'))->uniqueName(); */
        /* } */
        $form->saved(function (Form $form) {
            if ($form->model()->thumb_type === 'video') {
                $disk = Storage::disk('qiniu');
                $path = $form->model()->thumb_video_url;
                $url = $disk->getUrl($path);
                $disk->put($path, Storage::disk('admin')->get($path));
                $form->model()->thumb_url = $url . '?vframe/png/offset/1/w/300' ;
                $form->model()->save();
            }
       });
        return $form;
    }

    public function store()
    {
        $this->is_store = true;
        return $this->form()->store();
    }
}
