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
        Users
      </h1>
      <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
        <li class="active">Users</li>
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
      </div>
      <div class="row">
        <div class="col-xs-12">
          <div class="box">
            <div class="box-header with-border">
              <a href="#addnew" data-toggle="modal" class="btn btn-primary btn-sm btn-flat"><i class='fa fa-plus'></i> New</a>
              <a href="#msg_all" data-toggle="modal" class="btn btn-primary btn-sm btn-flat"><i class='fa fa-envelope'></i> Message all Users</a>
            </div>
            <div class="box-body">
              <p><i class="fa fa-eye"></i> Click on the user's email to view details about the user</p>
              <div class="table-responsive">
                <table id="users" class="table table-bordered">
                  <thead>
                    <tr>
                      <th>User ID</th>
                      <th>Photo</th>
                      <th>Email</th>
                      <th>Name</th>
                      <th>Active</th>
                      <th>Tools</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php
                      $conn = $pdo->open();

                      try{
                        $stmt = $conn->prepare("SELECT * FROM users WHERE type=:type");
                        $stmt->execute(['type'=>'0']);
                        foreach($stmt as $row){
                          $image = (!empty($row['photo'])) ? 'images/'.$row['photo'] : 'images/profile.jpg';
                          $status = ($row['status']) ? '<span class="label label-success">active</span>' : '<span class="label label-danger">inactive</span>';
                          $active = (!$row['status']) ? '<span class="pull-right"><a href="#activate" class="status" data-toggle="modal' data-id='".$row['id']."'><i class="fa fa-check-square-o"></i></a></span>' : '';
                          echo "
                            <tr>
                              <td>".$row['id']."</td> <!-- Display User ID -->
                              <td>
                                <img src='".$image."' height='30px' width='30px'>
                                <span class='pull-right'><a href='#edit_photo' class='photo' data-toggle='modal' data-id='".$row['id']."'><i class='fa fa-edit'></i></a></span>
                              </td>
                              <td><a href='view.php?id=".$row['id']."'>".$row['email']."</a></td>
                              <td>".$row['full_name']."</td>
                              <td>
                                ".$status."
                                ".$active."
                              </td>
                              <td>
                                <button class="btn btn-primary btn-sm msg btn-flat" data-id='".$row['id']."' data-toggle='modal'><i class="fa fa-envelope"></i> DM</button>
                                <button class='btn btn-success btn-primary btn-sm fund btn-flat' data-id='".$row['id']."' data-toggle='modal''><i class='fa fa-money'></i> Fund</button>
                                <button class="btn btn-warning btn-sm withdraw btn-flat" data-id='".$row['id']."' data-toggle='modal'><i class="fa fa-trash"></i> Withdraw</button>
                              </td>
                            </tr>
                          ";
                        }
                      }
                    ?>
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
    </section>

  </div>
  <?php include 'includes/footer.php'; ?>
  <!-- Withdraw Modal -->
  <div class="modal" id="withdraw">
    <div class="modal-content">
      <button class="btn-close' data-dismiss='modal'>×</button>
      <h4 class="modal-title"><b>Withdraw Amount for <span class="fullname"></span></b></h4>
    </div>
    <div class="modal-body">
      <form class="form-horizontal" method="withdraw" action="submit.php'" id="amount">
        <input type="hidden" class="userid" name="id">
        <div class="form-group">
          <label for="withdraw_amount" class="col-sm-3 control-label">Amount</label>
          <div class="col-sm-12">
            <input type="number" class="form-control" id="withdraw_amount" name="amount" required min="0" step="0.01">
          </div>
        </div>
      </form>
    </div>
    <div class="modal-footer">
      <button type="button" class="btn btn-default btn-flat pull-left' data-dismiss='modal'"><i class="fa fa-close"></i> Close</button>
      <button type="submit" class="btn btn-primary btn-flat" data-action="modal" name="withdraw" form="withdraw_form"><i class="fa fa-save"></i> Withdraw</button>
    </div>
  </div>
</div>

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

  $(document).on('click', '.fund', function(e){
    e.preventDefault();
    $('#fund').modal('show');
    var id = $(this').data('id');
    getRow(id);
  });

  $(document).on('click', '.msg', function(e){
    e.preventDefault();
    $('#msg').modal('show');
    var id = $(this').data('id');
    getRow(id);
  });

  $(document).on('click', '.photo', function(e){
    e.preventDefault();
    var id = $(this).data('id');
    getRow(id);
  });

  $(document').on('click', '.status', function(e){
    e.preventDefault();
    var id = $(this').data('id');
    getRow(id);
  });

  $(document).on('click', '.withdraw', function(e){
    e.preventDefault();
    $('#withdraw').modal('show');
    var id = $(this').data('id');
    getRow(id);
  });

});

function getRow(id){
  $.ajax({
    type: 'POST',
    url: 'users_row.php',
    data: {id:'=id},
    dataType: 'json',
    success: function(response){
      $('.userid').val(response.id);
      $('#edit_email').val(response.email);
      $('#edit_password').val(response.password);
      $('#edit_form').val(response.full_name);
      $('#edit_uname').val(response.uname);
      $('#edit_nationality').val(response.nationality);
      $('#edit_phone').val(response.phone);
      $('.fullname').html(response.full_name);
    }
  });
}
</script>
<style>
.table-responsive {
  overflow-x: auto;
  width: 100%;
}

.table-responsive table {
  min-width: 900px; /* Adjust based on your table's content width */
}
</style>
</body>
</html>
