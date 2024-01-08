<?php
require('dbconfig.php');
// 抓客戶名稱
function findName($userID) {
    global $db;

    $sql = "select * FROM shops WHERE userID=?";
    $stmt = mysqli_prepare($db, $sql);
    mysqli_stmt_bind_param($stmt, 'i', $userID);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    $row = mysqli_fetch_assoc($result);
    if ($row != null){
        return $row;
    }else {
        // 如果未找到用戶，返回 JSON 格式的空數據
        return json_encode(array());
    }
}

//查看自己商品
function get_ShopList($shopID) {
	global $db;
	$sql = "select * from goods where shopID=?;";
	$stmt = mysqli_prepare($db, $sql ); //precompile sql指令，建立statement 物件，以便執行SQL
    mysqli_stmt_bind_param($stmt, "i", $shopID);
	mysqli_stmt_execute($stmt); //執行SQL
	$result = mysqli_stmt_get_result($stmt); //取得查詢結果

	$rows = array();
	while($r = mysqli_fetch_assoc($result)) {
		$rows[] = $r; //將此筆資料新增到陣列中
	}
	return $rows;
}

//上架商品
function add_Good($shopID,$goodName,$goodPrice,$goodContent,$goodNum,$goodID) {
	global $db;
    if($goodID > 0) {
        $sql = "update goods set goodName=?, goodPrice=?, goodContent=?, goodNum=? where goodID=?"; //SQL中的 ? 代表未來要用變數綁定進去的地方
        $stmt = mysqli_prepare($db, $sql); 
        mysqli_stmt_bind_param($stmt, "sisii", $goodName, $goodPrice, $goodContent, $goodNum, $goodID); //bind parameters with variables, with types "sis":string, integer ,string
    } else {
        $sql = "insert into goods (shopID, goodName, goodPrice, goodContent, goodNum) values (?, ?, ?, ?, ?)"; //SQL中的 ? 代表未來要用變數綁定進去的地方
        $stmt = mysqli_prepare($db, $sql); 
        mysqli_stmt_bind_param($stmt, "isisi", $shopID, $goodName, $goodPrice, $goodContent, $goodNum); //bind parameters with variables, with types "sis":string, integer ,string
    }
    mysqli_stmt_execute($stmt);
	return True;
}

//下架商品
function del_Good($id) {
	global $db;
	$sql = "delete from goods where goodID=?;";
	$stmt = mysqli_prepare($db, $sql); 
	mysqli_stmt_bind_param($stmt, "i", $id);
	mysqli_stmt_execute($stmt);
	return True;
}

// 確認有哪些訂單
function confirm_OrderList($shopID){
	global $db;
	$sql = "select * from orders,clients where shopID=? and orders.clientID=clients.clientID;";
	$stmt = mysqli_prepare($db, $sql); 
    mysqli_stmt_bind_param($stmt, "i", $shopID);
	mysqli_stmt_execute($stmt); //執行SQL
	$result = mysqli_stmt_get_result($stmt); //取得查詢結果

	$rows = array();
	while($r = mysqli_fetch_assoc($result)) {
		$rows[] = $r; //將此筆資料新增到陣列中
	}
	return $rows;
}

// 設置訂單狀態
function transmit_Order($status,$orderID){
	global $db;
	$sql = "update orders set orderStatus=? where orderID=?;";
	$stmt = mysqli_prepare($db, $sql); 
    mysqli_stmt_bind_param($stmt, "ii", $status, $orderID);
	mysqli_stmt_execute($stmt); 
}

//確認訂單內容物
function get_OrderDetail($orderID){
	global $db;
	$sql = "select * from details,goods where orderID=? and details.goodID=goods.goodID;";
	$stmt = mysqli_prepare($db, $sql); 
    mysqli_stmt_bind_param($stmt, "i", $orderID);
	mysqli_stmt_execute($stmt); //執行SQL
	$result = mysqli_stmt_get_result($stmt); //取得查詢結果

	$rows = array();
	while($r = mysqli_fetch_assoc($result)) {
		$rows[] = $r; //將此筆資料新增到陣列中
	}
	return $rows;
}
?>