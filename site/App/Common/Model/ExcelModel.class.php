<?php
namespace Common\Model;

class ExcelModel {

    public function __construct() {
        /*导入phpExcel核心类 */
        vendor('PHPExcel.PHPExcel');
        vendor('PHPExcel.PHPExcel.IOFactory');
        vendor('PHPExcel.PHPExcel.Writer.Excel5');  // 用于其他低版本xls
        vendor('PHPExcel.PHPExcel.Writer.Excel2007'); // 用于 excel-2007 格式
        vendor('PHPExcel.PHPExcel.Style.Alignment'); // 用于 excel-2007 格式
    }

    public function import($filePath){
        $this->__construct();
        $PHPExcel = new \PHPExcel();

        /**默认用excel2007读取excel，若格式不对，则用之前的版本进行读取*/
        vendor('PHPExcel.PHPExcel.Reader.Excel2007'); // 用于 excel-2007 格式
        $PHPReader = new \PHPExcel_Reader_Excel2007();
        if(!$PHPReader->canRead($filePath)){
            $PHPReader = new \PHPExcel_Reader_Excel5();
            if(!$PHPReader->canRead($filePath)){
                echo 'no Excel';
                return;
            }
        }

        $PHPExcel = $PHPReader->load($filePath);
        $currentSheet = $PHPExcel->getSheet(0);  //读取excel文件中的第一个工作表
        $allColumn = $currentSheet->getHighestColumn(); //取得最大的列号
        $allRow = $currentSheet->getHighestRow(); //取得一共有多少行
        $erp_orders_id = array();  //声明数组

        /**从第二行开始输出，因为excel表中第一行为列名*/
        for($currentRow = 1;$currentRow <= $allRow;$currentRow++){

            /**从第A列开始输出*/
            for($currentColumn= 'A';$currentColumn<= $allColumn; $currentColumn++){

                $val = $currentSheet->getCellByColumnAndRow(ord($currentColumn) - 65,$currentRow)->getValue();/**ord()将字符转为十进制数*/
                if($val!=''){
                    $erp_orders_id[] = $val;
                }
            }
        }
        return $erp_orders_id;
    }

    //导入excel内容转换成数组
    public function import_arr($filePath){
        $this->__construct();
        $PHPExcel = new \PHPExcel();

        /**默认用excel2007读取excel，若格式不对，则用之前的版本进行读取*/
        vendor('PHPExcel.PHPExcel.Reader.Excel2007'); // 用于 excel-2007 格式
        $PHPReader = new \PHPExcel_Reader_Excel2007();
        if(!$PHPReader->canRead($filePath)){
            $PHPReader = new \PHPExcel_Reader_Excel5();
            if(!$PHPReader->canRead($filePath)){
                echo 'no Excel';
                return;
            }
        }

        $PHPExcel = $PHPReader->load($filePath);
        $currentSheet = $PHPExcel->getSheet(0);  //读取excel文件中的第一个工作表
        $allColumn = $currentSheet->getHighestColumn(); //取得最大的列号
        $allRow = $currentSheet->getHighestRow(); //取得一共有多少行
        $erp_orders_id = array();  //声明数组

        /**从第二行开始输出，因为excel表中第一行为列名*/
        for($currentRow = 2;$currentRow <= $allRow;$currentRow++){
            $tmp = array();
            /**从第A列开始输出*/
            for($currentColumn= 'A';$currentColumn<= $allColumn; $currentColumn++){

                $val = $currentSheet->getCellByColumnAndRow(ord($currentColumn) - 65,$currentRow)->getValue();/**ord()将字符转为十进**/
                $tmp[] = (string)$val;
            }
            $erp_orders_id[] = $tmp;
        }
        return $erp_orders_id;
    }

}
