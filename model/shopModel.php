<?php
require('dbconfig.php');

//�Ӯa�\��
//�d�ݦۤv�ӫ~
function get_ShopList($shopID) {
	global $db;
	$sql = "select * from goods where shopID=?;";
	$stmt = mysqli_prepare($db, $sql ); //precompile sql���O�A�إ�statement ����A�H�K����SQL
    mysqli_stmt_bind_param($stmt, "i", $shopID);
	mysqli_stmt_execute($stmt); //����SQL
	$result = mysqli_stmt_get_result($stmt); //���o�d�ߵ��G

	$rows = array();
	while($r = mysqli_fetch_assoc($result)) {
		$rows[] = $r; //�N������Ʒs�W��}�C��
	}
	return $rows;
}
//�W�[�ӫ~
function add_Good($shopID,$goodName,$goodPrice,$goodContent,$goodNum,$goodID) {
	global $db;
    if($goodID > 0) {
        $sql = "update goods set goodName=?, goodPrice=?, goodContent=?, goodNum=? where goodID=?"; //SQL���� ? �N���ӭn���ܼƸj�w�i�h���a��
        $stmt = mysqli_prepare($db, $sql); 
        mysqli_stmt_bind_param($stmt, "sisii", $goodName, $goodPrice, $goodContent, $goodNum, $goodID); //bind parameters with variables, with types "sis":string, integer ,string
    } else {
        $sql = "insert into goods (shopID, goodName, goodPrice, goodContent, goodNum) values (?, ?, ?, ?, ?)"; //SQL���� ? �N���ӭn���ܼƸj�w�i�h���a��
        $stmt = mysqli_prepare($db, $sql); 
        mysqli_stmt_bind_param($stmt, "isisi", $shopID, $goodName, $goodPrice, $goodContent, $goodNum); //bind parameters with variables, with types "sis":string, integer ,string
    }
    mysqli_stmt_execute($stmt);
	return True;
}
//�U�[�ӫ~
function del_Good($id) {
	global $db;
	$sql = "delete from goods where goodID=?;";
	$stmt = mysqli_prepare($db, $sql); 
	mysqli_stmt_bind_param($stmt, "i", $id);
	mysqli_stmt_execute($stmt);
	return True;
}

//�T�{�q��
function confirm_OrderList($shopID){
	global $db;
	$sql = "select * from orders where shopID=? and orderStatus=1;";
	$stmt = mysqli_prepare($db, $sql); 
    mysqli_stmt_bind_param($stmt, "i", $shopID);
	mysqli_stmt_execute($stmt); //����SQL
	$result = mysqli_stmt_get_result($stmt); //���o�d�ߵ��G

	$rows = array();
	while($r = mysqli_fetch_assoc($result)) {
		$rows[] = $r; //�N������Ʒs�W��}�C��
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

//�]�˥X�f
function transmit_OrderList($shopID){
	global $db;
	$sql = "select * from orders where shopID=? and orderStatus=2;";
	$stmt = mysqli_prepare($db, $sql); 
    mysqli_stmt_bind_param($stmt, "i", $shopID);
	mysqli_stmt_execute($stmt); //����SQL
	$result = mysqli_stmt_get_result($stmt); //���o�d�ߵ��G

	$rows = array();
	while($r = mysqli_fetch_assoc($result)) {
		$rows[] = $r; //�N������Ʒs�W��}�C��
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
?>