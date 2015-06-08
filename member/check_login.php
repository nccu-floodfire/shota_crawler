<?php
/*
檢查會員是否已登入
 */
   session_start(); // 使用session
   header("Content-Type:text/html; charset=utf-8"); // 指定編碼
   
   // 檢查是否有登入的cookie
   if ( !isset( $_SESSION['session_id'] ) ) // 代表尚未登入
   {
       // 轉跳回原登入頁面
       header('Location:http://140.119.164.218/~shota/udn_crawler/member/login.php');
   }
?>
