<?php

// index.php用
// 主要為儲存使用者的query進資料庫, 等候排班爬新聞
//===========================
session_start();

//===========================
class Crawler {

// 搜尋欄位變數
    public $SearchString;
    public $operator_2;
    public $SearchString_2;
    public $operator_3;
    public $SearchString_3;
    public $operator_4;
    public $SearchString_4;
    public $ago; //選擇今天往後多久以前還是一個時間區間
    public $end;
    public $start;
    public $united;        //"聯合報";
    public $economic;      //"經濟日報";
    public $minsen;        //"民生報";
    public $united_late;   //"聯合晚報";
    public $star;          //"星報";
    public $upaper;        //"Upaper";
    public $world;         //"世界日報";
    public $europe;        //"歐洲日報";
    public $News_count;    //新聞則數
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
//=====從使用者查詢的參數中, 重組出新聞標題頁的連結
    public function News_title_link_maker($Case_ID, $page, $out) { // 要找哪個case_id, 要產出第幾頁的url, 是否為國外報系
        // 要先抓到總頁數, 就要先找出這次搜尋產生出的獨立url
        if ($out == 0) { // 國內報系
            $link = "http://udndata.com/ndapp/Searchdec2007?udndbid=udndata&page=" . $page . "&SearchString=";
        } else if ($out == 1) { // 國外報系
            $link = "http://udndata.com/ndapp/Searchdec2007?udndbid=udn_abord&page=" . $page . "&SearchString=";
        }
// udn是吃big5
        $link = iconv("UTF-8", "BIG5", $link);
//==========連線資料庫
        $this->DB_link();
        $query = "SELECT * FROM `Udn_Case` WHERE binary `Case_ID` = '$Case_ID'";
//echo $query;
        $result = $this->mysqli->query($query) or die($this->mysqli->error . __LINE__);
//echo $result;
        $Case = $result->fetch_assoc();
        //var_dump($Case);
//==========斷開連結
        $this->DB_unlink();
// ==========以下這段需要做url編碼
        $link_temp = $Case['SearchString'];
        if (!empty($Case['SearchString_2'])) { // 如果還有第二關鍵字
            $link_temp = $link_temp . $Case['operator_2'] . $Case['SearchString_2'];
        }
        if (!empty($Case['SearchString_3'])) { // 如果還有第三關鍵字
            $link_temp = $link_temp . $Case['operator_3'] . $Case['SearchString_3'];
        }
        if (!empty($Case['SearchString_4'])) { // 如果還有第四關鍵字
            $link_temp = $link_temp . $Case['operator_4'] . $Case['SearchString_4'];
        }
        //=====日期處理
        if ($Case['start'] != "0000-00-00" && $Case['end'] != "0000-00-00") { // 如果不是選擇所有日期
            $link_temp = $link_temp . "+日期>=" . str_replace('-', '', $Case['start']) . "+日期<=" . str_replace('-', '', $Case['end']);
        }
        // 如果全部"國內"報紙都勾選就不用再加報別, 只要有任一種報紙不要, 就要加報別
        if ($out == 0) { // 國內報系
            if ($Case['united'] != 1 || $Case['economic'] != 1 || $Case['minsen'] != 1 || $Case['united_late'] != 1 || $Case['star'] != 1 || $Case['upaper'] != 1) {
                if ($Case['united'] == 1) {
                    $link_temp = $link_temp . "+報別=聯合報";
                    if ($Case['economic'] == 1) {
                        $link_temp = $link_temp . "|經濟日報";
                    }
                    if ($Case['minsen'] == 1) {
                        $link_temp = $link_temp . "|民生報";
                    }
                    if ($Case['united_late'] == 1) {
                        $link_temp = $link_temp . "|聯合晚報";
                    }
                    if ($Case['star'] == 1) {
                        $link_temp = $link_temp . "|星報";
                    }
                    if ($Case['upaper'] == 1) {
                        $link_temp = $link_temp . "|Upaper";
                    }
                } else if ($Case['economic'] == 1) {
                    $link_temp = $link_temp . "+報別=經濟日報";
                    if ($Case['minsen'] == 1) {
                        $link_temp = $link_temp . "|民生報";
                    }
                    if ($Case['united_late'] == 1) {
                        $link_temp = $link_temp . "|聯合晚報";
                    }
                    if ($Case['star'] == 1) {
                        $link_temp = $link_temp . "|星報";
                    }
                    if ($Case['upaper'] == 1) {
                        $link_temp = $link_temp . "|Upaper";
                    }
                } else if ($Case['minsen'] == 1) {
                    $link_temp = $link_temp . "+報別=民生報";
                    if ($Case['united_late'] == 1) {
                        $link_temp = $link_temp . "|聯合晚報";
                    }
                    if ($Case['star'] == 1) {
                        $link_temp = $link_temp . "|星報";
                    }
                    if ($Case['upaper'] == 1) {
                        $link_temp = $link_temp . "|Upaper";
                    }
                } else if ($Case['united_late'] == 1) {
                    $link_temp = $link_temp . "+報別=聯合晚報";
                    if ($Case['star'] == 1) {
                        $link_temp = $link_temp . "|星報";
                    }
                    if ($Case['upaper'] == 1) {
                        $link_temp = $link_temp . "|Upaper";
                    }
                } else if ($Case['star'] == 1) {
                    $link_temp = $link_temp . "+報別=星報";
                    if ($Case['upaper'] == 1) {
                        $link_temp = $link_temp . "|Upaper";
                    }
                } else if ($Case['upaper'] == 1) {
                    $link_temp = $link_temp . "+報別=Upaper";
                }
            }
        } else if ($out == 1) { // 國外報系, 都要加上報別
            if ($Case['world'] == 1) {
                $link_temp = $link_temp . "+報別=世界日報";
                if ($Case['europe'] == 1) {
                    $link_temp = $link_temp . "|歐洲日報";
                }
            } else if ($Case['europe'] == 1) {
                $link_temp = $link_temp . "+報別=歐洲日報";
            }
        }
        // ==========轉big5, url編碼
        $link_temp = urlencode(iconv("UTF-8", "BIG5", $link_temp));
        // ==========連接
        $link = $link . $link_temp;
        // 加入其他參數
        $link = $link . "&sharepage=" . "50"; //固定不變 資料筆數 (10)(20)(30)(40)(50)
        $link = $link . "&select=" . "1";     //固定不變 資料排序 (1 由近到遠)(0 由遠到近)(8 出現次數)
        $link = $link . "&kind=" . "3";       //固定不變 呈現形式 (預設2 詳目式加導言)(3 簡目式)
        return $link;
    }

//===========================
//=====從新聞標題頁抓取出新聞總數, 並回傳
    public function find_news_count($URL) {
        // 登入udn
        $this->udn_login();
        // 抓取新聞則數
        curl_setopt($this->curl, CURLOPT_URL, $URL);
        curl_setopt($this->curl, CURLOPT_COOKIEFILE, $this->cookieFile); // 包含cookie信息的文件名稱。
        curl_setopt($this->curl, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($this->curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($this->curl, CURLOPT_STDERR, $this->errlogFile);
        $html = curl_exec($this->curl);
        //$html = iconv("big5", "UTF-8", $html); // 不能轉, 因為內容是set為big5
        //echo $html;
        //$html = file_get_contents('123.html'); // 先嘗試用文本抓
        $doc = new DOMDocument;
        @$doc->loadHTML($html); // @忽略此行warning
        $font_doms = $doc->getElementsByTagName('font');

        // 登出udn
        $this->udn_logout();

        //echo $font_doms->item(3)->nodeValue . "</br>";
        return (int) $font_doms->item(3)->nodeValue; // 回傳新聞總數(第4格font)
    }

//===========================
//=====更新這個case的所有新聞Udn_News_URL到資料庫
    public function News_URL_saver($case_id, $URL_list, $IS_LAST_PAGE, $TOTAL_news_count, $Today_Case) {
        // 哪一個Case, 要爬的標題頁url, 這是不是最後一頁標題頁, 新聞總數, 是不是今天要抓的Case
        curl_setopt($this->curl, CURLOPT_URL, $URL_list);
        curl_setopt($this->curl, CURLOPT_COOKIEFILE, $this->cookieFile); // 包含cookie信息的文件名稱。
        curl_setopt($this->curl, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($this->curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($this->curl, CURLOPT_STDERR, $this->errlogFile);
        $html = curl_exec($this->curl);

        $doc = new DOMDocument;
        @$doc->loadHTML($html); // @忽略此行warning
        $a_doms = $doc->getElementsByTagName('a');

// 爬某個case的某個標題頁的url
        $find_head = 0; // 是否找到頭一則新聞的link
        if ($IS_LAST_PAGE == TRUE) {
            $this_page_news_count = $TOTAL_news_count % 50;
        } else {
            $this_page_news_count = 50; // 不是最後一頁的話，一頁50則
        }
        $count = 0;
        foreach ($a_doms as $a_dom) {
            if ($a_dom->getAttribute('href')) {
                //===印出新聞的url
                if ($find_head == 1) {
                    //=====登入資料庫
                    $this->DB_link();
                    $URL = "http://udndata.com" . $a_dom->getAttribute('href');
                    //=====找news_id
                    $start = strpos($URL, "&news_id=") + 9;
                    $end = strlen($URL);
                    $News_ID = (int) substr($URL, $start, $end - $start);
                    //=====更新Udn_News_URL
                    //echo $URL . "</br>";
                    if ($Today_Case == TRUE) { // 是不是今天要抓的Case
                        $query = "INSERT INTO `Udn_News_URL`(`Case_ID`, `URL`, `News_ID`, `DONE`, `Today_Case`) VALUES ($case_id, '$URL', $News_ID, 0, 1)";
                    } else {
                        $query = "INSERT INTO `Udn_News_URL`(`Case_ID`, `URL`, `News_ID`, `DONE`, `Today_Case`) VALUES ($case_id, '$URL', $News_ID, 0, 0)";
                    }
                    $result = $this->mysqli->query($query) or die($this->mysqli->error . __LINE__);
                    //=====登出資料庫
                    $this->DB_unlink();
                    $count++;
                    if ($count == $this_page_news_count) { // 爬完這頁了!!
                        break;
                    }
                }
                //===找尋頭一則新聞的link
                if ($IS_LAST_PAGE == FALSE) {
                    // 不是最後標題頁
                    if ($a_dom->nodeValue == "下一頁") { // 不是最後一頁, 都會有下一頁的link
                        $find_head = 1; // 下一個就是頭一則新聞的link了!!
                    }
                } else {
                    // 是最後標題頁
                    if ($a_dom->nodeValue == "最末頁") { // 是最後一頁
                        $find_head = 1; // 下一個就是頭一則新聞的link了!!
                    }
                }
            }
        }
    }

//===========================
//=====將使用者的查詢存進資料庫
    public function query_saver() {
// 確認POST有無問題
//$i = 0;
//$Ary = & $_POST;
        /* foreach ($Ary as $AryKey[$i] => $a[$i]) {
          //印出陣列索引號$i,POST欄位名稱,POST的值
          echo $i . " " . $AryKey[$i] . "=>" . $a[$i] . "<br>";
          $i++;
          } */
// 第一個關鍵字(必須)
        $this->SearchString = $_POST['SearchString'];
// 第二個關鍵字
        $this->operator_2 = $_POST['operator_2']; // + and, / or, - not
        $this->SearchString_2 = $_POST['SearchString_2'];
// 第三個關鍵字
        $this->operator_3 = $_POST['operator_3'];
        $this->SearchString_3 = $_POST['SearchString_3'];
// 第四個關鍵字
        $this->operator_4 = $_POST['operator_4'];
        $this->SearchString_4 = $_POST['SearchString_4'];
// 查詢日期範圍
        if ($_POST['date'] == "00") { // 選擇一，選從今天往後多久以前
            $this->ago = $_POST['ago']; // 起始日期~結束日期的天數(days)
            $this->end = date('Ymd'); // 結束日期(今天), 例如2015年6月30日 → 20150630
            $this->start = date('Ymd', strtotime("-" . $this->ago . " day"));
        } else if ($_POST['date'] == "99") {
            $this->end = $_POST['end'];
            $this->start = $_POST['start'];
        }
// 查詢"國內"報紙
        $this->united = $_POST['united'];        //"聯合報";
        $this->economic = $_POST['economic'];      //"經濟日報";
        $this->minsen = $_POST['minsen'];        //"民生報";
        $this->united_late = $_POST['united_late'];   //"聯合晚報";
        $this->star = $_POST['star'];          //"星報";
        $this->upaper = $_POST['upaper'];        //"Upaper";
// 查詢"國外"報紙
        $this->world = $_POST['world'];         //"世界日報";
        $this->europe = $_POST['europe'];        //"歐洲日報";
// ※沒登入政大ip不能查詢所有日期
        if ($_POST['ago'] == -1) {
// 如果選擇所有日期就把start, end變null
            $this->end = NULL;
            $this->start = NULL;
        }
// 把這次的query查詢存進資料庫
//==========連線
        $this->DB_link();
//==========儲存case
        $Query_Time = date('Y-m-d H:i:s');
        $User_Account = $_SESSION['session_id'];
        $query = "INSERT INTO `Udn_Case`(`User_Account`, `Query_Time`, `SearchString`, `SearchString_2`, `SearchString_3`, `SearchString_4`, `operator_2`, `operator_3`, `operator_4`, `end`, `start`, `united`, `economic`, `minsen`, `united_late`, `star`, `upaper`, `world`, `europe`) VALUES ('$User_Account','$Query_Time','$this->SearchString','$this->SearchString_2','$this->SearchString_3','$this->SearchString_4','$this->operator_2','$this->operator_3','$this->operator_4','$this->end','$this->start','$this->united','$this->economic','$this->minsen','$this->united_late','$this->star','$this->upaper','$this->world','$this->europe')";
//echo $query;
        $result = $this->mysqli->query($query) or die($this->mysqli->error . __LINE__);
//echo $result;
        $query = "SELECT `Case_ID` FROM `Udn_Case` WHERE `User_Account` = '$User_Account' AND `Query_Time` = '$Query_Time' AND `SearchString` = '$this->SearchString' AND `SearchString_2` = '$this->SearchString_2' AND `SearchString_3` = '$this->SearchString_3' AND `SearchString_4` = '$this->SearchString_4' AND `operator_2` = '$this->operator_2' AND `operator_3` = '$this->operator_3' AND `operator_4` = '$this->operator_4' AND `end` = '$this->end' AND `start` = '$this->start' AND `united` = '$this->united' AND `economic` = '$this->economic' AND `minsen` = '$this->minsen' AND `united_late` = '$this->united_late' AND `star` = '$this->star' AND `upaper` = '$this->upaper' AND `world` = '$this->world' AND `europe` = '$this->europe'";
        $result = $this->mysqli->query($query) or die($this->mysqli->error . __LINE__);
        $temp = $result->fetch_assoc();
//==========斷開連結
        $this->DB_unlink();
// 抓出這個case的總新聞數(有勾選的國內報系+國外報系新聞數和)
        $News_count_in = 0; //國內報系新聞則數
        $News_count_out = 0; //國外報系新聞則數
        // 有勾選的國內報系
        if ($this->united == 1 || $this->economic == 1 || $this->minsen == 1 || $this->united_late == 1 || $this->star == 1 || $this->upaper == 1) {
            $URL = $this->News_title_link_maker($temp['Case_ID'], 1, 0); // (要還原哪個CASE的URL, 國內報系第一頁的url, 非國外報系) 回傳url
            // 爬資料!!
            //echo $URL . "</br>";
            $News_count_in = $News_count_in + $this->find_news_count($URL);
        }
        // 有勾選的國外報系
        if ($this->world == 1 || $this->europe == 1) {
            $URL_out = $this->News_title_link_maker($temp['Case_ID'], 1, 1); // (要還原哪個CASE的URL, 國外報系第一頁, 國外報系) 回傳url
            // 爬資料!!
            //echo $URL_out . "</br>";
            $News_count_out = $News_count_out + $this->find_news_count($URL_out);
        }
        $News_count_sum = $News_count_in + $News_count_out; // 該case的全部新聞總數
//==========連線
        $this->DB_link();
        //==========更新這個case的總新聞數
        $case_id = $temp['Case_ID']; // case_id
        $query = "UPDATE `Udn_Case` SET `News_count` = $News_count_sum WHERE `Case_ID` = $case_id";
        $result = $this->mysqli->query($query) or die($this->mysqli->error . __LINE__);
        //echo $result;
//==========斷開連結
        $this->DB_unlink();
//==========更新這個case的所有新聞Udn_News_URL
        // 有勾選的國內報系
        if ($this->united == 1 || $this->economic == 1 || $this->minsen == 1 || $this->united_late == 1 || $this->star == 1 || $this->upaper == 1) {
            if ($News_count_in > 0) { // 至少找到一篇新聞
                $page_in = (int) ($News_count_in / 50) + 1;
                //=====登入udn
                $this->udn_login();
                for ($p = 1; $p <= $page_in; $p++) { // 一頁一頁抓
                    $URL = $this->News_title_link_maker($case_id, $p, 0); // (要還原哪個CASE的URL, 國內報系標題頁的url, 非國外報系) 回傳url
                    if ($p == $page_in) { // 最後一頁標題頁
                        if ($this->end == date('Ymd') && $this->start == date('Ymd')) {// 搜尋範圍是今天的case
                            $this->News_URL_saver($case_id, $URL, TRUE, $News_count_in, TRUE);
                            // (哪個case, 要爬的標題頁url, 這是不是最後一頁標題頁, 國內新聞總數, 是不是今天的case) 存進資料庫
                        } else {
                            $this->News_URL_saver($case_id, $URL, TRUE, $News_count_in, FALSE);
                        }
                    } else {
                        if ($this->end == date('Ymd') && $this->start == date('Ymd')) {// 搜尋範圍是今天的case
                            $this->News_URL_saver($case_id, $URL, FALSE, $News_count_in, TRUE);
                            // (哪個case, 要爬的標題頁url, 這是不是最後一頁標題頁, 國內新聞總數, 是不是今天的case) 存進資料庫
                        } else {
                            $this->News_URL_saver($case_id, $URL, FALSE, $News_count_in, FALSE);
                        }
                    }
                    sleep(rand(1, 4)); // 每頁暫停1~4秒
                }
                //=====登出udn
                $this->udn_logout();
                //echo $page_in;
            }
        }
        // 有勾選的國外報系
        if ($this->world == 1 || $this->europe == 1) {
            if ($News_count_out > 0) { // 至少找到一篇新聞
                $page_out = (int) ($News_count_out / 50) + 1;
                //=====登入udn
                $this->udn_login();
                for ($p = 1; $p <= $page_out; $p++) { // 一頁一頁抓
                    $URL_out = $this->News_title_link_maker($case_id, $p, 1); // (要還原哪個CASE的URL, 國外報系標題頁的url, 國外報系) 回傳url
                    if ($p == $page_out) { // 最後一頁標題頁
                        if ($this->end == date('Ymd') && $this->start == date('Ymd')) {// 搜尋範圍是今天的case
                            $this->News_URL_saver($case_id, $URL_out, TRUE, $News_count_out, TRUE);
                            // (哪個case, 要爬的標題頁url, 這是不是最後一頁標題頁, 國外新聞總數, 是不是今天的case) 存進資料庫
                        } else {
                            $this->News_URL_saver($case_id, $URL_out, TRUE, $News_count_out, FALSE);
                        }
                    } else {
                        if ($this->end == date('Ymd') && $this->start == date('Ymd')) {// 搜尋範圍是今天的case
                            $this->News_URL_saver($case_id, $URL_out, FALSE, $News_count_out, TRUE);
                            // (哪個case, 要爬的標題頁url, 這是不是最後一頁標題頁, 國外新聞總數, 是不是今天的case) 存進資料庫
                        } else {
                            $this->News_URL_saver($case_id, $URL_out, FALSE, $News_count_out, FALSE);
                        }
                    }
                    sleep(rand(1, 4)); // 每頁暫停1~4秒
                }
                //=====登出udn
                $this->udn_logout();
                //echo $page_out;
            }
        }
    }

}

// test
//$c = new Crawler;
//===測試
//$c->News_URL_saver(1, "http://udndata.com/ndapp/Searchdec2007?udndbid=udndata&page=1&SearchString=%AC%5F%A4%E5%AD%F5%2B%A4%E9%B4%C1%3E%3D20150803%2B%A4%E9%B4%C1%3C%3D20150803&sharepage=50&select=1&kind=2", 1, 7);
?>