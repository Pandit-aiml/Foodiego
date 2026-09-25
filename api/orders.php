<?php
header('Content-Type: application/json'); require 'config.php';
$result=$conn->query('SELECT id,customer_name,phone,address,total,payment_method,payment_status,status,created_at FROM orders ORDER BY id DESC');
$data=[]; while($row=$result->fetch_assoc()) $data[]=$row; echo json_encode($data);
?>
