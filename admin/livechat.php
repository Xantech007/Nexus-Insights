<?php
// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Include session and dependencies
include('includes/session.php');
include('includes/format.php');
$base_dir = __DIR__ . '/';
if (!file_exists($base_dir . 'inc/config.php')) {
    die('Configuration file not found: includes/config.php');
}
include($base_dir . 'inc/config.php');

// Check if admin is logged in
if (!isset($_SESSION['admin'])) {
    $_SESSION['error'] = 'Please log in to access the admin panel';
    if (file_exists($base_dir . '../login.php')) {
        header('location: ../login.php');
    } else {
        die('Login page not found: ../login.php');
    }
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
?>

<?php include 'includes/header.php'; ?>
<body class="hold-transition skin-blue sidebar-mini">
<div class="wrapper">

  <?php 
  if (!file_exists($base_dir . 'includes/navbar.php')) {
      echo '<div class="alert alert-warning">Navbar file not found: includes/navbar.php</div>';
      echo '<nav class="main-header navbar navbar-expand navbar-dark">Admin Panel</nav>';
  } else {
      include 'includes/navbar.php';
  }
  ?>
  
  <?php 
  if (!file_exists($base_dir . 'includes/menubar.php')) {
      echo '<div class="alert alert-warning">Menubar file not found: includes/menubar.php</div>';
      echo '<aside class="main-sidebar"><ul class="sidebar-menu"><li><a href="#">Dashboard</a></li><li class="active"><a href="#">Live Chat</a></li></ul></aside>';
  } else {
      include 'includes/menubar.php';
  }
  ?>

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
              <h3 class="box-title">Chat Management</h3>
            </div>
            <div class="box-body">
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
                    <div class="chat-box" style="max-height: 400px; overflow-y: auto; border: 1px solid #ddd; padding: 10px;">
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
                      <div class="form-group mt-3">
                        <div class="input-group">
                          <textarea name="message" class="form-control" rows="3" placeholder="Type your message..." required></textarea>
                          <span class="input-group-btn">
                            <button type="submit" class="btn btn-primary btn-flat"><i class="fa fa-send"></i> Send</button>
                          </span>
                        </div>
                      </div>
                    </form>
                  <?php else : ?>
                    <p>Select a user to view their chat.</p>
                  <?php endif; ?>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>
  </div>

  <?php 
  if (!file_exists($base_dir . 'includes/footer.php')) {
      echo '<div class="alert alert-warning">Footer file not found: includes/footer.php</div>';
      echo '<footer class="main-footer"><div class="pull-right hidden-xs"><b>Version</b> 1.0</div><strong>Copyright &copy; ' . date('Y') . '</strong></footer>';
  } else {
      include 'includes/footer.php';
  }
  ?>
</div>
<!-- ./wrapper -->

<?php 
if (!file_exists($base_dir . 'includes/scripts.php')) {
    echo '<div class="alert alert-warning">Scripts file not found: includes/scripts.php</div>';
    echo '
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.5.1/dist/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@3.3.7/dist/js/bootstrap.min.js"></script>
    ';
} else {
    include 'includes/scripts.php';
}
?>
<style>
.chat-box {
  max-height: 400px;
  overflow-y: auto;
  border: 1px solid #ddd;
  padding: 10px;
}
.text-right .card {
  background-color: #f8f9fa;
}
.text-left .card {
  background-color: #007bff;
  color: white;
}
</style>
</body>
</html>
