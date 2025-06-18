<?php
include('../inc/config.php');
include('includes/format.php');
include('includes/session.php');

$page_name = 'Admin Live Chat';
$page_parent = '';
$page_title = 'Admin Panel - ' . $settings->siteTitle;
$page_description = 'Manage live chat conversations for ' . $settings->siteTitle;

include('../inc/head.php');

// Check if admin is logged in
if (!isset($_SESSION['admin'])) {
    $_SESSION['error'] = 'Please log in to access the admin panel';
    header('location: ../login.php');
    exit;
}

$conn = $pdo->open();

// Handle message submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['message']) && isset($_POST['user_id'])) {
    $message = trim($_POST['message']);
    $user_id = intval($_POST['user_id']);
    if (!empty($message) && $user_id > 0) {
        try {
            // Insert admin message
            $stmtInsert = $conn->prepare("INSERT INTO live_chat (user_id, sender, message, date_sent, status) VALUES (:user_id, 'admin', :message, NOW(), 0)");
            $stmtInsert->execute(['user_id' => $user_id, 'message' => $message]);

            // Update status of user's messages to read
            $stmtUpdate = $conn->prepare("UPDATE live_chat SET status = 1 WHERE user_id = :user_id AND sender = 'user'");
            $stmtUpdate->execute(['user_id' => $user_id]);

            $_SESSION['success'] = "Message sent successfully!";
        } catch (PDOException $e) {
            $_SESSION['error'] = 'Database error occurred: ' . $e->getMessage();
            error_log("Database error in admin message send: " . $e->getMessage(), 3, __DIR__ . "/error_log.txt");
        }
    } else {
        $_SESSION['error'] = "Message cannot be empty or invalid user.";
    }
    header("location: livechat.php?user_id=$user_id");
    exit;
}

