<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

require_once 'includes/db_connection.php';

try {
    // Fetch FAQs from database
    $faqs = [];
    $result = $conn->query("SELECT id, question, answer, created_at FROM faqs ORDER BY created_at DESC");

    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $faqs[] = [
                'id' => $row['id'],
                'question' => $row['question'],
                'answer' => $row['answer'],
                'created_at' => $row['created_at']
            ];
        }
    }

    // Return FAQs as JSON
    echo json_encode([
        'success' => true,
        'faqs' => $faqs,
        'count' => count($faqs)
    ]);
} catch (Exception $e) {
    // Return error response
    echo json_encode([
        'success' => false,
        'error' => 'Database error: ' . $e->getMessage(),
        'faqs' => []
    ]);
} finally {
    if (isset($conn)) {
        $conn->close();
    }
}
