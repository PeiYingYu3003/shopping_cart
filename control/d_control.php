<?php
require('../model/model.php');
$act=$_REQUEST['act'];

switch ($act) {
    case "OrderList": // 抓訂單清單
        $Order = confirm_OrderList();

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

    default:
}