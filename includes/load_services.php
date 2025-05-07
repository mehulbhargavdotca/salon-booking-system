<?php
include 'db.php';

if (isset($_GET['category_id'])) {
    $cat_id = (int)$_GET['category_id'];
    $query = $conn->prepare("SELECT id, name, price FROM services WHERE category_id = ?");
    $query->bind_param("i", $cat_id);
    $query->execute();
    $result = $query->get_result();

    $services = [];
    while ($row = $result->fetch_assoc()) {
        $services[] = $row;
    }

    echo json_encode($services);
}
?>
