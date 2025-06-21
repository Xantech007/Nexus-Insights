<?php
    // Includes from the dashboard code
    include_once('../inc/config.php');
    include_once('../inc/conn.php')
    include_once('../admin/includes/format.php');


    // Page metadata
    $page_name = 'Dashboard';
    $page_parent = '';
    $page_title = 'Welcome to the Official Website of ' . htmlspecialchars($settings->siteTitle);
    $page_description = htmlspecialchars($settings->siteTitle) . ' provides quality infrastructure backed high-performance cloud computing services for cryptocurrency mining. Choose a plan to get started today! What are you waiting for? Together We Grow!...';

    // Set PDO error mode to exception
    try {
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch (PDOException $e) {
        error_log("Error setting PDO error mode: " . $e->getMessage());
        echo "Database configuration error. Please try again later.";
        exit();
    }

    // Fetch investment plans
    try {
        $allplanQuery = $conn->prepare("SELECT * FROM investment_plans ORDER BY id ASC");
        $allplanQuery->execute();
        $investment_plans = $allplanQuery->rowCount() ? $allplanQuery->fetchAll(PDO::FETCH_OBJ) : [];
    } catch (PDOException $e) {
        error_log("Error fetching investment plans: " . $e->getMessage());
        $investment_plans = [];
    }

    // Fetch recent news
    try {
        $newQuery = $conn->prepare("SELECT * FROM news ORDER BY id DESC LIMIT 7");
        $newQuery->execute();
        $news = $newQuery->rowCount() ? $newQuery->fetchAll(PDO::FETCH_OBJ) : [];
    } catch (PDOException $e) {
        error_log("Error fetching news: " . $e->getMessage());
        $news = [];
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <?php include_once('inc/head.php'); ?>
</head>
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
                                    <h4 class="page-title">Mining Dashboard</h4>
                                    <ol class="breadcrumb">
                                        <li class="breadcrumb-item active">Home</li>
                                    </ol>
                                    <div class="alert custom-alert custom-alert-primary icon-custom-alert alert-secondary-shadow fade show" role="alert">
                                        <i class="mdi mdi-rocket-outline alert-icon text-primary align-self-center font-30 mr-3"></i>
                                        <div class="alert-text my-1">
                                            <span><a href="../signup.php" class="btn mb-1 btn-primary">Sign Up Now</a> to Start Mining Today!</span>
                                        </div>
                                        <div class="alert-close">
                                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                                <span aria-hidden="true"><i class="mdi mdi-close font-16"></i></span>
                                            </button>
                                        </div>
                                    </div>
                                </div><!--end col-->
                                <div class="col-auto align-self-center">
                                    <a href="#" class="btn btn-sm btn-outline-primary" id="Dash_Date">
                                        <span class="day-name" id="Day_Name">Today:</span> 
                                        <span class="" id="Select_date">
                                            <?php
                                                $dash_date = new DateTime('now', new DateTimeZone('Europe/Paris'));
                                                $dash_date->setTimezone(new DateTimeZone('UTC'));
                                                echo $dash_date->format('M d, Y, g:i A') . ' (UTC)';
                                            ?>
                                        </span>
                                        <i data-feather="calendar" class="align-self-center icon-xs ml-1"></i>
                                    </a>
                                </div><!--end col-->
                            </div><!--end row-->
                        </div><!--end page-title-box-->
                    </div><!--end col-->
                </div><!--end row-->
                <!-- end page title end breadcrumb -->

                <div class="row">
                    <div class="col-lg-9">
                        <!-- Cryptocurrency Prices -->
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="card">
                                    <div class="card-body">
                                        <h4 class="card-title">Cryptocurrency Prices (USD)</h4>
                                        <div id="crypto-prices">
                                            <table class="table border-dashed mb-0">
                                                <thead>
                                                    <tr>
                                                        <th>Cryptocurrency</th>
                                                        <th class="text-right">Price (USD)</th>
                                                        <th class="text-right">24h Change</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="price-table-body">
                                                    <!-- Prices will be populated via JavaScript -->
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div><!--end card-->
                            </div><!--end col-->
                        </div><!--end row-->

                        <!-- Mining Plans -->
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="card">
                                    <div class="card-header">
                                        <div class="row align-items-center">
                                            <div class="col">
                                                <h4 class="card-title">Available Mining Plans</h4>
                                            </div><!--end col-->
                                            <div class="col-auto">
                                                <a href="../signup.php" class="btn btn-sm btn-outline-primary">Get Started</a>
                                            </div><!--end col-->
                                        </div><!--end row-->
                                    </div><!--end card-header-->
                                    <div class="card-body">
                                        <?php if (!empty($investment_plans)): ?>
                                            <div class="row">
                                                <?php foreach ($investment_plans as $plan): ?>
                                                    <div class="col-md-4">
                                                        <div class="card report-card">
                                                            <div class="card-body">
                                                                <h5 class="text-dark mb-2"><?= htmlspecialchars($plan->name) ?></h5>
                                                                <p class="mb-1"><strong>Minimum Investment:</strong> $<?= number_format($plan->min_investment, 2) ?></p>
                                                                <p class="mb-1"><strong>Daily Profit:</strong> <?= htmlspecialchars($plan->daily_profit) ?>%</p>
                                                                <p class="mb-3"><strong>Duration:</strong> <?= htmlspecialchars($plan->duration) ?> days</p>
                                                                <a href="../signup.php" class="btn btn-sm btn-primary">Join Plan</a>
                                                            </div><!--end card-body-->
                                                        </div><!--end card-->
                                                    </div><!--end col-->
                                                <?php endforeach; ?>
                                            </div><!--end row-->
                                        <?php else: ?>
                                            <p class="text-muted">No mining plans available at the moment. Check back soon!</p>
                                        <?php endif; ?>
                                    </div><!--end card-body-->
                                </div><!--end card-->
                            </div><!--end col-->
                        </div><!--end row-->
                    </div><!--end col-lg-9-->

                    <div class="col-lg-3">
                        <!-- What's New -->
                        <div class="card">
                            <div class="card-header">
                                <div class="row align-items-center">
                                    <div class="col">
                                        <h4 class="card-title">What's New</h4>
                                    </div><!--end col-->
                                </div><!--end row-->
                            </div><!--end card-header-->
                            <div class="card-body">
                                <ul class="list-group custom-list-group mb-n3">
                                    <?php if (!empty($news)): ?>
                                        <?php
                                        $index = 1;
                                        foreach ($news as $new):
                                            $tag1 = $index == 1 ? "Crypto News" : ($index == 2 ? "Cryptocurrency" : "Bitcoin");
                                            $tag2 = $index == 1 ? "Apps" : "Tech";
                                        ?>
                                            <li class="list-group-item align-items-center d-flex justify-content-between pt-0">
                                                <div class="media">
                                                    <img src="../admin/images/<?= htmlspecialchars($new->photo); ?>" height="30" class="mr-3 align-self-center rounded" alt="...">
                                                    <div class="media-body align-self-center">
                                                        <h6 class="m-0"><?= htmlspecialchars(substrwords($new->short_title, 30)); ?></h6>
                                                        <p class="mb-0 text-muted"><?= htmlspecialchars($tag1); ?>, <?= htmlspecialchars($tag2); ?></p>
                                                    </div><!--end media-body-->
                                                </div>
                                                <div class="align-self-center">
                                                    <a target="_blank" href="../news-detail.php?id=<?= htmlspecialchars($new->id); ?>&title=<?= htmlspecialchars($new->slug); ?>" class="btn btn-sm btn-soft-primary">Read <i class="las la-external-link-alt font-15"></i></a>
                                                </div>
                                            </li>
                                        <?php
                                            $index++;
                                        endforeach;
                                        ?>
                                    <?php else: ?>
                                        <li class="list-group-item">No news available.</li>
                                    <?php endif; ?>
                                </ul>
                            </div><!--end card-body-->
                        </div><!--end card-->

                        <!-- Call to Action -->
                        <div class="card">
                            <div class="card-header">
                                <h4 class="card-title">Start Mining Today</h4>
                            </div><!--end card-header-->
                            <div class="card-body">
                                <p class="text-muted">Join thousands of miners and start earning with our high-performance cloud mining services.</p>
                                <a href="../signup.php" class="btn btn-primary btn-block">Sign Up Now</a>
                                <a href="../login.php" class="btn btn-outline-primary btn-block">Log In</a>
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

    <?php include('inc/scripts.php'); ?>

    <!-- JavaScript to Fetch Cryptocurrency Prices -->
    <script>
    $(document).ready(function() {
        const apiUrl = 'https://api.coingecko.com/api/v3/simple/price?ids=bitcoin,ethereum,tether,tron,solana&vs_currencies=usd&include_24hr_change=true';

        $.ajax({
            url: apiUrl,
            method: 'GET',
            success: function(data) {
                const coins = [
                    { id: 'bitcoin', name: 'Bitcoin' },
                    { id: 'ethereum', name: 'Ethereum' },
                    { id: 'tether', name: 'USDT' },
                    { id: 'tron', name: 'Tron' },
                    { id: 'solana', name: 'Solana' }
                ];

                let tableBody = '';
                coins.forEach(coin => {
                    const price = data[coin.id]?.usd || 'N/A';
                    const change = data[coin.id]?.usd_24h_change || 0;
                    const changeFormatted = change >= 0 
                        ? `<span class="text-success">+${change.toFixed(2)}%</span>` 
                        : `<span class="text-danger">${change.toFixed(2)}%</span>`;

                    tableBody += `
                        <tr>
                            <td>${coin.name}</td>
                            <td class="text-right">$${parseFloat(price).toFixed(2)}</td>
                            <td class="text-right">${changeFormatted}</td>
                        </tr>
                    `;
                });

                $('#price-table-body').html(tableBody);
            },
            error: function(xhr, status, error) {
                console.error('Error fetching CoinGecko data:', error);
                $('#price-table-body').html('<tr><td colspan="3" class="text-center text-danger">Failed to load prices. Please try again later.</td></tr>');
            }
        });
    });
    </script>

    <!-- Additional CSS -->
    <style>
        .card-body .row.align-items-center {
            margin-bottom: 10px;
        }
    </style>
</body>
</html>
