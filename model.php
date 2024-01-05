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

function del_Good($id) {
	global $db;
	$sql = "delete from goods where goodID=?;";
	$stmt = mysqli_prepare($db, $sql); 
	mysqli_stmt_bind_param($stmt, "i", $id);
	mysqli_stmt_execute($stmt);
	return True;
}



//客戶功能
function get_CartList($clientID) {
	global $db;
	$sql = "SELECT orders.orderID, details.goodID, goods.goodName, goods.goodPrice, details.purNum
            FROM orders JOIN details ON orders.orderID = details.orderID JOIN goods ON details.goodID = goods.goodID
            WHERE orders.orderStatus = 0 AND orders.clientID =?;";
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

function checkNum($orderID, $goodID){
    global $db;
    $sql = "select * from details where orderID=? and goodID=?";
    $stmt = mysqli_prepare($db, $sql); 
    mysqli_stmt_bind_param($stmt, "ii", $orderID, $goodID);
    mysqli_stmt_execute($stmt); // 執行SQL
    $result = mysqli_stmt_get_result($stmt); // 取得查詢結果
    
    // 檢查是否有符合條件的資料
    if ($row = mysqli_fetch_assoc($result)) {
        $nowNum = $row['purNum'];
        return $nowNum;
    } else {
        return -1;  // 沒有符合條件的資料
    }
}


function add_Cart($clientID, $shopID, $goodID) {
    global $db;
    $goodNum = 1;

    // 檢查客戶是否已經有該商家的訂單
    $orderID = checkOrder($clientID, $shopID);

    if ($orderID > 0) {
        // 客戶已有該商家的訂單，檢查商品是否已存在
        $nowNum = checkNum($orderID, $goodID);

        if ($nowNum > 0) {
            // 商品已存在，將其數量加一
            $newNum = $nowNum + $goodNum;
            $sql = "update details set purNum = ? where orderID=? and goodID = ?";
            $stmt = mysqli_prepare($db, $sql);
            mysqli_stmt_bind_param($stmt, "iii", $newNum, $orderID, $goodID);
            mysqli_stmt_execute($stmt);
        } else {
            // 商品不存在，新增一個購物車項目
            $sql = "insert into details (orderID, goodID, purNum) values (?, ?, ?)";
            $stmt = mysqli_prepare($db, $sql);
            mysqli_stmt_bind_param($stmt, "iii", $orderID, $goodID, $goodNum);
            mysqli_stmt_execute($stmt);
        }
    } else {
        // 客戶還沒有該商家的訂單，建立新的訂單再新增商品
        $orderID = createOrder($clientID, $shopID);
        $sql = "insert into details (orderID, goodID, purNum) values (?, ?, ?)";
        $stmt = mysqli_prepare($db, $sql);
        mysqli_stmt_bind_param($stmt, "iii", $orderID, $goodID, $goodNum);
        mysqli_stmt_execute($stmt);
    }

    return true;
}

// 檢查客戶是否已經有該商家的訂單
function checkOrder($clientID, $shopID) {
    global $db;
    $sql = "select orderID from orders where clientID=? and shopID=? and orderStatus = 0";
    $stmt = mysqli_prepare($db, $sql);
    mysqli_stmt_bind_param($stmt, "ii", $clientID, $shopID);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if (mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        return $row['orderID'];
    } else {
        return -1;
    }
}

// 建立新的訂單
function createOrder($clientID, $shopID) {
    global $db;
    
    $sql = "insert into orders (clientID, shopID, orderStatus) values (?, ?, 0)";
    $stmt = mysqli_prepare($db, $sql);
    mysqli_stmt_bind_param($stmt, "ii", $clientID, $shopID);
    mysqli_stmt_execute($stmt);

    // 回傳orderID
    return mysqli_insert_id($db);
}


function del_Cart($detailID, $clientID, $goodID) {
    global $db;
    $goodNum = 1;
    $orderID = checkOrder($clientID, $goodID);
    $nowNum = checkNum($orderID, $goodID);

    if ($nowNum > 1) {
        // 商品已存在，將其數量減一
        $newNum = $nowNum - $goodNum;
        $sql = "update details set purNum = ? where goodID = ? and detailID = ?";
        $stmt = mysqli_prepare($db, $sql);
        mysqli_stmt_bind_param($stmt, "iii", $newNum, $goodID, $detailID);
        mysqli_stmt_execute($stmt);
    } else if ($nowNum == 1) {
        // 商品不存在，刪除購物車項目
        $sql = "delete from details where detailID=?";
        $stmt = mysqli_prepare($db, $sql);
        mysqli_stmt_bind_param($stmt, "i", $detailID);
        mysqli_stmt_execute($stmt);

        // 檢查是否為客戶商家間最後一個商品
        $count = count_details($orderID);

        if ($count == 0) {
            deleteOrder($orderID);
        }
    }

    return true;
}

// 檢查訂單內有幾筆
function count_details($orderID) {
    global $db;
    $sql = "SELECT COUNT(*) as count FROM details WHERE orderID = ?";
    $stmt = mysqli_prepare($db, $sql);
    mysqli_stmt_bind_param($stmt, "i", $orderID);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);

    return $row['count'];
}

