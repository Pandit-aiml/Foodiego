<?php
header('Content-Type: application/json');
require 'config.php';
$result = $conn->query('SELECT * FROM restaurants ORDER BY rating DESC');
$data=[]; while($row=$result->fetch_assoc()) $data[]=$row;
echo json_encode($data);
?>
