<?php
  require_once('conn.php');
  require_once('utils.php');
  
  $username = $_POST['username'];
  $role_id = $_POST['role_id'];

  $row_user = getUserFromUsername($username);
  
  $sql = sprintf(
    "update naomi_users_roles
    set role_id=?
    where user_id=?",
    $role_id,
    $row_user['user_id']
  );
  $stmt = $conn->prepare($sql);
  $stmt->bind_param('ii', $role_id, $row_user['user_id']);
  $result = $stmt->execute();
  if (!$result) {
    header('Location: roles.php?errCode=1');
    die($conn->error);
  }
?>