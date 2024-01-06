<?php
require('dbconfig.php');

//���y�\��
//�w�H�e
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

//�w�e�F
function deal_ready(){
	global $db;
	$sql = "select * from orders where orderStatus=4;"; //����������
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

//���U�b��
function registerUser($account, $password, $role) {
    global $db;
    
    if(isExist($account)){ //�ˬd���Ƶ��U
        return false;
    }
    
    $newpassword = password_hash($password, PASSWORD_DEFAULT);
    $sql = "insert into users (account, password, role) values (?, ?, ?)";
    $stmt = mysqli_prepare($db, $sql);
    mysqli_stmt_bind_param($stmt, "ssi", $account, $newpassword, $role);
    mysqli_stmt_execute($stmt);

    return true;  // ���U���\
    
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

//�n�J�b��
function login($account, $password) {
    global $db;

    $sql="select * from users where account=?";
    $stmt = mysqli_prepare($db, $sql);
    mysqli_stmt_bind_param($stmt,'s',$account);
    mysqli_stmt_execute($stmt);
    $result=mysqli_stmt_get_result($stmt);
    if ($row=mysqli_fetch_assoc($result)) { 
        if (password_verify($password,$row['password']))//���ұK�X
            $msg = "Welcome!";
        else
            $msg = "Wrong Password!";
    }
    else
      $msg = "Login fail!";
    return $msg;
}

?>