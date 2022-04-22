<?php
session_set_cookie_params(0, "/mtr04group1/naomi/hw1/");
session_start();
require_once "conn.php";
require_once "utils.php";

$username = $_SESSION['username'];
if (empty($username)) {
    header('Location: index.php?errCode=10');
    die('沒有 username 請先登入');
}

$user_row = getUserFromUsername($username);
$user_nickname = $user_row['nickname'];
$user_id = $user_row['user_id'];
// 如果不是管理員就導回首頁
if (!isRoleAdminByUserid($user_id)) {
    header("Location: index.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="zh-tw">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>身份管理系統</title>
    <link rel="stylesheet" href="./style.css" />
    <script src="./script.js"></script>
  </head>
  <body>
    <div class="container">
      <div class="top">
        <h1 class="title">
          角色管理
        </h1>
        <div class="btnwrap">
          <p>你好，<?php echo escape($user_nickname) ?>~</p>
          <a class="btnwrap__btn" href="index.php">回留言板</a>
        </div>
      </div>
      <div class="bottom">
        <?php 
          if (!empty($_GET["errCode"])) {
            $code = $_GET["errCode"];
            $msg = "Error";
            if ($code === "1") {
              $msg = "修改失敗!!!";
            }
            echo "<h3 class='red'>" . $msg . "</h3>";
          }
          if(!empty($_GET["update"]) && $_GET["update"] === "yap") {
            echo "<h3 class='red'>修改成功!!!</h3>";
          }
        ?>
        <div>
          <h3 class="deco-h3">使用者對應角色</h3>
          <div class="wrapRoles">          
            <table class="roles">
              <thead>
              <tr>
                <th>username</th>
                <th>role</th>
              </tr>
              </thead>
              <tbody>          
                <?php
                  foreach (getAllUsernameAndRoles() as $idx=>$data) {
                      $u = escape($data["1"]) == "user" ? 'checked>':'>';
                      $l = escape($data["1"]) == "user_lock" ? 'checked>':'>';
                      
                      echo '<tr><td>' . escape($data["0"]) . '</td>' .
                      '<td>'.
                        '<input type="radio" name="role'. $idx.'" id="user_'. $data["0"] . '"'. $u .
                        '<label for="user_'. $data["0"] . '">user</label>'.
                        '<input type="radio" name="role'. $idx.'" id="userlock_'. $data["0"] . '"'. $l .
                        '<label for="userlock_'. $data["0"] . '">user_lock</label>'.
                      '</td></tr>';
                  }
                ?>
              </tbody>
            </table>
            <div class="red"><strong>※修改完成後，請記得按送出。</strong></div>
            <button class="submit-btn" disabled>送出</button>
          </div>

          <h3 class="deco-h3">管理員</h3>
          <table>
            <thead>
            <tr>
              <th>user_id</th>
              <th>username</th>
              <th>nickname</th>
            </tr>
            </thead>
            <tbody>
              <?php
                foreach (getAdminUserData() as $data) {
                    echo "<tr><td>" . escape($data["1"]) . "</td>" .
                    "<td>" . escape($data["0"]) . "</td>" .
                    "<td>" . escape($data["2"]) . "</td></tr>";
                }
              ?>
            </tbody>
          </table>

          <h3 class="deco-h3">角色對應功能</h3>
          <table>
            <thead>
            <tr>
              <th>role_id</th>
              <th>rolename</th>
              <th>feature_id</th>
              <th>featurename</th>
            </tr>
            </thead>
            <tbody>            
              <?php
                foreach (getAllRolesAndFeatures() as $idx=>$data) {
                  echo "<tr><td>" . escape($data["0"]) . "</td>" .
                  "<td>" . escape($data["1"]) . "</td>" .
                  "<td>" . escape($data["2"]) . "</td>" .
                  "<td>" . escape($data["3"]) . "</td></tr>";
                }
              ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </body>
</html>
