<?php
include 'includes/session.php';
include '../account/connect.php';

// Get IP address from query parameter
$ip = $_GET['ip'] ?? null;

if (!$ip) {
    $_SESSION['error'] = "No IP address provided.";
    header('Location: tracking_logs.php');
    exit();
}

// Query for visitor logs
$stmt_logs = $conne->prepare("
    SELECT id, visitor_id, page_name, visit_time, location, ip_address, user_id, user_agent
    FROM visitor_logs
    WHERE ip_address = ?
    ORDER BY visit_time DESC
");
$stmt_logs->bind_param("s", $ip);
$stmt_logs->execute();
$result_logs = $stmt_logs->get_result();

// Get distinct user IDs associated with this IP
$user_ids = [];
if ($result_logs->num_rows > 0) {
    while ($row = $result_logs->fetch_assoc()) {
        if ($row['user_id']) {
            $user_ids[$row['user_id']] = true;
        }
        $logs[] = $row; // Store logs for display
    }
    $result_logs->data_seek(0); // Reset pointer
}

// Query for user details for all associated user IDs
$users = [];
if (!empty($user_ids)) {
    $user_id_list = implode(',', array_keys($user_ids));
    $stmt_user = $conne->prepare("SELECT id, full_name, uname, email, photo FROM users WHERE id IN ($user_id_list)");
    $stmt_user->execute();
    $result_user = $stmt_user->get_result();
    while ($row_user = $result_user->fetch_assoc()) {
        $users[$row_user['id']] = $row_user;
    }
    $stmt_user->close();
}

$stmt_logs->close();
?>

<?php include 'includes/header.php'; ?>
<body class="hold-transition skin-blue sidebar-mini">
<div class="wrapper">
  <?php include 'includes/navbar.php'; ?>
  <?php include 'includes/menubar.php'; ?>

  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <section class="content">
      <div class="row">
        <?php
          if (isset($_SESSION['error'])) {
            echo "
              <div class='alert alert-danger alert-dismissible'>
                <button type='button' class='close' data-dismiss='alert' aria-hidden='true'>×</button>
                <h4><i class='icon fa fa-warning'></i> Error!</h4>
                ".$_SESSION['error']."
              </div>
            ";
            unset($_SESSION['error']);
          }
          if (isset($_SESSION['success'])) {
            echo "
              <div class='alert alert-success alert-dismissible'>
                <button type='button' class='close' data-dismiss='alert' aria-hidden='true'>×</button>
                <h4><i class='icon fa fa-check'></i> Success!</h4>
                ".$_SESSION['success']."
              </div>
            ";
            unset($_SESSION['success']);
          }
        ?>
        <div class="marbtm50 wdt-100">
          <div class="col-lg-4 col-md-4 col-sm-12 col-xs-12">
            <span class="portfolio-img-column image_hover">
              <?php if (!empty($users)) { ?>
                <img src="images/<?php echo htmlspecialchars($users[array_key_first($users)]['photo'] ?? 'default.jpg'); ?>" class="img-responsive zoom_img_effect" style="height: 24rem" alt="user-image">
              <?php } else { ?>
                <img src="images/default.jpg" class="img-responsive zoom_img_effect" style="height: 24rem" alt="user-image">
              <?php } ?>
            </span>
          </div>
          <div class="col-lg-8 col-md-8 col-sm-12 col-xs-12 project-desc">
            <ul class="profile_info list_none mb-4 pt-2 border-bottom">
              <li>
                <span class="title"><i class="fa fa-user"></i> Associated Users:</span>
                <p>
                  <?php if (!empty($users)) {
                      foreach ($users as $user) {
                          echo '<a href="view.php?i_id='.urlencode($user['id']).'">'.htmlspecialchars($user['full_name']).'</a><br>';
                      }
                  } else {
                      echo 'No associated users';
                  } ?>
                </p>
              </li>
              <li>
                <span class="title"><i class="fa fa-envelope"></i> Usernames:</span>
                <p>
                  <?php if (!empty($users)) {
                      foreach ($users as $user) {
                          echo htmlspecialchars($user['uname']).'<br>';
                      }
                  } else {
                      echo 'N/A';
                  } ?>
                </p>
              </li>
              <li>
                <span class="title"><i class="fa fa-hourglass-end"></i> Emails:</span>
                <p>
                  <?php if (!empty($users)) {
                      foreach ($users as $user) {
                          echo htmlspecialchars($user['email']).'<br>';
                      }
                  } else {
                      echo 'N/A';
                  } ?>
                </p>
              </li>
              <li>
                <span class="title"><i class="fa fa-map-marker"></i> IP Address:</span>
                <p><?php echo htmlspecialchars($ip); ?></p>
              </li>
            </ul>
          </div>
        </div>
        <div class="col-md-12 marbtm50 wdt-100">
          <section class="content-header">
            <h1>Tracking Details for IP: <?php echo htmlspecialchars($ip); ?></h1>
          </section>
          <div class="box-body">
            <?php if ($result_logs->num_rows > 0) { ?>
              <div class="table-responsive">
                <table id="example1" class="table table-bordered">
                  <thead>
                    <th>Log ID</th>
                    <th>Visitor ID</th>
                    <th>IP Address</th>
                    <th>Page Name</th>
                    <th>Visit Time (UTC)</th>
                    <th>Location</th>
                    <th>User Agent</th>
                    <th>User ID</th>
                  </thead>
                  <tbody>
                    <?php while ($row = $result_logs->fetch_assoc()) { ?>
                      <tr>
                        <td><?php echo htmlspecialchars($row['id']); ?></td>
                        <td>
                          <a href="visitor_logs.php?visitor_id=<?php echo urlencode($row['visitor_id']); ?>" title="View logs for Visitor ID <?php echo htmlspecialchars($row['visitor_id']); ?>">
                            <?php echo htmlspecialchars(substr($row['visitor_id'], 0, 8)); ?>...
                          </a>
                        </td>
                        <td><?php echo htmlspecialchars($row['ip_address']); ?></td>
                        <td><?php echo htmlspecialchars($row['page_name']); ?></td>
                        <td>
                          <?php
                            try {
                              $time = new DateTime($row['visit_time'], new DateTimeZone('Europe/Paris'));
                              $time->setTimezone(new DateTimeZone('UTC'));
                              echo htmlspecialchars($time->format("d/m/Y, g:i A") . ' UTC');
                            } catch (Exception $e) {
                              echo 'Invalid date';
                            }
                          ?>
                        </td>
                        <td><?php echo htmlspecialchars($row['location']); ?></td>
                        <td><?php echo htmlspecialchars(substr($row['user_agent'] ?? 'N/A', 0, 50)); ?>...</td>
                        <td>
                          <?php if ($row['user_id']) { ?>
                            <a href="view.php?i_id=<?php echo urlencode($row['user_id']); ?>">
                              <?php echo htmlspecialchars($row['user_id']); ?>
                            </a>
                          <?php } else { ?>
                            Guest
                          <?php } ?>
                        </td>
                      </tr>
                    <?php } ?>
                  </tbody>
                </table>
              </div>
            <?php } else { ?>
              <section class="content-header">
                <h1>No tracking logs found for IP: <?php echo htmlspecialchars($ip); ?></h1>
              </section>
            <?php } ?>
          </div>
        </div>
      </div>
    </section>
  </div>
  <?php include 'includes/footer.php'; ?>
</div>
<?php include 'includes/scripts.php'; ?>
<script>
$(document).ready(function(){
  $('#example1').DataTable({
    "order": [[4, "desc"]], // Sort by Visit Time
    "pageLength": 25,
    "columnDefs": [
      { "orderable": true, "targets": [0, 1, 2, 3, 4, 7] }, // Sortable: Log ID, Visitor ID, IP Address, Page Name, Visit Time, User ID
      { "orderable": false, "targets": [5, 6] } // Non-sortable: Location, User Agent
    ]
  });
});
</script>
<style>
.table-responsive {
  overflow-x: auto;
  width: 100%;
}
.table-responsive table {
  min-width: 1000px;
}
</style>
</body>
</html>
