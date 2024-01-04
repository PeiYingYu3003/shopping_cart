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


//下一步(不確定)

class UserController {
    private $users;

    public function __construct() {
        // 建立一個使用者資料陣列，模擬資料庫中的使用者資料
        $this->users = [
            ['id' => 1, 'username' => 'user1', 'password' => 'password1'],
            ['id' => 2, 'username' => 'user2', 'password' => 'password2'],
        ];
    }

    public function login($username, $password) {
        // 透過迴圈檢查提供的帳號和密碼是否與陣列中的某個使用者匹配
        foreach ($this->users as $user) {
            if ($user['username'] == $username && $user['password'] == $password) {
                // 登入成功，將使用者的 ID 存入 session 中
                $_SESSION['user_id'] = $user['id'];
                return true;
            }
        }

        // 登入失敗
        return false;
    }

    public function register($username, $password) {
        // 假設簡單註冊，直接將新的帳號和密碼加入使用者陣列
        $newUser = ['id' => count($this->users) + 1, 'username' => $username, 'password' => $password];
        $this->users[] = $newUser;
        return true;
    }

    // 未顯示的部分可能包含其他功能，例如修改密碼、取得使用者資訊等等
}

