<footer class="cps-footer mt-5">
    <div class="container p-4">
        <div class="row">
            <div class="col-lg-4 col-md-12 mb-4 cps-footer-about">
                <h5 class="text-uppercase cps-footer-title">
                    <span class="cps-logo-red">Cellphone</span><span class="cps-logo-dark">S</span> Admin
                </h5>
                <p class="cps-footer-text">
                    Hệ thống quản lý hàng hóa và điều phối sản phẩm độc quyền. Tối ưu hóa quy trình cập nhật danh mục, theo dõi kho hàng trực quan và chính xác.
                </p>
                <div class="cps-contact-info">
                    <p><i class="fas fa-map-marker-alt"></i> 475 Điện Biên Phủ, P.25, Q.Bình Thạnh, TP.HCM</p>
                    <p><i class="fas fa-phone-alt"></i> Hotline hỗ trợ: 1800.2097 (Miễn phí)</p>
                </div>
            </div>

            <div class="col-lg-4 col-md-6 mb-4">
                <h5 class="text-uppercase cps-footer-title">Liên kết hệ thống</h5>
                <ul class="list-unstyled cps-footer-links">
                    <li>
                        <a href="/webbanhang/Product/list"><i class="fas fa-chevron-right"></i> Danh sách sản phẩm</a>
                    </li>
                    <li>
                        <a href="/webbanhang/Product/add"><i class="fas fa-chevron-right"></i> Thêm sản phẩm mới</a>
                    </li>
                    <li>
                        <a href="#"><i class="fas fa-chevron-right"></i> Quản lý danh mục</a>
                    </li>
                    <li>
                        <a href="#"><i class="fas fa-chevron-right"></i> Thống kê báo cáo doanh thu</a>
                    </li>
                </ul>
            </div>

            <div class="col-lg-4 col-md-6 mb-4">
                <h5 class="text-uppercase cps-footer-title">Tổng đài hỗ trợ</h5>
                <ul class="list-unstyled cps-support-list">
                    <li>Gọi mua hàng: <strong class="text-white">1800.2044</strong> (08h00 - 22h00)</li>
                    <li>Khiếu nại, khiếu từ: <strong class="text-white">1800.2063</strong> (08h00 - 21h30)</li>
                    <li>Bảo hành, sửa chữa: <strong class="text-white">1800.2064</strong> (08h00 - 21h00)</li>
                </ul>
            </div>
        </div>
    </div>

    <div class="cps-footer-bottom">
        <div class="container d-flex flex-column flex-md-row justify-content-between align-items-center">
            <div class="cps-copyright mb-3 mb-md-0">
                &copy; <?php echo date('Y'); ?> <strong>CellphoneS Management System</strong>. Thiết kế đồng bộ bởi HUTECH Student.
            </div>
            
            <div class="cps-social-platforms">
                <span class="cps-social-label d-none d-sm-inline-block">Kết nối với chúng tôi:</span>
                <a href="https://facebook.com" target="_blank" class="cps-icon-box cps-facebook" title="Theo dõi chúng tôi trên Facebook">
                    <i class="fab fa-facebook-f"></i>
                </a>
                <a href="https://youtube.com" target="_blank" class="cps-icon-box cps-youtube" title="Đăng ký kênh YouTube">
                    <i class="fab fa-youtube"></i>
                </a>
                <a href="https://instagram.com" target="_blank" class="cps-icon-box cps-instagram" title="Theo dõi trên Instagram">
                    <i class="fab fa-instagram"></i>
                </a>
            </div>
        </div>
    </div>
</footer>

<script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/js/all.min.js" crossorigin="anonymous"></script>

<style>
    /* Tổng thể khối Footer */
    .cps-footer {
        background-color: #1a1a1a; /* Màu xám đen sang trọng */
        color: #b3b3b3;
        font-family: 'Inter', sans-serif;
        font-size: 0.9rem;
    }
    .cps-footer-title {
        color: #ffffff;
        font-weight: 700;
        font-size: 1.05rem;
        margin-bottom: 20px;
        position: relative;
        padding-bottom: 8px;
        letter-spacing: 0.5px;
    }
    /* Thanh gạch đỏ nhỏ dưới mỗi tiêu đề cột */
    .cps-footer-title::after {
        content: '';
        position: absolute;
        left: 0;
        bottom: 0;
        width: 40px;
        height: 2px;
        background-color: #d70018; 
    }
    .cps-logo-red {
        color: #d70018;
    }
    .cps-logo-dark {
        color: #ffffff;
    }
    .cps-footer-text {
        line-height: 1.6;
    }
    .cps-contact-info p {
        margin-bottom: 8px;
        font-size: 0.85rem;
    }
    .cps-