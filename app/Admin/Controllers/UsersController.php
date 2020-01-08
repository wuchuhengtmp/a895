<?php

namespace App\Admin\Controllers;

use App\Model\User;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Illuminate\Support\Facades\Storage;

class UsersController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '用户列表';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new User());
        $grid->model()->where('is_admin', 0);

        $grid->column('id', __('Id'));
        $grid->column('nickname', __('Nickname'));
        $grid->column('avatar', __('Avatar'))
        ->display(function() {
            $url = Storage::disk('admin')->url($this->avatar);
            return "<img src='" . $url . "' style='width:50px'/>";
        });
        $grid->column('phone', __('Phone'));
        $grid->column('credit', __('Credit'));
        $grid->column('created_at', __('Created at'));
        $grid->column('updated_at', __('Updated at'));

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
        $show = new Show(User::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('name', __('Name'));
        $show->field('email', __('Email'));
        $show->field('email_verified_at', __('Email verified at'));
        $show->field('password', __('Password'));
        $show->field('is_admin', __('Is admin'));
        $show->field('avatar', __('Avatar'));
        $show->field('nickname', __('Nickname'));
        $show->field('credit', __('Credit'));
        $show->field('phone', __('Phone'));
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
        $form = new Form(new User());

        $form->text('nickname', __('Nickname'));
        $form->password('password', __('Password'));
        $form->image('avatar', __('Avatar'));
        $form->number('credit', __('Credit'));
        $form->mobile('phone', __('Phone')); 
        $form->saving(function (Form $form) {
            if (bcrypt($form->password) !== $form->model()->password) {
                $form->password = bcrypt($form->password);
            }
        });
        return $form;
    }
}
