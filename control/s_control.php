<?php
require('../model/shopModel.php');
$act=$_REQUEST['act'];

switch ($act) {
    case "findUserName": // 抓客戶姓名
        $id = (int)$_REQUEST['id']; 
        $result = findName($id);
        echo json_encode($result);
        return;

    case "GoodsList": // 取得所有商品列表
        $shopID = (int)$_REQUEST['id']; 
        $Goods = get_ShopList($shopID);
        echo json_encode($Goods);
        return;

    case "delGood": // 刪除商品
        $id=(int)$_REQUEST['id']; 
        del_Good($id);
        return;

    case "addGood": // 新增商品
        $jsonStr = $_POST['dat'];
        $good = json_decode($jsonStr);
        add_Good($good->shopID,$good->goodName,$good->goodPrice,$good->goodContent,$good->goodNum,$good->goodID);
        return;   
         
    case "OrderList": // 抓訂單清單
        $shopID = (int)$_REQUEST['id']; 
        $Order = confirm_OrderList($shopID);

        foreach ($Order as &$order) {
            $originalStatus = $order['orderStatus'];
            if($originalStatus===1)
                $order['orderStatus'] = '已結帳';
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

    case "setOrderStatus":
        $orderID = (int)$_REQUEST['orderID']; 
        $status = $_REQUEST['status']; 
        transmit_Order($status,$orderID);
        echo json_encode('');
        return;
    case "checkOderDetail":
        $id=(int)$_REQUEST['id'];
        $Details = get_OrderDetail($id);
        echo json_encode($Details);
        return; 

    default:
}
?>