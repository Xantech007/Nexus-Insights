<?php
// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Include session and dependencies
include('includes/session.php');
include('includes/format.php');
include('../inc/config.php');

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
$chatUsers = [];
try {
    $stmtUsers = $conn->query("SELECT DISTINCT u.id, u.full_name, u.email 
                               FROM users u 
                               JOIN live_chat lc ON u.id = lc.user_id 
                               ORDER BY u.full_name");
    $chatUsers = $stmtUsers->fetchAll(PDO::FETCH_OBJ);
} catch (PDOException $e) {
    $_SESSION['error'] = 'Database error occurred: ' . $e->getMessage();
    error_log("Database error in fetching users: " . $e->getMessage(), 3, __DIR__ . "/error_log.txt");
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

// Include template files
include('includes/header.php');
include('includes/navbar.php');
include('includes/menubar.php');
include('includes/footer.php');
include('includes/scripts.php');
?>
<body class="hold-transition skin-blue sidebar-mini">
<div class="wrapper">

  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <h1>Live Chat</h1>
      <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
        <li class="active">Live Chat</li>
      </ol>
    </section>

    <!-- Main content -->
    <section class="content">
      <?php
        if (isset($_SESSION['error'])) {
          echo "
            <div class='alert alert-danger alert-dismissible'>
              <button type='button' class='close' data-dismiss='alert' aria-hidden='true'>×</button>
              <h4><i class='icon fa fa-warning'></i> Error!</h4>
              " . htmlspecialchars($_SESSION['error']) . "
            </div>
          ";
          unset($_SESSION['error']);
        }
        if (isset($_SESSION['success'])) {
          echo "
            <div class='alert alert-success alert-dismissible'>
              <button type='button' class='close' data-dismiss='alert' aria-hidden='true'>×</button>
              <h4><i class='icon fa fa-check'></i> Success!</h4>
              " . htmlspecialchars($_SESSION['success']) . "
            </div>
          ";
          unset($_SESSION['success']);
        }
      ?>
      <div class="row">
        <div class="col-xs-12">
          <div class="box">
            <div class="box-header with-border">
              <h3 class="box-title">Live Chat</h3>
            </div>
            <div class="box-body" style="padding: 20px;">
              <div class="row">
                <!-- User List -->
                <div class="col-md-4">
                  <h5>Users with Chats</h5>
                  <ul class="list-group">
                    <?php if (!empty($chatUsers)) : ?>
                      <?php foreach ($chatUsers as $user) : ?>
                        <li class="list-group-item">
                          <a href="livechat.php?user_id=<?php echo $user->id; ?>" class="<?php echo $selected_user_id == $user->id ? 'active' : ''; ?>">
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
                    <h5>Chat with <?php
                      $selected_user_name = 'Unknown User';
                      foreach ($chatUsers as $user) {
                        if ($user->id == $selected_user_id) {
                          $selected_user_name = $user->full_name;
                          break;
                        }
                      }
                      echo htmlspecialchars($selected_user_name);
                    ?></h5>
                    <div class="chat-box" style="max-height: 400px; overflow-y: auto;">
                      <?php if (!empty($chatMessages)) : ?>
                        <?php foreach ($chatMessages as $msg) : ?>
                          <div class="chat-message mb-3 <?php echo $msg->sender == 'admin' ? 'text-right' : 'text-left'; ?>">
                            <div class="card p-2 d-inline-block <?php echo $msg->sender === 'admin' ? 'bg-admin' : 'bg-user'; ?>">
                              <p class="mb-1"><?php echo htmlspecialchars($msg->message); ?></p>
                              <small class="text-muted"><?php echo $msg->date_sent; ?> - <?php echo $msg->sender == 'admin' ? 'You' : 'User'; ?></small>
                            </div>
                          </div>
                        <?php endforeach; ?>
                      <?php else : ?>
                        <p>No messages yet. Start a conversation below!</p>
                      <?php endif; ?>
                    </div>
                    <!-- Message Input Form -->
                    <form method="POST" action="">
                      <input type="hidden" name="user_id" value="<?php echo htmlspecialchars($selected_user_id); ?>">
                      <div class="input-group mt-3">
                        <textarea name="message" class="form-control" rows="3" placeholder="Type your message..." required></textarea>
                        <div class="input-group-append">
                          <button type="submit" class="btn btn-primary">Send</button>
                        </div>
                      </div>
                    </form>
                  <?php else : ?>
                    <p>Select a user to view their messages.</p>
                  <?php endif; ?>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>
  </div>

</div>
<!-- ./wrapper -->

<style>
.chat-box {
  max-height: 400px;
  overflow-y: auto;
}
.bg-admin {
  background-color: #f8f9fa;
}
.bg-user {
  background-color: #e3e3e3;
}
.list-group-item {
  margin-bottom: 5px;
  border-radius: 5px;
}
.input-group textarea {
  padding: 10px;
}
.input-group-append .btn {
  padding: 10px 20px;
}
</style>
</body>
</html>
