<!DOCTYPE html>
<!--
輸入帳號和密碼的網頁
-->
<html>
    <head>
        <meta charset="UTF-8">
        <title>Login</title>
    </head>
    
    <body>
        <form action="action_login.php" method="POST">
        <!--
        當提交表單時，表單數據會提交到名為"actionlogin.php" 的頁面：
        -->
            <p>Account: <input type="text" name="Account" /></p>
            <p>Password: <input type="text" name="Password" /></p>
            <input type="submit" name="submit" value="Login" />
        </form>
    </body>
</html>
