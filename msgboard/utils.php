<?php
require_once "conn.php";

function getUserFromUsername($username)
{
    global $conn;
    $sql = sprintf(
        "select * from naomi_users where username = ?",
        $username
    );
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('s', $username);
    $result = $stmt->execute();
    if (!$result) {
        die($conn->error);
    }
    // Gets a result set from a prepared statement 拿到 sql 結果
    $result = $stmt->get_result();
    // Fetch a result row as an associative array 將 sql 結果轉成 php 物件
    $row = $result->fetch_assoc();
    return $row; // username, id, nickname
}

function escape($str)
{
    return htmlspecialchars($str, ENT_QUOTES);
}

// 來源: http://itman.in/en/how-to-get-client-ip-address-in-php/
function getRealIpAddr()
{
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) //check ip from share internet
    {
        $ip = $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) //to check ip is pass from proxy
    {
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
    } else {
        $ip = $_SERVER['REMOTE_ADDR'];
    }
    return $ip;
}

function isRoleAdminByUserid($user_id)
{
    global $conn;
    $sql = sprintf(
        "select * from naomi_users_roles where user_id=? and role_id=1",
        $user_id
    );
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('s', $user_id);
    $result = $stmt->execute();
    if (!$result) {
        die($conn->error);
    }
    $result = $stmt->get_result();
    $result = $result->fetch_assoc();
    return $result > 0;
}

function isFeaturesHasFeatureid($feature_id, $features)
{
    foreach ($features as $data) {
        if ($data["feature_id"] == $feature_id) {
            return 1;
        }
    }
    return 0;
}

function getAllUserFeatureidByUsername($username)
{
    global $conn;
    if (!$username) {
        return 3;
    }
    $row_user = getUserFromUsername($username);
    $user_id = $row_user['user_id'];
    // 從 feature 找 features
    $sql = sprintf(
        "select feature_id from naomi_roles_features as rf
      join naomi_roles as r
      on rf.role_id = r.role_id
      join naomi_users_roles as ur
      on r.role_id = ur.role_id and ur.user_id = ?",
        $user_id
    );
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $user_id);
    $result = $stmt->execute();
    if (!$result) {
        die($conn->error);
    }
    $result = $stmt->get_result();
    $result = $result->fetch_all(MYSQLI_ASSOC);
    return $result;
}

function getAdminUserData()
{
    global $conn;
    $sql = sprintf(
        "select username,u.user_id,nickname from naomi_users_roles as ur
      join naomi_users as u
      on ur.user_id=u.user_id and ur.role_id=1"
    );
    $result = $conn->query($sql);
    if (!$result) {
        die($conn->error);
    }
    return $result->fetch_all();
}
function getAllUsernameAndRoles()
{
    global $conn;
    $sql = sprintf(
        "select username,r.role_name
    from naomi_users as u
    join naomi_users_roles as ur
    on u.user_id = ur.user_id
    join naomi_roles as r
    on ur.role_id = r.role_id and ur.role_id <> 1"
    );
    $result = $conn->query($sql);
    if (!$result) {
        die($conn->error);
    }
    return $result->fetch_all();
}
function getAllRolesAndFeatures()
{
    global $conn;
    $sql = sprintf(
        "select r.role_id, r.role_name,
    group_concat(distinct f.feature_id),
    group_concat(distinct f.feature_name)
    from naomi_roles as r
    join naomi_roles_features as rf
    on r.role_id = rf.role_id
    join naomi_features as f
    on rf.feature_id = f.feature_id
    group by r.role_id"
    );
    $result = $conn->query($sql);
    if (!$result) {
        die($conn->error);
    }
    return $result->fetch_all();
}

function getCommentByCommentidUserid($comment_id, $user_id)
{
    global $conn;
    $sql = sprintf("select * from naomi_comments where comment_id=? and user_id=?", $comment_id, $user_id);
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $comment_id, $user_id);
    $result = $stmt->execute();
    if (!$result) {
        die('Error:' . $conn->error);
    }
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    return $row;
}

function getCommentByCommentid($comment_id)
{
    global $conn;
    $sql = sprintf("select * from naomi_comments where comment_id=?", $comment_id);
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $comment_id);
    $result = $stmt->execute();
    if (!$result) {
        die('Error:' . $conn->error);
    }
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    return $row;
}