<?php
// Enable error reporting for debugging (remove in production)
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

// Handle chat deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_chat'])) {
    $user_id = isset($_POST['user_id']) ? intval($_POST['user_id']) : 0;
    $guest_id = isset($_POST['guest_id']) ? trim($_POST['guest_id']) : null;

    if ($user_id > 0 || !empty($guest_id)) {
        try {
            if ($user_id > 0) {
                $stmtDelete = $conn->prepare("DELETE FROM live_chat WHERE user_id = :user_id");
                $stmtDelete->bindParam(':user_id', $user_id, PDO::PARAM_INT);
            } else {
                $stmtDelete = $conn->prepare("DELETE FROM live_chat WHERE guest_id = :guest_id");
                $stmtDelete->bindParam(':guest_id', $guest_id, PDO::PARAM_STR);
            }
            $stmtDelete->execute();
            $_SESSION['success'] = "Chat deleted successfully!";
        } catch (PDOException $e) {
            $_SESSION['error'] = 'Database error occurred: ' . $e->getMessage();
            error_log("Database error in chat deletion: " . $e->getMessage(), 3, __DIR__ . "/error_log.txt");
        }
    } else {
        $_SESSION['error'] = "Invalid user or guest ID.";
    }
    header("location: livechat.php");
    exit;
}

// Handle message submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['message']) && (isset($_POST['user_id']) || isset($_POST['guest_id']))) {
    $message = trim($_POST['message']);
    $user_id = isset($_POST['user_id']) ? intval($_POST['user_id']) : 0;
    $guest_id = isset($_POST['guest_id']) ? trim($_POST['guest_id']) : null;

    if (!empty($message) && ($user_id > 0 || !empty($guest_id))) {
        try {
            // Insert admin message
            $stmtInsert = $conn->prepare("INSERT INTO live_chat (user_id, guest_id, sender, message, date_sent, status) VALUES (:user_id, :guest_id, 'admin', :message, NOW(), 0)");
            $stmtInsert->bindParam(':user_id', $user_id, PDO::PARAM_INT);
            $stmtInsert->bindParam(':guest_id', $guest_id, PDO::PARAM_STR);
            $stmtInsert->bindParam(':message', $message, PDO::PARAM_STR);
            $stmtInsert->execute();

            // Update status of user's or guest's messages to read
            if ($user_id > 0) {
                $stmtUpdate = $conn->prepare("UPDATE live_chat SET status = 1 WHERE user_id = :user_id AND sender = 'user'");
                $stmtUpdate->bindParam(':user_id', $user_id, PDO::PARAM_INT);
                $stmtUpdate->execute();
            } elseif (!empty($guest_id)) {
                $stmtUpdate = $conn->prepare("UPDATE live_chat SET status = 1 WHERE guest_id = :guest_id AND sender = 'user'");
                $stmtUpdate->bindParam(':guest_id', $guest_id, PDO::PARAM_STR);
                $stmtUpdate->execute();
            }

            $_SESSION['success'] = "Message sent successfully!";
        } catch (PDOException $e) {
            $_SESSION['error'] = 'Database error occurred: ' . $e->getMessage();
            error_log("Database error in admin message send: " . $e->getMessage(), 3, __DIR__ . "/error_log.txt");
        }
    } else {
        $_SESSION['error'] = "Message cannot be empty or invalid user/guest.";
    }
    // Redirect to the same user/guest chat
    $redirect_param = $user_id > 0 ? "user_id=$user_id" : "guest_id=" . urlencode($guest_id);
    header("location: livechat.php?$redirect_param");
    exit;
}

