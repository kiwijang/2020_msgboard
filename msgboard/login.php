<?php
  // 防止重複登入
  session_start();
  if (!empty($_SESSION['username'])) {
    header('Location: index.php');
    exit();
  }
?>

<!DOCTYPE html>
<html lang="zh-tw">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>登入</title>
    <link rel="stylesheet" href="./style.css" />
    <script src="./script.js"></script>
  </head>
  <body>
    <!-- <header>
      注意!本站為練習用網站，因教學用途刻意忽略資安的實作，註冊時請勿使用任何真實的帳號或密碼。
    </header> -->
    <div class="container">
      <div class="top">
        <h1 class="title">
          請登入
        </h1>
        <div class="btnwrap">
          <a class="btnwrap__btn" href="index.php">回留言板</a>
          <a class="btnwrap__btn registerbtn" href="register.php">註冊</a>
        </div>
      </div>
      <div class="bottom">
        <?php 
          $msg = NULL;
          if (!empty($_GET["errCode"])) {
            $code = $_GET["errCode"];
            $msg = "Error";
            if ($code === "1") {
              $msg = "資料不齊全";
            } else if ($code === "2") {
              $msg = "帳號或密碼輸入錯誤";
            }
            echo "<h3 class='red'>" . $msg . "</h3>";
          }
          if(isset($_GET["registerCode"]) && $_GET["registerCode"]  === "0") {
            $msg = "註冊成功，請登入!";
            echo "<h3 class='red'>" . $msg . "</h3>";
          }
        ?>
        <form method="POST" action="./handle_login.php">   
          <h3>帳號<span class="red"> *</span></h3>
          <input type="text" name="username"/> 
          <h3>密碼<span class="red"> *</span></h3>
          <input type="password" name="password"/>
          <button type="submit">提交</button>
        </form>
      </div>
    </div>
  </body>
</html>
