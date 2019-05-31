<?php

namespace App\Admin\Controllers;

use App\Models\Product;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Show;

class ProductsController extends Controller
{
    use HasResourceActions;

    /**
     * Index interface.
     *
     * @param Content $content
     * @return Content
     */
    public function index(Content $content)
    {
        return $content
            ->header('Index')
            ->description('description')
            ->body($this->grid());
    }

    /**
     * Show interface.
     *
     * @param mixed $id
     * @param Content $content
     * @return Content
     */
    public function show($id, Content $content)
    {
        return $content
            ->header('Detail')
            ->description('description')
            ->body($this->detail($id));
    }

    /**
     * Edit interface.
     *
     * @param mixed $id
     * @param Content $content
     * @return Content
     */
    public function edit($id, Content $content)
    {
        return $content
            ->header('Edit')
            ->description('description')
            ->body($this->form()->edit($id));
    }

    /**
     * Create interface.
     *
     * @param Content $content
     * @return Content
     */
    public function create(Content $content)
    {
        return $content
            ->header('Create')
            ->description('description')
            ->body($this->form());
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Product);

        $grid->id('Id')->sortable();
        $grid->title('标题');
        $grid->description('描述');
        $grid->image('Image')->display(function($img){
            return "<img src='".$this->image_url."'  width='200'/>";
        });
        $grid->on_sale('上架')->display(function($onsale){
            return  $onsale ? '是' : '否';
        });
        $grid->rating('评分');
        $grid->sold_count('销量');
        $grid->review_count('评论数');
        $grid->price('价格');
        $grid->created_at('创建时间');
        $grid->updated_at('更新时间');
        // filter($callback)方法用来设置表格的简单搜索框
        $grid->filter(function ($filter) {
            // 设置created_at字段的范围查询
            $filter->between('created_at', 'Created Time')->datetime();
        });
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
        $show = new Show(Product::findOrFail($id));

        $show->id('Id');
        $show->title('标题');
        $show->description('描述')->unescape();
        $show->image_url('Image')->setEscape(false)->as(function($img){
            return "<img src='".$img."' width='200'/>";
        });
        $show->on_sale('上架')->as(function($onsale){
            return  $onsale ? '是' : '否';
        });
        $show->rating('评分');
        $show->sold_count('销量');
        $show->review_count('评论数');
        $show->price('价格');
        $show->created_at('创建时间');
        $show->updated_at('更新时间');

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new Product);

        $form->text('title', '标题')->rules('required');
        $form->textarea('description', '描述')->rules('required');
        $form->image('image', '图片')->uniqueName()->rules('required|image');
        $form->switch('on_sale', '是否在售')->default(1);
        $form->decimal('rating', '评分')->default(5.00);
        $form->number('sold_count', '销量');
        $form->number('review_count', '评论数');
        $form->hasMany('skus','SKU 列表',function(Form\NestedForm $form){
            $form->text('title', 'SKU 名称')->rules('required');
            $form->text('description', 'SKU 描述')->rules('required');
            $form->text('price', '单价')->rules('required|numeric|min:0.01');
            $form->text('stock', '剩余库存')->rules('required|integer|min:0');
        });
        $form->saving(function (Form $form) {
            $form->model()->price = collect($form->input('skus'))->where(Form::REMOVE_FLAG_NAME, 0)->min('price') ?: 0;
        });
        return $form;
    }
}
