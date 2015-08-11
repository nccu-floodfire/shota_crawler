<?php

//===========================檔名
$file_name = "Case_ID_" . (string) $_GET['Case_ID'] . "_" . (string) $_GET['SearchString'];
try {
    //===========================檔頭設定
    // Redirect output to a client’s web browser (Excel2007)
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'); // 本文類型
    header('Content-Disposition: attachment;filename=' . $file_name . '.xlsx'); // 檔名
    header('Cache-Control: max-age=0');
    // If you're serving to IE 9, then the following may be needed
    /* header('Cache-Control: max-age=1');
      // If you're serving to IE over SSL, then the following may be needed
      header('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
      header('Last-Modified: ' . date('D, d M Y H:i:s') . ' GMT'); // always modified
      header('Cache-Control: cache, must-revalidate'); // HTTP/1.1
      header('Pragma: public'); // HTTP/1.0 */
//===========================
// 把完成的case打包成excel檔匯出!!
//===========================Error reporting
//    error_reporting(E_ALL);
//ini_set('display_errors', TRUE);
//ini_set('display_startup_errors', TRUE);
//date_default_timezone_set('Asia/Taipei');
//    if (PHP_SAPI == 'cli') {
//        die('This example should only be run from a Web Browser');
//    }
//==========連線
    require "../member/config.php";
    $mysqli = new mysqli($db_host, $db_user, $db_pass, $db_name);
    $mysqli->set_charset("utf8"); // 連線使用UTF-8
//===========================Include PHPExcel
    require_once("/home/shota/public_html/udn_crawler/crawler/PHPExcel_1.8.0_doc/Classes/PHPExcel.php");
//===========================Create new PHPExcel object
    $objPHPExcel = new PHPExcel();
//===========================Set document properties
    $objPHPExcel->getProperties()->setCreator("Shota@NCCU")
            ->setLastModifiedBy("FloodFire") // 上次存檔者
            ->setTitle($file_name) // 標題
            ->setSubject("僅供學術研究") // 主旨
            ->setDescription("僅供學術研究") // 註解
            ->setKeywords("學術研究") // 標籤
            ->setCategory("學術研究"); // 類別
//===========================Miscellaneous glyphs, UTF-8
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
//===========================塞資料
//==========連線測試
    /* if (mysqli_connect_errno()) {
      printf("<p><strong><font color=#FF6600>抱歉，連線失敗: %s</font></strong></p>", mysqli_connect_error());
      } */
//==========查詢
    $Case_ID = $_GET['Case_ID'];
    $query = "SELECT `Udn_News_URL`.`URL`,`Udn_News_Content`.`News_ID`,`Udn_News_Content`.`Date`,`Udn_News_Content`.`story_title`,`Udn_News_Content`.`story_sub_title`,`Udn_News_Content`.`story_author`,`Udn_News_Content`.`text`,`Udn_News_Content`.`newspaper`,`Udn_News_Content`.`page`,`Udn_News_Content`.`category` FROM `Udn_News_URL`,`Udn_News_Content` WHERE `Udn_News_URL`.`Case_ID` = $Case_ID AND `Udn_News_URL`.`News_ID` = `Udn_News_Content`.`News_ID`";
    $result = $mysqli->query($query); // or die($mysqli->error . __LINE__);
    $next = 2;
    if ($result->num_rows > 0) { // 如果有資料
        while ($row = $result->fetch_assoc()) {
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
            $next++;
        }
    }
//==========斷開連結
    $mysqli->close();
//===========================
// Rename worksheet
    $objPHPExcel->getActiveSheet()->setTitle($file_name);
// Set active sheet index to the first sheet, so Excel opens this as the first sheet
    $objPHPExcel->setActiveSheetIndex(0);
//===========================匯出
    $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
    $objWriter->save('php://output');
    exit;
} catch (Exception $exc) {
    echo $exc->getMessage();
    die();
}
?>