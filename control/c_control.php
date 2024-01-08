<?php
require('../model/clientModel.php');
$act=$_REQUEST['act'];

switch ($act) {
    case "findUserName": // 抓客戶姓名
        $id = (int)$_REQUEST['id']; 
        $result = findName($id);
        echo  json_encode($result);
        return;

    case "GoodsList": // 取得所有商品列表
        $Goods = get_GoodList();
        echo json_encode($Goods);
        return;

    case "addGood_cart": // 新增商品至購物車
        $goodid = $_POST['id'];
        $shopID = $_POST['shopID'];
        $clientID = $_POST['userID'];

        $result = add_Cart($clientID, $shopID, $goodid);
        echo json_encode($result);
        return;

    case "delGood_cart": // 刪除購物車內商品
        $goodID = $_POST['id'];
        $detailID = $_POST['detailID'];
        $clientID = $_POST['userID'];

        del_Cart($detailID, $clientID, $goodID);
        return;
    case "OrderList": // 抓訂單清單
        $clientID = (int)$_REQUEST['id']; 
        $Order = get_OrderList($clientID);

        foreach ($Order as &$order) {
            $originalStatus = $order['orderStatus'];
            if($originalStatus===1)
                $order['orderStatus'] = '以結帳';
            else if($originalStatus===2)
                $order['orderStatus'] = '處理中';
            else if($originalStatus===3)
                $order['orderStatus'] = '寄送中';
            else if($originalStatus===4)
                $order['orderStatus'] = '已寄送';
            else if($originalStatus===5)
                $order['orderStatus'] = '已送達';
            else if($originalStatus===6)
                $order['orderStatus'] = '已完成並評價';
        }
        echo json_encode($Order);
        return;  
    
    case "CartList": // 取得購物車內容物清單
        $clientID = (int)$_REQUEST['id']; 
        $Cart = get_CartList($clientID);
        echo json_encode($Cart);
        return;

    case "get_total": // 取得總金額
        $clientID = (int)$_REQUEST['id']; 
        $Price = get_Total($clientID);
        echo json_encode($Price);
		return;

    case "sendOrder": // 結帳
        $clientID = (int)$_REQUEST['id']; 
        send_Order($clientID);
        echo '';
        return;

    case "setStar":
        $id = (int)$_REQUEST['id']; 
        $star = (int)$_REQUEST['star']; 
        feedback_Order($star,$id);
        echo json_encode("成功");
        return;

    default:
        
}
?>
