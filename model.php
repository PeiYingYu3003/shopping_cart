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

function addGood($goodName,$goodPrice,$goodContent) {
	global $db;

	$sql = "insert into goods (goodName, goodPrice, goodContent) values (?, ?, ?)"; //SQL中的 ? 代表未來要用變數綁定進去的地方
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