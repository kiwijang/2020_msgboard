<?php
session_set_cookie_params(0, "/mtr04group1/naomi/hw1/");
session_start();
require_once "conn.php";
require_once "utils.php";

$comment_id = $_POST['id'];

if (empty($_POST['content'])) {
    header('Location: update_comment.php?errCode=1&id=' . $comment_id);
    die('請輸入 content');
}

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

// 拿目前登入者資料
$user_row = getUserFromUsername($username);
$user_id = $user_row['user_id'];
$modify_own_comment = isFeaturesHasFeatureid(3, $features);
$modify_other_comment = isFeaturesHasFeatureid(5, $features);

// 這個 user_id 可以 modify_other_comment
if ($modify_other_comment == 1) {
    updateComment($comment_id);
    header("Location: index.php?update=yap");
    exit();
}
// 這個 user_id 可以 modify_own_comment
if ($modify_own_comment == 1) {
    // 找這個 user_id 是否有這個 comment_id，
    $row = getCommentByCommentidUserid($comment_id, $user_id);
    // 有的話才能改
    if (empty($row)) {
        echo "這個 id 對你來說不存在喔 很猛=_=";
    }
    updateComment($comment_id);
    header("Location: index.php?update=yap");
}

function updateComment($comment_id)
{
    global $conn;
    // log
    $origin = getRealIpAddr();
    $content = $_POST['content'];
    $sql = sprintf(
        "update naomi_comments
      set content=?, origin=?
      where comment_id=?",
        $content,
        $origin,
        $comment_id
    );
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('ssi', $content, $origin, $comment_id);
    $result = $stmt->execute();
    if (!$result) {
        die($conn->error);
    }
}
