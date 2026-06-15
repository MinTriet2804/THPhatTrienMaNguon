/**
 * components.js — Render Header & Footer động bằng jQuery
 * Tự động cập nhật trạng thái đăng nhập, giỏ hàng
 */
var Components = {

    loadHeader: function () {
        var user = Auth.getUser();
        var isLoggedIn = Auth.isLoggedIn();
        var isAdmin = Auth.isAdmin();

        var cartBadge = '';
        var userMenu = '';

        if (isLoggedIn && user) {
            var adminBadge = isAdmin ? '<span class="admin-badge"><i class="fas fa-crown"></i> Admin</span>' : '';
            userMenu = '<div class="dropdown d-none d-sm-inline-block ml-1">'
                + '<button class="btn-header-action dropdown-toggle" type="button" id="userDropdown" data-toggle="dropdown"'
                + ' style="background:rgba(255,255,255,0.15);border-radius:8px;border:1px solid rgba(255,255,255,0.3);">'
                + '<i class="fas fa-user-circle mr-1"></i>'
                + '<span>' + escHtml(user.fullname || user.username) + '</span>' + adminBadge
                + '</button>'
                + '<div class="dropdown-menu dropdown-menu-right" style="border-radius:10px;box-shadow:0 8px 24px rgba(0,0,0,0.12);border:none;min-width:200px;">'
                + '<div class="px-3 py-2 border-bottom"><div class="font-weight-700 text-dark">' + escHtml(user.fullname || '') + '</div>'
                + '<small class="text-muted">@' + escHtml(user.username || '') + '</small></div>'
                + '<a class="dropdown-item" href="profile.html"><i class="fas fa-id-card mr-2 text-primary"></i>Hồ sơ cá nhân</a>'
                + '<a class="dropdown-item" href="change-password.html"><i class="fas fa-key mr-2 text-warning"></i>Đổi mật khẩu</a>'
                + (isAdmin
                    ? '<div class="dropdown-divider"></div>'
                    + '<a class="dropdown-item" href="admin-users.html"><i class="fas fa-users-cog mr-2 text-danger"></i>Quản lý người dùng</a>'
                    + '<a class="dropdown-item" href="admin-categories.html"><i class="fas fa-folder-open mr-2 text-secondary"></i>Quản lý danh mục</a>'
                    + '<a class="dropdown-item" href="admin-orders.html"><i class="fas fa-clipboard-list mr-2 text-secondary"></i>Quản lý đơn hàng</a>'
                    + '<a class="dropdown-item" href="admin-products.html"><i class="fas fa-box mr-2 text-secondary"></i>Quản lý sản phẩm</a>'
                    : '')
                + '<div class="dropdown-divider"></div>'
                + '<a class="dropdown-item text-danger" href="#" id="btn-logout"><i class="fas fa-sign-out-alt mr-2"></i>Đăng xuất</a>'
                + '</div></div>';
        } else {
            userMenu = '<a href="login.html" class="btn-header-action"><i class="fas fa-sign-in-alt"></i> Đăng nhập</a>';
        }

        var html = '<header class="cps-header-top">'
            + '<div class="container-fluid px-md-5">'
            + '<div class="row align-items-center">'
            + '<div class="col-lg-2 col-md-3 col-5">'
            + '<a href="index.html" class="cps-logo">Tech<span class="logo-badge">S</span></a>'
            + '</div>'
            + '<div class="col-lg-5 d-none d-lg-block">'
            + '<div class="cps-search-wrapper">'
            + '<i class="fas fa-search cps-search-icon"></i>'
            + '<input type="text" id="global-search" class="cps-search-bar" placeholder="Tìm kiếm sản phẩm...">'
            + '</div></div>'
            + '<div class="col-lg-5 col-md-9 col-7">'
            + '<div class="header-actions">'
            + '<a href="index.html" class="btn-header-action d-none d-md-inline-flex"><i class="fas fa-store"></i> Cửa hàng</a>'
            + (isAdmin ? '<a href="admin-products.html" class="btn-header-action d-none d-md-inline-flex"><i class="fas fa-plus-circle"></i> Thêm SP</a>' : '')
            + '<a href="cart.html" class="btn-header-action btn-cart">'
            + '<i class="fas fa-shopping-cart"></i>'
            + '<span class="d-none d-sm-inline"> Giỏ hàng</span>'
            + '<span class="cart-badge" id="cart-badge-count" style="display:none;">0</span>'
            + '</a>'
            + userMenu
            + '</div></div></div></div></header>'
            + '<nav class="cps-nav-menu">'
            + '<div class="container-fluid px-md-5">'
            + '<ul class="cps-menu-list">'
            + '<li class="cps-menu-item"><a href="index.html"><i class="fas fa-home"></i> Tất cả</a></li>'
            + '<li class="cps-menu-item"><a href="index.html?category=1"><i class="fas fa-mobile-alt"></i> Điện thoại</a></li>'
            + '<li class="cps-menu-item"><a href="index.html?category=2"><i class="fas fa-laptop"></i> Laptop</a></li>'
            + '<li class="cps-menu-item"><a href="index.html?category=3"><i class="fas fa-tablet-alt"></i> Máy tính bảng</a></li>'
            + '<li class="cps-menu-item"><a href="index.html?category=4"><i class="fas fa-plug"></i> Phụ kiện</a></li>'
            + (isLoggedIn
                ? '<li class="cps-menu-item" style="margin-left:auto;"><a href="orders.html"><i class="fas fa-clipboard-list"></i> Đơn hàng</a></li>'
                : '')
            + '</ul></div></nav>';

        $('#header-placeholder').html(html);

        // Logout handler
        $(document).on('click', '#btn-logout', function (e) {
            e.preventDefault();
            API.authPost('/api/auth/logout', {}).always(function () {
                Auth.clear();
                Toast.success('Đăng xuất thành công!');
                setTimeout(function () { window.location.href = 'login.html'; }, 800);
            });
        });

        // Global search
        var searchTimeout;
        $(document).on('input', '#global-search', function () {
            clearTimeout(searchTimeout);
            var q = $(this).val().trim();
            searchTimeout = setTimeout(function () {
                if (q.length >= 2) {
                    window.location.href = 'index.html?search=' + encodeURIComponent(q);
                }
            }, 500);
        });

        // Cập nhật badge giỏ hàng nếu đã login
        if (isLoggedIn) {
            Components.refreshCartBadge();
        }
    },

    loadFooter: function () {
        var html = '<footer class="cps-footer mt-5">'
            + '<div class="container-fluid px-md-5 py-4"><div class="row">'
            + '<div class="col-lg-4 col-md-12 mb-4">'
            + '<h5 class="cps-footer-title"><span style="color:#d70018;font-weight:900;">Tech</span><span style="color:#fff;font-weight:900;">Store</span></h5>'
            + '<p class="cps-footer-text">Hệ thống bán lẻ công nghệ uy tín. Hàng chính hãng, giá tốt nhất.</p>'
            + '<p style="color:#b3b3b3;font-size:0.85rem;"><i class="fas fa-phone-alt" style="color:#d70018;"></i> Hotline: <strong style="color:#fff;">1800.2097</strong></p>'
            + '</div>'
            + '<div class="col-lg-2 col-md-4 col-6 mb-4">'
            + '<h5 class="cps-footer-title">Mua sắm</h5>'
            + '<ul class="list-unstyled cps-footer-links">'
            + '<li><a href="index.html"><i class="fas fa-chevron-right"></i> Tất cả sản phẩm</a></li>'
            + '<li><a href="cart.html"><i class="fas fa-chevron-right"></i> Giỏ hàng</a></li>'
            + '<li><a href="orders.html"><i class="fas fa-chevron-right"></i> Đơn hàng</a></li>'
            + '</ul></div>'
            + '<div class="col-lg-3 col-md-4 col-6 mb-4">'
            + '<h5 class="cps-footer-title">Tài khoản</h5>'
            + '<ul class="list-unstyled cps-footer-links">'
            + '<li><a href="login.html"><i class="fas fa-chevron-right"></i> Đăng nhập</a></li>'
            + '<li><a href="register.html"><i class="fas fa-chevron-right"></i> Đăng ký</a></li>'
            + '<li><a href="profile.html"><i class="fas fa-chevron-right"></i> Hồ sơ cá nhân</a></li>'
            + '</ul></div>'
            + '</div></div>'
            + '<div class="cps-footer-bottom"><div class="container-fluid px-md-5 d-flex justify-content-between align-items-center">'
            + '<div class="cps-copyright">&copy; ' + new Date().getFullYear() + ' <strong>TechStore</strong> — jQuery Frontend</div>'
            + '</div></div></footer>';
        $('#footer-placeholder').html(html);
    },

    updateCartBadge: function (count) {
        var badge = $('#cart-badge-count');
        if (count > 0) {
            badge.text(count > 99 ? '99+' : count).show();
        } else {
            badge.hide();
        }
    },

    refreshCartBadge: function () {
        if (!Auth.isLoggedIn()) return;
        API.authGet('/api/cart/total').done(function (res) {
            if (res.success && res.data) {
                Components.updateCartBadge(res.data.item_count || 0);
            }
        });
    }
};
