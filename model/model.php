<?php
require('dbconfig.php');

function login($acc, $pwd) {
    global $db;

    $sql = "select * FROM user WHERE account=? and password=?";
    $stmt = mysqli_prepare($db, $sql);
    mysqli_stmt_bind_param($stmt, 'ss', $acc,$pwd);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    $row = mysqli_fetch_assoc($result);
    if ($row == null){
        return -1;
    }else{
        return $row;
    }
}

// 確認有哪些訂單
function confirm_OrderList(){
	global $db;
	$sql = "select * from orders,clients,shops where orders.clientID=clients.clientID and orders.shopID = shops.shopID;";
	$stmt = mysqli_prepare($db, $sql); 
	mysqli_stmt_execute($stmt); //執行SQL
	$result = mysqli_stmt_get_result($stmt); //取得查詢結果

	$rows = array();
	while($r = mysqli_fetch_assoc($result)) {
		$rows[] = $r; //將此筆資料新增到陣列中
	}
	return $rows;
}

function registerUser($account, $password, $role) {
    global $db;
    $sql = "insert into user (account, password, role) values (?, ?, ?)";
    $stmt = mysqli_prepare($db, $sql);
    mysqli_stmt_bind_param($stmt, "ssi", $account, $password, $role);
    mysqli_stmt_execute($stmt);
}

function registerUser_client($account,$password,$ClinetName,$ClinetAddress){
    global $db;
    registerUser($account, $password, 0); // 將帳號密碼資料加入 user 資料庫
    $userID = mysqli_insert_id($db);
    
    // 將對應用戶加入clients資料庫中
    $sql = "insert into clients (`userID`, `clientName`, `clientAddress`) values (?, ?, ?)";
    $stmt = mysqli_prepare($db, $sql);
    mysqli_stmt_bind_param($stmt, "iss", $userID, $ClinetName, $ClinetAddress);
    mysqli_stmt_execute($stmt);
    return "finish";
}

// 新商家註冊
function registerUser_shop($account,$password,$ShopName){
    global $db;
    registerUser($account, $password, 1); // 將帳號密碼資料加入 user 資料庫
    $userID = mysqli_insert_id($db);
    $shopStar = 0;
    // 將對應用戶加入clients資料庫中
    $sql = "insert into shops (`userID`, `shopName`,`shopStar`) values (?, ?, ?)";
    $stmt = mysqli_prepare($db, $sql);
    mysqli_stmt_bind_param($stmt, "isi", $userID, $ShopName, $shopStar);
    mysqli_stmt_execute($stmt);
    return "finish";
}

function isExist($account) {
    global $db;
    
    $sql = "SELECT * FROM users WHERE account = ?";
    $stmt = mysqli_prepare($db, $sql);
    mysqli_stmt_bind_param($stmt, "s", $account);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    return mysqli_num_rows($result) > 0;
}
?>