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
// 在需要授權的地方，檢查用戶是否已經登錄
if (!isUserLoggedIn()) {
    // 未登錄，進行適當處理，例如跳轉到登錄頁面
    header("Location: login.php");
    exit();
}

// 檢查用戶是否擁有訪問商家資源的權限
if (!userHasPermission('access_merchant_resources')) {
    // 未授權，返回錯誤或顯示相應的訊息
    echo json_encode(['error' => 'Permission denied']);
    exit();
}
//通知狀態 查詢網路資料後加上的
//在訂單狀態更新時，發送通知給用戶
function sendOrderStatusNotification($userId, $orderId, $newStatus) {
    // 實現通知的邏輯，可以使用電子郵件、簡訊、推送通知等方式
    // ...
}

// 調用通知函式
sendOrderStatusNotification($userId, $orderId, $newStatus);
