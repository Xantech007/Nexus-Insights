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

// Fetch users with chats
$users = [];
try {
    $stmtUsers = $conn->prepare("
        SELECT u.id, u.full_name, u.email,
               (SELECT MAX(date_sent) FROM live_chat lc WHERE lc.user_id = u.id) as last_message_time,
               (SELECT COUNT(*) FROM live_chat lc WHERE lc.user_id = u.id) as message_count
        FROM users u
        JOIN live_chat lc ON u.id = lc.user_id
        WHERE lc.user_id > 0
        GROUP BY u.id
        ORDER BY last_message_time DESC
    ");
    $stmtUsers->execute();
    $users = $stmtUsers->fetchAll(PDO::FETCH_OBJ);
} catch (PDOException $e) {
    $_SESSION['error'] = 'Database error occurred: ' . $e->getMessage();
    error_log("Database error in fetching users: " . $e->getMessage(), 3, __DIR__ . "/error_log.txt");
}

// Fetch guests with chats
$guests = [];
try {
    $stmtGuests = $conn->prepare("
        SELECT lc.guest_id,
               MAX(lc.date_sent) as last_message_time,
               COUNT(*) as message_count
        FROM live_chat lc
        WHERE lc.guest_id IS NOT NULL AND lc.user_id = 0
        GROUP BY lc.guest_id
        ORDER BY last_message_time DESC
    ");
    $stmtGuests->execute();
    $guests = $stmtGuests->fetchAll(PDO::FETCH_OBJ);
} catch (PDOException $e) {
    $_SESSION['error'] = 'Database error occurred: ' . $e->getMessage();
    error_log("Database error in fetching guests: " . $e->getMessage(), 3, __DIR__ . "/error_log.txt");
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

// Temporary debug log to verify selection (remove in production)
error_log("Selected user_id: $selected_user_id, Selected guest_id: $selected_guest_id", 3, __DIR__ . "/debug_log.txt");

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
                <!-- Users Table -->
                <div class="col-md-6">
                  <h5>Users</h5>
                  <p><i class="fa fa-eye"></i> Click on the User ID to view chat details</p>
                  <div class="table-responsive">
                    <table id="usersTable" class="table table-bordered">
                      <thead>
                        <th>User ID</th>
                        <th>Full Name</th>
                        <th>Email</th>
                        <th>Last Message Time</th>
                        <th>Message Count</th>
                        <th>Actions</th>
                      </thead>
                      <tbody>
                        <?php if (!empty($users)) : ?>
                          <?php foreach ($users as $user) : ?>
                            <tr>
                              <td>
                                <a href="livechat.php?user_id=<?php echo urlencode($user->id); ?>" 
                                   title="View chat for User ID <?php echo htmlspecialchars($user->id); ?>">
                                   <?php echo htmlspecialchars($user->id); ?>
                                </a>
                              </td>
                              <td><?php echo htmlspecialchars($user->full_name); ?></td>
                              <td><?php echo htmlspecialchars($user->email); ?></td>
                              <td><?php echo $user->last_message_time ? date('M d, Y H:i', strtotime($user->last_message_time)) : 'N/A'; ?></td>
                              <td><?php echo $user->message_count; ?></td>
                              <td>
                                <button class="btn btn-danger btn-sm delete btn-flat" 
                                        data-id="<?php echo $user->id; ?>" 
                                        data-type="user" 
                                        title="Delete chat history">
                                  <i class="fa fa-trash"></i> Delete
                                </button>
                              </td>
                            </tr>
                          <?php endforeach; ?>
                        <?php else : ?>
                          <tr><td colspan="6">No user chats found.</td></tr>
                        <?php endif; ?>
                      </tbody>
                    </table>
                  </div>
                </div>
                <!-- Guests Table -->
                <div class="col-md-6">
                  <h5>Guests</h5>
                  <p><i class="fa fa-eye"></i> Click on the Guest ID to view chat details</p>
                  <div class="table-responsive">
                    <table id="guestsTable" class="table table-bordered">
                      <thead>
                        <th>Guest ID</th>
                        <th>Last Message Time</th>
                        <th>Message Count</th>
                        <th>Actions</th>
                      </thead>
                      <tbody>
                        <?php if (!empty($guests)) : ?>
                          <?php foreach ($guests as $guest) : ?>
                            <tr>
                              <td>
                                <a href="livechat.php?guest_id=<?php echo urlencode($guest->guest_id); ?>" 
                                   title="View chat for Guest ID <?php echo htmlspecialchars($guest->guest_id); ?>">
                                   <?php echo htmlspecialchars(substr($guest->guest_id, 0, 8)); ?>...
                                </a>
                              </td>
                              <td><?php echo $guest->last_message_time ? date('M d, Y H:i', strtotime($guest->last_message_time)) : 'N/A'; ?></td>
                              <td><?php echo $guest->message_count; ?></td>
                              <td>
                                <button class="btn btn-danger btn-sm delete btn-flat" 
                                        data-id="<?php echo htmlspecialchars($guest->guest_id); ?>" 
                                        data-type="guest" 
                                        title="Delete chat history">
                                  <i class="fa fa-trash"></i> Delete
                                </button>
                              </td>
                            </tr>
                          <?php endforeach; ?>
                        <?php else : ?>
                          <tr><td colspan="4">No guest chats found.</td></tr>
                        <?php endif; ?>
                      </tbody>
                    </table>
                  </div>
                </div>
              </div>
              <!-- Chat Area -->
              <div class="row mt-4">
                <div class="col-xs-12">
                  <?php if ($selected_user_id > 0 || !empty($selected_guest_id)) : ?>
                    <h5>Chat with <?php
                      $selected_name = 'Unknown';
                      if ($selected_user_id > 0) {
                        foreach ($users as $user) {
                          if ($user->id == $selected_user_id) {
                            $selected_name = $user->full_name . ' (' . $user->email . ')';
                            break;
                          }
                        }
                      } else {
                        foreach ($guests as $guest) {
                          if ($guest->guest_id == $selected_guest_id) {
                            $selected_name = 'Guest (ID: ' . substr($guest->guest_id, 0, 8) . '...)';
                            break;
                          }
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
                    <p>Select a user or guest from the tables above to view their messages.</p>
                  <?php endif; ?>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>
  </div>

  <?php include 'includes/livechat_modal.php'; ?>
  <?php include 'includes/footer.php'; ?>
  <?php include 'includes/scripts.php'; ?>
</div>
<!-- ./wrapper -->

<script>
$(document).ready(function(){
  // Initialize Users Table
  if ($.fn.DataTable.isDataTable('#usersTable')) {
    $('#usersTable').DataTable().destroy();
  }
  $('#usersTable').DataTable({
    "order": [[3, "desc"]], // Sort by Last Message Time
    "columnDefs": [
      { "orderable": true, "targets": [0, 1, 2, 3, 4] }, // Sortable: User ID, Full Name, Email, Last Message Time, Message Count
      { "orderable": false, "targets": [5] } // Non-sortable: Actions
    ],
    "pageLength": 10
  });

  // Initialize Guests Table
  if ($.fn.DataTable.isDataTable('#guestsTable')) {
    $('#guestsTable').DataTable().destroy();
  }
  $('#guestsTable').DataTable({
    "order": [[1, "desc"]], // Sort by Last Message Time
    "columnDefs": [
      { "orderable": true, "targets": [0, 1, 2] }, // Sortable: Guest ID, Last Message Time, Message Count
      { "orderable": false, "targets": [3] } // Non-sortable: Actions
    ],
    "pageLength": 10
  });

  // Delete button click handler
  $(document).on('click', '.delete', function(e){
    e.preventDefault();
    var id = $(this).data('id');
    var type = $(this).data('type');
    var name = type === 'user' ? 'User ID: ' + id : 'Guest ID: ' + id.substr(0, 8) + '...';
    $('#delete').modal('show');
    $('.did').val(id);
    $('.type').val(type);
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
.table-responsive {
  overflow-x: auto;
  width: 100%;
}
.table-responsive table {
  min-width: 600px;
}
.box-body p {
  font-weight: bold;
  margin-bottom: 15px;
}
.mt-4 {
  margin-top: 2rem;
}
</style>
</body>
</html>
