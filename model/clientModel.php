<?php
require('dbconfig.php');
//商品清單
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
//購物車清單
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

//確認目前數量
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

//加入購物車
function add_Cart($clientID, $shopID, $goodID) {
    global $db;
    $goodNum = 1;

    // 檢查客戶是否已經有該商家的訂單
    $orderID = checkOrder($clientID, $shopID);
    $nowgoodNum = get_goodNum($goodID);
    
    if ($orderID > 0) {
        // 檢查購物車內有幾個
        $nowNum = checkNum($orderID, $goodID);

        if ($nowNum > 0) {
            // 商品已存在，將其數量加一
            $newNum = $nowNum + $goodNum;
            if ($newNum > $nowgoodNum){//不能超過商品最大庫存
                return false;
            }
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
        // 沒有該商家的訂單，建立新訂單
        $orderID = createOrder($clientID, $shopID);
        $sql = "insert into details (orderID, goodID, purNum) values (?, ?, ?)";
        $stmt = mysqli_prepare($db, $sql);
        mysqli_stmt_bind_param($stmt, "iii", $orderID, $goodID, $goodNum);
        mysqli_stmt_execute($stmt);
    }

    return true;
}

function get_goodNum($goodID) {
    global $db;
    $sql = "SELECT goodNum FROM goods WHERE goodID = ?";
    $stmt = mysqli_prepare($db, $sql);
    mysqli_stmt_bind_param($stmt, "i", $goodID);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);
    return $row['goodNum'];
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
function send_Order($clientID) {
    global $db;
    
    // 更新訂單狀態
    $sql = "update orders set orderStatus = 1 where clientID = ? and orderStatus = 0;";
    $stmt = mysqli_prepare($db, $sql);
    mysqli_stmt_bind_param($stmt, "i", $clientID);
    mysqli_stmt_execute($stmt);
    
    // 取得訂單中的商品及數量
    $sql2 = "select goodID, purNum from details where orderID in (select orderID from orders where clientID = ? and orderStatus = 1)";
    $stmt2 = mysqli_prepare($db, $sql2);
    mysqli_stmt_bind_param($stmt2, "i", $clientID);
    mysqli_stmt_execute($stmt2);
    $result = mysqli_stmt_get_result($stmt2);
    
    // 依序更新庫存
    while ($row = mysqli_fetch_assoc($result)) {
        update_goodNum($row['goodID'], $row['purNum']);
    }
}
// 更新庫存
function update_goodNum($goodID, $purNum) {
    global $db;
    
    $sql = "UPDATE goods SET goodNum = goodNum - ? WHERE goodID = ?";
    $stmt = mysqli_prepare($db, $sql);
    mysqli_stmt_bind_param($stmt, "ii", $purNum, $goodID);
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

//給予評價
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

//更新商家評價
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