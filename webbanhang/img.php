<?php
/**
 * Image server — serve ảnh từ public/images/ an toàn
 * URL: /webbanhang/img.php?f=tenfile.webp
 */

$file = $_GET['f'] ?? '';

// Chặn path traversal
$file = basename($file);

if (empty($file)) {
    http_response_code(400);
    exit;
}

$base_dir  = __DIR__ . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'images' . DIRECTORY_SEPARATOR;
$full_path = $base_dir . $file;

if (!file_exists($full_path) || !is_file($full_path)) {
    http_response_code(404);
    // Trả về ảnh placeholder 1x1 trong suốt thay vì lỗi trắng
    header('Content-Type: image/gif');
    echo base64_decode('R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7');
    exit;
}

// Xác định MIME type
$ext = strtolower(pathinfo($full_path, PATHINFO_EXTENSION));
$mime_map = [
    'jpg'  => 'image/jpeg',
    'jpeg' => 'image/jpeg',
    'png'  => 'image/png',
    'gif'  => 'image/gif',
    'webp' => 'image/webp',
    'svg'  => 'image/svg+xml',
];
$mime = $mime_map[$ext] ?? 'application/octet-stream';

// Cache headers
$mtime = filemtime($full_path);
$etag  = md5($file . $mtime);
header('ETag: "' . $etag . '"');
header('Cache-Control: public, max-age=86400');
header('Last-Modified: ' . gmdate('D, d M Y H:i:s', $mtime) . ' GMT');

// 304 Not Modified nếu browser đã có cache
if (
    (isset($_SERVER['HTTP_IF_NONE_MATCH']) && trim($_SERVER['HTTP_IF_NONE_MATCH']) === '"' . $etag . '"') ||
    (isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) && strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']) >= $mtime)
) {
    http_response_code(304);
    exit;
}

header('Content-Type: ' . $mime);
header('Content-Length: ' . filesize($full_path));
readfile($full_path);
exit;
?>
