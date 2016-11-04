<?php
/**
 * Created by PhpStorm.
 * User: ManonLoki1
 * Date: 15/9/16
 * Time: 上午11:43
 */

namespace Controls\Samples\Pager;

use Controls\Control\PagerControl;
use Controls\Model\PagerControlModel;

class PagerSample{

    //创建一个简单的只有 上下页的分页
    function  create_SimplePager(){
        //创建一个当前页码是第1页，总共有20行数据，每页显示10个数据的分页控件
        $model=new PagerControlModel(1,20);
        //创建分页控件
        $pager=new PagerControl($model);
        //输出HTML
        echo $pager->fetch();
    }

    //创建一个复杂的有首页末页的Pager
    function  create_FullPager(){
        //创建一个当前页码是第1页，总共有20行数据，每页显示10个数据的分页控件
        $model=new PagerControlModel(1,20,10);
        //创建分页控件，设置类型枚举
        $pager=new PagerControl($model,PagerControl::$Enum_First_Prev_Next_Last);
        //输出HTML
        echo $pager->fetch();
    }



}