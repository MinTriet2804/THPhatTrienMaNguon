<?php
if (session_status() === PHP_SESSION_NONE) session_start();
include 'app/views/shares/header.php';
?>

<style>
.form-page { padding: 30px 0 60px; }
.form-card {
    background: #fff; border-radius: 12px; overflow: hidden;
    box-shadow: 0 4px 20px rgba(0,0,0,0.06); max-width: 620px; margin: 0 auto;
}
.form-card-header {
    background: #d70018; color: #fff; padding: 20px 28px;
    font-weight: 700; font-size: 1.2rem; display: flex; align-items: center; gap: 10px;
}
.form-card-body { padding: 30px 28px; }
.form-group { margin-bottom: 22px; }
.form-group label { font-weight: 600; font-size: 0.9rem; color: #222; margin-bottom: 7px; display: block; }
.form-group label span { color: #d70018; }
.form-control {
    border-radius: 8px; border: 1px solid #e0e0e0;
    padding: 11px 14px; font-size: 0.95rem;
    transition: all 0.2s; background: #f9fafb; width: 100%;
}
.form-control:focus {
    border-color: #d70018; outline: none;
    box-shadow: 0 0 0 3px rgba(215,0,24,0.1); background: #fff;
}
.is-invalid { border-color: #d70018 !important; }
.invalid-feedback { color: #d70018; font-size: 0.82rem; margin-top: 5px; display: block; }
.btn-submit {
    background: #d70018; color: #fff; border: none; border-radius: 8px;
    padding: 13px; font-weight: 700; font-size: 0.95rem; width: 100%;
    cursor: pointer; transition: all 0.2s; box-shadow: 0 4px 12px rgba(215,0,24,0.2);
}
.btn-submit:hover { background: #b50013; }
.btn-back {
    display: flex; align-items: center; justify-content: center; gap: 7px;
    color: #d70018; border: 1px solid #d70018; border-radius: 8px;
    padding: 11px; font-weight: 600; font-size: 0.9rem; width: 100%;
    text-decoration: none; margin-top: 10px; transition: all 0.2s;
}
.btn-back:hover { background: #fff5f5; color: #b50013; }
</style>

<div class="container form-page">
    <div class="form-card">
        <div class="form-card-header">
            <i class="fas fa-folder-plus"></i> Thêm danh mục mới
        </div>
        <div class="form-card-body">
            <form method="POST" action="/webbanhang/Category/save">
                <div class="form-group">
                    <label>Tên danh mục <span>*</span></label>
                    <input type="text" name="name" class="form-control <?php echo isset($errors['name']) ? 'is-invalid' : ''; ?>"
                           value="<?php echo htmlspecialchars($_POST['name'] ?? '', ENT_QUOTES); ?>"
                           placeholder="Ví dụ: Điện thoại, Laptop..." required>
                    <?php if (isset($errors['name'])): ?>
                        <span class="invalid-feedback"><i class="fas fa-exclamation-circle"></i> <?php echo $errors['name']; ?></span>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label>Mô tả</label>
                    <textarea name="description" class="form-control" rows="4"
                              placeholder="Mô tả ngắn về danh mục này..."><?php echo htmlspecialchars($_POST['description'] ?? '', ENT_QUOTES); ?></textarea>
                </div>

                <button type="submit" class="btn-submit">
                    <i class="fas fa-plus-circle"></i> THÊM DANH MỤC
                </button>
            </form>
            <a href="/webbanhang/Category" class="btn-back">
                <i class="fas fa-arrow-left"></i> Quay lại danh sách
            </a>
        </div>
    </div>
</div>

<?php include 'app/views/shares/footer.php'; ?>
