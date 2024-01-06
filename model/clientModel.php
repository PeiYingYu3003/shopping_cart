<?php
require('dbconfig.php');
//�ӫ~�M��
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
//�ʪ����M��
function get_CartList($clientID) {
	global $db;
	$sql = "SELECT orders.orderID, details.goodID, goods.goodName, goods.goodPrice, details.purNum
            FROM orders JOIN details ON orders.orderID = details.orderID JOIN goods ON details.goodID = goods.goodID
            WHERE orders.orderStatus = 0 AND orders.clientID =?;";
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

//�T�{�ثe�ƶq
function checkNum($orderID, $goodID){
    global $db;
    $sql = "select * from details where orderID=? and goodID=?";
    $stmt = mysqli_prepare($db, $sql); 
    mysqli_stmt_bind_param($stmt, "ii", $orderID, $goodID);
    mysqli_stmt_execute($stmt); // ����SQL
    $result = mysqli_stmt_get_result($stmt); // ���o�d�ߵ��G
    
    // �ˬd�O�_���ŦX���󪺸��
    if ($row = mysqli_fetch_assoc($result)) {
        $nowNum = $row['purNum'];
        return $nowNum;
    } else {
        return -1;  // �S���ŦX���󪺸��
    }
}

//�[�J�ʪ���
function add_Cart($clientID, $shopID, $goodID) {
    global $db;
    $goodNum = 1;

    // �ˬd�Ȥ�O�_�w�g���ӰӮa���q��
    $orderID = checkOrder($clientID, $shopID);
    $nowgoodNum = get_goodNum($goodID);
    
    if ($orderID > 0) {
        // �ˬd�ʪ��������X��
        $nowNum = checkNum($orderID, $goodID);

        if ($nowNum > 0) {
            // �ӫ~�w�s�b�A�N��ƶq�[�@
            $newNum = $nowNum + $goodNum;
            if ($newNum > $nowgoodNum){//����W�L�ӫ~�̤j�w�s
                return false;
            }
            $sql = "update details set purNum = ? where orderID=? and goodID = ?";
            $stmt = mysqli_prepare($db, $sql);
            mysqli_stmt_bind_param($stmt, "iii", $newNum, $orderID, $goodID);
            mysqli_stmt_execute($stmt);
        } else {
            // �ӫ~���s�b�A�s�W�@���ʪ�������
            $sql = "insert into details (orderID, goodID, purNum) values (?, ?, ?)";
            $stmt = mysqli_prepare($db, $sql);
            mysqli_stmt_bind_param($stmt, "iii", $orderID, $goodID, $goodNum);
            mysqli_stmt_execute($stmt);
        }
    } else {
        // �S���ӰӮa���q��A�إ߷s�q��
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
// �ˬd�Ȥ�O�_�w�g���ӰӮa���q��
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

// �إ߷s���q��
function createOrder($clientID, $shopID) {
    global $db;
    
    $sql = "insert into orders (clientID, shopID, orderStatus) values (?, ?, 0)";
    $stmt = mysqli_prepare($db, $sql);
    mysqli_stmt_bind_param($stmt, "ii", $clientID, $shopID);
    mysqli_stmt_execute($stmt);

    // �^��orderID
    return mysqli_insert_id($db);
}


function del_Cart($detailID, $clientID, $goodID) {
    global $db;
    $goodNum = 1;
    $orderID = checkOrder($clientID, $goodID);
    $nowNum = checkNum($orderID, $goodID);

    if ($nowNum > 1) {
        // �ӫ~�w�s�b�A�N��ƶq��@
        $newNum = $nowNum - $goodNum;
        $sql = "update details set purNum = ? where goodID = ? and detailID = ?";
        $stmt = mysqli_prepare($db, $sql);
        mysqli_stmt_bind_param($stmt, "iii", $newNum, $goodID, $detailID);
        mysqli_stmt_execute($stmt);
    } else if ($nowNum == 1) {
        // �ӫ~���s�b�A�R���ʪ�������
        $sql = "delete from details where detailID=?";
        $stmt = mysqli_prepare($db, $sql);
        mysqli_stmt_bind_param($stmt, "i", $detailID);
        mysqli_stmt_execute($stmt);

        // �ˬd�O�_���Ȥ�Ӯa���̫�@�Ӱӫ~
        $count = count_details($orderID);

        if ($count == 0) {
            deleteOrder($orderID);
        }
    }

    return true;
}

// �ˬd�q�椺���X��
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

// �R���q��
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
    return $row['total']; //�T�O���|����L��
}
function send_Order($clientID) {
    global $db;
    
    // ��s�q�檬�A
    $sql = "update orders set orderStatus = 1 where clientID = ? and orderStatus = 0;";
    $stmt = mysqli_prepare($db, $sql);
    mysqli_stmt_bind_param($stmt, "i", $clientID);
    mysqli_stmt_execute($stmt);
    
    // ���o�q�椤���ӫ~�μƶq
    $sql2 = "select goodID, purNum from details where orderID in (select orderID from orders where clientID = ? and orderStatus = 1)";
    $stmt2 = mysqli_prepare($db, $sql2);
    mysqli_stmt_bind_param($stmt2, "i", $clientID);
    mysqli_stmt_execute($stmt2);
    $result = mysqli_stmt_get_result($stmt2);
    
    // �̧ǧ�s�w�s
    while ($row = mysqli_fetch_assoc($result)) {
        update_goodNum($row['goodID'], $row['purNum']);
    }
}
// ��s�w�s
function update_goodNum($goodID, $purNum) {
    global $db;
    
    $sql = "UPDATE goods SET goodNum = goodNum - ? WHERE goodID = ?";
    $stmt = mysqli_prepare($db, $sql);
    mysqli_stmt_bind_param($stmt, "ii", $purNum, $goodID);
    mysqli_stmt_execute($stmt);
}

//�d�ݭq��
function get_OrderList($clientID){
	global $db;
	$sql = "select * from orders where clientID=? and orderStatus > 0;";
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

//��������
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
    update_ShopStar($shopID);//��s����
}

//��s�Ӯa����
function update_ShopStar($shopID) {
    global $db;
    //�p�⥭��
    $sql = "select avg(feedback) as avgfeedback from orders where shopID = ?";
    $stmt = mysqli_prepare($db, $sql);
    mysqli_stmt_bind_param($stmt, "i", $shopID);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);
    $avg = $row['avgfeedback'];
    //��s����
    $sql2 = "update shops set shopStar = ? where shopID = ?";
    $stmt2 = mysqli_prepare($db, $sql2);
    mysqli_stmt_bind_param($stmt2, "ii", $avg, $shopID);
    mysqli_stmt_execute($stmt2);
}
?>