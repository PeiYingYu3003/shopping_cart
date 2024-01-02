<?php
require('dbconfig.php');

function get_GoodList() {
	global $db;
	$sql = "select * from goods;";
	$stmt = mysqli_prepare($db, $sql ); //precompile sql指令，建立statement 物件，以便執行SQL
	mysqli_stmt_execute($stmt); //執行SQL
	$result = mysqli_stmt_get_result($stmt); //取得查詢結果

	$rows = array();
	while($r = mysqli_fetch_assoc($result)) {
		$rows[] = $r; //將此筆資料新增到陣列中
	}
	return $rows;
}

//商家功能
function add_Good($goodName,$goodPrice,$goodContent,$goodID) {
	global $db;
    if($goodID > 0) {
        $sql = "update goods set goodName=?, goodPrice=?, goodContent=? where goodID=?"; //SQL中的 ? 代表未來要用變數綁定進去的地方
        $stmt = mysqli_prepare($db, $sql); 
        mysqli_stmt_bind_param($stmt, "sisi", $goodName, $goodPrice, $goodContent, $goodID); //bind parameters with variables, with types "sis":string, integer ,string
    } else {
        $sql = "insert into goods (goodName, goodPrice, goodContent) values (?, ?, ?)"; //SQL中的 ? 代表未來要用變數綁定進去的地方
        $stmt = mysqli_prepare($db, $sql); 
        mysqli_stmt_bind_param($stmt, "sis", $goodName, $goodPrice, $goodContent); //bind parameters with variables, with types "sis":string, integer ,string
    }
    mysqli_stmt_execute($stmt);
	return True;
}

function del_Good($id) {
	global $db;
	$sql = "delete from goods where goodID=?;";
	$stmt = mysqli_prepare($db, $sql); 
	mysqli_stmt_bind_param($stmt, "i", $id);
	mysqli_stmt_execute($stmt);
	return True;
}

