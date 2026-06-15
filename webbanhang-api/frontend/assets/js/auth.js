/**
 * auth.js — Quản lý JWT token trong localStorage
 */
var Auth = {
    TOKEN_KEY: 'techstore_jwt',
    USER_KEY:  'techstore_user',

    // Lưu sau khi login thành công
    save: function (token, user) {
        localStorage.setItem(this.TOKEN_KEY, token);
        localStorage.setItem(this.USER_KEY, JSON.stringify(user));
    },

    // Xóa khi logout
    clear: function () {
        localStorage.removeItem(this.TOKEN_KEY);
        localStorage.removeItem(this.USER_KEY);
    },

    getToken: function () {
        return localStorage.getItem(this.TOKEN_KEY);
    },

    getUser: function () {
        try { return JSON.parse(localStorage.getItem(this.USER_KEY)) || null; }
        catch (e) { return null; }
    },

    isLoggedIn: function () {
        var token = this.getToken();
        if (!token) return false;
        // Kiểm tra hết hạn từ payload JWT (không verify chữ ký, chỉ check exp)
        try {
            var payload = JSON.parse(atob(token.split('.')[1]));
            if (payload.exp && payload.exp < Math.floor(Date.now() / 1000)) {
                this.clear(); // token hết hạn, xóa luôn
                return false;
            }
            return true;
        } catch (e) { return false; }
    },

    isAdmin: function () {
        var user = this.getUser();
        return user && user.role === 'admin';
    },

    // Bắt buộc đăng nhập — redirect về login nếu chưa có token
    requireLogin: function (redirectUrl) {
        if (!this.isLoggedIn()) {
            window.location.href = redirectUrl || 'login.html';
            return false;
        }
        return true;
    },

    // Bắt buộc Admin
    requireAdmin: function () {
        if (!this.isLoggedIn()) { window.location.href = 'login.html'; return false; }
        if (!this.isAdmin()) { window.location.href = 'index.html'; return false; }
        return true;
    }
};
