<?php
require('../model/model.php');
header("Content-Type: application/json");

// 接收 POST 請求中的帳號和密碼
$acc = isset($_POST['acc']) ? $_POST['acc'] : '';
$pwd = isset($_POST['pwd']) ? $_POST['pwd'] : '';

// 這裡只是一個簡單的例子，總是回傳成功的訊息
$response = login($acc,$pwd);

echo json_encode($response);
?>
