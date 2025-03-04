<?php


error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once('./logics.class.php');
$Obj = new logics();

header('Content-Type: application/json');

$searchTerm = $_GET['query'] ?? '';

if (!empty($searchTerm)) {
    $results = $Obj->searchProducts($searchTerm);
    
    if ($results['status'] === 1) {
        $products = array();
        for ($i = 0; $i < $results['count']; $i++) {
            $products[] = array(
                'id' => $results['id'][$i],
                'product_name' => $results['product_name'][$i],
                'slug' => $results['slug'][$i],
                'featured_image' => $results['featured_image'][$i],
                'discounted_price' => number_format($results['discounted_price'][$i], 2),
                'product_price' => number_format($results['product_price'][$i], 2),
                'discount_percentage' => $results['discount_percentage'][$i]
            );
        }
        echo json_encode($products);
    } else {
        echo json_encode([]);
    }
} else {
    echo json_encode([]);
}