// 刪除訂單
function deleteOrder($orderID) {
    global $db;
    $sql = "delete from orders where orderID = ?";
    $stmt = mysqli_prepare($db, $sql);
    mysqli_stmt_bind_param($stmt, "i", $orderID);
    mysqli_stmt_execute($stmt);
}


function get_Total($clientID) {
    global $db;

    $sql = "SELECT SUM(goods.goodPrice * details.purNum) AS total
            FROM orders JOIN details ON orders.orderID = details.orderID JOIN goods ON details.goodID = goods.goodID
            WHERE orders.clientID = ? AND orders.orderStatus = 0";
    $stmt = mysqli_prepare($db, $sql);
    mysqli_stmt_bind_param($stmt, "i", $clientID);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    $row = mysqli_fetch_assoc($result);
    return $row['total']; //確保不會抓到其他值
}

//客戶
//送出訂單
function send_Order($clientID){
    global $db;
    $sql = "update orders set orderStatus = 1 where clientID = ? and orderStatus = 0;";
    $stmt = mysqli_prepare($db, $sql);
    mysqli_stmt_bind_param($stmt, "i", $clientID);
    mysqli_stmt_execute($stmt);
}

//查看訂單
function get_OrderList($clientID){
	global $db;
	$sql = "select * from orders where clientID=? and orderStatus > 0;";
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

//商家
//確認訂單
function confirm_OrderList($shopID){
	global $db;
	$sql = "select * from orders where shopID=? and orderStatus=1;";
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
	$sql = "update orders set orderStatus=2 where orderID=?;";
	$stmt = mysqli_prepare($db, $sql); 
    mysqli_stmt_bind_param($stmt, "i", $orderID);
	mysqli_stmt_execute($stmt); 
}
//包裝出貨
function transmit_OrderList($shopID){
	global $db;
	$sql = "select * from orders where shopID=? and orderStatus=2;";
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
	$sql = "update orders set orderStatus=3 where orderID=?;";
	$stmt = mysqli_prepare($db, $sql); 
    mysqli_stmt_bind_param($stmt, "i", $orderID);
	mysqli_stmt_execute($stmt); 
}

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

//客戶
//收到並評價訂單
function feedback_OrderList($clientID){
	global $db;
	$sql = "select * from orders where orderStatus=5 and clientID=?;";
	$stmt = mysqli_prepare($db, $sql);
	mysqli_stmt_bind_param($stmt, "i", $clientID);
	$result = mysqli_stmt_get_result($stmt); 
	$rows = array();
	while($r = mysqli_fetch_assoc($result)) {
		$rows[] = $r; 
	}
	return $rows;
}
function feedback_Order($feedback,$orderID){ 
	global $db;
	$sql = "update orders set feedback=? where orderID=?;";
	$stmt = mysqli_prepare($db, $sql); 
    mysqli_stmt_bind_param($stmt, "si", $feedback, $orderID);
	mysqli_stmt_execute($stmt); 
    
    $sql2 = "select shopID from orders where orderID=?";
    $stmt2 = mysqli_prepare($db, $sql2);
    mysqli_stmt_bind_param($stmt2, "i", $orderID);
    mysqli_stmt_execute($stmt2);
    $result = mysqli_stmt_get_result($stmt2);
    $row = mysqli_fetch_assoc($result);
    
    $shopID = $row['shopID'];
    update_ShopStar($shopID);//更新評價
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
//計算商家評價
function update_ShopStar($shopID) {
    global $db;
    //計算平均
    $sql = "select avg(feedback) as avgfeedback from orders where shopID = ?";
    $stmt = mysqli_prepare($db, $sql);
    mysqli_stmt_bind_param($stmt, "i", $shopID);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);
    $avg = $row['avgfeedback'];
    //更新評價
    $sql2 = "update shops set shopStar = ? where shopID = ?";
    $stmt2 = mysqli_prepare($db, $sql2);
    mysqli_stmt_bind_param($stmt2, "ii", $avg, $shopID);
    mysqli_stmt_execute($stmt2);
}
?>