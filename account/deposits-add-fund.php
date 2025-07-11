<?php
    include('../inc/config.php');
    include '../inc/session.php';

    $page_name = 'Deposits';
    $page_parent = '';
    $page_title = 'Welcome to the Official Website of ' . $settings->siteTitle;
    $page_description = 'Manage Investment provides quality infrastructure backed high-performance cloud computing services for cryptocurrency mining. Choose a plan to get started today! What are you waiting for? Together We Grow!...';

    include('inc/head.php');

    $id = $_SESSION['user'];

    if (!isset($_SESSION['user'])) {
        header('location: ../login.php');
        exit();
    }

    $conn = $pdo->open();

    // Fetch deposit limits from the limits table
    try {
        $stmt = $conn->prepare("SELECT min_deposit, max_deposit FROM limits WHERE id = :id");
        $stmt->execute(['id' => 1]); // Assuming single row with id=1
        $limits = $stmt->fetch(PDO::FETCH_OBJ);
        $min_deposit = $limits ? $limits->min_deposit : 100; // Fallback to 100 if query fails
        $max_deposit = $limits ? $limits->max_deposit : 100000; // Fallback to 100000 if query fails
    } catch (PDOException $e) {
        $min_deposit = 100; // Fallback value
        $max_deposit = 100000; // Fallback value
        $_SESSION['error'] = 'Error fetching deposit limits: ' . $e->getMessage();
    }

    $deposit_madeQuery = $conn->query("SELECT * FROM request WHERE user_id=$id AND type=1 ORDER BY user_id DESC");
    if ($deposit_madeQuery->rowCount()) {
        $deposit_made = $deposit_madeQuery->fetchAll(PDO::FETCH_OBJ);
    }

    $payment_methodQuery = $conn->query("SELECT * FROM payment_methods");
    if ($payment_methodQuery->rowCount()) {
        $payment_method = $payment_methodQuery->fetchAll(PDO::FETCH_OBJ);
    }

    $payment_completeQuery = $conn->query("SELECT * FROM payment_methods ORDER BY id ASC LIMIT 1");
    if ($payment_completeQuery->rowCount()) {
        $payment_complete = $payment_completeQuery->fetchAll(PDO::FETCH_OBJ);
    }

    $depositHistory = "SELECT * FROM transaction WHERE user_id = :id AND type = 1";

    $pdo->close();
?>

