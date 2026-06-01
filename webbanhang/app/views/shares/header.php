<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1. Tính số lượng giỏ hàng cho badge
$_cartCount = 0;
if (!empty($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $item) {
        $_cartCount += $item['quantity'];
    }
}

// 2. Kiểm tra thông tin người dùng đã đăng nhập
$currentUser   = $_SESSION['username'] ?? null;
$currentRole   = $_SESSION['role']     ?? 'guest';
$currentAvatar = $_SESSION['avatar']   ?? '';
$currentFullname = $_SESSION['fullname'] ?? $currentUser;
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TechStore - Mua sắm công nghệ</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        * { box-sizing: border-box; }
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f4f6f9;
            color: #444;
            margin: 0;
        }

        /* ===== HEADER TOP ===== */
        .cps-header-top {
            background: linear-gradient(135deg, #d70018, #a8000f);
            padding: 12px 0;
            color: white;
            box-shadow: 0 2px 10px rgba(215,0,24,0.3);
        }
        .cps-logo {
            font-weight: 800;
            font-size: 1.5rem;
            color: white !important;
            text-decoration: none !important;
            letter-spacing: -0.5px;
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }
        .cps-logo .logo-badge {
            background-color: white;
            color: #d70018;
            padding: 2px 8px;
            border-radius: 6px;
            font-size: 1.1rem;
            font-weight: 900;
        }
        .cps-search-wrapper {
            position: relative;
        }
        .cps-search-bar {
            border-radius: 25px;
            border: none;
            padding: 10px 20px 10px 44px;
            font-size: 0.9rem;
            width: 100%;
            background: rgba(255,255,255,0.95);
            transition: all 0.2s;
        }
        .cps-search-bar:focus {
            outline: none;
            background: #fff;
            box-shadow: 0 0 0 3px rgba(255,255,255,0.3);
        }
        .cps-search-icon {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #aaa;
            font-size: 0.9rem;
        }
        .header-actions {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: 6px;
        }
        .btn-header-action {
            color: white !important;
            font-weight: 600;
            font-size: 0.85rem;
            padding: 8px 14px;
            border-radius: 8px;
            transition: all 0.2s;
            text-decoration: none !important;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            white-space: nowrap;
        }
        .btn-header-action:hover {
            background-color: rgba(255,255,255,0.18);
        }
        /* Nút giỏ hàng nổi bật */
        .btn-cart {
            background: rgba(255,255,255,0.2);
            border: 1px solid rgba(255,255,255,0.4);
            position: relative;
        }
        .btn-cart:hover {
            background: rgba(255,255,255,0.3) !important;
        }
        .cart-badge {
            position: absolute;
            top: -6px;
            right: -6px;
            background: #fff;
            color: #d70018;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            font-size: 0.7rem;
            font-weight: 800;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 2px solid #d70018;
            line-height: 1;
        }
        /* Nút quản lý danh mục */
        .btn-admin {
            background: #fff;
            color: #d70018 !important;
            border: none;
        }
        .btn-admin:hover {
            background: #fff0f0 !important;
        }
        .user-greeting {
            font-size: 0.85rem;
            color: rgba(255, 255, 255, 0.9);
            white-space: nowrap;
        }

        /* ===== NAV MENU ===== */
        .cps-nav-menu {
            background-color: #ffffff;
            box-shadow: 0 4px 10px rgba(0,0,0,0.06);
            border-bottom: 1px solid #e8e8e8;
        }
        .cps-menu-list {
            display: flex;
            align-items: center;
            list-style: none;
            margin: 0;
            padding: 0;
            overflow-x: auto;
            white-space: nowrap;
            scrollbar-width: none;
        }
        .cps-menu-list::-webkit-scrollbar { display: none; }
        .cps-menu-item a {
            display: flex;
            align-items: center;
            gap: 6px;
            padding: 14px 18px;
            color: #444;
            font-weight: 600;
            font-size: 0.88rem;
            text-decoration: none !important;
            transition: all 0.2s;
            border-bottom: 3px solid transparent;
        }
        .cps-menu-item a i { color: #999; transition: color 0.2s; font-size: 0.9rem; }
        .cps-menu-item a:hover {
            color: #d70018;
            border-bottom-color: #d70018;
        }
        .cps-menu-item a:hover i { color: #d70018; }

        /* ===== BANNER ===== */
        .cps-banner-container { margin-top: 20px; margin-bottom: 20px; }
        .cps-banner-wrapper {
            background: linear-gradient(135deg, #ff424e 0%, #69000a 100%);
            border-radius: 14px;
            padding: 36px 40px;
            color: white;
            position: relative;
            overflow: hidden;
            box-shadow: 0 8px 24px rgba(215,0,24,0.2);
        }
        .cps-banner-content { position: relative; z-index: 2; }
        .cps-banner-content h2 {
            font-weight: 800;
            font-size: 1.8rem;
            margin-bottom: 8px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .cps-banner-content p { font-size: 0.95rem; opacity: 0.9; margin-bottom: 0; }
        .cps-banner-circle {
            position: absolute;
            background: rgba(255,255,255,0.07);
            border-radius: 50%;
        }
        .cps-banner-circle-1 { width: 280px; height: 280px; top: -60px; right: -40px; }
        .cps-banner-circle-2 { width: 160px; height: 160px; bottom: -40px; right: 200px; }
        .cps-banner-circle-3 { width: 80px; height: 80px; top: 20px; right: 260px; }

        /* ===== MAIN CONTENT WRAPPER ===== */
        .main-content { padding-bottom: 20px; }
    </style>
</head>
<body>

    <header class="cps-header-top">
        <div class="container-fluid px-md-5">
            <div class="row align-items-center">
                <div class="col-lg-2 col-md-3 col-5">
                    <a href="/webbanhang/Product" class="cps-logo">
                        Tech<span class="logo-badge">S</span>
                    </a>
                </div>

                <div class="col-lg-5 d-none d-lg-block">
                    <div class="cps-search-wrapper">
                        <i class="fas fa-search cps-search-icon"></i>
                        <input type="text" class="cps-search-bar" placeholder="Tìm kiếm sản phẩm công nghệ...">
                    </div>
                </div>

                <div class="col-lg-5 col-md-9 col-7">
                    <div class="header-actions">
                        <a href="/webbanhang/Product" class="btn-header-action d-none d-md-inline-flex">
                            <i class="fas fa-store"></i> Cửa hàng
                        </a>

                        <?php if ($currentUser): ?>
                            <a href="/webbanhang/Product/add" class="btn-header-action d-none d-md-inline-flex">
                                <i class="fas fa-plus-circle"></i> Thêm SP
                            </a>
                        <?php endif; ?>

                        <a href="/webbanhang/Cart" class="btn-header-action btn-cart">
                            <i class="fas fa-shopping-cart"></i>
                            <span class="d-none d-sm-inline">Giỏ hàng</span>
                            <?php if ($_cartCount > 0): ?>
                                <span class="cart-badge"><?php echo $_cartCount > 99 ? '99+' : $_cartCount; ?></span>
                            <?php endif; ?>
                        </a>

                        <?php if ($currentUser): ?>
                            <a href="/webbanhang/Category" class="btn-header-action btn-admin d-none d-lg-inline-flex">
                                <i class="fas fa-cog"></i> Quản lý
                            </a>
                            <!-- Dropdown thông tin user -->
                            <div class="dropdown d-none d-sm-inline-block ml-1">
                                <button class="btn-header-action dropdown-toggle" type="button"
                                        id="userDropdown" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"
                                        style="background:rgba(255,255,255,0.15);border-radius:8px;border:1px solid rgba(255,255,255,0.3);">
                                    <?php if (!empty($currentAvatar)): ?>
                                        <img src="/webbanhang/img.php?f=<?= urlencode($currentAvatar) ?>"
                                             alt="avatar"
                                             style="width:26px;height:26px;border-radius:50%;object-fit:cover;margin-right:6px;border:2px solid rgba(255,255,255,0.5);">
                                    <?php else: ?>
                                        <i class="fas fa-user-circle mr-1"></i>
                                    <?php endif; ?>
                                    <span><?= htmlspecialchars($currentFullname, ENT_QUOTES, 'UTF-8') ?></span>
                                    <?php if ($currentRole === 'admin'): ?>
                                        <span style="background:rgba(255,255,255,0.25);border-radius:10px;padding:1px 7px;font-size:0.72rem;margin-left:4px;">
                                            <i class="fas fa-crown"></i> Admin
                                        </span>
                                    <?php endif; ?>
                                </button>
                                <div class="dropdown-menu dropdown-menu-right" aria-labelledby="userDropdown"
                                     style="border-radius:10px;box-shadow:0 8px 24px rgba(0,0,0,0.12);border:none;min-width:200px;">
                                    <div class="px-3 py-2 border-bottom">
                                        <div class="font-weight-700 text-dark"><?= htmlspecialchars($currentFullname, ENT_QUOTES, 'UTF-8') ?></div>
                                        <small class="text-muted">@<?= htmlspecialchars($currentUser, ENT_QUOTES, 'UTF-8') ?></small>
                                    </div>
                                    <a class="dropdown-item" href="/webbanhang/account/profile">
                                        <i class="fas fa-id-card mr-2 text-primary"></i>Hồ sơ cá nhân
                                    </a>
                                    <a class="dropdown-item" href="/webbanhang/account/changePassword">
                                        <i class="fas fa-key mr-2 text-warning"></i>Đổi mật khẩu
                                    </a>
                                    <?php if ($currentRole === 'admin'): ?>
                                        <div class="dropdown-divider"></div>
                                        <a class="dropdown-item" href="/webbanhang/account/manageUsers">
                                            <i class="fas fa-users-cog mr-2 text-danger"></i>Quản lý người dùng
                                        </a>
                                        <a class="dropdown-item" href="/webbanhang/Category">
                                            <i class="fas fa-folder-open mr-2 text-secondary"></i>Quản lý danh mục
                                        </a>
                                        <a class="dropdown-item" href="/webbanhang/Order">
                                            <i class="fas fa-clipboard-list mr-2 text-secondary"></i>Quản lý đơn hàng
                                        </a>
                                    <?php endif; ?>
                                    <div class="dropdown-divider"></div>
                                    <a class="dropdown-item text-danger" href="/webbanhang/account/logout">
                                        <i class="fas fa-sign-out-alt mr-2"></i>Đăng xuất
                                    </a>
                                </div>
                            </div>
                            <!-- Nút logout nhỏ trên mobile -->
                            <a href="/webbanhang/account/logout" class="btn-header-action text-warning d-sm-none ml-1" title="Đăng xuất">
                                <i class="fas fa-sign-out-alt"></i>
                            </a>
                        <?php else: ?>
                            <a href="/webbanhang/account/login" class="btn-header-action text-white">
                                <i class="fas fa-sign-in-alt"></i> Đăng nhập
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <nav class="cps-nav-menu">
        <div class="container-fluid px-md-5">
            <ul class="cps-menu-list">
                <li class="cps-menu-item">
                    <a href="/webbanhang/Product"><i class="fas fa-home"></i> Tất cả</a>
                </li>
                <li class="cps-menu-item">
                    <a href="/webbanhang/Product/category/1"><i class="fas fa-mobile-alt"></i> Điện thoại</a>
                </li>
                <li class="cps-menu-item">
                    <a href="/webbanhang/Product/category/2"><i class="fas fa-laptop"></i> Laptop</a>
                </li>
                <li class="cps-menu-item">
                    <a href="/webbanhang/Product/category/3"><i class="fas fa-tablet-alt"></i> Máy tính bảng</a>
                </li>
                <li class="cps-menu-item">
                    <a href="/webbanhang/Product/category/4"><i class="fas fa-plug"></i> Phụ kiện</a>
                </li>
                <li class="cps-menu-item">
                    <a href="/webbanhang/Product/category/5"><i class="fas fa-headphones"></i> Âm thanh</a>
                </li>

                <?php if ($currentUser): ?>
                    <li class="cps-menu-item" style="margin-left:auto;">
                        <a href="/webbanhang/Category" style="color:#888;">
                            <i class="fas fa-folder-open"></i> Danh mục
                        </a>
                    </li>
                    <li class="cps-menu-item">
                        <a href="/webbanhang/Order" style="color:#888;">
                            <i class="fas fa-clipboard-list"></i> Đơn hàng
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </nav>

    <?php 
    $currentUri = $_SERVER['REQUEST_URI'];
    if (strpos($currentUri, '/account/login') === false && strpos($currentUri, '/account/register') === false): 
    ?>
    <div class="container-fluid px-md-5 cps-banner-container">
        <div class="cps-banner-wrapper">
            <div class="cps-banner-circle cps-banner-circle-1"></div>
            <div class="cps-banner-circle cps-banner-circle-2"></div>
            <div class="cps-banner-circle cps-banner-circle-3"></div>
            <div class="cps-banner-content">
                <h2><i class="fas fa-bolt"></i> TechStore — Công nghệ đỉnh cao</h2>
                <p>Hàng chính hãng · Giá tốt nhất · Giao hàng nhanh toàn quốc · Bảo hành 12 tháng</p>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <div class="container-fluid px-md-5 main-content">