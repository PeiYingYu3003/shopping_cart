<?php
// index.php

require('model.php');

$act = $_REQUEST['act'];

switch ($act) {
    case "GoodsList":
        getGoodsList();
        break;

    case "addGood":
        addGood();
        break;

    case "delGood":
        delGood();
        break;

    case "CartList":
        getCartList();
        break;

    case "addGood_cart":
        addGoodToCart();
        break;

    case "delGood_cart":
        delGoodFromCart();
        break;

    case "get_total":
        getTotal();
        break;

    default:
        // Handle default case
}

// 關閉資料庫連線
mysqli_close($db);
?>



<?php
// users.php

require('dbconfig.php');

define('ROLE_USER', 0);
define('ROLE_MERCHANT', 1);

function registerUser($account, $password)
{
    global $db;
    $role = ROLE_USER;
    $sql = "INSERT INTO users (account, password, role) VALUES (?, ?, ?)";
    $stmt = mysqli_prepare($db, $sql);
    mysqli_stmt_bind_param($stmt, "sss", $account, $password, $role);
    mysqli_stmt_execute($stmt);
    return mysqli_insert_id($db);
}

function loginUser($account, $password)
{
    global $db;
    $sql = "SELECT * FROM users WHERE account=? AND password=?";
    $stmt = mysqli_prepare($db, $sql);
    mysqli_stmt_bind_param($stmt, "ss", $account, $password);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    return mysqli_fetch_assoc($result);
}
php
// clients.php

require('dbconfig.php');

function addClient($userID, $clientName, $clientAddress)
{
    global $db;
    $sql = "INSERT INTO clients (userID, clientName, clientAddress) VALUES (?, ?, ?)";
    $stmt = mysqli_prepare($db, $sql);
    mysqli_stmt_bind_param($stmt, "iss", $userID, $clientName, $clientAddress);
    mysqli_stmt_execute($stmt);
    return mysqli_insert_id($db);
}
?>

<?php
// shops.php

require('dbconfig.php');

function addShop($userID, $shopName, $shopStar)
{
    global $db;
    $sql = "INSERT INTO shops (userID, shopName, shopStar) VALUES (?, ?, ?)";
    $stmt = mysqli_prepare($db, $sql);
    mysqli_stmt_bind_param($stmt, "iss", $userID, $shopName, $shopStar);
    mysqli_stmt_execute($stmt);
    return mysqli_insert_id($db);
}
?>
<?php
// model.php

require('users.php');
require('clients.php');
require('shops.php');

$act = $_REQUEST['act'];

switch ($act) {
    case "registerUser":
        registerUserHandler();
        break;

    default:
        // Handle default case
}

function registerUserHandler()
{
    $account = $_POST['account'];
    $password = $_POST['password'];
    $userID = registerUser($account, $password);
    echo $userID;
}
?>
<?php
// orders.php

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
?>
<?php
// goods.php

require('dbconfig.php');

function addGood($shopID, $goodName, $goodPrice, $goodContent, $goodNum)
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

function delGood($goodID)
{
    global $db;
    $sql = "DELETE FROM goods WHERE goodID=?";
    $stmt = mysqli_prepare($db, $sql);
    mysqli_stmt_bind_param($stmt, "i", $goodID);
    mysqli_stmt_execute($stmt);
}

function getGoodList()
{
    global $db;
    $sql = "SELECT * FROM goods";
    $result = mysqli_query($db, $sql);
    $goods = mysqli_fetch_all($result, MYSQLI_ASSOC);
    return $goods;
}
?>

