<?php
  session_set_cookie_params(0, "/mtr04group1/naomi/hw1/");
  session_start();
  require_once("conn.php");
  require_once("utils.php");

  
  $username = $_SESSION['username'];
  if(empty($username)) {
    header('Location: index.php?errCode=10');
    die('沒有 username 請先登入');
  }
  
  // 授權
  $features = $_SESSION['features'];
  if(empty($features)) {
    header('Location: index.php?errCode=777');
    die('您沒有使用權限');
  }

  if (empty($_POST['content'])) {
    header('Location: index.php?errCode=1');
    die('請輸入 content');
  }

  $feature_id = 1;
  if(isFeaturesHasFeatureid($feature_id, $features) == 0){
    header('Location: index.php?errCode=555');
    die('沒有新增留言的權限');
  }

  // username 是從 session 裡拿來的
  $user = getUserFromUsername($username);
  if (!empty($username)) {  
    $nickname = $user['nickname'];
    $id = $user['user_id'];
  }

  // log
  $origin = getRealIpAddr();
  $content = $_POST['content'];
  $sql = sprintf(
    "insert into naomi_comments(user_id,content,origin)
    values(?,?,?)",
    $id,
    $content,
    $origin
  );
  $stmt = $conn->prepare($sql);
  $stmt->bind_param('sss', $id, $content, $origin );
  $result = $stmt->execute();
  if (!$result) {
    die($conn->error);
  }

  header("Location: index.php?add=yap");
?>