<?php
include 'includes/session.php';
include '../account/connect.php';

$visitor_id = isset($_GET['visitor_id']) ? $_GET['visitor_id'] : '';

if (!$visitor_id) {
    $_SESSION['error'] = "No Visitor ID provided.";
    header('Location: tracking_logs.php');
    exit();
}

// Query for visitor logs
$stmt_logs = $conne->prepare("
    SELECT id, visitor_id, page_name, visit_time, location, ip_address, user_id, user_agent
    FROM visitor_logs
    WHERE visitor_id = ?
    ORDER BY visit_time DESC
");
$stmt_logs->bind_param("s", $visitor_id);
$stmt_logs->execute();
$result_logs = $stmt_logs->get_result();
$stmt_logs->close();
?>

<?php include 'includes/header.php'; ?>
<body class="hold-transition skin-blue sidebar-mini">
<div class="wrapper">
  <?php include 'includes/navbar.php'; ?>
  <?php include 'includes/menubar.php'; ?>

  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <section class="content-header">
      <h1>Visitor Logs for Visitor ID: <?php echo htmlspecialchars(substr($visitor_id, 0, 8)); ?>...</h1>
      <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
        <li><a href="tracking_logs.php">Tracking Logs</a></li>
        <li class="active">Visitor Details</li>
      </ol>
    </section>
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
        <div class="col-md-12">
          <div class="box">
            <div class="box-body">
              <?php if ($result_logs->num_rows > 0) { ?>
                <div class="table-responsive">
                  <table id="visitorDetails" class="table table-bordered">
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
                          <td><?php echo htmlspecialchars(substr($row['visitor_id'], 0, 8)); ?>...</td>
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
                  <h1>No logs found for Visitor ID: <?php echo htmlspecialchars(substr($visitor_id, 0, 8)); ?>...</h1>
                </section>
              <?php } ?>
            </div>
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
  $('#visitorDetails').DataTable({
    "order": [[4, "desc"]], // Sort by Visit Time
    "pageLength": 25,
    "columnDefs": [
      { "orderable": true, "targets": [0, 2, 3, 4, 7] }, // Sortable: Log ID, IP Address, Page Name, Visit Time, User ID
      { "orderable": false, "targets": [1, 5, 6] } // Non-sortable: Visitor ID, Location, User Agent
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
