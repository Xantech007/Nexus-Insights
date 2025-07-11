<?php include 'includes/session.php'; ?>
<?php include 'includes/header.php'; ?>
<body class="hold-transition skin-blue sidebar-mini">
<div class="wrapper">

  <?php include 'includes/navbar.php'; ?>
  <?php include 'includes/menubar.php'; ?>

  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <h1>
        Investment Plans
      </h1>
      <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
        <li>Investment Plans</li>
        <li class="active">Investment Plans</li>
      </ol>
    </section>

    <!-- Main content -->
    <section class="content">
      <?php
        if(isset($_SESSION['error'])){
          echo "
            <div class='alert alert-danger alert-dismissible'>
              <button type='button' class='close' data-dismiss='alert' aria-hidden='true'>×</button>
              <h4><i class='icon fa fa-warning'></i> Error!</h4>
              ".$_SESSION['error']."
            </div>
          ";
          unset($_SESSION['error']);
        }
        if(isset($_SESSION['success'])){
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
              <a href="#addnew" data-toggle="modal" class="btn btn-primary btn-sm btn-flat"><i class="fa fa-plus"></i> New</a>
              <a href="#editLimits" data-toggle="modal" class="btn btn-primary btn-sm btn-flat edit-limits"><i class="fa fa-edit"></i> Deposit and Withdrawal Limits</a>
            </div>
            <div class="box-body">
              <div class="table-responsive">
                <table id="example1" class="table table-bordered">
                  <thead>
                    <th>Name</th>
                    <th>Duration</th>
                    <th>Rate</th>
                    <th>Minimum Investment</th>
                    <th>Maximum Investment</th>
                    <th>Actions</th>
                  </thead>
                  <tbody>
                    <?php
                      $conn = $pdo->open();

                      try{
                        $stmt = $conn->prepare("SELECT * FROM investment_plans ORDER BY id ASC");
                        $stmt->execute();
                        foreach($stmt as $row){
                          echo "
                            <tr>
                              <td>".$row['name']."</td>
                              <td>".$row['duration']."</td>
                              <td>".$row['rate']."</td>
                              <td>".$row['min_invest']."</td>
                              <td>".$row['max_invest']."</td>
                              <td>
                                <button class='btn btn-success btn-sm edit btn-flat' data-id='".$row['id']."'><i class='fa fa-edit'></i> Edit</button>
                                <button class='btn btn-danger btn-sm delete btn-flat' data-id='".$row['id']."'><i class='fa fa-trash'></i> Delete</button>
                              </td>
                            </tr>
                          ";
                        }
                      }
                      catch(PDOException $e){
                        echo $e->getMessage();
                      }

                      $pdo->close();
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
  <?php include 'includes/investment_plans_modal.php'; ?>
  <?php include 'includes/investment_plans_modal2.php'; ?>
  <?php include 'includes/limits_modal.php'; ?>

</div>
<!-- ./wrapper -->

<?php include 'includes/scripts.php'; ?>
<script>
$(function(){
  $(document).on('click', '.edit', function(e){
    e.preventDefault();
    $('#edit').modal('show');
    var id = $(this).data('id');
    getRow(id);
  });

  $(document).on('click', '.delete', function(e){
    e.preventDefault();
    $('#delete').modal('show');
    var id = $(this).data('id');
    getRow(id);
  });

  $(document).on('click', '.photo', function(e){
    e.preventDefault();
    var id = $(this).data('id');
    getRow(id);
  });

  $(document).on('click', '.desc', function(e){
    e.preventDefault();
    var id = $(this).data('id');
    getRow(id);
  });

  $(document).on('click', '.edit-limits', function(e){
    e.preventDefault();
    $('#editLimits').modal('show');
    getLimits();
  });

  $('#addreview').click(function(e){
    e.preventDefault();
    getCategory();
    getBrand();
  });

  $("#addnew").on("hidden.bs.modal", function () {
      $('.append_items').remove();
  });

  $("#edit").on("hidden.bs.modal", function () {
      $('.append_items').remove();
  });

  function getRow(id){
    $.ajax({
      type: 'POST',
      url: 'investment_plans_row.php',
      data: {id:id},
      dataType: 'json',
      success: function(response){
        $('.name').html(response.ipname);
        $('.ipid').val(response.ipid);
        $('#edit_name').val(response.ipname);
        $('#edit_duration').val(response.duration);
        $('#edit_rate').val(response.rate);
        $('#edit_min_invest').val(response.min_invest);
        $('#edit_max_invest').val(response.max_invest);
      }
    });
  }

  function getLimits(){
    $.ajax({
      type: 'POST',
      url: 'limits_row.php',
      data: {id: 1}, // Assuming single row with id=1
      dataType: 'json',
      success: function(response){
        $('#edit_min_deposit').val(response.min_deposit);
        $('#edit_max_deposit').val(response.max_deposit);
        $('#edit_min_withdraw').val(response.min_withdraw);
        $('#edit_max_withdraw').val(response.max_withdraw);
      }
    });
  }
});
</script>
<style>
.table-responsive {
  overflow-x: auto;
  width: 100%;
}

.table-responsive table {
  min-width: 800px; /* Adjust based on your table's content width */
}
</style>
</body>
</html>
