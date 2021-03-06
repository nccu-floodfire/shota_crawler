<?php

// 抓取每則新聞內容用
// 需要排班程式輔助!!
//===========================
session_start();

//===========================
class News_content {

// 登入udn用變數
    public $curl; // 連結udn用
    public $cookieFile; // 連接關閉以後，存放cookie信息的文件名稱
    public $errlogFile;
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
//=====登入udn用
    public function udn_login() {
// 先登入成功
        $url_login = "http://udndata.com/ndapp/member/MbFixLogin"; // 登入用網頁

        $useragent = "Mozilla/5.0 (Windows NT 6.3; rv:36.0) Gecko/20100101 Firefox/36.0";

        $this->cookieFile = dirname(__FILE__) . "/udn-cookie.txt";
        $this->errlogFile = dirname(__FILE__) . "/udn-errlog.txt";

        $this->curl = curl_init(); // create a new cURL resource
        curl_setopt($this->curl, CURLOPT_URL, $url_login);
        curl_setopt($this->curl, CURLOPT_USERAGENT, $useragent);
        curl_setopt($this->curl, CURLOPT_COOKIEJAR, $this->cookieFile); // 連接關閉以後，存放cookie信息的文件名稱
        curl_setopt($this->curl, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($this->curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($this->curl, CURLOPT_STDERR, $this->errlogFile);
        $html_login = curl_exec($this->curl); // 必須的
        //echo $html_login;
        sleep(rand(1, 4)); // 暫停1~4秒
    }

//===========================
//=====爬完資料, 登出udn用
    public function udn_logout() {
// 關閉CURL連線
        curl_close($this->curl);
    }

//===========================
//=====檢查此News_ID(每則新聞在udn上的獨立id), 是否已經被抓取過
    public function check_News_ID_exist($News_ID) { // 每則新聞在udn上的獨立id
        //=====登入資料庫
        $this->DB_link();
        //=====檢查
        $query = "SELECT * FROM `Udn_News_Content` WHERE `News_ID` = '$News_ID'";
        $result = $this->mysqli->query($query) or die($this->mysqli->error . __LINE__);
        $row = $result->fetch_assoc();
        if (empty($row)) {
            return FALSE; // 沒找到(此新聞還沒被抓到)
        } else {
            return TRUE;  // 找到了(此新聞已被抓到)
        }
        //=====登出資料庫
        $this->DB_unlink();
    }

//===========================
//=====抓一則新聞的內文資料
    public function One_News_Crawler($URL, $News_ID) { // 本則新聞的url, 每則新聞在udn上的獨立id
        //=====會用到的變數
        $Crawl_Time = date('Y-m-d H:i:s'); // 抓取本則news的時間
        $News_Date = NULL;      // 新聞報導時間
        $Story_Title = NULL;    // 標題
        $Story_Sub_Title = NULL; // 副標題
        $Story_Author = NULL;   // 作者
        $Text = NULL;           // 新聞內文
        $Newspaper = NULL;      // 報紙
        $Page = NULL;           // 版號
        $Category = NULL;       // 種類
        //=====爬資料
        curl_setopt($this->curl, CURLOPT_URL, $URL);
        curl_setopt($this->curl, CURLOPT_COOKIEFILE, $this->cookieFile); // 包含cookie信息的文件名稱。
        curl_setopt($this->curl, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($this->curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($this->curl, CURLOPT_STDERR, $this->errlogFile);
        $html = curl_exec($this->curl);

        //$html = file_get_contents('haha.html'); // 先嘗試用文本抓
        $doc = new DOMDocument;
        @$doc->loadHTML($html); // @忽略此行warning
        //=====找Story_Title標題
        $title_doms = $doc->getElementsByTagName('span');
        foreach ($title_doms as $title_dom) {
            if ($title_dom->getAttribute('class') == "story_title") {
                if ($Story_Title == NULL) { // 第一行Story_Title
                    $Story_Title = $Story_Title . $title_dom->nodeValue;
                } else {
                    $Story_Title = $Story_Title . " " . $title_dom->nodeValue; //Story_Title可能有一行以上, 用空白隔開
                }
            }
        }
        //=====處理特殊字元(加上跳脫字元, 不然資料庫不給存
        $Story_Title = addslashes($Story_Title);
        //=====找story_sub_title副標題
        $sub_title_doms = $doc->getElementsByTagName('span');
        foreach ($sub_title_doms as $sub_title_dom) {
            if ($sub_title_dom->getAttribute('class') == "story_sub_title") {
                if ($Story_Sub_Title == NULL) { // 第一行Story_Sub_Title
                    $Story_Sub_Title = $Story_Sub_Title . $sub_title_dom->nodeValue;
                } else {
                    $Story_Sub_Title = $Story_Sub_Title . " " . $sub_title_dom->nodeValue;
                    //Story_Sub_Title有一行以上的話, 用空白隔開
                }
            }
        }
        //=====處理特殊字元(加上跳脫字元, 不然資料庫不給存
        $Story_Sub_Title = addslashes($Story_Sub_Title);
        //=====找Story_Author作者
        $Author_doms = $doc->getElementsByTagName('td');
        foreach ($Author_doms as $Author_dom) {
            if ($Author_dom->getAttribute('class') == "story_author") {
                $Story_Author = $Story_Author . $Author_dom->nodeValue;
            }
        }
        //=====處理特殊字元(加上跳脫字元, 不然資料庫不給存
        $Story_Author = addslashes($Story_Author);
        /* Please note that all the discussion about mb_str_replace in the comments is pretty pointless.
         * str_replace works just fine with multibyte strings: */
        $Story_Author = str_replace("【", "", $Story_Author); // 去除不要的字元
        $Story_Author = str_replace("】", "", $Story_Author);
        //=====找新聞內文
        $News_doms = $doc->getElementsByTagName('td');
        // ↑先找整個文章裡的td
        foreach ($News_doms as $News_dom) {
            if ($News_dom->getAttribute('class') == "story") {
                // ↑找整個文章裡的td且class是story(目前就一個)
                $News_Parts = $News_dom->getElementsByTagName('p');
                // ↑再找這整個文章中所有的p
                foreach ($News_Parts as $News_Part) {
                    if ($Text == NULL) {
                        $Text = $Text . $News_Part->nodeValue;
                    } else if (!empty($News_Part->nodeValue)) { // 把文章分段
                        //$Text = $Text . "\n" . $News_Part->nodeValue;
                        $Text = $Text . $News_Part->nodeValue;
                    }
                }
            }
        }
        //=====處理特殊字元(加上跳脫字元, 不然資料庫不給存
        $Text = addslashes($Text);
        //去掉不要的字元, 換行(有可能有英文, 所以空白不能去除)
        //$Text = str_replace(" ", NULL, $Text); // (取代前的字串,取代後字串,要取代的字串)
        //$Text = str_replace("	", NULL, $Text);
        //$Text = str_replace("\n", "", $Text);
        //=====從Text中抓取 新聞報導時間, 報紙, 版號, 種類
        // strpos 找出字串A在字串B中第一次出現的位置
        // mb_strrchr 找出字串A在字串B中最後一次出現的位置, 並回傳該位置到字串結尾的所有字 (可處理) Multibyte
        $Sub_Text = mb_strrchr($Text, "【");
        $start = mb_strpos($Sub_Text, "【") + 3; // 因為【佔3個字元
        $end = mb_strpos($Sub_Text, "】");
        // substr 函數取得部分字串，可設定字串長度
        $Sub_Text = mb_substr($Sub_Text, $start, $end - $start);
        $Sub_Text = explode("/", $Sub_Text);
        $News_Date = $Sub_Text[0];  // 新聞報導時間
        $Newspaper = $Sub_Text[1];  // 報紙
        $Page = $Sub_Text[2];       // 版號
        $Category = $Sub_Text[3];  //種類
        //=====處理本文(去除上述四項欄位
        $Text = mb_substr($Text, 0, strpos($Text, mb_strrchr($Text, "【")));
//==============記得轉utf8(目前轉會有錯誤, 忽略錯誤會回傳空值, 不轉直接存資料庫目前沒有問題)
        //$News_Date = iconv("big5", "UTF-8//TRANSLIT//IGNORE", $News_Date);
        //@$Story_Title = iconv("big5", "UTF-8//TRANSLIT//IGNORE", $Story_Title);
        //$Story_Author = iconv("big5", "UTF-8//TRANSLIT//IGNORE", $Story_Author);
        //$Text = iconv("big5", "UTF-8//TRANSLIT//IGNORE", $Text);
        //$Newspaper = iconv("big5", "UTF-8//TRANSLIT//IGNORE", $Newspaper);
        //$Page = iconv("big5", "UTF-8//TRANSLIT//IGNORE", $Page);
        //$Category = iconv("big5", "UTF-8//TRANSLIT//IGNORE", $Category);
        //=====登入資料庫
        $this->DB_link();
        //=====把新聞資料存入資料庫
        $query = "INSERT INTO `Udn_News_Content`(`News_ID`, `Crawl_Time`, `Date`, `story_title`, `story_sub_title`, `story_author`, `text`, `newspaper`, `page`, `category`) VALUES ($News_ID, '$Crawl_Time', '$News_Date', '$Story_Title', '$Story_Sub_Title', '$Story_Author', '$Text', '$Newspaper', '$Page', '$Category')";
        $result = $this->mysqli->query($query) or die($this->mysqli->error . __LINE__);
        //=====登出資料庫
        $this->DB_unlink();
    }

//===========================
//=====主程式, 每次排班執行此文件時, 以Case為單位
//=====例: 雖然程式執行一次可抓100則, 但此Case只剩12則還沒抓, 12則抓完就結束, 等下次再處理其他Case
    public function News_Saver() {
        //=====登入資料庫
        $this->DB_link();
        //=====先從Udn_News_URL表中尋找出Case_ID最前面(最小)的Case_ID
        // LIMIT 資料起始的index, 要顯示幾筆資料
        $query = "SELECT `Case_ID` FROM `Udn_News_URL` WHERE `DONE` = 0 LIMIT 0, 1";
        $result = $this->mysqli->query($query) or die($this->mysqli->error . __LINE__);
        $URL_Case = $result->fetch_assoc();
        //=====登出資料庫
        $this->DB_unlink();
        //=====如果所有的Case都抓完了, 直接結束程式
        if (empty($URL_Case)) {
            exit;
        }
        //=====
        $Case_ID = $URL_Case['Case_ID'];
        //=====登入資料庫
        $this->DB_link();
        //=====找到Case_ID後, 最多就處理100則新聞
        $query = "SELECT * FROM `Udn_News_URL` WHERE `DONE` = 0 AND `Case_ID` = '$Case_ID' LIMIT 0, 100"; // 最多就處理100則
        $result = $this->mysqli->query($query) or die($this->mysqli->error . __LINE__);
        //=====登出資料庫
        $this->DB_unlink();

        //=====登入udn
        $this->udn_login();
        //$count = 0;
        //echo "start\n";
        while ($row = $result->fetch_assoc()) {
            $URL_ID = $row['U_N_URL_ID'];
            //echo "U_N_URL_ID = " . $URL_ID . "\n";
            //=====代表此新聞還沒進資料庫, 要抓(反之就不抓)!!
            if ($this->check_News_ID_exist($row['News_ID']) == FALSE) {
                $this->One_News_Crawler($row['URL'], $row['News_ID']); // 一次只抓一則
            }
            //=====登入資料庫
            $this->DB_link();
            //=====把這Case查詢到的此則新聞改成已抓取(DONE)
            $query = "UPDATE `Udn_News_URL` SET `DONE`= 1 WHERE `U_N_URL_ID` = '$URL_ID'";
            $this->mysqli->query($query) or die($this->mysqli->error . __LINE__);
            //=====登出資料庫
            $this->DB_unlink();
            //=====每則新聞抓取間隔暫停5~10秒
            sleep(rand(5, 10));
            //$count++;
            //echo $count . "\n";
        }
        //echo "end\n";
        //=====登出udn
        $this->udn_logout();
    }

}

//===========================
//=====啟動此程式
$s = new News_content;
$s->News_Saver();
//=====測試
//=====登入udn
//$s->udn_login();
//$s->One_News_Crawler("http://udndata.com/ndapp/Story2007?no=367&page=37&udndbid=udn_abord&SearchString=tW6kcyuz%2BKdPPaVArMmk6bP4fLzarHek6bP4&sharepage=10&select=1&kind=2&article_date=2000-03-08&news_id=46466",46466);
//$s->One_News_Crawler("http://udndata.com/ndapp/Story2007?no=50&page=1&udndbid=udndata&SearchString=vdK69Suk6bTBPj0yMDE1MDcwNiuk6bTBPD0yMDE1MDgwNA%3D%3D&sharepage=50&select=1&kind=3&article_date=2015-08-03&news_id=8066051", 8066051);
//$s->One_News_Crawler("http://udndata.com/ndapp/Story2007?no=56&page=2&udndbid=udndata&SearchString=cGFyYWdyYXBo&sharepage=50&select=1&kind=2&article_date=1961-07-06&news_id=105653866",105653866);
//=====登出udn
//$s->udn_logout();
?>