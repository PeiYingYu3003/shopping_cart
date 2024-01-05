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


function add_Cart($clientID, $shopID, $goodID) {
    global $db;
    $goodNum = 1;

    // �ˬd�Ȥ�O�_�w�g���ӰӮa���q��
    $orderID = checkOrder($clientID, $shopID);

    if ($orderID > 0) {
        // �Ȥ�w���ӰӮa���q��A�ˬd�ӫ~�O�_�w�s�b
        $nowNum = checkNum($orderID, $goodID);

        if ($nowNum > 0) {
            // �ӫ~�w�s�b�A�N��ƶq�[�@
            $newNum = $nowNum + $goodNum;
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
        // �Ȥ��٨S���ӰӮa���q��A�إ߷s���q��A�s�W�ӫ~
        $orderID = createOrder($clientID, $shopID);
        $sql = "insert into details (orderID, goodID, purNum) values (?, ?, ?)";
        $stmt = mysqli_prepare($db, $sql);
        mysqli_stmt_bind_param($stmt, "iii", $orderID, $goodID, $goodNum);
        mysqli_stmt_execute($stmt);
    }

    return true;
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

//�Ȥ�
//�e�X�q��
function send_Order($clientID){
    global $db;
    $sql = "update orders set orderStatus = 1 where clientID = ? and orderStatus = 0;";
    $stmt = mysqli_prepare($db, $sql);
    mysqli_stmt_bind_param($stmt, "i", $clientID);
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

//�Ӯa
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

//���y�\��
//�w�H�e
function deal_Deliver(){
	global $db;
	$sql = "select * from orders where orderStatus=3;";
	$stmt = mysqli_prepare($db, $sql);
	mysqli_stmt_execute($stmt); 
	$result = mysqli_stmt_get_result($stmt); 
	$rows = array();
	while($r = mysqli_fetch_assoc($result)) {
		$rows[] = $r; 
	}
	return $rows;
}
function check_Deliver($orderID){ 
	global $db;
	$sql = "update orders set orderStatus=4 where orderID=?;";
	$stmt = mysqli_prepare($db, $sql); 
    mysqli_stmt_bind_param($stmt, "i", $orderID);
	mysqli_stmt_execute($stmt); 
}

//�w�e�F
function deal_ready(){
	global $db;
	$sql = "select * from orders where orderStatus=4;"; //����������
	$stmt = mysqli_prepare($db, $sql);
	mysqli_stmt_execute($stmt); 
	$result = mysqli_stmt_get_result($stmt);
	$rows = array();
	while($r = mysqli_fetch_assoc($result)) {
		$rows[] = $r; 
	}
	return $rows;
}
function check_ready($orderID){ 
	global $db;
	$sql = "update orders set status=5 where orderID=?;";
	$stmt = mysqli_prepare($db, $sql); 
    mysqli_stmt_bind_param($stmt, "i", $orderID);
	mysqli_stmt_execute($stmt); 
}

//�Ȥ�
//����õ����q��
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

//���U�b��
function registerUser($account, $password, $role) {
    global $db;
    
    if(isExist($account)){ //�ˬd���Ƶ��U
        return false;
    }
    
    $newpassword = password_hash($password, PASSWORD_DEFAULT);
    $sql = "insert into users (account, password, role) values (?, ?, ?)";
    $stmt = mysqli_prepare($db, $sql);
    mysqli_stmt_bind_param($stmt, "ssi", $account, $newpassword, $role);
    mysqli_stmt_execute($stmt);

    return true;  // ���U���\
    
}
function isExist($account) {
    global $db;
    
    $sql = "SELECT * FROM users WHERE account = ?";
    $stmt = mysqli_prepare($db, $sql);
    mysqli_stmt_bind_param($stmt, "s", $account);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    return mysqli_num_rows($result) > 0;
}
//�n�J�b��
function login($account, $password) {
    global $db;

    $sql="select * from users where account=?";
    $stmt = mysqli_prepare($db, $sql);
    mysqli_stmt_bind_param($stmt,'s',$account);
    mysqli_stmt_execute($stmt);
    $result=mysqli_stmt_get_result($stmt);
    if ($row=mysqli_fetch_assoc($result)) { 
        if (password_verify($password,$row['password']))//���ұK�X
            $msg = "Welcome!";
        else
            $msg = "Wrong Password!";
    }
    else
      $msg = "Login fail!";
    return $msg;
}
//�p��Ӯa����
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