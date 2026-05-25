<?php
/**
 * Sinh URL hiển thị ảnh sản phẩm
 * Dùng img.php để serve ảnh — không phụ thuộc Apache config
 *
 * @param  string $filename  Tên file ảnh lưu trong DB (vd: 1234_iphone.webp)
 * @return string            URL đầy đủ để dùng trong src=""
 */
function imageUrl(string $filename): string
{
    if (empty(trim($filename))) {
        return '';
    }
    return '/webbanhang/img.php?f=' . urlencode(basename($filename));
}

/**
 * Render thẻ <img> sản phẩm với fallback khi ảnh lỗi
 *
 * @param  string $filename  Tên file ảnh
 * @param  string $alt       Alt text
 * @param  string $class     CSS class
 * @param  string $style     Inline style
 */
function productImage(string $filename, string $alt = '', string $class = '', string $style = ''): string
{
    $alt = htmlspecialchars($alt, ENT_QUOTES, 'UTF-8');

    if (empty(trim($filename))) {
        return '<div class="no-img-placeholder ' . htmlspecialchars($class) . '" style="' . htmlspecialchars($style) . '">
                    <i class="fas fa-image"></i>
                    <span>Chưa có ảnh</span>
                </div>';
    }

    $src = imageUrl($filename);
    return '<img src="' . $src . '" alt="' . $alt . '"'
         . ($class ? ' class="' . htmlspecialchars($class) . '"' : '')
         . ($style ? ' style="' . htmlspecialchars($style) . '"' : '')
         . ' onerror="this.parentElement.innerHTML=\'<div class=\\\'no-img-placeholder\\\'><i class=\\\'fas fa-image\\\'></i><span>Không tìm thấy ảnh</span></div>\'"'
         . '>';
}
?>
