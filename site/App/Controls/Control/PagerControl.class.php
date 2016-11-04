<?php

namespace Controls\Control;

use Think\View;
use Controls\Model\PagerControlModel;
/**
 * 分页控件
 * 带数字的分页请用Think自带的
 * 具体的分页请求逻辑请自己用JS写 仅支持JS+Ajax 所有href='#'
 * Class Pager
 * @package Controls\Control
 * CT 2014.09.16 10:35 by manonloki
 */
class PagerControl
{
    /**
     * @var 枚举，上一页，下一页
     */
    public static $Enum_Prev_Next = 0;
    /**
     * @var 枚举 首页，末页，上一页，下一页
     */
    public static $Enum_First_Prev_Next_Last = 1;

    /**
     * @var 枚举 首页,末页,页码,上一页,下一页
     */
    public static $Enum_First_Prev_Number_Next_Last=2;

    //封装的View
    protected $view;
    //分页类型
    protected $pager_type;
    //分页Model
    protected $pager_model;

    /**构
     * 造函数
     * @param $model PagerControlModel 分页Model
     * @param int $type 分页类型
     * CT 2015.09.16 10:42 by manonloki
     */
    public function __construct($model, $type = 0)
    {
        $this->view = new View();
        $this->pager_type = $type;
        $this->pager_model = $model;
    }

    /**
     * 获取Html
     * CT 2015.09.16 10:48 by manonloki
     */
    public function fetch()
    {

        $result_html = '';

        //判断model类型是否为PagerControlModel  以及PagerType是否为int类型
        if ($this->pager_model != null && $this->pager_model instanceof PagerControlModel && is_int($this->pager_type)) {

            //设置分页Model
            $this->view->assign('model', $this->pager_model);
            //根据模板产生HTML
            switch ($this->pager_type) {
                case self::$Enum_Prev_Next: {
                    $result_html = $this->view->fetch('Controls:Pager:prev_next');
                    break;
                }
                case self::$Enum_First_Prev_Next_Last: {
                    $result_html = $this->view->fetch('Controls:Pager:first_prev_next_last');
                    break;
                }
                case self::$Enum_First_Prev_Number_Next_Last:{
                    $result_html = $this->view->fetch('Controls:Pager:first_prev_number_next_last');
                    break;
                }
            }
        }
        return $result_html;
    }
}



