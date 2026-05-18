<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CellphoneS Admin - Hệ thống Quản lý</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
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
        
        /* TẦNG 1: TOP BAR */
        .cps-header-top {
            background-color: #d70018; /* Màu đỏ CellphoneS */
            padding: 12px 0;
            color: white;
        }
        .cps-logo {
            font-weight: 800;
            font-size: 1.4rem;
            color: white !important;
            text-decoration: none !important;
            letter-spacing: -0.5px;
        }
        .cps-logo span {
            background-color: white;
            color: #d70018;
            padding: 2px 6px;
            border-radius: 6px;
            margin-left: 4px;
            font-size: 1.2rem;
        }
        .cps-search-bar {
            border-radius: 8px;
            border: none;
            padding: 8px 15px;
            font-size: 0.9rem;
            width: 100%;
        }
        .btn-cps-nav {
            color: white !important;
            font-weight: 600;
            font-size: 0.9rem;
            padding: 8px 14px;
            border-radius: 8px;
            transition: all 0.2s;
            text-decoration: none !important;
        }
        .btn-cps-nav:hover {
            background-color: rgba(255, 255, 255, 0.15);
        }
        /* Nút quản lý danh mục */
        .btn-cps-category-mgr {
            background-color: #fff;
            color: #d70018 !important;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        .btn-cps-category-mgr:hover {
            background-color: #fff0f0;
        }

        /* TẦNG 2: NAVIGATION MENU (DANH MỤC) */
        .cps-nav-menu {
            background-color: #ffffff;
            box-shadow: 0 4px 10px rgba(0,0,0,0.05);
            border-bottom: 1px solid #e0e0e0;
        }
        .cps-menu-list {
            display: flex;
            align-items: center;
            list-style: none;
            margin: 0;
            padding: 0;
            overflow-x: auto;
            white-space: nowrap;
        }
        .cps-menu-item a {
            display: block;
            padding: 15px 20px;
            color: #444;
            font-weight: 600;
            font-size: 0.9rem;
            text-decoration: none !important;
            transition: all 0.2s;
            border-bottom: 3px solid transparent;
        }
        .cps-menu-item a i {
            margin-right: 6px;
            color: #777;
            transition: color 0.2s;
        }
        .cps-menu-item a:hover {
            color: #d70018;
            border-bottom-color: #d70018;
        }
        .cps-menu-item a:hover i {
            color: #d70018;
        }

        /* KHU VỰC BANNER TOÀN MÀN HÌNH */
        .cps-banner-container {
            margin-top: 20px;
            margin-bottom: 20px;
        }
        .cps-banner-wrapper {
            background: linear-gradient(135deg, #ff424e, #69000a);
            border-radius: 12px;
            padding: 40px;
            color: white;
            position: relative;
            overflow: hidden;
            box-shadow: 0 6px 20px rgba(215, 0, 24, 0.15);
        }
        .cps-banner-content h2 {
            font-weight: 800;
            font-size: 2rem;
            margin-bottom: 10px;
            text-transform: uppercase;
        }
        .cps-banner-content p {
            font-size: 1rem;
            opacity: 0.9;
            margin-bottom: 0;
        }
        .cps-banner-circle {
            position: absolute;
            background: rgba(255, 255, 255, 0.06);
            border-radius: 50%;
            width: 300px;
            height: 300px;
            top: -50px;
            right: -50px;
        }
    </style>
</head>
<body>

    <header class="cps-header-top">
        <div class="container-fluid px-md-5">
            <div class="row align-items-center">
                <div class="col-lg-3 col-md-4 col-6">
                    <a href="/webbanhang/Product/index" class="cps-logo">
                        TechStore<span>S</span>
                    </a>
                </div>
                
                <div class="col-lg-4 d-none d-lg-block">
                    <input type="text" class="cps-search-bar" placeholder="Bạn cần tìm sản phẩm gì?">
                </div>

                <div class="col-lg-5 col-md-8 col-6 text-right">
                    <a href="/webbanhang/Product/index" class="btn-cps-nav">
                        <i class="fas fa-list-ul"></i> Sản phẩm
                    </a>
                    <a href="/webbanhang/Product/add" class="btn-cps-nav">
                        <i class="fas fa-plus-circle"></i> Thêm mới
                    </a>
                    <a href="/webbanhang/Category/index" class="btn-cps-nav btn-cps-category-mgr ml-2">
                        <i class="fas fa-folder-open"></i> Quản lý danh mục
                    </a>
                </div>
            </div>
        </div>
    </header>

    <nav class="cps-nav-menu">
        <div class="container-fluid px-md-5">
            <ul class="cps-menu-list">
                <li class="cps-menu-item">
                    <a href="/webbanhang/Product/index"><i class="fas fa-home"></i> Tất cả</a>
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
                    <a href="/webbanhang/Product/category/5"><i class="fas fa-headphones"></i> Thiết bị âm thanh</a>
                </li>
            </ul>
        </div>
    </nav>

    <div class="container-fluid px-md-5 cps-banner-container">
        <div class="cps-banner-wrapper">
            <div class="cps-banner-circle"></div>
            <div class="cps-banner-content">
                <h2>Hệ Thống Quản Trị Hệ Thống CellphoneS</h2>
                <p>Nơi cập nhật thông tin sản phẩm công nghệ, kiểm soát danh mục và tối ưu hóa trải nghiệm cửa hàng.</p>
            </div>
        </div>
    </div>

    <div class="container-fluid px-md-5 my-4">