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

//�Ӯa�\��
function add_Good($goodName,$goodPrice,$goodContent) {
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

//�Ȥ�\��
function get_CartList() {
    global $db;
    
    $sql = "select carts.goodID, goods.goodName, goods.goodPrice, goods.goodContent, carts.goodNum from carts join goods on carts.goodID = goods.goodID"
    $stmt = mysqli_prepare($db, $sql);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    $rows = array();
	while($r = mysqli_fetch_assoc($result)) {
		$rows[] = $r; //�N������Ʒs�W��}�C��
	}
	return $rows;
    
}

function add_Cart($goodID, $goodNum) {
    global $db;

    $nowNum = checkCart($goodID);

    if ($existingItem) {
        // �ӫ~�w�s�b�A�N��ƶq�[�@
        $newNum = $nowNum['goodNum'] + $goodNum;
        $sql = "update carts set goodNum = ? where goodID = ?";
        $stmt = mysqli_prepare($db, $sql);
        mysqli_stmt_bind_param($stmt, "ii", $newNum, $goodID);
        mysqli_stmt_execute($stmt);
        
    } else {
        // �ӫ~���s�b�A�s�W�@���ʪ�������
        $sql = "insert into carts (goodID, goodNum) values (?, ?)";
        $stmt = mysqli_prepare($db, $sql);
        mysqli_stmt_bind_param($stmt, "ii", $goodID, $goodNum);
        mysqli_stmt_execute($stmt);
    }

    return true;
}


function del_Cart($cartID) {
    global $db;

    $sql = "delete from carts where cartID = ?";
    $stmt = mysqli_prepare($db, $sql);
    mysqli_stmt_bind_param($stmt, "i", $cartID);
    mysqli_stmt_execute($stmt);

    return true;
}

function get_Total() {
    global $db;

    $sql = "select sum(goods.goodPrice * carts.goodNum) as total from carts join goods on carts.goodID = goods.goodID"; //inner join
    $stmt = mysqli_prepare($db, $sql);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    $row = mysqli_fetch_assoc($result);
    return $row['total']; //�T�O���|����L��
}

function checkCart($goodID) { //�ˬd�ʪ������O�_�w�g���ۦP���ӫ~
    global $db;

    $sql = "select * from carts where goodID = ?";
    $stmt = mysqli_prepare($db, $sql);
    mysqli_stmt_bind_param($stmt, "i", $goodID);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    return mysqli_fetch_assoc($result);
}
?>