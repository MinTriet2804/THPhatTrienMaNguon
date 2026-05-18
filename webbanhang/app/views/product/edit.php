<?php include 'app/views/shares/header.php'; ?>

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<style>
    body {
        font-family: 'Inter', sans-serif;
        background-color: #f4f6f9;
        color: #444;
    }
    .cps-container {
        max-width: 700px;
        margin: 40px auto;
        padding: 0 15px;
    }
    .cps-card {
        background: #ffffff;
        border-radius: 12px;
        border: none;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
        overflow: hidden;
    }
    .cps-card-header {
        background: #d70018; /* Màu đỏ thương hiệu TechStore */
        color: #fff;
        padding: 20px;
        text-align: center;
        font-weight: 700;
        font-size: 1.35rem;
        letter-spacing: 0.5px;
        text-transform: uppercase;
    }
    .cps-card-body {
        padding: 30px;
    }
    .form-group {
        margin-bottom: 20px;
    }
    .form-group label {
        font-weight: 600;
        font-size: 0.9rem;
        color: #222;
        margin-bottom: 8px;
        display: block;
    }
    .form-control {
        border-radius: 8px;
        border: 1px solid #e0e0e0;
        padding: 12px 16px;
        font-size: 0.95rem;
        transition: all 0.2s ease;
        background-color: #f9fafb;
    }
    .form-control:focus {
        border-color: #d70018;
        box-shadow: 0 0 0 3px rgba(215, 0, 24, 0.15);
        background-color: #fff;
    }
    select.form-control {
        height: auto;
    }
    .btn-cps-update {
        background-color: #d70018;
        color: white;
        border: none;
        border-radius: 8px;
        padding: 14px;
        font-weight: 600;
        font-size: 1rem;
        width: 100%;
        transition: background-color 0.2s ease;
        cursor: pointer;
        box-shadow: 0 4px 12px rgba(215, 0, 24, 0.2);
    }
    .btn-cps-update:hover {
        background-color: #b50013;
        color: white;
    }
    .btn-cps-back {
        display: flex;
        align-items: center;
        justify-content: center;
        background-color: #fff;
        color: #d70018;
        border: 1px solid #d70018;
        border-radius: 8px;
        padding: 12px;
        font-weight: 600;
        font-size: 0.95rem;
        width: 100%;
        margin-top: 12px;
        transition: all 0.2s ease;
        text-decoration: none !important;
    }
    .btn-cps-back:hover {
        background-color: #fff5f5;
        color: #b50013;
        border-color: #b50013;
    }
    .btn-cps-back i {
        margin-right: 8px;
    }
    .alert-danger {
        border-radius: 8px;
        font-size: 0.9rem;
        background-color: #fff5f5;
        border: 1px solid #ffe3e3;
        color: #d70018;
    }
</style>

<div class="cps-container">
    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger shadow-sm mb-4">
            <div class="font-weight-bold mb-1"><i class="fas fa-exclamation-circle"></i> Có lỗi xảy ra:</div>
            <ul class="pl-4 mb-0">
                <?php foreach ($errors as $error): ?>
                    <li><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <div id="client-error" class="alert alert-danger shadow-sm mb-4" style="display: none;">
        <div class="font-weight-bold mb-1"><i class="fas fa-exclamation-triangle"></i> Lỗi định dạng ảnh:</div>
        <span id="client-error-msg"></span>
    </div>

    <div class="cps-card">
        <div class="cps-card-header">
            <i class="fas fa-edit"></i> Cập Nhật Thông Tin Sản Phẩm
        </div>
        
        <div class="cps-card-body">
            <form method="POST" action="/webbanhang/Product/update" enctype="multipart/form-data" onsubmit="return validateForm();">
                
                <input type="hidden" name="id" value="<?php echo $product->id; ?>">
                
                <div class="form-group">
                    <label for="name"><i class="fas fa-mobile-alt"></i> Tên sản phẩm</label>
                    <input type="text" id="name" name="name" class="form-control" 
                           value="<?php echo htmlspecialchars($product->name, ENT_QUOTES, 'UTF-8'); ?>" required>
                </div>

                <div class="form-group">
                    <label for="category_id"><i class="fas fa-th-large"></i> Danh mục sản phẩm</label>
                    <select id="category_id" name="category_id" class="form-control" required>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?php echo $category->id; ?>" <?php echo $category->id == $product->category_id ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($category->name, ENT_QUOTES, 'UTF-8'); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="price"><i class="fas fa-tags"></i> Giá bán (đơn vị: VNĐ)</label>
                    <input type="number" id="price" name="price" class="form-control" step="0.01" min="0"
                           value="<?php echo htmlspecialchars($product->price, ENT_QUOTES, 'UTF-8'); ?>" required>
                </div>

                <div class="form-group">
                    <label for="description"><i class="fas fa-file-alt"></i> Mô tả chi tiết thông số kỹ thuật</label>
                    <textarea id="description" name="description" class="form-control" rows="5" required><?php echo htmlspecialchars($product->description, ENT_QUOTES, 'UTF-8'); ?></textarea>
                </div>

                <div class="form-group">
                    <label for="image"><i class="fas fa-image"></i> Hình ảnh sản phẩm</label>
                    
                    <?php if (!empty($product->image) && file_exists("public/images/" . $product->image)): ?>
                        <div class="mb-3 p-2 bg-light text-center border" style="border-radius: 8px; max-width: 150px;">
                            <span class="text-muted d-block small mb-1">Ảnh hiện tại:</span>
                            <img src="/webbanhang/public/images/<?php echo $product->image; ?>" alt="Product Image" style="max-height: 100px; max-width: 100%; object-fit: contain;">
                        </div>
                    <?php endif; ?>
                    
                    <input type="file" id="image" name="image" class="form-control-file p-2" style="border: 1px dashed #d70018; border-radius: 8px; background: #fff5f5; width: 100%;">
                    <small class="text-muted d-block mt-1">* Để trống nếu bạn muốn giữ nguyên hình ảnh cũ.</small>
                </div>

                <button type="submit" class="btn-cps-update">
                    <i class="fas fa-save"></i> LƯU THAY ĐỔI
                </button>
            </form>

            <a href="/webbanhang/Product" class="btn-cps-back">
                <i class="fas fa-arrow-left"></i> Quay lại danh sách sản phẩm
            </a>
        </div>
    </div>
</div>

<script>
function validateForm() {
    const fileInput = document.getElementById('image');
    const errorDiv = document.getElementById('client-error');
    const errorMsg = document.getElementById('client-error-msg');
    
    errorDiv.style.display = 'none';

    if (fileInput.files.length > 0) {
        const file = fileInput.files[0];
        const validExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        const fileExtension = file.name.split('.').pop().toLowerCase();
        
        if (!validExtensions.includes(fileExtension)) {
            errorMsg.innerHTML = "Định dạng file không khớp! Vui lòng chỉ tải lên ảnh (.jpg, .jpeg, .png, .webp, .gif)";
            errorDiv.style.display = 'block';
            window.scrollTo(0, 0);
            return false;
        }

        if (file.size > 5 * 1024 * 1024) {
            errorMsg.innerHTML = "Dung lượng ảnh vượt giới hạn 5MB!";
            errorDiv.style.display = 'block';
            window.scrollTo(0, 0);
            return false;
        }
    }
    return true;
}
</script>

<?php include 'app/views/shares/footer.php'; ?>