//確認訂單
function confirm_OrderList($shopID){
	global $db;
	$sql = "select * from orders where shopID=? and status='未處理';";
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
function confirm_Order($orderID){
	global $db;
	$sql = "update orders set status='處理中' where orderID=?;";
	$stmt = mysqli_prepare($db, $sql); 
    mysqli_stmt_bind_param($stmt, "i", $orderID);
	mysqli_stmt_execute($stmt); 
}
//包裝出貨
function transmit_OrderList($shopID){
	global $db;
	$sql = "select * from orders where shopID=? and status='處理中';";
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
function transmit_Order($orderID){
	global $db;
	$sql = "update orders set status='寄送中' where orderID=?;";
	$stmt = mysqli_prepare($db, $sql); 
    mysqli_stmt_bind_param($stmt, "i", $orderID);
	mysqli_stmt_execute($stmt); 
}

//客戶功能
function get_CartList($clientID) {
	global $db;
	$sql = "select `carts`.goodID,`goodName`,`goodNum`,`goodPrice`
            from `goods`,`carts`
            where `carts`.goodID=`goods`.goodID
            and `carts`.`clientID`=?;";
	$stmt = mysqli_prepare($db, $sql); 
    mysqli_stmt_bind_param($stmt, "i", $clientID);
	mysqli_stmt_execute($stmt); //執行SQL
	$result = mysqli_stmt_get_result($stmt); //取得查詢結果

	$rows = array();
	while($r = mysqli_fetch_assoc($result)) {
		$rows[] = $r; //將此筆資料新增到陣列中
	}
	return $rows;
}

function checkNum($clientID,$goodID){
    global $db;
    $sql = "select * from carts where clientID=? and goodID=?";
    $stmt = mysqli_prepare($db, $sql); 
    mysqli_stmt_bind_param($stmt, "ii", $clientID, $goodID);
    mysqli_stmt_execute($stmt); // 執行SQL
    $result = mysqli_stmt_get_result($stmt); // 取得查詢結果
    
    // 檢查是否有符合條件的資料
    if (mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        $nowNum = $row['goodNum'];
        return $nowNum;
    } else {
        return -1;  // 沒有符合條件的資料
    }
}

function add_Cart($goodID) {
    global $db;
    $clientID = 101;
    $goodNum = 1;
    $nowNum = checkNum($clientID,$goodID);
    if ($nowNum > 0) {
        // 商品已存在，將其數量加一
        $newNum = $nowNum + $goodNum;
        $sql = "update carts set goodNum = ? where goodID = ?";
        $stmt = mysqli_prepare($db, $sql);
        mysqli_stmt_bind_param($stmt, "ii", $newNum, $goodID);
        mysqli_stmt_execute($stmt);
        
    } else {
        // 商品不存在，新增一個購物車項目
        $sql = "insert into carts (clientID, goodID, goodNum) values (?, ?, ?)";
        $stmt = mysqli_prepare($db, $sql);
        mysqli_stmt_bind_param($stmt, "iii", $clientID, $goodID, $goodNum);
        mysqli_stmt_execute($stmt);
    }

    return true;
}

function del_Cart($goodID) {
    global $db;
    $clientID = 101;
    $goodNum = 1;
    $nowNum = checkNum($clientID,$goodID);
    if ($nowNum > 1) {
        // 商品已存在，將其數量加一
        $newNum = $nowNum - $goodNum;
        $sql = "update carts set goodNum = ? where goodID = ?";
        $stmt = mysqli_prepare($db, $sql);
        mysqli_stmt_bind_param($stmt, "ii", $newNum, $goodID);
        mysqli_stmt_execute($stmt);
        
    } else if ($nowNum == 1) {
        // 商品不存在，新增一個購物車項目
        $sql = "delete from carts where goodID=?;";
        $stmt = mysqli_prepare($db, $sql);
        mysqli_stmt_bind_param($stmt, "i",$goodID);
        mysqli_stmt_execute($stmt);
    }

    return true;
}

function get_Total() {
    global $db;

    $sql = "select sum(goods.goodPrice * carts.goodNum) as total from carts join goods on carts.goodID = goods.goodID"; //inner join
    $stmt = mysqli_prepare($db, $sql);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    $row = mysqli_fetch_assoc($result);
    return $row['total']; //確保不會抓到其他值
}

//送出訂單
function send_Order($clientID){
    global $db;

    $cartList = get_CartList($clientID);
    $shopID = 201;
    $cartText = '';
    foreach ($cartList as $cartItem) {
        $cartText .= "Good: {$cartItem['goodName']}, Num: {$cartItem['goodNum']}, Price: {$cartItem['goodPrice']}\n";
    }
    $sql = "insert into orders (shopID, clientID, context) values (?, ?, ?)";
    $stmt = mysqli_prepare($db, $sql);
    mysqli_stmt_bind_param($stmt, "iis", $shopID, $clientID, $cartText);
    mysqli_stmt_execute($stmt);

    // 清除購物車
    $sql = "delete from carts where clientID = ?";
    $stmt = mysqli_prepare($db, $sql);
    mysqli_stmt_bind_param($stmt, "i", $clientID);
    mysqli_stmt_execute($stmt);
}

//查看訂單
function get_OrderList($clientID){
	global $db;
	$sql = "select * from orders where clientID=?;";
	$stmt = mysqli_prepare($db, $sql); 
    mysqli_stmt_bind_param($stmt, "i", $clientID);
	mysqli_stmt_execute($stmt); //執行SQL
	$result = mysqli_stmt_get_result($stmt); //取得查詢結果

	$rows = array();
	while($r = mysqli_fetch_assoc($result)) {
		$rows[] = $r; //將此筆資料新增到陣列中
	}
	return $rows;
}

//收到並評價訂單
function finish_OrderList($clientID){
	global $db;
	$sql = "select * from orders where status='已寄送' and clientID=?;";
	$stmt = mysqli_prepare($db, $sql);
	mysqli_stmt_bind_param($stmt, "i", $clientID);
	$result = mysqli_stmt_get_result($stmt); 
	$rows = array();
	while($r = mysqli_fetch_assoc($result)) {
		$rows[] = $r; 
	}
	return $rows;
}
function finish_Order($satisfaction,$orderID){ 
	global $db;
	$sql = "update orders set satisfaction=? where orderID=?;";
	$stmt = mysqli_prepare($db, $sql); 
    mysqli_stmt_bind_param($stmt, "si", $satisfaction, $orderID);
	mysqli_stmt_execute($stmt); 
}

//物流功能
//已寄送
function deal_Deliver(){
	global $db;
	$sql = "select * from orders where status='寄送中';";
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
	$sql = "update orders set status='已寄送' where orderID=?;";
	$stmt = mysqli_prepare($db, $sql); 
    mysqli_stmt_bind_param($stmt, "i", $orderID);
	mysqli_stmt_execute($stmt); 
}

//已送達
function deal_Star(){
	global $db;
	$sql = "select * from orders where satisfaction!='';"; //評價不為空
	$stmt = mysqli_prepare($db, $sql);
	mysqli_stmt_execute($stmt); 
	$result = mysqli_stmt_get_result($stmt);
	$rows = array();
	while($r = mysqli_fetch_assoc($result)) {
		$rows[] = $r; 
	}
	return $rows;
}
function check_Order($orderID){ 
	global $db;
	$sql = "update orders set status='已送達' where orderID=?;";
	$stmt = mysqli_prepare($db, $sql); 
    mysqli_stmt_bind_param($stmt, "i", $orderID);
	mysqli_stmt_execute($stmt); 
}
?>