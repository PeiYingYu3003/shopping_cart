<?php
require('model.php');
$act=$_REQUEST['act'];

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
		add_Good($good->goodName,$good->goodPrice,$good->goodContent,$good->goodID);
		return;

	case "delGood": // 刪除商品
		$id=(int)$_REQUEST['id']; 
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
		$id=(int)$_REQUEST['id']; 
		//verify
		add_Cart($id);
		return;

	case "delGood_cart": // 從購物車移除商品
		$id=(int)$_REQUEST['id']; 
		//verify
		del_Cart($id);
		return;

	case "get_total": // 計算購物車內商品總價set_Good
		$Price = get_Total();
		echo json_encode($Price);
		return;

	default:
  
}

//後面直接加前面沒動
//users.php
<?php
require('dbconfig.php');

define('ROLE_USER', 0);
define('ROLE_MERCHANT', 1);

function register_User($account, $password, $role) {
    global $db;
    $sql = "INSERT INTO users (account, password, role) VALUES (?, ?, ?)";
    $stmt = mysqli_prepare($db, $sql);
    mysqli_stmt_bind_param($stmt, "sss", $account, $password, $role);
    mysqli_stmt_execute($stmt);
    return mysqli_insert_id($db);
}

function login_User($account, $password) {
    global $db;
    $sql = "SELECT * FROM users WHERE account=? AND password=?";
    $stmt = mysqli_prepare($db, $sql);
    mysqli_stmt_bind_param($stmt, "ss", $account, $password);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    return mysqli_fetch_assoc($result);
}
//clients.php
<?php
require('dbconfig.php');

function add_Client($userID, $clientName, $clientAddress) {
    global $db;
    $sql = "INSERT INTO clients (userID, clientName, clientAddress) VALUES (?, ?, ?)";
    $stmt = mysqli_prepare($db, $sql);
    mysqli_stmt_bind_param($stmt, "iss", $userID, $clientName, $clientAddress);
    mysqli_stmt_execute($stmt);
    return mysqli_insert_id($db);
}

//shops.php
<?php
require('dbconfig.php');

function add_Shop($userID, $shopName, $shopStar) {
    global $db;
    $sql = "INSERT INTO shops (userID, shopName, shopStar) VALUES (?, ?, ?)";
    $stmt = mysqli_prepare($db, $sql);
    mysqli_stmt_bind_param($stmt, "iss", $userID, $shopName, $shopStar);
    mysqli_stmt_execute($stmt);
    return mysqli_insert_id($db);
}
//model.php
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







