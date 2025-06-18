<?php
    include('init.php');
    include('admin/includes/format.php');

    $page_name = 'Contact Us';
    $page_parent = '';
    $page_title = 'Welcome to the Official Website of '.$settings->siteTitle;
    $page_description = $settings->siteTitle.' provides quality infrastructure backed high-performance cloud computing services for cryptocurrency mining. Choose a plan to get started today! What are you waiting for? Together We Grow!...';
    include('inc/head.php');


   

?>
  <body>
  <!-- scroll-to-top start -->
  <?php include('inc/scroll-to-top.php'); ?>  
  <!-- scroll-to-top end -->

  <!-- STAR ANIMATION -->
  <?php include('inc/star-animation.php'); ?>
  <!-- / STAR ANIMATION -->

  <div class="page-wrapper">
    <!-- header-section start  -->
    <?php include('inc/header.php'); ?>    
    <!-- header-section end  -->
    
    <!-- inner hero start -->
    <section class="inner-hero bg_img" data-background="assets/images/bg/bg-1.jpg">
      <div class="container">
        <div class="row">
          <div class="col-lg-6">
            <h2 class="page-title">Contact Us</h2>
            <ul class="page-breadcrumb">
              <li><a href="<?= $baseurl ?>">Home</a></li>
              <li>Contact</li>
            </ul>
          </div>
        </div>
      </div>
    </section>
    <!-- inner hero end -->
      
      </div>
      <div class="container pt-120">
        <div class="row justify-content-center">
          <div class="col-lg-10">
            <div class="row mb-none-30">
              <div class="col-md-4 col-sm-6 mb-30">
                <div class="contact-item">
                  <i class="fas fa-phone-alt"></i>
                  <h5 class="mt-2">WhatsApp Us</h5>
                  <div class="mt-4">
                    <p><?= $settings->phoneNumber; ?></p>
                  </div>
                </div><!-- contact-item end -->
              </div>
              <div class="col-md-4 col-sm-6 mb-30">
                <div class="contact-item">
                  <i class="fas fa-envelope"></i>
                  <h5 class="mt-2">Mail Us</h5>
                  <div class="mt-4">
                    <p><?= $settings->email2; ?><br/>
                    </p>
                  </div>
                </div><!-- contact-item end -->
              </div>
              <div class="col-md-4 col-sm-6 mb-30">
                <div class="contact-item">
                  <i class="fas fa-map-marker-alt"></i>
                  <h5 class="mt-2">Visit Us</h5>
                  <div class="mt-4">
                    <p><?= $settings->address; ?></p>
                  </div>
                </div><!-- contact-item end -->
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>
    <!-- contact section end -->
    
    <!-- footer section start -->
    <?php include('inc/footer.php') ?>
    <!-- footer section end -->
  </div> <!-- page-wrapper end -->
  <?php include('inc/scripts.php') ?>
  </body>

<!-- Mirrored from template.viserlab.com/hyiplab/demo/index.html by HTTrack Website Copier/3.x [XR&CO'2014], Sat, 16 Oct 2021 16:37:40 GMT -->
</html>