// Fetch all users with chats
try {
    $stmtUsers = $conn->query("SELECT DISTINCT u.id, u.full_name, u.email 
                               FROM users u 
                               JOIN live_chat lc ON u.id = lc.user_id 
                               ORDER BY u.full_name");
    $chatUsers = $stmtUsers->fetchAll(PDO::FETCH_OBJ);
} catch (PDOException $e) {
    $_SESSION['error'] = 'Database error occurred: ' . $e->getMessage();
    error_log("Database error in fetching users: " . $e->getMessage(), 3, __DIR__ . "/error_log.txt");
    $chatUsers = [];
}

// Fetch messages for selected user
$chatMessages = [];
$selected_user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : 0;
if ($selected_user_id > 0) {
    try {
        $stmtMessages = $conn->prepare("SELECT * FROM live_chat WHERE user_id = :user_id ORDER BY date_sent ASC");
        $stmtMessages->execute(['user_id' => $selected_user_id]);
        $chatMessages = $stmtMessages->fetchAll(PDO::FETCH_OBJ);
    } catch (PDOException $e) {
        $_SESSION['error'] = 'Database error occurred: ' . $e->getMessage();
        error_log("Database error in fetching messages: " . $e->getMessage(), 3, __DIR__ . "/error_log.txt");
    }
}

$pdo->close();
?>

<body class="dark-topbar">
    <!-- Left Sidenav -->
    <?php include('includes/menubar.php'); ?>
    <!-- end left-sidenav-->

    <div class="page-wrapper">
        <!-- Top Bar Start -->
        <?php include('includes/header.php'); ?>
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
                                    <h4 class="page-title">Admin Live Chat</h4>
                                </div>
                                <div class="col-auto align-self-center">
                                    <a href="#" class="btn btn-sm btn-outline-primary" id="Dash_Date">
                                        <span class="day-name" id="Day_Name">Today:</span> 
                                        <span class="" id="Select_date"><?php echo date('M d'); ?></span>
                                        <i data-feather="calendar" class="align-self-center icon-xs ml-1"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- end page title -->

                <!-- Display Success/Error Messages -->
                <?php
                if (isset($_SESSION['error'])) {
                    echo "
                        <div class='alert alert-danger border-0' role='alert'>
                            <i class='la la-skull-crossbones alert-icon text-danger align-self-center font-30 mr-3'></i>
                            <button type='button' class='close' data-dismiss='alert' aria-label='Close'>
                                <span aria-hidden='true'><i class='mdi mdi-close align-middle font-16'></i></span>
                            </button>
                            <strong>Oh snap!</strong> " . $_SESSION['error'] . "
                        </div>";
                    unset($_SESSION['error']);
                }
                if (isset($_SESSION['success'])) {
                    echo "
                        <div class='alert alert-success border-0' role='alert'>
                            <i class='mdi mdi-check-all alert-icon'></i>
                            <button type='button' class='close' data-dismiss='alert' aria-label='Close'>
                                <span aria-hidden='true'><i class='mdi mdi-close align-middle font-16'></i></span>
                            </button>
                            <strong>Well done!</strong> " . $_SESSION['success'] . "
                        </div>";
                    unset($_SESSION['success']);
                }
                ?>

                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <div class="row">
                                    <!-- User List -->
                                    <div class="col-md-4">
                                        <h5>Users with Chats</h5>
                                        <ul class="list-group">
                                            <?php if (!empty($chatUsers)) : ?>
                                                <?php foreach ($chatUsers as $user) : ?>
                                                    <li class="list-group-item">
                                                        <a href="livechat.php?user_id=<?php echo $user->id; ?>" class="<?php echo $selected_user_id == $user->id ? 'text-primary' : ''; ?>">
                                                            <?php echo htmlspecialchars($user->full_name); ?> (<?php echo htmlspecialchars($user->email); ?>)
                                                        </a>
                                                    </li>
                                                <?php endforeach; ?>
                                            <?php else : ?>
                                                <p>No active chats found.</p>
                                            <?php endif; ?>
                                        </ul>
                                    </div>
                                    <!-- Chat Area -->
                                    <div class="col-md-8">
                                        <?php if ($selected_user_id > 0) : ?>
                                            <h5>Chat with <?php echo htmlspecialchars($chatUsers[array_search($selected_user_id, array_column($chatUsers, 'id'))]->full_name); ?></h5>
                                            <div class="chat-box" style="max-height: 400px; overflow-y: auto;">
                                                <?php if (!empty($chatMessages)) : ?>
                                                    <?php foreach ($chatMessages as $msg) : ?>
                                                        <div class="chat-message mb-3 <?php echo $msg->sender === 'admin' ? 'text-right' : 'text-left'; ?>">
                                                            <div class="card p-2 d-inline-block <?php echo $msg->sender === 'admin' ? 'bg-light' : 'bg-primary text-white'; ?>">
                                                                <p class="mb-1"><?php echo htmlspecialchars($msg->message); ?></p>
                                                                <small class="text-muted"><?php echo $msg->date_sent; ?> - <?php echo $msg->sender === 'admin' ? 'You' : 'User'; ?></small>
                                                            </div>
                                                        </div>
                                                    <?php endforeach; ?>
                                                <?php else : ?>
                                                    <p>No messages yet. Start the conversation below!</p>
                                                <?php endif; ?>
                                            </div>
                                            <!-- Message Input Form -->
                                            <form method="POST" action="">
                                                <input type="hidden" name="user_id" value="<?php echo $selected_user_id; ?>">
                                                <div class="input-group mt-3">
                                                    <textarea name="message" class="form-control" rows="3" placeholder="Type your message..." required></textarea>
                                                    <div class="input-group-append">
                                                        <button type="submit" class="btn btn-primary">Send</button>
                                                    </div>
                                                </div>
                                            </form>
                                        <?php else : ?>
                                            <p>Select a user to view their chat.</p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div><!--end card-body-->
                        </div><!--end card-->
                    </div><!--end col-->
                </div><!--end row-->
            </div><!-- container -->

            <?php include('includes/footer.php'); ?><!--end footer-->
        </div>
        <!-- end page content -->
    </div>
    <!-- end page-wrapper -->

    <?php include('includes/scripts.php'); ?>
</body>
</html>
