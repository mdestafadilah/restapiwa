<?php
/**
 * WhatsApp Gateway Admin API
 * REST API endpoint for CRUD operations
 */

require_once __DIR__ . '/../vendor/autoload.php';

use Mdestafadilah\ApiWaRest\DatabaseManager;

header('Content-Type: application/json');

try {
    $db = new DatabaseManager(__DIR__ . '/../data/wa_gateway.db');
    $db->init();
    
    $action = $_GET['action'] ?? '';
    
    switch ($action) {
        case 'list':
            $servers = $db->getAll();
            echo json_encode(['success' => true, 'data' => $servers]);
            break;
            
        case 'get':
            $id = $_GET['id'] ?? 0;
            $server = $db->getById($id);
            if ($server) {
                echo json_encode(['success' => true, 'data' => $server]);
            } else {
                http_response_code(404);
                echo json_encode(['success' => false, 'message' => 'Server not found']);
            }
            break;
            
        case 'create':
            $data = json_decode(file_get_contents('php://input'), true);
            if (empty($data)) {
                $data = $_POST;
            }
            
            $id = $db->create($data);
            if ($id) {
                echo json_encode(['success' => true, 'id' => $id, 'message' => 'Server created successfully']);
            } else {
                http_response_code(500);
                echo json_encode(['success' => false, 'message' => 'Failed to create server']);
            }
            break;
            
        case 'update':
            $id = $_GET['id'] ?? 0;
            $data = json_decode(file_get_contents('php://input'), true);
            if (empty($data)) {
                $data = $_POST;
            }
            
            $result = $db->update($id, $data);
            if ($result) {
                echo json_encode(['success' => true, 'message' => 'Server updated successfully']);
            } else {
                http_response_code(500);
                echo json_encode(['success' => false, 'message' => 'Failed to update server']);
            }
            break;
            
        case 'delete':
            $id = $_GET['id'] ?? $_POST['id'] ?? 0;
            $result = $db->delete($id);
            if ($result) {
                echo json_encode(['success' => true, 'message' => 'Server deleted successfully']);
            } else {
                http_response_code(500);
                echo json_encode(['success' => false, 'message' => 'Failed to delete server']);
            }
            break;
            
        case 'toggle':
            $id = $_GET['id'] ?? $_POST['id'] ?? 0;
            $result = $db->toggleActive($id);
            if ($result) {
                echo json_encode(['success' => true, 'message' => 'Server status toggled successfully']);
            } else {
                http_response_code(500);
                echo json_encode(['success' => false, 'message' => 'Failed to toggle server status']);
            }
            break;
            
        default:
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
            break;
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
