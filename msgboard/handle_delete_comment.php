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

// 檢查有無 id
$comment_id = $_GET['id'];
if (empty($_GET['id'])) {
    header("Location: index.php");
    exit();
}

// 檢查要刪的 comment_id 存不存在，避免其他頁面已刪，使用者還可打開編輯
$comment_row = getCommentByCommentid($comment_id);
if (empty($comment_row)) {
    header("Location: index.php?errCode=9");
    exit();
}

// 授權 feature_ids
$features = $_SESSION['features'];
if (empty($_SESSION['features'])) {
  header("Location: login.php");
}
// 拿目前登入者資料
$user_row = getUserFromUsername($username);
$user_id = $user_row['user_id'];
$delete_other_comment = isFeaturesHasFeatureid(4, $features);
$delete_own_comment = isFeaturesHasFeatureid(2, $features);

// 這個 user_id 可以 delete_other_comment
if ($delete_other_comment == 1) {
    deleteComment($comment_id);
    header("Location: index.php?delete=yap");
    exit();
}
// 這個 user_id 可以 delete_own_comment
if ($delete_own_comment == 1) {
    // 找這個 user_id 是否有這個 comment_id，
    $sql = sprintf("select * from naomi_comments where comment_id=? and user_id=?", $comment_id, $user_id);
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $comment_id, $user_id);
    $result = $stmt->execute();
    if (!$result) {
        die('Error:' . $conn->error);
    }
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    // 有的話才能刪除
    if (!empty($row)) {
        deleteComment($comment_id);
        header("Location: index.php?delete=yap");
    } else {
        echo "這個 id 對你來說不存在喔 很猛=_=";
    }
}

function deleteComment($comment_id)
{
    global $conn;
    $sql = sprintf(
        "delete from naomi_comments
        where comment_id=?",
        $comment_id
    );
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $comment_id);
    $result = $stmt->execute();
    if (!$result) {
        die($conn->error);
    }
}
