<?php
require('model.php');
$act=$_REQUEST['act'];

switch ($act) {
	// ***********************
	// 商家可用功能
	// ***********************
	case "GoodsList": // 取得所有商品列表
		$Goods = get_GoodList();
		echo json_encode($Goods);
		return;
	  
	case "addGood": // 新增商品
		$jsonStr = $_POST['dat'];
		$good = json_decode($jsonStr);
		add_Good($good->goodName,$good->goodPrice,$good->goodContent,$good->goodID);
		return;

	case "delGood": // 刪除商品
		$id=(int)$_REQUEST['id']; 
		del_Good($id);
		return;
	
	// ***********************
	// 客戶可用功能
	// ***********************
	case "CartList": // 取得購物車內容物清單
		$Cart = get_CartList(101);
		echo json_encode($Cart);
		return;

	case "addGood_cart": // 新增商品至購物車
		$id=(int)$_REQUEST['id']; 
		//verify
		add_Cart($id);
		return;

	case "delGood_cart": // 從購物車移除商品
		$id=(int)$_REQUEST['id']; 
		//verify
		del_Cart($id);
		return;

	case "get_total": // 計算購物車內商品總價set_Good
		$Price = get_Total();
		echo json_encode($Price);
		return;

	default:
  
}

?>