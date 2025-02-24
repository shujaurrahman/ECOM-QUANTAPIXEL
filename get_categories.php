<?php
require_once('./logics.class.php');
$logic = new logics();

// Get categories and subcategories
$categories = $logic->getCategories();
$subcategories = $logic->getSubcategories();

// Format categories
$formattedCategories = [];
for($i = 0; $i < $categories['count']; $i++) {
    if($categories['statusval'][$i] == 1) {
        $formattedCategories[] = [
            'id' => $categories['id'][$i],
            'name' => $categories['name'][$i]
        ];
    }
}

// Format subcategories
$formattedSubcategories = [];
for($i = 0; $i < $subcategories['count']; $i++) {
    if($subcategories['status'][$i] == 1) {
        $formattedSubcategories[] = [
            'id' => $subcategories['id'][$i],
            'name' => $subcategories['name'][$i],
            'category_id' => $subcategories['category_id'][$i]
        ];
    }
}

// Return JSON response
header('Content-Type: application/json');
echo json_encode([
    'categories' => $formattedCategories,
    'subcategories' => $formattedSubcategories
]);