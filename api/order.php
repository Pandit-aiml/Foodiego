<?php
header('Content-Type: application/json');
require 'config.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); echo json_encode(['error'=>'POST required']); exit; }
$input=json_decode(file_get_contents('php://input'),true);
$name=trim($input['name']??''); $phone=trim($input['phone']??''); $address=trim($input['address']??''); $items=$input['items']??[];
$userId=isset($input['user_id']) ? intval($input['user_id']) : null;
$paymentMethod=trim($input['payment_method']??'COD');
$paymentStatus=trim($input['payment_status']??'Pending');

if(!$name||!$phone||!$address||!is_array($items)||count($items)===0){ http_response_code(400); echo json_encode(['error'=>'Please provide customer details and cart items.']); exit; }
$conn->begin_transaction();
try {
  $total=0; $clean=[];
  $stmt=$conn->prepare('SELECT price FROM menu_items WHERE id=?');
  foreach($items as $item){ $id=intval($item['id']); $qty=max(1,intval($item['quantity']??1)); $stmt->bind_param('i',$id); $stmt->execute(); $r=$stmt->get_result()->fetch_assoc(); if(!$r) throw new Exception('Invalid menu item'); $price=(float)$r['price']; $total += $price*$qty; $clean[]=[$id,$qty,$price]; }
  
  $stmt=$conn->prepare('INSERT INTO orders(user_id,customer_name,phone,address,total,payment_method,payment_status) VALUES(?,?,?,?,?,?,?)');
  $stmt->bind_param('isssdss',$userId,$name,$phone,$address,$total,$paymentMethod,$paymentStatus);
  $stmt->execute(); $orderId=$conn->insert_id;

  $oi=$conn->prepare('INSERT INTO order_items(order_id,menu_item_id,quantity,price) VALUES(?,?,?,?)');
  foreach($clean as [$id,$qty,$price]) { $oi->bind_param('iiid',$orderId,$id,$qty,$price); $oi->execute(); }
  $conn->commit(); echo json_encode(['success'=>true,'order_id'=>$orderId,'total'=>$total]);
} catch(Exception $e){ $conn->rollback(); http_response_code(500); echo json_encode(['error'=>$e->getMessage()]); }
?>
