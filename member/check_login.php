<?php
/*
檢查會員是否已登入
 */
   header("Content-Type:text/html; charset=utf-8"); // 指定編碼
   
   printf( "%d<br>", !isset( $_COOKIE['cookie_id']));
   // 檢查是否有登入的cookie
   if ( !isset( $_COOKIE['cookie_id'] ) ) // 代表尚未登入
   {
       // 轉跳回原登入頁面
       //printf( "代表尚未登入代表尚未登入代表尚未登入<br>" );
       header('Location:http://140.119.164.218/~shota/udn_crawler/member/login.php');
   }
?>
