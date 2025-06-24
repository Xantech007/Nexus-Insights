<?php
include 'includes/session.php';
include '../account/connect.php';

// Function to parse user agent details
function parseUserAgent($userAgent) {
    include 'includes/browsers.php'; // Load browser patterns from includes/browsers.php

    $result = [
        'browser' => 'Unknown',
        'browser_version' => 'Unknown',
        'os' => 'Unknown',
        'os_version' => 'Unknown',
        'device_type' => 'Unknown',
        'device_brand' => 'Unknown',
        'device_model' => 'Unknown',
        'engine' => 'Unknown',
        'is_bot' => false
    ];

    // OS patterns
    $oses = [
        'Windows' => 'Windows NT ([0-9.]+)',
        'macOS' => 'Mac OS X ([0-9_\.]+)',
        'Linux' => 'Linux',
        'Ubuntu' => 'Ubuntu',
        'Android' => 'Android ([0-9.]+)',
        'iOS' => '(?:iPhone OS|CPU OS) ([0-9_\.]+)'
    ];

    // Device type patterns
    $devices = [
        'Mobile' => 'Mobile|Android|iPhone|iPod',
        'Tablet' => 'iPad|Kindle|Nexus 7|Nexus 9|Nexus 10|Tablet',
        'Desktop' => 'Windows NT|Mac OS X|Linux|X11'
    ];

    // Browser engine patterns
    $engines = [
        'Blink' => 'Chrome|Edg|OPR|SamsungBrowser|UCBrowser|Brave|Vivaldi',
        'WebKit' => 'Safari|Version',
        'Gecko' => 'Firefox|PaleMoon|Waterfox|SeaMonkey|Iceweasel|IceCat',
        'Trident' => 'MSIE|Trident'
    ];

    // Bot patterns
    $bots = [
        'Googlebot' => 'Googlebot',
        'Bingbot' => 'Bingbot',
        'Slurp' => 'Yahoo! Slurp',
        'DuckDuckBot' => 'DuckDuckBot',
        'Baiduspider' => 'Baiduspider'
    ];

    // Device brand/model patterns
    $brands = [
        'Apple' => 'iPhone|iPad|iPod',
        'Samsung' => 'SAMSUNG|Galaxy',
        'Huawei' => 'Huawei',
        'Xiaomi' => 'Xiaomi|Redmi',
        'Amazon' => 'Kindle|Fire'
    ];

    // Detect browser
    foreach ($browsers as $browser => $pattern) {
        if (preg_match("/$pattern/i", $userAgent, $match)) {
            $result['browser'] = $browser;
            $result['browser_version'] = $match[1] ?? ($match[2] ?? 'Unknown'); // Handle IE dual patterns
            break;
        }
    }

    // Detect OS
    foreach ($oses as $os => $pattern) {
        if (preg_match("/$pattern/i", $userAgent, $match)) {
            $result['os'] = $os;
            $result['os_version'] = str_replace('_', '.', $match[1] ?? 'Unknown');
            break;
        }
    }

    // Detect device type
    foreach ($devices as $type => $pattern) {
        if (preg_match("/$pattern/i", $userAgent)) {
            $result['device_type'] = $type;
            break;
        }
    }

    // Detect browser engine
    foreach ($engines as $engine => $pattern) {
        if (preg_match("/$pattern/i", $userAgent)) {
            $result['engine'] = $engine;
            break;
        }
    }

    // Detect bot
    foreach ($bots as $bot => $pattern) {
        if (preg_match("/$pattern/i", $userAgent)) {
            $result['is_bot'] = true;
            $result['browser'] = $bot;
            break;
        }
    }

    // Detect device brand
    foreach ($brands as $brand => $pattern) {
        if (preg_match("/$pattern/i", $userAgent)) {
            $result['device_brand'] = $brand;
            if (preg_match("/($pattern\s+[\w\-\s\/]+)/i", $userAgent, $modelMatch)) {
                $result['device_model'] = trim($modelMatch[1]) ?? 'Unknown';
            }
            break;
        }
    }

    // Log unknown user agents for review
    if ($result['browser'] === 'Unknown') {
        if (!file_exists('logs')) {
            mkdir('logs', 0755, true);
        }
        file_put_contents('logs/unknown_useragents.log', $userAgent . "\n", FILE_APPEND);
    }

    return $result;
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
                    <th>OS</th>
                    <th>Device Type</th>
                    <th>Device Brand</th>
                    <th>Device Model</th>
                    <th>Browser Engine</th>
                    <th>Last Visit</th>
                    <th>Visit Count</th>
                    <th>User ID</th>
                    <th>Is Bot</th>
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
                          while ($row = $result->fetch_assoc()) {
                            $uaInfo = parseUserAgent($row['user_agent']);
                            ?>
                            <tr>
                              <td><a href="visitor_logs.php?visitor_id=<?php echo urlencode($row['visitor_id']); ?>" title="View details for Visitor ID <?php echo htmlspecialchars($row['visitor_id']); ?>"><?php echo htmlspecialchars(substr($row['visitor_id'], 0, 8)); ?>...</a></td>
                              <td><?php echo htmlspecialchars($row['ip_address']); ?></td>
                              <td><?php echo htmlspecialchars($row['location']); ?></td>
                              <td><?php echo htmlspecialchars($uaInfo['browser'] . ' ' . $uaInfo['browser_version']); ?></td>
                              <td><?php echo htmlspecialchars($uaInfo['os'] . ' ' . $uaInfo['os_version']); ?></td>
                              <td><?php echo htmlspecialchars($uaInfo['device_type']); ?></td>
                              <td><?php echo htmlspecialchars($uaInfo['device_brand']); ?></td>
                              <td><?php echo htmlspecialchars($uaInfo['device_model']); ?></td>
                              <td><?php echo htmlspecialchars($uaInfo['engine']); ?></td>
                              <td><?php echo htmlspecialchars(substr($row['user_agent'] ?? 'N/A', 0, 50)); ?>...</td>
                              <td><?php echo date('M d, Y H:i', strtotime($row['visit_time'])); ?></td>
                              <td><?php echo $row['visit_count']; ?></td>
                              <td><?php echo $row['user_id'] ? '<a href="view.php?i_id='.urlencode($row['user_id']).'">'.htmlspecialchars($row['user_id']).'</a>' : 'Guest'; ?></td>
                              <td><?php echo $uaInfo['is_bot'] ? 'Yes' : 'No'; ?></td>
                              <td>
                                <button class="btn btn-danger btn-sm delete btn-flat" data-id="<?php echo $row['id']; ?>" data-visitor-id="<?php echo htmlspecialchars($row['visitor_id']); ?>"><i class="fa fa-trash"></i> Delete</button>
                              </td>
                            </tr>
                          <?php }
                        } else {
                          echo "<tr><td colspan='15'>No visitor logs found.</td></tr>";
                        }
                        $stmt->close();
                      } catch (Exception $e) {
                        echo "<tr><td colspan='15'>Error: " . htmlspecialchars($e->getMessage()) . "</td></tr>";
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
    "order": [[10, "desc"]], // Sort by Last Visit Time (adjusted column index)
    "columnDefs": [
      { "orderable": true, "targets": [0, 1, 3, 4, 5, 6, 7, 8, 10, 11, 12] }, // Sortable: Visitor ID, IP Address, Browser, OS, Device Type, Device Brand, Device Model, Engine, Last Visit, Visit Count, User ID
      { "orderable": false, "targets": [2, 9, 13, 14] } // Non-sortable: Location, User Agent, Is Bot, Actions
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
  min-width: 1200px; /* Increased to accommodate new columns */
}
.box-body p {
  font-weight: bold;
  margin-bottom: 15px;
}
</style>
</body>
</html>
