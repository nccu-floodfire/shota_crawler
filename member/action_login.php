<?php
/* 
驗證會員的檔案
 */
    require('config.php');
 
    $Account = $_POST['Account'];
    $Password = $_POST['Password'];
 
    if ( empty($Account) || empty($Password) )
    {
        header('Location:http://140.119.164.218/~shota/udn_crawler/member/login.php');
    }else{
        
    }
?>