<?php
// inc/header.php
// Ensure $settings, $conn, and $id are available (loaded via config.php or session.php)
if (!isset($settings)) {
    include('../inc/config.php');
}
if (!isset($id)) {
    $id = $_SESSION['user'] ?? 0; // Fallback to 0 if user not logged in
}

// Use PDO ($conn) for consistency
try {
    $stmt0 = $conn->prepare("SELECT * FROM users WHERE id = ?");
    $stmt0->execute([$id]);
    $row0 = $stmt0->fetch(PDO::FETCH_ASSOC);

    $stmt = $conn->prepare("SELECT *, COUNT(*) AS numrows FROM direct_message WHERE user_id = 0 OR (user_id = ? AND status = 0)");
    $stmt->execute([$id]);
    $drow = $stmt->fetch(PDO::FETCH_ASSOC);
    $no_of_msg = $drow['numrows'];

    $stmtQuery = $conn->prepare("SELECT * FROM direct_message WHERE (user_id = ? OR user_id = 0) AND status < 2 ORDER BY msg_id DESC LIMIT 4");
    $stmtQuery->execute([$id]);
    $dmrow = $stmtQuery->fetchAll(PDO::FETCH_OBJ);
} catch (PDOException $e) {
    error_log("Error in header.php queries: " . $e->getMessage(), 3, "../errors.log");
    $row0 = ['full_name' => 'Guest', 'photo' => 'profile.jpg'];
    $no_of_msg = 0;
    $dmrow = [];
}
?>

<style>
    .topbar, .navbar-custom, .dropdown-menu, .notification-menu {
        background-color: #1a1a1a !important;
        color: #ffffff !important;
    }
    .dropdown-item, .app-search-topbar input {
        background-color: #2a2a2a !important;
        color: #ffffff !important;
    }
    .dropdown-item:hover {
        background-color: #3a3a2a !important; /* Fixed typo: #3a3a2a to #3a3a2a */
    }
    /* Set top bar icons to grayish */
    .topbar .feather-menu,
    .topbar .feather-search,
    .topbar .feather-bell,
    .topbar .topbar-icon {
        stroke: #c9c7c7 !important; /* Grayish color */
    }
</style>

<div class="topbar">            
    <!-- Navbar -->
    <nav class="navbar-custom" style="display: flex; justify-content: space-between; align-items: center;" data-bs-theme="dark">    
        <ul class="list-unstyled topbar-nav mb-0" style="display: flex; align-items: center;">                        
            <li>
                <button class="nav-link button-menu-mobile">
                    <i data-feather="menu" class="align-self-center topbar-icon"></i>
                </button>
            </li>
            <li class="topbar-logo" style="margin-left: 10px;">
                <a href="/" title="<?php echo htmlspecialchars($settings->siteTitle); ?>">
                    <img src="../assets/images/logo.png" alt="Logo" style="max-height: 30px;">
                </a>
            </li>                          
        </ul>

        <ul class="list-unstyled topbar-nav mb-0" style="display: flex; align-items: center; margin-left: auto;">  
            <li class="dropdown notification-list">
                <a class="nav-link dropdown-toggle arrow-none waves-light waves-effect" data-toggle="dropdown" href="#" role="button"
                    aria-haspopup="false" aria-expanded="false">
                    <i data-feather="bell" class="align-self-center topbar-icon"></i>
                    <?php if ($no_of_msg > 0) {
                        echo "<span class='badge badge-danger badge-pill noti-icon-badge'>".$no_of_msg."</span>";
                    } else { echo ""; } ?>
                </a>
                <div class="dropdown-menu dropdown-menu-right dropdown-lg pt-0">
                    <?php
                    if ($no_of_msg > 0) { ?>
                        <h6 class="dropdown-item-text font-15 m-0 py-3 border-bottom d-flex justify-content-between align-items-center">
                            Notifications <span class="badge badge-primary badge-pill"><?= $no_of_msg; ?></span>
                        </h6> 
                        <div class="notification-menu" data-simplebar>
                            <!-- item-->
                            <?php foreach ($dmrow as $dm) : ?>
                                <a href="message.php?id=<?= $dm->msg_id; ?>&readmessage" class="dropdown-item py-3">
                                    <div class="media">
                                        <div class="media-body align-self-center ml-2 text-truncate">
                                            <h6 class="my-0 font-weight-normal text-dark"><?= $dm->subject; ?></h6>
                                            <?php if ($dm->status == 0) { echo "<strong>"; } ?>
                                            <small class="text-muted mb-0"><?= substrwords($dm->message, 50); ?></small>
                                            <?php if ($dm->status == 0) { echo "</strong>"; } ?>
                                        </div>
                                    </div><!--end media-->
                                </a>
                            <?php endforeach; ?>
                            <!--end-item-->
                        </div>
                        <!-- All-->
                        <a href="messages" class="dropdown-item text-center text-primary">
                            View all <i class="fi-arrow-right"></i>
                        </a>
                    <?php } else {
                        echo "
                            <h6 class='dropdown-item-text font-15 m-0 py-3 border-bottom d-flex justify-content-between align-items-center'>
                                No Notifications Yet
                            </h6>
                        ";
                    } ?>
                </div>
            </li>

            <li class="dropdown">
                <a class="nav-link dropdown-toggle waves-effect waves-light nav-user" data-toggle="dropdown" href="#" role="button"
                    aria-haspopup="false" aria-expanded="false">
                    <span class="ml-1 nav-user-name hidden-sm"><?php echo $row0["full_name"] ?></span>
                    <img src="../admin/images/<?php if (!empty($row0["photo"])) { echo $row0["photo"]; } else { echo "profile.jpg"; } ?>" alt="profile-user" class="rounded-circle thumb-xs" />                                 
                </a>
                <div class="dropdown-menu dropdown-menu-right">
                    <a class="dropdown-item" href="profile"><i data-feather="user" class="align-self-center icon-xs icon-dual mr-1"></i> Profile</a>
                    <a class="dropdown-item" href="profile-edit"><i data-feather="settings" class="align-self-center icon-xs icon-dual mr-1"></i> Settings</a>
                    <a class="dropdown-item" href="password-update"><i data-feather="lock" class="align-self-center icon-xs icon-dual mr-1"></i> Change Password</a>
                    <div class="dropdown-divider mb-0"></div>
                    <a class="dropdown-item" href="logout_action"><i data-feather="power" class="align-self-center icon-xs icon-dual mr-1"></i> Logout</a>
                </div>
            </li>
        </ul><!--end topbar-nav-->
    </nav>
    <!-- end navbar-->
</div>
