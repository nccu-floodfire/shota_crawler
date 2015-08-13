<?php

// crawling.php用
// 主要為顯示目前每個case的情況
//===========================
session_start();

//===========================
class Show_case {

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
//=====檢查是否有選擇該報系
    public function check_mark_show($row) {
        $show = null;
        if ($row['united'] == "1") {
            $show = $show . "聯、";
        }
        if ($row['economic'] == "1") {
            $show = $show . "經、";
        }
        if ($row['minsen'] == "1") {
            $show = $show . "民、";
        }
        if ($row['united_late'] == "1") {
            $show = $show . "聯晚、";
        }
        if ($row['star'] == "1") {
            $show = $show . "星、";
        }
        if ($row['upaper'] == "1") {
            $show = $show . "U、";
        }
        if ($row['world'] == "1") {
            $show = $show . "世、";
        }
        if ($row['europe'] == "1") {
            $show = $show . "歐";
        }
        return $show;
    }

//===========================
//=====切斷資料庫連結
    public function DB_unlink() {
        //==========close connection
        $this->mysqli->close();
    }

//===========================
//=====Show case
    public function Show_table() {
        //==========連線
        $this->DB_link();
        //==========Query case表
        $query = "SELECT * FROM `Udn_Case` WHERE 1";
        $result = $this->mysqli->query($query) or die($this->mysqli->error . __LINE__);
        //==========Show
        echo "<strong>目前抓取速度為<font color=#FF6600>每半小時100則，先查先抓，請耐心等候，謝謝!!</font></br>";
        echo "搜尋時段為<font color=#FF6600>今天(\"從\"、\"到\"兩欄位等於今天日期)的Case，由於新聞連結時效性問題，會和一般Case同時抓取。</font></br>";
        echo "詞二→使用者下的第二個關鍵字(以此類推)。+→and；/→or；-→not(運算子目前忽略顯示)</br>";
        echo "<font color=#FF6600>\"從\"、\"到\"兩欄如果皆為空值(0000-00-00)代表搜尋所有日期。</font></br>";
        echo "聯→聯合報；經→經濟日報；民→民生報；聯晚→聯合晚報；星→星報；U→Upaper；世→世界日報；歐→歐洲日報</strong>";
        echo "<table border=\"1\">";
        echo "<tr>";
        echo "<th>" . "<strong>編號</strong>" . "</th>";
        echo "<th>" . "<strong>使用者</strong>" . "</th>";
        echo "<th>" . "<strong>查詢時間</strong>" . "</th>";
        echo "<th>" . "<strong>關鍵字</strong>" . "</th>";
        //echo "<th>" . "<strong>運二</strong>" . "</th>";
        echo "<th>" . "<strong>詞二</strong>" . "</th>";
        //echo "<th>" . "<strong>運三</strong>" . "</th>";
        echo "<th>" . "<strong>詞三</strong>" . "</th>";
        //echo "<th>" . "<strong>運四</strong>" . "</th>";
        echo "<th>" . "<strong>詞四</strong>" . "</th>";
        echo "<th>" . "<strong>則數</strong>" . "</th>";
        echo "<th>" . "<strong>已抓取數</strong>" . "</th>";
        echo "<th>" . "<strong>從</strong>" . "</th>";
        echo "<th>" . "<strong>到</strong>" . "</th>";
        echo "<th>" . "<strong>選擇報系</strong>" . "</th>";
        echo "</tr>";
        if ($result->num_rows > 0) { // 如果有資料
            while ($row = $result->fetch_assoc()) {
                echo "<tr>";
                echo "<th>" . $row['Case_ID'] . "</th>";
                echo "<th>" . $row['User_Account'] . "</th>";
                echo "<th>" . $row['Query_Time'] . "</th>";
                echo "<th>" . $row['SearchString'] . "</th>";
                //echo "<th>" . $row['operator_2'] . "</th>";
                echo "<th>" . $row['SearchString_2'] . "</th>";
                //echo "<th>" . $row['operator_3'] . "</th>";
                echo "<th>" . $row['SearchString_3'] . "</th>";
                //echo "<th>" . $row['operator_4'] . "</th>";
                echo "<th>" . $row['SearchString_4'] . "</th>";
                echo "<th>" . $row['News_count'] . "</th>";
                //"目前已抓取數"用算的
                $case_id = $row['Case_ID'];
                $SearchString = $row['SearchString'];
                $query_2 = "SELECT COUNT(*) AS News_finished FROM `Udn_News_URL` WHERE `Case_ID` = $case_id AND `DONE` = 1";
                $result_2 = $this->mysqli->query($query_2) or die($this->mysqli->error . __LINE__);
                if ($result_2->num_rows > 0) { // 如果有資料
                    $News_finished = $result_2->fetch_assoc();
                    if ($row['News_count'] != 0) {
                        $percent = (int) ($News_finished['News_finished'] / $row['News_count'] * 100);
                        if ($percent == 100) { // 代表已完成, 橘色粗體明顯, 並且提供下載excel的連結
                            //==========因為如果在excel.export.php連結資料庫, 匯出的.xlsx檔會打不開
                            //$query_3 = "SELECT `Udn_News_URL`.`URL`,`Udn_News_Content`.`News_ID`,`Udn_News_Content`.`Date`,`Udn_News_Content`.`story_title`,`Udn_News_Content`.`story_sub_title`,`Udn_News_Content`.`story_author`,`Udn_News_Content`.`text`,`Udn_News_Content`.`newspaper`,`Udn_News_Content`.`page`,`Udn_News_Content`.`category` FROM `Udn_News_URL`,`Udn_News_Content` WHERE `Udn_News_URL`.`Case_ID` = $case_id AND `Udn_News_URL`.`News_ID` = `Udn_News_Content`.`News_ID`";
                            //$result_3 = $this->mysqli->query($query_3) or die($this->mysqli->error . __LINE__);
                            //echo "<th>" . gettype($result_3) . "</th>";
                            echo "<th><strong><font color=#FF6600><a href='http://140.119.164.218/~shota/udn_crawler/crawler/excel_export.php?Case_ID=$case_id&SearchString=$SearchString' target='_blank'>" . $News_finished['News_finished'] . "(" . $percent . "%)</a></font></strong></th>";
                        } else {
                            echo "<th>" . $News_finished['News_finished'] . "(" . $percent . "%)</th>";
                        }
                    } else {
                        echo "<th>" . "N/A" . "</th>"; // 分母(新聞則數為0)
                    }
                } else {
                    echo "<th>" . "錯誤" . "</th>";
                }
                echo "<th>" . $row['start'] . "</th>";
                echo "<th>" . $row['end'] . "</th>";
                echo "<th>" . $this->check_mark_show($row) . "</th>";
                echo "</tr>";
            }
        }
        echo "</table>";
        //==========斷開連結
        $this->DB_unlink();
    }

}

?>