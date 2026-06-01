<?php
// Debug toàn bộ pipeline ảnh
$pdo = new PDO('mysql:host=localhost;dbname=webbanhang;charset=utf8mb4', 'root', '');

// 1. Lấy vài sản phẩm có ảnh từ DB
$rows = $pdo->query("SELECT id, name, image FROM product WHERE image != '' LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);

echo "<h2>1. Giá trị image trong DB</h2><pre>";
foreach ($rows as $r) {
    echo "ID {$r['id']}: [{$r['image']}]\n";
}
echo "</pre>";

// 2. Kiểm tra file tồn tại trên disk
echo "<h2>2. File trên disk</h2><pre>";
foreach ($rows as $r) {
    $basename = basename($r['image']);
    $path1 = __DIR__ . '/public/images/' . $basename;
    $path2 = __DIR__ . '/' . $r['image'];
    echo "ID {$r['id']}:\n";
    echo "  basename       : {$basename}\n";
    echo "  path via basename: " . ($path1) . " => " . (file_exists($path1) ? "EXISTS" : "MISSING") . "\n";
    echo "  path via db val  : " . ($path2) . " => " . (file_exists($path2) ? "EXISTS" : "MISSING") . "\n\n";
}
echo "</pre>";

// 3. Kiểm tra img.php hoạt động không
echo "<h2>3. Test img.php trực tiếp</h2>";
foreach ($rows as $r) {
    $basename = basename($r['image']);
    $url = '/webbanhang/img.php?f=' . urlencode($basename);
    echo "<div style='margin:10px;border:1px solid #ccc;padding:10px;'>";
    echo "<b>ID {$r['id']} — {$r['name']}</b><br>";
    echo "DB value: <code>{$r['image']}</code><br>";
    echo "img.php URL: <code>{$url}</code><br>";
    echo "<img src='{$url}' style='max-height:100px;border:2px solid green;' onerror=\"this.style.border='2px solid red';this.alt='FAILED';\">";
    echo "</div>";
}

// 4. Kiểm tra direct URL
echo "<h2>4. Test direct URL (public/images/...)</h2>";
foreach ($rows as $r) {
    $basename = basename($r['image']);
    $directUrl = '/webbanhang/public/images/' . $basename;
    echo "<div style='margin:10px;border:1px solid #ccc;padding:10px;'>";
    echo "<b>Direct URL:</b> <code>{$directUrl}</code><br>";
    echo "<img src='{$directUrl}' style='max-height:100px;border:2px solid green;' onerror=\"this.style.border='2px solid red';this.alt='FAILED';\">";
    echo "</div>";
}

// 5. Thông tin server
echo "<h2>5. Server Info</h2><pre>";
echo "DOCUMENT_ROOT : " . $_SERVER['DOCUMENT_ROOT'] . "\n";
echo "__DIR__       : " . __DIR__ . "\n";
echo "HTTP_HOST     : " . $_SERVER['HTTP_HOST'] . "\n";
echo "REQUEST_URI   : " . $_SERVER['REQUEST_URI'] . "\n";
echo "public/images exists: " . (is_dir(__DIR__ . '/public/images') ? 'YES' : 'NO') . "\n";
$files = glob(__DIR__ . '/public/images/*.{jpg,jpeg,png,webp,gif}', GLOB_BRACE);
echo "Files in public/images: " . count($files) . "\n";
foreach (array_slice($files, 0, 3) as $f) {
    echo "  " . basename($f) . "\n";
}
echo "</pre>";
?>
