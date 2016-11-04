<?php
/**
 * Created by PhpStorm.
 * User: ManonLoki1
 * Date: 15/12/10
 * Time: 下午12:08
 *
 * 数据库的基类 提供接口
 */
abstract class DBBase{


    /**
     * @var 配置
     */
    protected  $config;
    /**
     * @var sql连接
     */
    protected  $conn;

    /**
     * @var 状态 是否成功连接了数据库 Bool
     */
    protected  $isConnected;


    /**
     * @param $sql string sql语句
     * @return mixed 根据sql语句查询结果 返回array
     *
     * 执行查询 成功返回结果集(array) 失败返回null
     */
    abstract  public  function exec_query($sql);


    /**
     * @param $sql string sql语句
     * @return mixed 返回ID
     *
     * 执行插入操作 成功返回ID(array) 失败返回null
     */
    abstract  public  function exec_insert($sql);

    /**
     * @param $sql string sql语句
     * @return mixed 返回受影响行数
     *
     * 执行增删改
     */
    abstract  public  function  exec_update($sql);

    /**
     * 初始化事务保护
     */
    abstract public  function  transBegin();

    /**
     * 提交事务
     */
    abstract  public  function  transCommit();

    /**
     * 事务回滚
     */
    abstract  public  function  transRollback();

    /**
     * DBBase constructor.
     */
    public   function  __construct($config)
    {
        //加载Config
        $this->config=$config;
    }

    public  function  __destruct()
    {
        // TODO: Implement __destruct() method.
        unset($this->conn);
        unset($this->config);
    }
}