<body>
    <!-- Left Sidenav -->
    <?php include('inc/sidebar.php'); ?>
    <!-- end left-sidenav-->

    <div class="page-wrapper">
        <!-- Top Bar Start -->
        <?php include('inc/header.php'); ?>
        <!-- Top Bar End -->

        <!-- Add CSS to set icon colors to grayish -->
        <style>
            .topbar .feather-menu,
            .topbar .feather-search,
            .topbar .feather-bell {
                stroke: #6c757d; /* Grayish color to match the first file */
            }
        </style>

        <!-- Page Content-->
        <div class="page-content">
            <div class="container-fluid">
                <!-- Page-Title -->
                <div class="row">
                    <div class="col-sm-12">
                        <div class="page-title-box">
                            <div class="row">
                                <div class="col">
                                    <h4 class="page-title">Deposits</h4>
                                </div><!--end col-->
                                <div class="col-auto align-self-center">
                                    <a href="#" class="btn btn-sm btn-outline-primary" id="Dash_Date">
                                        <span class="day-name" id="Day_Name">Today:</span> 
                                        <span class="" id="Select_date">Jan 11</span>
                                        <i data-feather="calendar" class="align-self-center icon-xs ml-1" style="stroke: #6c757d;"></i>
                                    </a>
                                </div><!--end col-->
                            </div><!--end row-->
                        </div><!--end page-title-box-->
                    </div><!--end col-->
                </div><!--end row-->
                <!-- end page title end breadcrumb -->

                <?php
                    if (isset($_SESSION['error'])) {
                        echo "
                            <div class='alert alert-danger border-0' role='alert'>
                                <i class='la la-skull-crossbones alert-icon text-danger align-self-center font-30 mr-3'></i>
                                <button type='button' class='close' data-dismiss='alert' aria-label='Close'>
                                    <span aria-hidden='true'><i class='mdi mdi-close align-middle font-16'></i></span>
                                </button>
                                <strong>Oh snap!</strong> " . htmlspecialchars($_SESSION['error']) . "
                            </div>
                        ";
                        unset($_SESSION['error']);
                    }
                    if (isset($_SESSION['success'])) {
                        echo "
                            <div class='alert alert-success border-0' role='alert'>
                                <i class='mdi mdi-check-all alert-icon'></i>
                                <button type='button' class='close' data-dismiss='alert' aria-label='Close'>
                                    <span aria-hidden='true'><i class='mdi mdi-close align-middle font-16'></i></span>
                                </button>
                                <strong>Well done!</strong> " . htmlspecialchars($_SESSION['success']) . "
                            </div>
                        ";
                        unset($_SESSION['success']);
                    }
                ?>

                <div class="row">
                    <div class="col-lg-3"></div>
                    <div class="col-lg-6">
                        <div class="card">
                            <div class="card-header">
                                <h4 class="card-title">Fund Account</h4>
                                <p class="text-muted mb-0">Add Fund to your Account</p>
                            </div><!--end card-header-->
                            <div class="card-body">
                                <div class="card">
                                    <div class="card-body">
                                        <form class="form-horizontal auth-form" method="post" action="deposits-payment-option" id="deposit-form">
                                            <div class="form-group mb-2">
                                                <label>Deposit Charge: 0%</label>
                                            </div><!--end form-group-->
                                            <div class="form-group mb-2">
                                                <label>Deposit Limit: ($<?php echo number_format($min_deposit, 2); ?> - $<?php echo number_format($max_deposit, 2); ?>)</label>
                                            </div><!--end form-group-->
                                            <div class="form-group mb-2">
                                                <div class="input-group mb-3">
                                                    <div class="input-group-prepend"><span class="input-group-text">$</span></div>
                                                    <input type="number" name="deposit_amount" class="form-control" id="deposit-amount" placeholder="Enter Deposit Amount" aria-label="Amount (to the nearest dollar)" min="<?php echo $min_deposit; ?>" max="<?php echo $max_deposit; ?>" step="0.01" required />
                                                    <div class="input-group-append"></div>
                                                </div>
                                                <div id="deposit-error" class="invalid-feedback" style="display: none;"></div>
                                            </div><!--end form-group-->
                                            <div class="form-group mb-0 row">
                                                <div class="col-12">
                                                    <button class="btn btn-primary btn-block waves-effect waves-light" type="submit" name="addFund">Fund <i class="fas fa-money-bill ml-1"></i></button>
                                                </div><!--end col-->
                                            </div><!--end form-group-->
                                        </form><!--end form-->
                                    </div>
                                </div>
                            </div><!--end card-body-->
                        </div><!--end card-->
                    </div><!--end col-->
                </div><!--end row-->
            </div><!-- container -->

            <?php include('inc/footer.php'); ?><!--end footer-->
        </div>
        <!-- end page content -->
    </div>
    <!-- end page-wrapper -->

    <!-- JavaScript for deposit form validation -->
    <script>
        $(document).ready(function() {
            var $depositInput = $('#deposit-amount');
            var $error = $('#deposit-error');
            var minDeposit = <?php echo json_encode($min_deposit); ?>;
            var maxDeposit = <?php echo json_encode($max_deposit); ?>;

            // Function to validate the deposit amount
            function validateAmount(value) {
                var amount = parseFloat(value);
                $error.hide().text('');
                $depositInput.removeClass('is-invalid');

                if (isNaN(amount) || value === '') {
                    $error.text('Please enter a valid amount.').show();
                    $depositInput.addClass('is-invalid');
                    return false;
                }
                if (amount < minDeposit) {
                    $error.text('Deposit amount must be at least $' + minDeposit.toFixed(2) + '.').show();
                    $depositInput.addClass('is-invalid');
                    return false;
                }
                if (amount > maxDeposit) {
                    $error.text('Deposit amount cannot exceed $' + maxDeposit.toFixed(2) + '.').show();
                    $depositInput.addClass('is-invalid');
                    return false;
                }
                return true;
            }

            // Real-time validation on input
            $depositInput.on('input', function() {
                var value = $(this).val();
                validateAmount(value);
            });

            // Prevent negative input
            $depositInput.on('keypress', function(e) {
                if (e.key === '-' || $(this).val().includes('-')) {
                    e.preventDefault();
                    $error.text('Negative amounts are not allowed.').show();
                    $depositInput.addClass('is-invalid');
                }
            });

            // Form submission validation
            $('#deposit-form').on('submit', function(e) {
                var value = $depositInput.val();
                if (!validateAmount(value)) {
                    e.preventDefault();
                    return false;
                }
                return true;
            });
        });
    </script>

    <?php include('inc/scripts.php'); ?>
</body>
</html>
