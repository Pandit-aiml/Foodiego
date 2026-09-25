<?php
header('Content-Type: application/json'); require 'config.php';
$input=json_decode(file_get_contents('php://input'),true); $id=intval($input['id']??0); $status=$input['status']??'';
$allowed=['Pending','Confirmed','Preparing','Out for Delivery','Delivered','Cancelled'];
if(!$id || !in_array($status,$allowed,true)){http_response_code(400); echo json_encode(['error'=>'Invalid request']); exit;}
$stmt=$conn->prepare('UPDATE orders SET status=? WHERE id=?'); $stmt->bind_param('si',$status,$id); $stmt->execute(); echo json_encode(['success'=>true]);
?>
