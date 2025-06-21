<?php
    // Includes from the dashboard code
    include_once('../inc/config.php');
    include_once('../admin/includes/format.php');
    include_once('../inc/session.php');

    // Page metadata
    $page_name = 'Profile';
    $page_parent = 'Dashboard';
    $page_title = 'User Profile | ' . htmlspecialchars($settings->siteTitle);
    $page_description = 'View and manage your profile details on ' . htmlspecialchars($settings->siteTitle);

    // Ensure user is logged in
    if (!isset($_SESSION['user'])) {
        header('location: ../login.php');
        exit();
    }

    $user_id = $_SESSION['user'];

    // Set PDO error mode to exception
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Fetch user profile data (equivalent to the API fetch in getServerSideProps)
    try {
        $stmt = $conn->prepare("SELECT full_name, email, nationality, created_at FROM users WHERE id = :user_id");
        $stmt->execute(['user_id' => $user_id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            header('location: ../login.php');
            exit();
        }

        // Format the created_at date
        $created_at = new DateTime($user['created_at'], new DateTimeZone('Europe/Paris'));
        $created_at->setTimezone(new DateTimeZone('UTC'));
        $user['created_at'] = $created_at->format('Y-m-d, g:i A') . ' (UTC)';
    } catch (PDOException $e) {
        error_log("Error fetching user data: " . $e->getMessage());
        header('location: ../login.php');
        exit();
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <?php include_once('inc/head.php'); ?>
</head>
<body class="dark-topbar">
    <!-- Left Sidenav -->
    <?php include('inc/sidebar.php'); ?>
    <!-- end left-sidenav-->

    <div class="page-wrapper">
        <!-- Top Bar Start -->
        <?php include('inc/header.php'); ?>
        <!-- Top Bar End -->

        <!-- Page Content-->
        <div class="page-content">
            <div class="container-fluid">
                <!-- Page-Title -->
                <div class="row">
                    <div class="col-sm-12">
                        <div class="page-title-box">
                            <div class="row">
                                <div class="col">
                                    <h4 class="page-title">User Profile</h4>
                                    <ol class="breadcrumb">
                                        <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                                        <li class="breadcrumb-item active">Profile</li>
                                    </ol>
                                </div><!--end col-->
                                <div class="col-auto align-self-center">
                                    <a href="#" class="btn btn-sm btn-outline-primary" id="Dash_Date">
                                        <span class="day-name" id="Day_Name">Today:</span> 
                                        <span class="" id="Select_date">
                                            <?php
                                                $dash_date = new DateTime('now', new DateTimeZone('Europe/Paris'));
                                                $dash_date->setTimezone(new DateTimeZone('UTC'));
                                                echo $dash_date->format('M d, Y, g:i A') . ' (UTC)';
                                            ?>
                                        </span>
                                        <i data-feather="calendar" class="align-self-center icon-xs ml-1"></i>
                                    </a>
                                </div><!--end col-->
                            </div><!--end row-->
                        </div><!--end page-title-box-->
                    </div><!--end col-->
                </div><!--end row-->
                <!-- end page title end breadcrumb -->

                <?php if (isset($_SESSION['error'])): ?>
                    <div class="alert alert-danger border-0" role="alert">
                        <i class="la la-skull-crossbones alert-icon text-danger align-self-center font-30 mr-3"></i>
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true"><i class="mdi mdi-close align-middle font-16"></i></span>
                        </button>
                        <strong>Oh snap!</strong> <?= htmlspecialchars($_SESSION['error']) ?>
                    </div>
                    <?php unset($_SESSION['error']); ?>
                <?php endif; ?>
                <?php if (isset($_SESSION['success'])): ?>
                    <div class="alert alert-success border-0" role="alert">
                        <i class="mdi mdi-check-all alert-icon"></i>
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true"><i class="mdi mdi-close align-middle font-16"></i></span>
                        </button>
                        <strong>Well done!</strong> <?= htmlspecialchars($_SESSION['success']) ?>
                    </div>
                    <?php unset($_SESSION['success']); ?>
                <?php endif; ?>

                <!-- Profile Component -->
                <div class="row">
                    <div class="col-lg-12">
                        <div class="card">
                            <div class="card-header">
                                <div class="row align-items-center">
                                    <div class="col">
                                        <h4 class="card-title">Profile Details</h4>
                                    </div><!--end col-->
                                    <div class="col-auto">
                                        <a href="profile-edit.php" class="btn btn-sm btn-outline-primary">Edit Profile</a>
                                    </div><!--end col-->
                                </div><!--end row-->
                            </div><!--end card-header-->
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <p><strong>Full Name:</strong> <?= htmlspecialchars($user['full_name']) ?></p>
                                        <p><strong>Email:</strong> <?= htmlspecialchars($user['email']) ?></p>
                                    </div>
                                    <div class="col-md-6">
                                        <p><strong>Nationality:</strong> <?= htmlspecialchars($user['nationality'] ?: 'Not set') ?></p>
                                        <p><strong>Joined:</strong> <?= htmlspecialchars($user['created_at']) ?></p>
                                    </div>
                                </div>
                                <?php if (empty($user['nationality'])): ?>
                                    <div class="alert custom-alert custom-alert-primary icon-custom-alert alert-secondary-shadow fade show" role="alert">
                                        <i class="mdi mdi-alert-outline alert-icon text-primary align-self-center font-30 mr-3"></i>
                                        <div class="alert-text my-1">
                                            <span><a href="profile-edit.php" class="btn mb-1 btn-primary">Click Here</a> to Complete Your Profile Setup</span>
                                        </div>
                                        <div class="alert-close">
                                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                                <span aria-hidden="true"><i class="mdi mdi-close font-16"></i></span>
                                            </button>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div><!--end card-body-->
                        </div><!--end card-->
                    </div><!--end col-->
                </div><!--end row-->

            </div><!-- container -->
            <?php include('inc/footer.php'); ?><!--end footer-->
        </div>
        <!-- end page content -->
    </div>
    <!-- end page-wrapper -->

    <?php include('inc/scripts.php'); ?>
</body>
</html>
