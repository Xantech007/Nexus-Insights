<?php
include('../inc/config.php');
include '../inc/session.php';

$page_name = 'Withdrawals';
$page_parent = '';
$page_title = 'Welcome to the Official Website of ' . $settings->siteTitle;
$page_description = $settings->siteTitle . ' provides quality infrastructure backed high-performance cloud computing services for cryptocurrency mining. Choose a plan to get started today! What are you waiting for? Together We Grow!...';

include('inc/head.php');

$id = $_SESSION['user'];

if (!isset($_SESSION['user'])) {
    header('location: ../login.php');
    exit();
}

$conn = $pdo->open();

// Fetch user balance from transaction table (type=1 for credits, type=2 for debits)
try {
    $stmt = $conn->prepare("
        SELECT (
            COALESCE(SUM(CASE WHEN type = 1 THEN amount ELSE 0 END), 0) -
            COALESCE(SUM(CASE WHEN type = 2 THEN amount ELSE 0 END), 0)
        ) AS balance
        FROM transaction
        WHERE user_id = :user_id
    ");
    $stmt->execute(['user_id' => $id]);
    $user = $stmt->fetch(PDO::FETCH_OBJ);
    $balance = floatval($user->balance ?? 0); // Ensure balance is a float
} catch (PDOException $e) {
    $_SESSION['error'] = "Database error: " . $e->getMessage();
    error_log($e->getMessage(), 3, '/home/vol19_2/infinityfree.com/if0_39045086/htdocs/logs/error.log');
    header('location: withdrawals-remove-fund.php');
    exit();
}

// Fetch withdrawal limits from the limits table
try {
    $stmt = $conn->prepare("SELECT min_withdraw, max_withdraw FROM limits WHERE id = :id");
    $stmt->execute(['id' => 1]); // Assuming single row with id=1
    $limits = $stmt->fetch(PDO::FETCH_OBJ);
    $min_withdraw = $limits ? floatval($limits->min_withdraw) : 100; // Fallback to 100 if query fails
    $max_withdraw = $limits ? floatval($limits->max_withdraw) : 100000; // Fallback to 100000 if query fails
} catch (PDOException $e) {
    $min_withdraw = 100; // Fallback value
    $max_withdraw = 100000; // Fallback value
    $_SESSION['error'] = 'Error fetching withdrawal limits: ' . $e->getMessage();
    error_log($e->getMessage(), 3, '/home/vol19_2/infinityfree.com/if0_39045086/htdocs/logs/error.log');
}

// Adjust min and max withdrawal based on balance
$min_amount = $balance < $min_withdraw ? $balance : $min_withdraw;
$max_amount = $balance < $max_withdraw ? $balance : $max_withdraw;

// Fetch withdrawal requests using request_id
try {
    $withdrawal_madeQuery = $conn->prepare("SELECT * FROM request WHERE user_id = :user_id AND type = 2 ORDER BY request_id DESC");
    $withdrawal_madeQuery->execute(['user_id' => $id]);
    if ($withdrawal_madeQuery->rowCount()) {
        $withdrawal_made = $withdrawal_madeQuery->fetchAll(PDO::FETCH_OBJ);
    } else {
        $withdrawal_made = [];
    }
} catch (PDOException $e) {
    $_SESSION['error'] = "Database error: " . $e->getMessage();
    error_log($e->getMessage(), 3, '/home/vol19_2/infinityfree.com/if0_39045086/htdocs/logs/error.log');
}

// Fetch payment methods
try {
    $payment_methodQuery = $conn->query("SELECT * FROM payment_methods");
    if ($payment_methodQuery->rowCount()) {
        $payment_method = $payment_methodQuery->fetchAll(PDO::FETCH_OBJ);
    } else {
        $payment_method = [];
    }
} catch (PDOException $e) {
    $_SESSION['error'] = "Database error: " . $e->getMessage();
    error_log($e->getMessage(), 3, '/home/vol19_2/infinityfree.com/if0_39045086/htdocs/logs/error.log');
}

// Fetch default payment method
try {
    $payment_completeQuery = $conn->query("SELECT * FROM payment_methods ORDER BY id ASC LIMIT 1");
    if ($payment_completeQuery->rowCount()) {
        $payment_complete = $payment_completeQuery->fetchAll(PDO::FETCH_OBJ);
    } else {
        $payment_complete = [];
    }
} catch (PDOException $e) {
    $_SESSION['error'] = "Database error: " . $e->getMessage();
    error_log($e->getMessage(), 3, '/home/vol19_2/infinityfree.com/if0_39045086/htdocs/logs/error.log');
}

// Withdrawal history query
try {
    $withdrawalHistoryQuery = $conn->prepare("SELECT * FROM transaction WHERE user_id = :user_id AND type = 2");
    $withdrawalHistoryQuery->execute(['user_id' => $id]);
    $withdrawalHistory = $withdrawalHistoryQuery->fetchAll(PDO::FETCH_OBJ);
} catch (PDOException $e) {
    $_SESSION['error'] = "Database error: " . $e->getMessage();
    error_log($e->getMessage(), 3, '/home/vol19_2/infinityfree.com/if0_39045086/htdocs/logs/error.log');
}

$pdo->close();
?>

<body class="dark-topbar">
    <!-- Left Sidenav -->
    <?php include('inc/sidebar.php'); ?>
    <!-- end left-sidenav-->

    <div class="page-wrapper">
        <!-- Top Bar Start -->
        <?php include('inc/header.php'); ?>
        <!-- Top Bar End -->

        <!-- Page Content-->
        <div class="page-content">
            <div class="container-fluid">
                <!-- Page-Title -->
                <div class="row">
                    <div class="col-sm-12">
                        <div class="page-title-box">
                            <div class="row">
                                <div class="col">
                                    <h4 class="page-title">Withdrawals</h4>
                                </div><!--end col-->
                                <div class="col-auto align-self-center">
                                    <a href="#" class="btn btn-sm btn-outline-primary" id="Dash_Date">
                                        <span class="day-name" id="Day_Name">Today:</span> 
                                        <span class="" id="Select_date"><?php echo date('M d'); ?></span>
                                        <i data-feather="calendar" class="align-self-center icon-xs ml-1"></i>
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
                                <h4 class="card-title">Withdraw Funds</h4>
                                <p class="text-muted mb-0">Withdraw funds from your wallet balance</p>
                            </div><!--end card-header-->
                            <div class="card-body">
                                <div class="card">
                                    <div class="card-body">
                                        <form class="form-horizontal auth-form" method="post" action="withdrawals-payment-complete.php" id="withdrawal-form">
                                            <div class="form-group mb-2">
                                                <label>Available Balance: $<?php echo number_format($balance, 2); ?></label>
                                            </div><!--end form-group-->
                                            <div class="form-group mb-2">
                                                <label>Charge: 0%</label>
                                            </div><!--end form-group-->
                                            <div class="form-group mb-2">
                                                <label>Withdrawal Limit: ($<?php echo number_format($min_amount, 2); ?> - $<?php echo number_format($max_amount, 2); ?>)</label>
                                            </div><!--end form-group-->
                                            <div class="form-group mb-2">
                                                <div class="input-group mb-3">
                                                    <div class="input-group-prepend"><span class="input-group-text">$</span></div>
                                                    <input type="number" name="withdrawal_amount" id="withdrawal-amount" class="form-control" placeholder="Enter Amount to Withdraw" aria-label="Amount (to the nearest dollar)" min="<?php echo $min_amount; ?>" max="<?php echo $max_amount; ?>" step="0.01" required />
                                                    <div class="input-group-append"></div>
                                                </div>
                                                <div id="withdrawal-error" class="invalid-feedback" style="display: none;"></div>
                                            </div><!--end form-group-->
                                            <div class="form-group mb-0 row">
                                                <div class="col-12">
                                                    <button class="btn btn-primary btn-block waves-effect waves-light" type="submit" name="removeFund">Withdraw <i class="fas fa-money-bill ml-1"></i></button>
                                                </div><!--end col-->
                                            </div> <!--end form-group-->
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

    <!-- JavaScript for withdrawal form validation -->
    <script>
        $(document).ready(function() {
            var $withdrawalInput = $('#withdrawal-amount');
            var $error = $('#withdrawal-error');
            var balance = parseFloat(<?php echo json_encode($balance); ?>);
            var minAmount = parseFloat(<?php echo json_encode($min_amount); ?>);
            var maxAmount = parseFloat(<?php echo json_encode($max_amount); ?>);

            // Debug: Log values to console
            console.log('Balance:', balance);
            console.log('Min Amount:', minAmount);
            console.log('Max Amount:', maxAmount);

            // Function to validate the withdrawal amount
            function validateAmount(value) {
                var amount = parseFloat(value);
                $error.hide().text('');
                $withdrawalInput.removeClass('is-invalid');

                if (isNaN(amount) || value === '') {
                    $error.text('Please enter a valid amount.').show();
                    $withdrawalInput.addClass('is-invalid');
                    return false;
                }
                if (amount <= 0) {
                    $error.text('Withdrawal amount must be greater than zero.').show();
                    $withdrawalInput.addClass('is-invalid');
                    return false;
                }
                if (amount < minAmount) {
                    $error.text('Withdrawal amount must be at least $' + minAmount.toFixed(2) + '.').show();
                    $withdrawalInput.addClass('is-invalid');
                    return false;
                }
                if (amount > maxAmount) {
                    $error.text('Withdrawal amount cannot exceed $' + maxAmount.toFixed(2) + '.').show();
                    $withdrawalInput.addClass('is-invalid');
                    return false;
                }
                if (amount > balance) {
                    $error.text('Withdrawal amount cannot exceed your available balance ($' + balance.toFixed(2) + ').').show();
                    $withdrawalInput.addClass('is-invalid');
                    return false;
                }
                return true;
            }

            // Real-time validation on input
            $withdrawalInput.on('input', function() {
                var value = $(this).val();
                validateAmount(value);
            });

            // Prevent negative input
            $withdrawalInput.on('keypress', function(e) {
                if (e.key === '-' || $(this).val().includes('-')) {
                    e.preventDefault();
                    $error.text('Negative amounts are not allowed.').show();
                    $withdrawalInput.addClass('is-invalid');
                }
            });

            // Form submission validation
            $('#withdrawal-form').on('submit', function(e) {
                var value = $withdrawalInput.val();
                console.log('Submitted amount:', value); // Debug
                if (!validateAmount(value)) {
                    e.preventDefault();
                    console.log('Validation failed'); // Debug
                    return false;
                }
                console.log('Validation passed'); // Debug
                return true;
            });
        });
    </script>

    <?php include('inc/scripts.php'); ?>
</body>
</html>
