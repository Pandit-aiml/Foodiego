<?php
header('Content-Type: application/json');
require 'config.php';
$id = intval($_GET['restaurant_id'] ?? 0);
$stmt=$conn->prepare('SELECT * FROM menu_items WHERE restaurant_id=? ORDER BY id');
$stmt->bind_param('i',$id); $stmt->execute(); $result=$stmt->get_result();
$data=[]; while($row=$result->fetch_assoc()) $data[]=$row;
echo json_encode($data);
?>
