<?php
  require_once('conn.php');
  require_once('utils.php');

  if (
    empty($_POST['nickname']) ||
    empty($_POST['username']) ||
    empty($_POST['password'])  
  ) {
    header('Location: register.php?errCode=1');
    die();
  }

  $nickname = $_POST['nickname'];
  $username = $_POST['username'];
  $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

  $sql = sprintf(
    "insert into naomi_users(nickname, username, password)
    values(?, ?, ?)",
    $nickname,
    $username,
    $password
  );
  $stmt = $conn->prepare($sql);
  $stmt->bind_param('sss',
                    $nickname,
                    $username,
                    $password);
  $result = $stmt->execute();
  if (!$result) {
    // mysql 定義的 error code // duplicate username
    $code = $conn->errno;
    if ($code === 1062) {
      header("Location: register.php?errCode=2");
    }
    die($conn->error);
  }

  $row_user = getUserFromUsername($username);
  
  $sql = sprintf(
    "insert into naomi_users_roles(role_id, user_id)
    values(2, ?)",
    $row_user['user_id']
  );
  $stmt = $conn->prepare($sql);
  $stmt->bind_param('i', $row_user['user_id']);
  $result = $stmt->execute();
  if (!$result) {
    die($conn->error);
  }

  header("Location: login.php?registerCode=0");
?>