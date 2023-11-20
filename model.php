<?php
require('dbconfig.php');

function get_GoodList() {
	global $db;
	$sql = "select * from goods;";
	$stmt = mysqli_prepare($db, $sql ); //precompile sql���O�A�إ�statement ����A�H�K����SQL
	mysqli_stmt_execute($stmt); //����SQL
	$result = mysqli_stmt_get_result($stmt); //���o�d�ߵ��G

	$rows = array();
	while($r = mysqli_fetch_assoc($result)) {
		$rows[] = $r; //�N������Ʒs�W��}�C��
	}
	return $rows;
}

function addGood($goodName,$goodPrice,$goodContent) {
	global $db;

	$sql = "insert into goods (goodName, goodPrice, goodContent) values (?, ?, ?)"; //SQL���� ? �N���ӭn���ܼƸj�w�i�h���a��
	$stmt = mysqli_prepare($db, $sql); 
	mysqli_stmt_bind_param($stmt, "sis", $goodName, $goodPrice, $goodContent); //bind parameters with variables, with types "sis":string, integer ,string
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

function set_Good($goodID,$goodName,$goodPrice,$goodContent) {
	global $db;

	$sql = "update goods set goodName=?, goodPrice=?, goodContent=? where goodID=?"; 
	$stmt = mysqli_prepare($db, $sql);
	mysqli_stmt_bind_param($stmt, "sisi", $goodName,$goodPrice,$goodContent,$goodID);
	mysqli_stmt_execute($stmt);
}
?>