<?php
require('dbconfig.php');

//物流功能
//已寄送
function deal_Deliver(){
	global $db;
	$sql = "select * from orders where orderStatus=3;";
	$stmt = mysqli_prepare($db, $sql);
	mysqli_stmt_execute($stmt); 
	$result = mysqli_stmt_get_result($stmt); 
	$rows = array();
	while($r = mysqli_fetch_assoc($result)) {
		$rows[] = $r; 
	}
	return $rows;
}
function check_Deliver($orderID){ 
	global $db;
	$sql = "update orders set orderStatus=4 where orderID=?;";
	$stmt = mysqli_prepare($db, $sql); 
    mysqli_stmt_bind_param($stmt, "i", $orderID);
	mysqli_stmt_execute($stmt); 
}

//已送達
function deal_ready(){
	global $db;
	$sql = "select * from orders where orderStatus=4;"; //評價不為空
	$stmt = mysqli_prepare($db, $sql);
	mysqli_stmt_execute($stmt); 
	$result = mysqli_stmt_get_result($stmt);
	$rows = array();
	while($r = mysqli_fetch_assoc($result)) {
		$rows[] = $r; 
	}
	return $rows;
}
function check_ready($orderID){ 
	global $db;
	$sql = "update orders set status=5 where orderID=?;";
	$stmt = mysqli_prepare($db, $sql); 
    mysqli_stmt_bind_param($stmt, "i", $orderID);
	mysqli_stmt_execute($stmt); 
}

//註冊帳號
function registerUser($account, $password, $role) {
    global $db;
    
    if(isExist($account)){ //檢查重複註冊
        return false;
    }
    
    $newpassword = password_hash($password, PASSWORD_DEFAULT);
    $sql = "insert into users (account, password, role) values (?, ?, ?)";
    $stmt = mysqli_prepare($db, $sql);
    mysqli_stmt_bind_param($stmt, "ssi", $account, $newpassword, $role);
    mysqli_stmt_execute($stmt);

    return true;  // 註冊成功
    
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

//登入帳號
function login($account, $password) {
    global $db;

    $sql="select * from users where account=?";
    $stmt = mysqli_prepare($db, $sql);
    mysqli_stmt_bind_param($stmt,'s',$account);
    mysqli_stmt_execute($stmt);
    $result=mysqli_stmt_get_result($stmt);
    if ($row=mysqli_fetch_assoc($result)) { 
        if (password_verify($password,$row['password']))//驗證密碼
            $msg = "Welcome!";
        else
            $msg = "Wrong Password!";
    }
    else
      $msg = "Login fail!";
    return $msg;
}

?>