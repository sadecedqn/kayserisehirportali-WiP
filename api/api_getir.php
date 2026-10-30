<?php
require_once '../config/baglan.php';

header('Content-Type: application/json');

if (isset($_GET['endpoint'])) {
    $endpoint = $_GET['endpoint'];
    $stmt = $db->prepare("SELECT json_data FROM api_cache WHERE endpoint_name = ?");
    $stmt->execute([$endpoint]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($result) {
        echo $result['json_data'];
    } else {
        echo json_encode(['error' => 'Data not found']);
    }
} else {
    echo json_encode(['error' => 'Endpoint missing']);
}
?>