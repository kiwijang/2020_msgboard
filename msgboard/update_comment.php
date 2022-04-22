<?php
session_set_cookie_params(0, "/mtr04group1/naomi/hw1/");
session_start();
require_once "conn.php";
require_once "utils.php";

if (empty($_GET['id'])) {
    header("Location: index.php");
    exit();
}
$comment_id = $_GET['id'];

// 檢查要編輯的 comment_id 存不存在，避免其他頁面已刪，使用者還可打開編輯
$comment_row = getCommentByCommentid($comment_id);
if (empty($comment_row)) {
    header("Location: index.php?errCode=9");
    exit();
}

// 如果 session 裡面有 username
$username = $_SESSION['username'];
if (empty($username)) {
    header('Location: index.php?errCode=10');
    die('沒有 username 請先登入');
}

// 授權 feature_ids
$features = $_SESSION['features'];
if (empty($_SESSION['features'])) {
    header("Location: login.php");
}

$modify_own_comment = isFeaturesHasFeatureid(3, $features);
$modify_other_comment = isFeaturesHasFeatureid(5, $features);
$user_row = getUserFromUsername($username);
$user_id = $user_row['user_id'];

// 如果能 modify_own_comment，只顯示自己的
if ($modify_own_comment == 1 && $modify_other_comment == 0) {
    // 找這個 user_id 是否有這個 comment_id，
    $row = getCommentByCommentidUserid($comment_id, $user_id);
    // 沒有的話就離開
    if (empty($row)) {
        header("Location: index.php?errCode=4");
        exit();
    }
}
// 這個 user_id 可以 modify_other_comment，顯示所有訊息
else if ($modify_other_comment == 1) {
    $row = getCommentByCommentid($comment_id);
}
?>

<!DOCTYPE html>
<html lang="zh-tw">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>留言板</title>
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
          編輯留言
        </h1>
        <div class="btnwrap">
          <p>你好，<?php echo escape($username) ?></p>
          <a class="btnwrap__btn" href="index.php">返回留言板</a>
        </div>
      </div>
      <div class="bottom">
        <?php
if (!empty($_GET["errCode"])) {
    $code = $_GET["errCode"];
    $msg = "Error";
    if ($code === "1") {
        $msg = "資料不齊全!!!";
    }
    echo $code;
    echo "<h3 class='red'>" . $msg . "</h3>";
}
?>
        <form method="POST" action="./handle_update_comment.php">
          <textarea
            cols="30"
            rows="10"
            name="content"
          ><?php echo $row['content'] ?></textarea>
          <input type="hidden" name="id" value="<?php echo $row['comment_id'] ?>">
          <button type="submit">送出</button>
        </form>
      </div>
    </div>
  </body>
</html>