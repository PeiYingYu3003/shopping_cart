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
?>