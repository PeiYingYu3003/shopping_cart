<?php
require('model.php');
$act=$_REQUEST['act'];

switch ($act) {
	case "get_GoodsList":
		$Goods = get_GoodList();
		echo json_encode($Goods);
		return;  
	
	case "add_Good":
		$GoodName = $_POST['name']; //$_GET, $_REQUEST
		$GoodPrice = $_POST['price'];
		$GoodContent = $_POST['content'];
		//verify
		addGood($GoodName,$GoodUrgent,$GoodContent);
		return;
	
	case "del_Good":
		$id=(int)$_REQUEST['id']; //$_GET, $_REQUEST
		//verify
		del_Good($id);
		return;
	
	case "set_Good":
		$id=(int)$_REQUEST['id']; //$_GET, $_REQUEST
		//verify
		set_Good($id);
		return;
	
	default:
  
}

?>