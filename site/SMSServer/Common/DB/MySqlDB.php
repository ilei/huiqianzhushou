<?php
/**
 * Created by PhpStorm.
 * User: ManonLoki1
 * Date: 15/12/10
 * Time: 下午2:17
 */

require_once(dirname(__FILE__) . "/DBBase.php");


class MySqlDB extends DBBase
{


    /**
     * 实现接口
     */

    public function exec_query($sql)
    {
        $dataSet = null;

        if (!$this->isConnected) {
            echo "Exec_Query_Error : Server DisConnection" . "\n";
            return null;
        } else {
            try {

                $result = mysqli_query($this->conn, $sql);

                if ($result) {
                    //结果
                    while ($row = mysqli_fetch_assoc($result)) {
                        $dataSet[] = $row;
                    }

                    mysqli_free_result($result);

                }

            } catch (Exception $e) {
                echo "Exec_Query_Error :" . $e->getTraceAsString() . "\n";
            }
        }

        return $dataSet;
    }

    public function  exec_insert($sql)
    {
        $result_id = null;

        if (!$this->isConnected) {
            echo "Exec_Insert_Error : Server DisConnection" . "\n";
            return null;
        }


        try {
            mysqli_query($this->conn, $sql);

            $result_id = mysqli_insert_id($this->conn);

        } catch (Exception $e) {

            echo "Exec_Insert_Error :" . $e->getTraceAsString() . "\n";
        }

        return $result_id;
    }

    public function exec_update($sql)
    {
        $result_rows = null;

        if (!$this->isConnected) {
            echo "Exec_Update_Error : Server DisConnection" . "\n";
            return null;
        }


        try {
            mysqli_query($this->conn, $sql);

            $result_rows = mysqli_affected_rows($this->conn);

        } catch (Exception $e) {

            echo "Exec_Insert_Error :" . $e->getTraceAsString() . "\n";
        }

        return $result_rows;
    }

    public function  transBegin()
    {
        if (!$this->isConnected) {
            echo "Trans_Begin_Error : Server DisConnection" . "\n";
            return;
        }

        mysqli_begin_transaction($this->conn);

    }

    public function transCommit()
    {
        if (!$this->isConnected) {
            echo "Trans_Commit_Error : Server DisConnection" . "\n";
            return;
        }

        mysqli_commit($this->conn);
    }

    public function  transRollback()
    {
        if (!$this->isConnected) {
            echo "Trans_Rollback_Error : Server DisConnection" . "\n";
            return;
        }

        mysqli_rollback($this->conn);
    }


    //重写构造 析构函数
    public function __construct($config)
    {
        parent::__construct($config);

        try {
            //获取连接地址
            $host = empty($config['port']) ? $config['host'] : $config['host'] . ':' . $config['port'];
            $user = $config['user'];
            $password = $config['pwd'];
            $db_name = $config['dbname'];
            $charset = empty($config['charset']) ? 'utf8' : $config['charset'];


            //创建连接
            $this->conn = mysqli_connect($host, $user, $password);

            if (empty($this->conn)) {
                $this->isConnected = false;
                echo "Connection MySqlError" . "\n";
            } else {

                $this->isConnected = true;
                echo "Connection MySql Successed" . "\n";
                //变更操作数据库为目标库
                mysqli_select_db($this->conn, $db_name);
                //设置字符集
                mysqli_set_charset($this->conn, $charset);
                //支持中文
                mysqli_query($this->conn, 'SET NAMES utf8');
            }
            echo "MySqlDB Constructed..." . "\n";
        } catch (Exception $e) {
            echo "Construct Connection MySqlError:" . $e->getTraceAsString() . "\n";
            $this->isConnected = false;
        }


    }

    public function __destruct()
    {
        if (!empty($this->conn)) {
            mysqli_close($this->conn);
        }
        parent::__destruct();

        echo "MySqlDB Destructed..." . "\n";
    }

}