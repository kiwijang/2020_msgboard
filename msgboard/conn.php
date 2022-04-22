<?php
  $server_name = '';
  $username = '';
  $password = '';
  $db_name = '';

  $conn = new mysqli($server_name, $username, $password, $db_name);

  if ($conn->connect_error) {
    die('資料庫連線錯誤:' . $conn->connect_error);
  }
  $conn->query('SET NAMES UTF8'); //設定 query 編碼
  $conn->query('SET time_zone = "+8:00"'); //設定台灣時區
?>