// Fetch all users and guests with chats
$chatEntities = [];
try {
    // Fetch users with chats
    $stmtUsers = $conn->query("SELECT DISTINCT u.id, u.full_name, u.email, NULL as guest_id 
                               FROM users u 
                               JOIN live_chat lc ON u.id = lc.user_id 
                               WHERE lc.user_id > 0
                               ORDER BY u.full_name");
    $users = $stmtUsers->fetchAll(PDO::FETCH_OBJ);

    // Fetch guests with chats
    $stmtGuests = $conn->query("SELECT DISTINCT NULL as id, 'Guest' as full_name, 'N/A' as email, lc.guest_id 
                                FROM live_chat lc 
                                WHERE lc.guest_id IS NOT NULL AND lc.user_id = 0
                                ORDER BY lc.guest_id");
    $guests = $stmtGuests->fetchAll(PDO::FETCH_OBJ);

    // Combine users and guests
    $chatEntities = array_merge($users, $guests);
} catch (PDOException $e) {
    $_SESSION['error'] = 'Database error occurred: ' . $e->getMessage();
    error_log("Database error in fetching users/guests: " . $e->getMessage(), 3, __DIR__ . "/error_log.txt");
}

// Fetch messages for selected user or guest
$chatMessages = [];
$selected_user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : 0;
$selected_guest_id = isset($_GET['guest_id']) ? trim($_GET['guest_id']) : null;

if ($selected_user_id > 0 || !empty($selected_guest_id)) {
    try {
        if ($selected_user_id > 0) {
            $stmtMessages = $conn->prepare("SELECT * FROM live_chat WHERE user_id = :user_id ORDER BY date_sent ASC");
            $stmtMessages->bindParam(':user_id', $selected_user_id, PDO::PARAM_INT);
        } else {
            $stmtMessages = $conn->prepare("SELECT * FROM live_chat WHERE guest_id = :guest_id ORDER BY date_sent ASC");
            $stmtMessages->bindParam(':guest_id', $selected_guest_id, PDO::PARAM_STR);
        }
        $stmtMessages->execute();
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
                <!-- User/Guest List -->
                <div class="col-md-4">
                  <h5>Chats</h5>
                  <ul class="list-group">
                    <?php if (!empty($chatEntities)) : ?>
                      <?php foreach ($chatEntities as $entity) : ?>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                          <a href="livechat.php?<?php echo $entity->id > 0 ? 'user_id=' . $entity->id : 'guest_id=' . urlencode($entity->guest_id); ?>" 
                             class="<?php echo ($selected_user_id == $entity->id && $entity->id > 0) || ($selected_guest_id === $entity->guest_id && !empty($entity->guest_id)) ? 'active' : ''; ?>">
                            <?php echo htmlspecialchars($entity->full_name); ?>
                            <?php echo $entity->id > 0 ? ' (' . htmlspecialchars($entity->email) . ')' : ' (Guest ID: ' . htmlspecialchars(substr($entity->guest_id, 0, 8) . '...') . ')'; ?>
                          </a>
                          <?php
                            $name = $entity->full_name . ($entity->id > 0 ? ' (' . $entity->email . ')' : ' (Guest ID: ' . substr($entity->guest_id, 0, 8) . '...'));
                          ?>
                          <button class="btn btn-ash btn-sm delete-chat btn-flat" 
                                  data-user-id="<?php echo $entity->id > 0 ? htmlspecialchars($entity->id) : ''; ?>" 
                                  data-guest-id="<?php echo !empty($entity->guest_id) ? htmlspecialchars($entity->guest_id) : ''; ?>" 
                                  data-name="<?php echo htmlspecialchars($name); ?>">
                            <i class="fa fa-trash"></i>
                          </button>
                        </li>
                      <?php endforeach; ?>
                    <?php else : ?>
                      <p>No active chats found.</p>
                    <?php endif; ?>
                  </ul>
                </div>
                <!-- Chat Area -->
                <div class="col-md-8">
                  <?php if ($selected_user_id > 0 || !empty($selected_guest_id)) : ?>
                    <h5>Chat with <?php
                      $selected_name = 'Unknown';
                      foreach ($chatEntities as $entity) {
                        if ($entity->id == $selected_user_id || $entity->guest_id == $selected_guest_id) {
                          $selected_name = $entity->full_name . ($entity->id > 0 ? ' (' . $entity->email . ')' : ' (Guest ID: ' . substr($entity->guest_id, 0, 8) . '...)');
                          break;
                        }
                      }
                      echo htmlspecialchars($selected_name);
                    ?></h5>
                    <div class="chat-box" style="max-height: 400px; overflow-y: auto;">
                      <?php if (!empty($chatMessages)) : ?>
                        <?php foreach ($chatMessages as $msg) : ?>
                          <div class="chat-message mb-3 <?php echo $msg->sender == 'admin' ? 'text-right' : 'text-left'; ?>">
                            <div class="card p-2 d-inline-block <?php echo $msg->sender === 'admin' ? 'bg-admin' : 'bg-user'; ?>">
                              <p class="mb-1"><?php echo htmlspecialchars($msg->message); ?></p>
                              <small class="text-muted"><?php echo $msg->date_sent; ?> - <?php echo $msg->sender == 'admin' ? 'You' : ($msg->guest_id ? 'Guest' : 'User'); ?></small>
                            </div>
                          </div>
                        <?php endforeach; ?>
                      <?php else : ?>
                        <p>No messages yet. Start a conversation below!</p>
                      <?php endif; ?>
                    </div>
                    <!-- Message Input Form -->
                    <form method="POST" action="">
                      <?php if ($selected_user_id > 0) : ?>
                        <input type="hidden" name="user_id" value="<?php echo htmlspecialchars($selected_user_id); ?>">
                      <?php else : ?>
                        <input type="hidden" name="guest_id" value="<?php echo htmlspecialchars($selected_guest_id); ?>">
                      <?php endif; ?>
                      <div class="input-group mt-3">
                        <textarea name="message" class="form-control" rows="3" placeholder="Type your message..." required></textarea>
                        <div class="input-group-append">
                          <button type="submit" class="btn btn-primary">Send</button>
                        </div>
                      </div>
                    </form>
                  <?php else : ?>
                    <p>Select a user or guest to view their messages.</p>
                  <?php endif; ?>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>
  </div>

  <!-- Delete Chat Modal -->
  <div class="modal fade" id="deleteChat">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
          <h4 class="modal-title"><b>Deleting Chat...</b></h4>
        </div>
        <div class="modal-body">
          <form method="POST" action="">
            <input type="hidden" class="user-id" name="user_id">
            <input type="hidden" class="guest-id" name="guest_id">
            <input type="hidden" name="delete_chat" value="1">
            <p>Are you sure you want to delete the chat for <span class="name"></span>?</p>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-default btn-flat pull-left" data-dismiss="modal"><i class="fa fa-close"></i> Close</button>
            <button type="submit" class="btn btn-ash btn-flat"><i class="fa fa-trash"></i> Delete</button>
          </form>
        </div>
      </div>
    </div>
  </div>

</div>
<!-- ./wrapper -->

<?php include('includes/footer.php'); ?>
<?php include('includes/scripts.php'); ?>
<script>
$(document).ready(function(){
  $(document).on('click', '.delete-chat', function(e){
    e.preventDefault();
    var user_id = $(this).data('user-id');
    var guest_id = $(this).data('guest-id');
    var name = $(this).data('name');
    $('#deleteChat').modal('show');
    $('.user-id').val(user_id);
    $('.guest-id').val(guest_id);
    $('.name').text(name);
  });
});
</script>
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
.list-group-item a.active {
  background-color: #6c757d; /* Ash gray */
  color: white;
}
.list-group-item a:hover {
  background-color: #f8f9fa; /* Light gray hover */
  color: #333;
}
.input-group textarea {
  padding: 10px;
}
.input-group-append .btn {
  padding: 10px 20px;
}
.btn-ash {
  background-color: #6c757d; /* Ash gray */
  border-color: #6c757d;
  color: white;
}
.btn-ash:hover {
  background-color: #5a6268; /* Darker ash gray */
  border-color: #5a6268;
  color: white;
}
.delete-chat {
  padding: 2px 8px; /* Smaller padding for btn-sm */
}
</style>
</body>
</html>
