<?php
require('model.php');
$act = $_REQUEST['act'];

switch ($act) {
    // ***********************
    // 商家可用功能
    // ***********************
    case "GoodsList": // 取得所有商品列表
        $Goods = get_GoodList();
        echo json_encode($Goods);
        return;

    case "addGood": // 新增商品
        $jsonStr = $_POST['dat'];
        $good = json_decode($jsonStr);
        try {
            $insertedID = add_Good($good->goodID, $good->goodName, $good->goodPrice, $good->goodContent);
            echo json_encode(["success" => true, "insertedID" => $insertedID]);
        } catch (Exception $e) {
            echo json_encode(["success" => false, "error" => $e->getMessage()]);
        }
        return;

    case "delGood": // 刪除商品
        $id = (int)$_REQUEST['id'];
        del_Good($id);
        return;

    // ***********************
    // 客戶可用功能
    // ***********************
    case "CartList": // 取得購物車內容物清單
        $Cart = get_CartList(101);
        echo json_encode($Cart);
        return;

    case "addGood_cart": // 新增商品至購物車
        $id = (int)$_REQUEST['id'];
        //verify
        add_Cart($id);
        return;

    case "delGood_cart": // 從購物車移除商品
        $id = (int)$_REQUEST['id'];
        //verify
        del_Cart($id);
        return;

    case "get_total": // 計算購物車內商品總價set_Good
        $Price = get_Total();
        echo json_encode($Price);
        return;

    default:
}

// users.php
<?php
require('dbconfig.php');

define('ROLE_USER', 0);
define('ROLE_MERCHANT', 1);

function register_User($account, $password, $role)
{
    global $db;
    $sql = "INSERT INTO users (account, password, role) VALUES (?, ?, ?)";
    $stmt = mysqli_prepare($db, $sql);
    mysqli_stmt_bind_param($stmt, "sss", $account, $password, $role);
    mysqli_stmt_execute($stmt);
    return mysqli_insert_id($db);
}

function login_User($account, $password)
{
    global $db;
    $sql = "SELECT * FROM users WHERE account=? AND password=?";
    $stmt = mysqli_prepare($db, $sql);
    mysqli_stmt_bind_param($stmt, "ss", $account, $password);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    return mysqli_fetch_assoc($result);
}

// clients.php
<?php
require('dbconfig.php');

function add_Client($userID, $clientName, $clientAddress)
{
    global $db;
    $sql = "INSERT INTO clients (userID, clientName, clientAddress) VALUES (?, ?, ?)";
    $stmt = mysqli_prepare($db, $sql);
    mysqli_stmt_bind_param($stmt, "iss", $userID, $clientName, $clientAddress);
    mysqli_stmt_execute($stmt);
    return mysqli_insert_id($db);
}

// shops.php
<?php
require('dbconfig.php');

function add_Shop($userID, $shopName, $shopStar)
{
    global $db;
    $sql = "INSERT INTO shops (userID, shopName, shopStar) VALUES (?, ?, ?)";
    $stmt = mysqli_prepare($db, $sql);
    mysqli_stmt_bind_param($stmt, "iss", $userID, $shopName, $shopStar);
    mysqli_stmt_execute($stmt);
    return mysqli_insert_id($db);
}

// model.php
<?php
require('users.php');
require('clients.php');
require('shops.php');

$act = $_REQUEST['act'];

switch ($act) {
    case "registerUser":
        $account = $_POST['account'];
        $password = $_POST['password'];
        $role = ROLE_USER; // You can change this as needed
        $userID = register_User($account, $password, $role);
        echo $userID;
        return;

    default:
        // Handle default case
}

// orders.php
<?php
// 引入資料庫設定檔案
require('dbconfig.php');

// 定義 SQL 查詢語句
$sql = "CREATE TABLE IF NOT EXISTS `orders` (
  `orderID` int(10) NOT NULL AUTO_INCREMENT,
  `clientID` int(10) NOT NULL,
  `shopID` int(10) NOT NULL,
  `orderStatus` int(2) NOT NULL,
  `deliverID` int(10) DEFAULT 0 NOT NULL,
  PRIMARY KEY (`orderID`),
  FOREIGN KEY (`clientID`) REFERENCES `clients` (`clientID`),
  FOREIGN KEY (`shopID`) REFERENCES `shops` (`shopID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci";

// 執行 SQL 
if (mysqli_query($db, $sql)) {
    echo "Table orders created successfully";
} else {
    echo "Error creating table: " . mysqli_error($db);
}

// 關閉資料庫連線
mysqli_close($db);

// 新增商品
function add_Good($shopID, $goodName, $goodPrice, $goodContent, $goodNum)
{
    global $db;
    $sql = "INSERT INTO goods (shopID, goodName, goodPrice, goodContent, goodNum) VALUES (?, ?, ?, ?, ?)";
    $stmt = mysqli_prepare($db, $sql);
    mysqli_stmt_bind_param($stmt, "issdi", $shopID, $goodName, $goodPrice, $goodContent, $goodNum);

    if (mysqli_stmt_execute($stmt)) {
        // 插入成功
        return mysqli_insert_id($db);  // 返回新加入商品的ID
    } else {
        // 插入失敗，拋出例外
        throw new Exception("商品插入失敗: " . mysqli_error($db));
    }
}

// 刪除商品
function del_Good($goodID)
{
    global $db;
    $sql = "DELETE FROM goods WHERE goodID=?";
    $stmt = mysqli_prepare($db, $sql);
    mysqli_stmt_bind_param($stmt, "i", $goodID);
    mysqli_stmt_execute($stmt);
}

// 取得所有商品列表
function get_GoodList()
{
    global $db;
    $sql = "SELECT * FROM goods";
    $result = mysqli_query($db, $sql);
    $goods = mysqli_fetch_all($result, MYSQLI_ASSOC);
    return





