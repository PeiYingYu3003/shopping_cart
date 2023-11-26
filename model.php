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
function add_Good($goodName,$goodPrice,$goodContent,$goodID) {
	global $db;
    if($goodID > 0) {
        $sql = "update goods set goodName=?, goodPrice=?, goodContent=? where goodID=?"; //SQL���� ? �N���ӭn���ܼƸj�w�i�h���a��
        $stmt = mysqli_prepare($db, $sql); 
        mysqli_stmt_bind_param($stmt, "sisi", $goodName, $goodPrice, $goodContent, $goodID); //bind parameters with variables, with types "sis":string, integer ,string
    } else {
        $sql = "insert into goods (goodName, goodPrice, goodContent) values (?, ?, ?)"; //SQL���� ? �N���ӭn���ܼƸj�w�i�h���a��
        $stmt = mysqli_prepare($db, $sql); 
        mysqli_stmt_bind_param($stmt, "sis", $goodName, $goodPrice, $goodContent); //bind parameters with variables, with types "sis":string, integer ,string
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

//�Ȥ�\��
function get_CartList($clientID) {
	global $db;
	$sql = "select `carts`.goodID,`goodName`,`goodNum`,`goodPrice`
            from `goods`,`carts`
            where `carts`.goodID=`goods`.goodID
            and `carts`.`clientID`=?;";
	$stmt = mysqli_prepare($db, $sql); 
    mysqli_stmt_bind_param($stmt, "i", $clientID);
	mysqli_stmt_execute($stmt); //����SQL
	$result = mysqli_stmt_get_result($stmt); //���o�d�ߵ��G

	$rows = array();
	while($r = mysqli_fetch_assoc($result)) {
		$rows[] = $r; //�N������Ʒs�W��}�C��
	}
	return $rows;
}
function checkNum($clientID,$goodID){
    global $db;
    $sql = "select * from carts where clientID=? and goodID=?";
    $stmt = mysqli_prepare($db, $sql); 
    mysqli_stmt_bind_param($stmt, "ii", $clientID, $goodID);
    mysqli_stmt_execute($stmt); // ����SQL
    $result = mysqli_stmt_get_result($stmt); // ���o�d�ߵ��G
    
    // �ˬd�O�_���ŦX���󪺸��
    if (mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        $nowNum = $row['goodNum'];
        return $nowNum;
    } else {
        return -1;  // �S���ŦX���󪺸��
    }
}
function add_Cart($goodID) {
    global $db;
    $clientID = 101;
    $goodNum = 1;
    $nowNum = checkNum($clientID,$goodID);
    if ($nowNum > 0) {
        // �ӫ~�w�s�b�A�N��ƶq�[�@
        $newNum = $nowNum + $goodNum;
        $sql = "update carts set goodNum = ? where goodID = ?";
        $stmt = mysqli_prepare($db, $sql);
        mysqli_stmt_bind_param($stmt, "ii", $newNum, $goodID);
        mysqli_stmt_execute($stmt);
        
    } else {
        // �ӫ~���s�b�A�s�W�@���ʪ�������
        $sql = "insert into carts (clientID, goodID, goodNum) values (?, ?, ?)";
        $stmt = mysqli_prepare($db, $sql);
        mysqli_stmt_bind_param($stmt, "iii", $clientID, $goodID, $goodNum);
        mysqli_stmt_execute($stmt);
    }

    return true;
}


function del_Cart($goodID) {
    global $db;
    $clientID = 101;
    $goodNum = 1;
    $nowNum = checkNum($clientID,$goodID);
    if ($nowNum > 1) {
        // �ӫ~�w�s�b�A�N��ƶq�[�@
        $newNum = $nowNum - $goodNum;
        $sql = "update carts set goodNum = ? where goodID = ?";
        $stmt = mysqli_prepare($db, $sql);
        mysqli_stmt_bind_param($stmt, "ii", $newNum, $goodID);
        mysqli_stmt_execute($stmt);
        
    } else if ($nowNum == 1) {
        // �ӫ~���s�b�A�s�W�@���ʪ�������
        $sql = "delete from carts where goodID=?;";
        $stmt = mysqli_prepare($db, $sql);
        mysqli_stmt_bind_param($stmt, "i",$goodID);
        mysqli_stmt_execute($stmt);
    }

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

?>