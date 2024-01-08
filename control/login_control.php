<?php
require('../model/model.php');
header("Content-Type: application/json");
$act=$_REQUEST['act'];

switch ($act) {
    case "login":
        // 接收 POST 請求中的帳號和密碼
        $acc = isset($_POST['acc']) ? $_POST['acc'] : '';
        $pwd = isset($_POST['pwd']) ? $_POST['pwd'] : '';

        // 這裡只是一個簡單的例子，總是回傳成功的訊息
        $response = login($acc,$pwd);

        echo json_encode($response);
        return;
    
    case "newAccount":
        $role = $_REQUEST['role'];
        
        if($role === '0'){
            $acc = str_replace('"', '',$_POST['acc']);
            $pwd = str_replace('"', '',$_POST['pwd']);
            $ClinetName = str_replace('"', '', $_POST['name']);
            $ClinetAddress = str_replace('"', '', $_POST['addr']);
            $result = registerUser_client($acc,$pwd,$ClinetName,$ClinetAddress);

            echo $result;

        }elseif($role==='1'){
            $acc = str_replace('"', '',$_POST['acc']);
            $pwd = str_replace('"', '',$_POST['pwd']);
            $ShopName = str_replace('"', '', $_POST['name']);
            $result = registerUser_shop($acc,$pwd,$ShopName);

            echo $result;
        }
        return;
}
?>
