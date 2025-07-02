<!-- Preloader -->
<div id="preloader">
    <div class="preloader-inner">
        <div class="spinner"></div>
    </div>
</div>

<header class="header">
    <div class="header__bottom">
        <div class="container">
            <nav class="navbar navbar-expand-xl p-0 align-items-center">
                <a class="site-logo site-title" href="<?= $baseurl; ?>"><img src="assets/images/logo.png" alt="site-logo"></a>
                <ul class="account-menu mobile-acc-menu">
                    <li class="icon">
                        <a href="login"><i class="las la-user"></i></a>
                    </li>
                </ul> 
                <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="menu-toggle"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarSupportedContent">
                    <ul class="navbar-nav main-menu m-auto">
                        <li <?php echo ( $page_name == 'Home' || $page_parent == 'Home' ) ? 'class="active"' : ''; ?>> 
                            <a href="<?= $baseurl; ?>">Home</a>
                        </li>
                        <li <?php echo ( $page_name == 'About Us' || $page_parent == 'About Us' ) ? 'class="active"' : ''; ?>> 
                            <a href="about">About Us</a>
                        </li>
                        <li <?php echo ( $page_name == 'Investment Plan' || $page_parent == 'Investment Plan' ) ? 'class="active"' : ''; ?>> 
                            <a href="investment">Plan</a>
                        </li>
                        <li <?php echo ( $page_name == 'FAQ' || $page_parent == 'FAQ' ) ? 'class="active"' : ''; ?>>
                            <a href="<?= $baseurl; ?>#faq">Faqs</a>
                        </li>
                        <li <?php echo ( $page_name == 'News' || $page_parent == 'News' ) ? 'class="active"' : ''; ?>>
                            <a href="news">News</a>
                        </li>
                        <li <?php echo ( $page_name == 'Contact Us' || $page_parent == 'Contact Us' ) ? 'class="active"' : ''; ?>>
                            <a href="contact">Contact</a>
                        </li>
                    </ul>
                    <div class="nav-right">
                        <ul class="navbar-nav main-menu m-auto">
                            <li><?php include('inc/translate2.php'); ?></li>
                        </ul>
                        <ul class="account-menu ml-3">
                            <li class="icon">
                                <a href="login"><i class="las la-user"></i></a>
                            </li>
                        </ul>
                        <div class="translate">
                            <?php include('inc/translate.php'); ?>                  
                        </div>
                    </div>
                </div> 
            </nav>
        </div>
    </div><!-- header__bottom end -->
</header>

<!-- CSS for Preloader -->
<style>
#preloader {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: #ffffff;
    z-index: 9999;
    display: flex;
    justify-content: center;
    align-items: center;
}
.preloader-inner {
    text-align: center;
}
.spinner {
    width: 50px;
    height: 50px;
    border: 5px solid #f3f3f3;
    border-top: 5px solid #3498db;
    border-radius: 50%;
    animation: spin 1s linear infinite;
}
@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}
</style>

<!-- JavaScript for Preloader -->
<script>
window.addEventListener('load', function() {
    const preloader = document.getElementById('preloader');
    preloader.style.display = 'none';
});
</script>
