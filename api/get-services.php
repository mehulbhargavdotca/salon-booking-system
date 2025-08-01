<?php
// API endpoint to get services by category
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../includes/config.php';
require_once '../includes/functions.php';

// Check if request method is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    errorResponse('Method not allowed', 405);
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

// Validate input
if (!isset($input['category_id']) || empty($input['category_id'])) {
    errorResponse('Category ID is required');
}

$categoryId = (int)$input['category_id'];

try {
    $db = getDB();
    
    // Get category information
    $category = $db->fetch(
        "SELECT * FROM main_categories WHERE id = ? AND status = 'active'",
        [$categoryId]
    );
    
    if (!$category) {
        errorResponse('Category not found');
    }
    
    // Get subcategories with services
    $subcategories = $db->fetchAll(
        "SELECT * FROM subcategories WHERE main_category_id = ? AND status = 'active' ORDER BY name",
        [$categoryId]
    );
    
    $result = [
        'category' => $category,
        'subcategories' => []
    ];
    
    foreach ($subcategories as $subcategory) {
        // Get services for this subcategory
        $services = $db->fetchAll(
            "SELECT * FROM services WHERE subcategory_id = ? AND status = 'active' ORDER BY name",
            [$subcategory['id']]
        );
        
        if (!empty($services)) {
            $subcategory['services'] = $services;
            $result['subcategories'][] = $subcategory;
        }
    }
    
    successResponse('Services retrieved successfully', $result);
    
} catch (Exception $e) {
    error_log("Error in get-services.php: " . $e->getMessage());
    errorResponse('Failed to retrieve services');
}
?>