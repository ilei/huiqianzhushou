<?php

namespace Controls\Model;

/**
 * 分页Model
 * Class PageControlModel
 * @package Controls\Control
 * CT 2015.09.16 11:00 my manonloki
 */
class PagerControlModel
{
    /**
     * @var 当前页
     */
    public $currentPage;

    /**
     * @var 最大页
     */
    public $maxPage;

    /**
     * @var 每页容量
     */
    public $pageSize;

    /**
     * @var 容量种类
     */
    public $pageSizeArray;

    /**
     * @param $cp int 当前页
     * @param $mc int 最大条目数
     * @param $ps int 每页显示数据条目
     */
    public function __construct($cp,$mc,$ps=10){
        $this->currentPage=intval($cp);

        //提升兼容性 保证最小页码为1
        if($this->currentPage<1||is_nan($this->currentPage)){
            $this->currentPage=1;
        }


        $this->maxPage=ceil(floatval($mc)/floatval($ps));
        $this->pageSize=intval($ps);
        //暂时写死 封装在Model里
        $this->pageSizeArray=array(
            
            5,
            10,
            20,
            50,
            100
        );
    }
}
