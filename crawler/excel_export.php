<?php

// 把完成的case打包成excel檔匯出!!
//===========================
class Excel_export {

    // 登入資料庫用
    public $mysqli;

//===========================
//=====登入資料庫用
    public function DB_link() {
        require("/home/shota/public_html/udn_crawler/member/config.php");
        $this->mysqli = new mysqli($db_host, $db_user, $db_pass, $db_name);
        $this->mysqli->set_charset("utf8"); // 連線使用UTF-8
//==========連線測試
        if (mysqli_connect_errno()) {
            printf("<p><strong><font color=#FF6600>抱歉，連線失敗: %s</font></strong></p>", mysqli_connect_error());
        }
    }

//===========================
//=====切斷資料庫連結
    public function DB_unlink() {
//==========close connection
        $this->mysqli->close();
    }

//===========================
//=====選擇某個Case, 打包成Excel檔
    public function Case_to_Excel($Case_ID) {
        require_once("/home/shota/public_html/udn_crawler/crawler/PHPExcel_1.8.0_doc/Classes/PHPExcel.php");
        $file_name = "Case_ID_" . (string) $Case_ID;
        $file_name_sub = $file_name . "_sub";
        $file_description = "僅供學術研究";
        //=====新增PHPexcel物件
        $objPHPExcel = new PHPExcel();
        $objPHPExcel->getProperties()->setCreator("Shota")
                //上次修改者
                ->setLastModifiedBy("FloodFire")
                //檔案標題
                ->setTitle("$file_name")
                //檔案子標題
                ->setSubject(NULL)
                //檔案描述
                ->setDescription($file_description)
                //檔案標記
                ->setKeywords(NULL)
                //檔案類別
                ->setCategory(NULL);
        //=====許多資料都提到是顯示目前資料表，但把0改成1就會error，是否有大神願意求解
        $objPHPExcel->setActiveSheetIndex(0)
                ->setCellValue('A1', 'URL')
                ->setCellValue('B1', 'News_ID')
                ->setCellValue('C1', 'Date')
                ->setCellValue('D1', 'story_title')
                ->setCellValue('E1', 'story_sub_title')
                ->setCellValue('F1', 'story_author')
                ->setCellValue('G1', 'text')
                ->setCellValue('H1', 'newspaper')
                ->setCellValue('I1', 'page')
                ->setCellValue('J1', 'category');
        //==========連線
        $this->DB_link();
        //==========查詢
        $query = "SELECT `Udn_News_URL`.`URL`,`Udn_News_Content`.`News_ID`,`Udn_News_Content`.`Date`,`Udn_News_Content`.`story_title`,`Udn_News_Content`.`story_sub_title`,`Udn_News_Content`.`story_author`,`Udn_News_Content`.`text`,`Udn_News_Content`.`newspaper`,`Udn_News_Content`.`page`,`Udn_News_Content`.`category` FROM `Udn_News_URL`,`Udn_News_Content` WHERE `Udn_News_URL`.`Case_ID` = $Case_ID AND `Udn_News_URL`.`News_ID` = `Udn_News_Content`.`News_ID`";
        $result = $this->mysqli->query($query) or die($this->mysqli->error . __LINE__);
        $start = 0;
        if ($result->num_rows > 0) { // 如果有資料
            while ($row = $result->fetch_assoc()) {
                $next = $start + 2;
                $objPHPExcel->setActiveSheetIndex(0)
                        ->setCellValue('A' . $next, $row['URL'])
                        ->setCellValue('B' . $next, $row['News_ID'])
                        ->setCellValue('C' . $next, $row['Date'])
                        ->setCellValue('D' . $next, $row['story_title'])
                        ->setCellValue('E' . $next, $row['story_sub_title'])
                        ->setCellValue('F' . $next, $row['story_author'])
                        ->setCellValue('G' . $next, $row['text'])
                        ->setCellValue('H' . $next, $row['newspaper'])
                        ->setCellValue('I' . $next, $row['page'])
                        ->setCellValue('J' . $next, $row['category']);
                $start++;
            }
        }
        //==========斷開連結
        $this->DB_unlink();
        //=====匯出Excel
        $objPHPExcel->setActiveSheetIndex(0);
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $objWriter->save($file_name . ".xlsx"); // 檔名
        //$objWriter->save('php://output');
    }

}

//===========================
//=====測試
$Excel = new Excel_export;
$Excel->Case_to_Excel(1);
//$Excel->Case_to_Excel(3);
?>

