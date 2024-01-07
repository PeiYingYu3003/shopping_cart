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
