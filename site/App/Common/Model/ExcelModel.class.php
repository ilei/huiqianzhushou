<?php
namespace Common\Model;
use Think\Model;

/**
 * Excel导入导出
 * CT: 2014-09-28 14:00 by YLX
 */
class ExcelModel { //extends Model {

    public function __construct() {
        /*导入phpExcel核心类 */
        vendor('PHPExcel.PHPExcel');
        vendor('PHPExcel.PHPExcel.IOFactory');
        vendor('PHPExcel.PHPExcel.Writer.Excel5');  // 用于其他低版本xls
        vendor('PHPExcel.PHPExcel.Writer.Excel2007'); // 用于 excel-2007 格式
        vendor('PHPExcel.PHPExcel.Style.Alignment'); // 用于 excel-2007 格式
//        parent::__construct();
    }

    //导入excel内容转换成数组
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
                /**如果输出汉字有乱码，则需将输出内容用iconv函数进行编码转换，如下将gb2312编码转为utf-8编码输出*/
                //echo iconv('utf-8','gb2312', $val)."\t";

            }
        }
        return $erp_orders_id;
    }

    /**
     * 导出Excel表格
     * @param string $data_main_title 表格主标题
     * @param array $data_title 表格头
     * @param array $data 表格内容
     * @param string $file_name Excel文件名
     * @param string $sheet_title 工作簿名称
     * @param array $ext 其它内容,目前只支持二维数组, 值为字符串, 每一个子数组在表格末尾占用一行单元格
     *                   格式为: array( array( 0=>'key', 1=>'value' ), array( 0=>'key', 1=>'value' ) );
     * @throws \Exception
     * CT: 2015.02.06 14:20 BY YLX
     */
    public function export($data_main_title, $data_title, $data, $file_name, $ext = array(), $sheet_title = '工作表格'){

        $this->__construct();
        /* 实例化类 */
        $objPHPExcel = new \PHPExcel();
        $objPHPExcel->getProperties()->setCreator("www.shetuanbang.net")
                                    ->setLastModifiedBy("www.shetuanbang.net")
                                    ->setTitle("www.shetuanbang.net")
                                    ->setSubject("www.shetuanbang.net")
                                    ->setDescription("www.shetuanbang.net")
                                    ->setKeywords("www.shetuanbang.net")
                                    ->setCategory("www.shetuanbang.net");

        /* 设置输出的excel文件为2007兼容格式 */
        $objWriter = new \PHPExcel_Writer_Excel5($objPHPExcel);//非2007格式
//        $objWriter = new \PHPExcel_Writer_Excel2007($objPHPExcel);

        /* 设置当前的sheet */
        $objPHPExcel->setActiveSheetIndex(0);
        $objActSheet = $objPHPExcel->getActiveSheet();

        /*设置宽度*/
//        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(20);

        //设置行高度
        $objPHPExcel->getActiveSheet()->getRowDimension('1')->setRowHeight(22);
        $objPHPExcel->getActiveSheet()->getRowDimension('1')->setRowHeight(22);

        //set font size bold
//        $objPHPExcel->getActiveSheet()->getStyle('A1:N1')->getFont()->setSize(15);
//        $objPHPExcel->getActiveSheet()->getStyle('A1:N1')->getFont()->setBold(true);
//        $objPHPExcel->getActiveSheet()->getStyle('A1:N1')->getAlignment()->setVertical(\PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
//        $objPHPExcel->getActiveSheet()->getStyle('A1:N1')->getBorders()->getAllBorders()->setBorderStyle(\PHPExcel_Style_Border::BORDER_THIN);

        //设置水平居中
//        $objPHPExcel->getActiveSheet()->getStyle('A1')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
//        $objPHPExcel->getActiveSheet()->getStyle('A')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
//        $objPHPExcel->getActiveSheet()->getStyle('B')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
//        $objPHPExcel->getActiveSheet()->getStyle('C')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
//        $objPHPExcel->getActiveSheet()->getStyle('D')->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_LEFT);

        /* sheet标题 */
        $objActSheet->setTitle($sheet_title);

        // 表格标题
        $start = $k = 'A';
        foreach($data_title as $t) {
            $objActSheet->getColumnDimension($k)->setWidth(20); // 设置宽度
			$objAlign = $objActSheet->getStyle($k.'2')->getAlignment();  
			$objAlign->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_LEFT);  
			$objAlign->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
            $objActSheet->setCellValue($k.'2', $t);
            if(end($data_title) != $t) {
                $k++;
            }
        }
        // 设置标题行格式
        $objPHPExcel->getActiveSheet()->mergeCells($start.'1:'.$k.'1');
        $objPHPExcel->getActiveSheet()->setCellValue('A1', $data_main_title);
        $objPHPExcel->getActiveSheet()->getStyle($start.'1:'.$k.'2')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle($start.'1:'.$k.'2')->getBorders()->getAllBorders()->setBorderStyle(\PHPExcel_Style_Border::BORDER_THIN);

        // 表格内容
        $i = 3;
        foreach($data as $value) {
            $j = 'A';
            foreach($value as $v)
            {
//            $v=iconv("gbk", "utf-8", $v);
            	$objActSheet->getColumnDimension($j)->setWidth(20); // 设置宽度
				$objAlign = $objActSheet->getStyle($j.$i)->getAlignment();  
				$objAlign->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_LEFT);  
				$objAlign->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
                $objActSheet->setCellValue($j.$i, $v);
                $j++;
            }
            $i++;
        }

        //组装其它内容
        if(!empty($ext)){
            $i++;
            foreach($ext as $k => $v){
                $j = 'A';
            	$objActSheet->getColumnDimension($j)->setWidth(20); // 设置宽度
				$objAlign = $objActSheet->getStyle($j.$i)->getAlignment();  
				$objAlign->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_LEFT);  
				$objAlign->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
                $objActSheet->setCellValue($j.$i, $v[0]);
                $j++;
            	$objActSheet->getColumnDimension($j)->setWidth(20); // 设置宽度
				$objAlign = $objActSheet->getStyle($j.$i)->getAlignment();  
				$objAlign->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_LEFT);  
				$objAlign->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
                $objActSheet->setCellValue($j.$i, $v[1]);
                $i++;
            }
        }

        /* 生成到浏览器，提供下载 */
        ob_end_clean();  //清空缓存
        header("Pragma: public");
        header("Expires: 0");
        header("Cache-Control:must-revalidate,post-check=0,pre-check=0");
        header("Content-Type:application/force-download");
        header("Content-Type:application/vnd.ms-execl");
        header("Content-Type:application/octet-stream");
        header("Content-Type:application/download");
        header('Content-Disposition:attachment;filename="'.$file_name.'.xls"');
        header("Content-Transfer-Encoding:binary");
        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
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
