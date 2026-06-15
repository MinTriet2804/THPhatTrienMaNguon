/**
 * api.js — Lớp tiện ích gọi API bằng jQuery AJAX
 * Tự động gắn JWT token vào header Authorization
 */
var BASE_URL = 'http://localhost:8080/webbanhang-api';

var API = {
    // ── GET request ───────────────────────────────────────────────
    get: function (endpoint) {
        return $.ajax({
            url: BASE_URL + endpoint,
            method: 'GET',
            contentType: 'application/json',
        });
    },

    // ── GET với JWT ───────────────────────────────────────────────
    authGet: function (endpoint) {
        return $.ajax({
            url: BASE_URL + endpoint,
            method: 'GET',
            headers: this._authHeader(),
            contentType: 'application/json',
        });
    },

    // ── POST không cần auth ───────────────────────────────────────
    post: function (endpoint, data) {
        return $.ajax({
            url: BASE_URL + endpoint,
            method: 'POST',
            contentType: 'application/json',
            data: JSON.stringify(data),
        });
    },

    // ── POST với JWT ──────────────────────────────────────────────
    authPost: function (endpoint, data) {
        return $.ajax({
            url: BASE_URL + endpoint,
            method: 'POST',
            headers: this._authHeader(),
            contentType: 'application/json',
            data: JSON.stringify(data),
        });
    },

    // ── PUT với JWT ───────────────────────────────────────────────
    authPut: function (endpoint, data) {
        return $.ajax({
            url: BASE_URL + endpoint,
            method: 'PUT',
            headers: this._authHeader(),
            contentType: 'application/json',
            data: JSON.stringify(data),
        });
    },

    // ── DELETE với JWT ────────────────────────────────────────────
    authDelete: function (endpoint) {
        return $.ajax({
            url: BASE_URL + endpoint,
            method: 'DELETE',
            headers: this._authHeader(),
            contentType: 'application/json',
        });
    },

    // ── Helper: tạo Authorization header ─────────────────────────
    _authHeader: function () {
        var token = Auth.getToken();
        return token ? { 'Authorization': 'Bearer ' + token } : {};
    },

    // ── Helper: chuyển path ảnh sang URL đầy đủ ──────────────────
    imgUrl: function (path) {
        if (!path) return '';
        if (path.startsWith('http')) return path;
        return BASE_URL + '/' + path;
    }
};

// ── Utility functions dùng toàn trang ────────────────────────────
function escHtml(str) {
    return $('<div>').text(String(str || '')).html();
}

function numberFormat(n) {
    return Number(n).toLocaleString('vi-VN');
}

// ── Toast notification ────────────────────────────────────────────
var Toast = {
    show: function (msg, type) {
        type = type || 'success';
        var colors = { success: '#28a745', error: '#d70018', info: '#17a2b8', warning: '#ffc107' };
        var icons  = { success: 'fa-check-circle', error: 'fa-times-circle', info: 'fa-info-circle', warning: 'fa-exclamation-triangle' };
        var id = 'toast-' + Date.now();
        var toast = $('<div id="' + id + '" style="background:' + colors[type] + ';color:#fff;padding:12px 20px;border-radius:10px;margin-top:8px;font-size:0.9rem;box-shadow:0 4px 16px rgba(0,0,0,0.15);display:flex;align-items:center;gap:10px;min-width:240px;max-width:360px;animation:fadeInUp 0.3s ease;">'
            + '<i class="fas ' + icons[type] + '"></i><span>' + escHtml(msg) + '</span></div>');
        $('#toast-container').append(toast);
        setTimeout(function () { toast.fadeOut(400, function () { toast.remove(); }); }, 3500);
    },
    success: function (msg) { this.show(msg, 'success'); },
    error:   function (msg) { this.show(msg, 'error'); },
    info:    function (msg) { this.show(msg, 'info'); },
    warning: function (msg) { this.show(msg, 'warning'); },
};

// CSS animation cho toast
$('<style>@keyframes fadeInUp{from{opacity:0;transform:translateY(20px)}to{opacity:1;transform:translateY(0)}}</style>').appendTo('head');
