<?php
  session_set_cookie_params(0, "/mtr04group1/naomi/hw1/");
  session_start();
  require_once("conn.php");
  require_once("utils.php");
  /*
    1. 從 cookie 裡面讀取 PHPSESSION(token)
    2. 從檔案裡面讀取 session id 的內容
    3. 放到 $_SESSION
  */
  $username = NULL;
  $user_row = NULL;  
  $user_nickname =NULL;
  $user_id = NULL;
  $features = NULL;
  $modify_other_comment = NULL;
  $delete_other_comment = NULL;
  $add_own_comment = NULL;

  // 如果已經有登入才給值
  if (!empty($_SESSION['features']) && !empty($_SESSION['username'])) {
    $username = $_SESSION['username'];
    $user_row = getUserFromUsername($username); 
    
    $user_nickname = $user_row['nickname'];
    $user_id = $user_row['user_id'];
    // 授權 feature_ids
    $features = $_SESSION['features'];
    $modify_other_comment = isFeaturesHasFeatureid(5, $features);
    $delete_other_comment = isFeaturesHasFeatureid(4, $features);
    $add_own_comment = isFeaturesHasFeatureid(1, $features);
  } else {
    session_start();
    session_destroy();
  }

  $page = 1;
  if (isset($_GET["page"]) && !empty($_GET["page"])) {
    $page = intval($_GET["page"]);
  }
  if (isset($_GET["page"]) && intval($_GET["page"]) < 1 && !isset($_GET["errCode"])) {
    header("Location: index.php?page=1");
  }
  $per_page = 5;
  $offset = ($page - 1) * $per_page;

  $stmt = $conn->prepare(
    "select * from naomi_users as users 
    right join naomi_comments as com 
    on users.user_id = com.user_id 
    order by com.created_at desc
    limit ? offset ?");
  $stmt->bind_param('ii', $per_page, $offset);
  $result = $stmt->execute();
  if(!$result) {
    die('Error:' . $conn->error);
  }
  $result = $stmt->get_result();
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
          留言區
        </h1>
        <div class="btnwrap">
        <?php if (!$username) { ?>
          <a class="btnwrap__btn registerbtn" href="register.php">註冊</a>
          <a class="btnwrap__btn loginbtn" href="login.php">登入</a>
        <?php } else { ?>
          <p>你好，<?php echo escape($user_nickname) ?>，歡迎留言~&nbsp;&nbsp;&nbsp;</p>
          <a class="btnwrap__btn" href="logout.php">會員登出</a>
        <?php } ?>
        <?php if (isRoleAdminByUserid($user_id)) { ?>
          <a class="btnwrap__btn" href="roles.php" style="background-color: pink;">會員管理</a>
        <?php } ?>
        </div>
      </div>
      <div class="bottom">
        <?php 
          if (empty($_SESSION['features']) || empty($user_row) || empty($_SESSION['username'])) {
            echo "<h3 class='red'>歡迎註冊會員~~~</h3>";
          }
          if (!empty($_GET["errCode"])) {
            $code = $_GET["errCode"];
            $msg = "Error";
            if ($code === "1") {
              $msg = "資料不齊全";
            }
            if ($code === "555") {
              $msg = "你沒有新增留言的權限!!";
            }
            if ($code === "777") {
              $msg = "你沒有使用權限!!";
            }
            if ($code === "4") {
              $msg = "只能編輯自己的留言!!!";
            }
            if ($code === "9") {
              $msg = "這則留言已經不存在了喔，已幫您取得最新留言狀態~";
            }
            if ($code === "10") {
              $msg = "沒有 username 請先登入";
            }
            echo "<h3 class='red'>" . $msg . "</h3>";
          }
          if(!empty($_GET["update"]) && $_GET["update"] === "yap") {
            echo "<h3 class='red'>修改成功!!!</h3>";
          }
          if(!empty($_GET["delete"]) && $_GET["delete"] === "yap") {
            echo "<h3 class='red'>刪除成功!!!</h3>";
          }
          if(!empty($_GET["add"]) && $_GET["add"] === "yap") {
            echo "<h3 class='red'>新增成功!!!</h3>";
          }
          if($username && $add_own_comment == 0) {
            echo "<h3 class='red'>你沒有新增留言的權限。</h3>";
          }
        ?>
        <?php if ($username) { ?>
          <form method="POST" action="./handle_add_post.php">
            <h3>有什麼想說的嗎?<span class="red"> *</span></h3>
            <textarea
              cols="30"
              rows="10"
              placeholder="請輸入你的留言..."
              name="content"
              <?php 
                if($add_own_comment == 0) {
                  echo "disabled";
                }
              ?>
            ></textarea>
            <button type="submit" 
                    <?php if($add_own_comment == 0) {
                      echo "disabled";
                    }?>
            >送出</button>
          </form>
        <?php } else { ?>          
          <h3 class="red">登入才可以發布留言</h3>
        <?php } ?>

        <section class="comments">
          <?php
            while($row = $result->fetch_assoc()) {
          ?>
            <div class="comment">
              <div class="comment__userphoto"><?php                
                  preg_match_all('/./u', $row['nickname'], $matches);
                  echo $matches[0][0];
                ?></div>
              <div class="comment__wrap">
                <div class="comment__wrap__userinfo">
                  <span class="comment__wrap__userinfo__name">
                    <?php echo escape($row['nickname']) ?>
                    (@<?php echo escape($row['username']) ?>)
                  </span>
                  ·
                  <span class="comment__wrap__userinfo__date"> 
                    <?php echo escape($row['created_at']) ?>
                  </span>
                  <!-- 如果留言的username===登入者的username才可編輯刪除 -->
                  <?php if($row['username'] === $username && $modify_other_comment == 0 && $delete_other_comment == 0) {?>
                    <a href="update_comment.php?id=<?php echo escape($row['comment_id']) ?>">編輯</a>
                    <a href="handle_delete_comment.php?id=<?php echo escape($row['comment_id']) ?>">刪除</a>
                  <?php }?>
                  <?php if($modify_other_comment == 1) {?>
                    <a href="update_comment.php?id=<?php echo escape($row['comment_id']) ?>">編輯</a>
                  <?php }?>
                  <?php if($delete_other_comment == 1) {?>
                    <a href="handle_delete_comment.php?id=<?php echo escape($row['comment_id']) ?>">刪除</a>
                  <?php }?>
                </div>
                <div class="comment__wrap__content"><?php echo escape($row['content']) ?>
                </div>
              </div>
            </div>
          <?php } ?>
        </section>
        <?php        
          // 留言頁數
          $stmt = $conn->prepare("select count(comment_id) as count from naomi_comments");
          $result = $stmt->execute();
          $result = $stmt->get_result();
          $row = $result->fetch_assoc();
          $count = $row['count'];
          // 總頁數
          $total_page = ceil($count / $per_page);

          if (isset($_GET["page"]) && intval($_GET["page"]) > $total_page) {
            header("Location: index.php?page=$total_page");
          }
        ?>
        <hr>
        <div class="pagewrap">
          <div>
            <?php if ($page != 1) { ?>
              <a href="index.php?page=1">首頁</a>
              <a href="index.php?page=<?php echo $page - 1 ?>">上一頁</a>
            <?php } ?>
            <?php if ($page != $total_page) { ?>
              <a href="index.php?page=<?php echo $page + 1 ?>">下一頁</a>
              <a href="index.php?page=<?php echo $total_page ?>">末頁</a>
            <?php } ?>
          </div>
          <span class="pagewrap__pageinfo">總共有 <?php echo $count ?> 筆資料，目前頁數 <?php echo $page ?> / <?php echo $total_page ?>。</span>
        </div>
      </div>
    </div>
  </body>
</html>
