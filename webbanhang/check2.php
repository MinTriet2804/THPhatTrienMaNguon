<?php
$pdo  = new PDO('mysql:host=localhost;dbname=webbanhang;charset=utf8mb4', 'root', '');

// Lấy tất cả sản phẩm có ảnh
$rows = $pdo->query("SELECT id, name, image FROM product WHERE image IS NOT NULL AND image != '' LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);

echo "=== GIÁ TRỊ IMAGE TRONG DB ===\n";
foreach ($rows as $r) {
    echo "ID {$r['id']}: [{$r['image']}]\n";
    
    // Thử các cách tìm file
    $basename = basename($r['image']);
    
    $paths = [
        'basename only'        => __DIR__ . '/public/images/' . $basename,
        'full db value'        => __DIR__ . '/' . $r['image'],
        'db value as-is'       => $r['image'],
    ];
    
    foreach ($paths as $label => $path) {
        echo "  [{$label}] {$path} => " . (file_exists($path) ? "EXISTS ✓" : "MISSING ✗") . "\n";
    }
    echo "\n";
}

// Liệt kê file thực tế trong public/images
echo "=== FILES THỰC TẾ TRONG public/images ===\n";
$dir = __DIR__ . '/public/images/';
if (is_dir($dir)) {
    $files = scandir($dir);
    $imgFiles = array_filter($files, fn($f) => preg_match('/\.(jpg|jpeg|png|webp|gif)$/i', $f));
    foreach (array_slice(array_values($imgFiles), 0, 5) as $f) {
        echo "  $f\n";
    }
    echo "  Total: " . count($imgFiles) . " files\n";
} else {
    echo "  DIRECTORY NOT FOUND: $dir\n";
}

// Test imageUrl logic
echo "\n=== TEST imageUrl() LOGIC ===\n";
foreach ($rows as $r) {
    $basename = basename($r['image']);
    $url = '/webbanhang/img.php?f=' . urlencode($basename);
    echo "ID {$r['id']}: img.php URL = {$url}\n";
}
