<?php
include 'includes/session.php';
include '../account/connect.php';

// Function to parse browser from user agent
function getBrowser($userAgent) {
    $browsers = [
        'Edge' => 'Edg\/([0-9.]+)',
        'Chrome' => 'Chrome\/([0-9.]+)',
        'Firefox' => 'Firefox\/([0-9.]+)',
        'Safari' => 'Safari\/([0-9.]+)',
        'Opera' => 'Opera\/([0-9.]+)',
        'MSIE' => 'MSIE ([0-9.]+)',
        'Trident' => 'rv:([0-9.]+)' // For IE 11
    ];

    foreach ($browsers as $browser => $pattern) {
        if (preg_match("/$pattern/", $userAgent, $match)) {
            return "$browser " . $match[1];
        }
    }
    return 'Unknown';
}
?>

<?php include 'includes/header.php'; ?>
<body class="hold-transition skin-blue sidebar-mini">
<div class="wrapper">
  <?php include 'includes/navbar.php'; ?>
  <?php include 'includes/menubar.php'; ?>

  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <section class="content-header">
      <h1>Tracking Logs</h1>
      <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
        <li class="active">Tracking Logs</li>
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
      <div class="row">
        <div class="col-xs-12">
          <div class="box">
            <div class="box-header with-border">
              <h3 class="box-title">User's Tracking Logs</h3>
            </div>
            <div class="box-body">
              <p><i class="fa fa-eye"></i> Click on the Visitor ID to view tracking details</p>
              <div class="table-responsive">
                <table id="example1" class="table table-bordered">
                  <thead>
                    <th>Visitor ID</th>
                    <th>Latest IP Address</th>
                    <th>Location</th>
                    <th>Browser</th>
                    <th>User Agent</th>
                    <th>Last Visit</th>
                    <th>Visit Count</th>
                    <th>User ID</th>
                    <th>Actions</th>
                  </thead>
                  <tbody>
                    <?php
                      try {
                        // Query to get the latest entry for each visitor_id
                        $stmt = $conne->prepare("
                          SELECT v.id, v.visitor_id, v.ip_address, v.location, v.user_agent, v.visit_time, v.user_id,
                                 (SELECT COUNT(*) FROM visitor_logs v2 WHERE v2.visitor_id = v.visitor_id) as visit_count
                          FROM visitor_logs v
                          INNER JOIN (
                            SELECT visitor_id, MAX(visit_time) as max_visit_time
                            FROM visitor_logs
                            GROUP BY visitor_id
                          ) latest
                          ON v.visitor_id = latest.visitor_id AND v.visit_time = latest.max_visit_time
                          GROUP BY v.visitor_id -- Ensure unique visitor_id
                          ORDER BY v.visit_time DESC
                        ");
                        $stmt->execute();
                        $result = $stmt->get_result();

                        if ($result->num_rows > 0) {
                          while ($row = $result->fetch_assoc()) { ?>
                            <tr>
                              <td><a href="visitor_logs.php?visitor_id=<?php echo urlencode($row['visitor_id']); ?>" title="View details for Visitor ID <?php echo htmlspecialchars($row['visitor_id']); ?>"><?php echo htmlspecialchars(substr($row['visitor_id'], 0, 8)); ?>...</a></td>
                              <td><?php echo htmlspecialchars($row['ip_address']); ?></td>
                              <td><?php echo htmlspecialchars($row['location']); ?></td>
                              <td><?php echo htmlspecialchars(getBrowser($row['user_agent'])); ?></td>
                              <td><?php echo htmlspecialchars(substr($row['user_agent'] ?? 'N/A', 0, 50)); ?>...</td>
                              <td><?php echo date('M d, Y H:i', strtotime($row['visit_time'])); ?></td>
                              <td><?php echo $row['visit_count']; ?></td>
                              <td><?php echo $row['user_id'] ? '<a href="view.php?i_id='.urlencode($row['user_id']).'">'.htmlspecialchars($row['user_id']).'</a>' : 'Guest'; ?></td>
                              <td>
                                <button class="btn btn-danger btn-sm delete btn-flat" data-id="<?php echo $row['id']; ?>" data-visitor-id="<?php echo htmlspecialchars($row['visitor_id']); ?>"><i class="fa fa-trash"></i> Delete</button>
                              </td>
                            </tr>
                          <?php }
                        } else {
                          echo "<tr><td colspan='9'>No visitor logs found.</td></tr>";
                        }
                        $stmt->close();
                      } catch (Exception $e) {
                        echo "<tr><td colspan='9'>Error: " . htmlspecialchars($e->getMessage()) . "</td></tr>";
                      }
                    ?>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>
  </div>
  <?php include 'includes/footer.php'; ?>
  <?php include 'includes/visitor_logs_modal.php'; ?>
</div>

<?php include 'includes/scripts.php'; ?>
<script>
$(document).ready(function(){
  // Destroy any existing DataTable instance before initializing
  if ($.fn.DataTable.isDataTable('#example1')) {
    $('#example1').DataTable().destroy();
  }

  $('#example1').DataTable({
    "order": [[5, "desc"]], // Sort by Last Visit Time
    "columnDefs": [
      { "orderable": true, "targets": [0, 1, 3, 5, 6, 7] }, // Sortable: Visitor ID, IP Address, Browser, Last Visit, Visit Count, User ID
      { "orderable": false, "targets": [2, 4, 8] } // Non-sortable: Location, User Agent, Actions
    ],
    "pageLength": 25
  });

  $(document).on('click', '.delete', function(e){
    e.preventDefault();
    var id = $(this).data('id');
    var visitor_id = $(this).data('visitor-id');
    $('#delete').modal('show');
    $('.did').val(id);
    $('.visitor-id').val(visitor_id);
    $('.name').text(visitor_id.substr(0, 8) + '...');
  });
});
</script>
<style>
.table-responsive {
  overflow-x: auto;
  width: 100%;
}
.table-responsive table {
  min-width: 900px;
}
.box-body p {
  font-weight: bold;
  margin-bottom: 15px;
}
</style>
</body>
</html>
