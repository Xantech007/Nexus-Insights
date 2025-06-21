<?php
    // Includes from the dashboard code
    include_once('../inc/conn.php');
    include_once('../admin/includes/format.php');

    // Page metadata
    $page_name = 'Mining Dashboard';
    $page_parent = '';
    $page_title = 'Welcome to CryptoMine - Your Gateway to Cloud Mining';
    $page_description = 'CryptoMine offers high-performance cloud mining services for cryptocurrencies. Explore our plans and start mining today!';

    // Generate random cryptocurrency data
    $cryptos = [
        ['id' => 'bitcoin', 'name' => 'Bitcoin', 'price' => rand(30000, 80000), 'change' => rand(-10, 10) + (rand(0, 99) / 100)],
        ['id' => 'ethereum', 'name' => 'Ethereum', 'price' => rand(1500, 4000), 'change' => rand(-8, 8) + (rand(0, 99) / 100)],
        ['id' => 'litecoin', 'name' => 'Litecoin', 'price' => rand(50, 200), 'change' => rand(-7, 7) + (rand(0, 99) / 100)],
        ['id' => 'dogecoin', 'name' => 'Dogecoin', 'price' => rand(0, 1) + (rand(10, 99) / 100), 'change' => rand(-12, 12) + (rand(0, 99) / 100)],
        ['id' => 'solana', 'name' => 'Solana', 'price' => rand(20, 150), 'change' => rand(-9, 9) + (rand(0, 99) / 100)]
    ];

    // Generate random mining plans
    $plan_names = ['Starter', 'Pro', 'Elite', 'Master', 'Ultra'];
    $mining_plans = [];
    for ($i = 0; $i < 3; $i++) {
        $mining_plans[] = [
            'name' => $plan_names[array_rand($plan_names)] . ' Plan ' . ($i + 1),
            'min_investment' => rand(100, 1000),
            'daily_profit' => rand(1, 5) + (rand(0, 9) / 10),
            'duration' => rand(15, 90)
        ];
    }

    // Generate random news
    $news_titles = [
        'Crypto Market Soars to New Heights',
        'New Mining Algorithm Boosts Efficiency',
        'Bitcoin Halving Event Approaching',
        'Ethereum Upgrade Sparks Investor Interest',
        'Mining Pool Expands to New Regions'
    ];
    $news_images = ['crypto1.jpg', 'crypto2.jpg', 'crypto3.jpg', 'crypto4.jpg', 'crypto5.jpg'];
    $news = [];
    for ($i = 0; $i < 5; $i++) {
        $news[] = [
            'id' => $i + 1,
            'photo' => $news_images[array_rand($news_images)],
            'short_title' => $news_titles[array_rand($news_titles)],
            'slug' => strtolower(str_replace(' ', '-', $news_titles[array_rand($news_titles)]))
        ];
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
                                    <h4 class="page-title">Crypto Mining Dashboard</h4>
                                    <ol class="breadcrumb">
                                        <li class="breadcrumb-item active">Home</li>
                                    </ol>
                                    <div class="alert custom-alert custom-alert-primary icon-custom-alert alert-secondary-shadow fade show" role="alert">
                                        <i class="mdi mdi-rocket-outline alert-icon text-primary align-self-center font-30 mr-3"></i>
                                        <div class="alert-text my-1">
                                            <span><a href="../signup.php" class="btn mb-1 btn-primary">Join Now</a> to Start Earning with Crypto Mining!</span>
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
                                                <tbody>
                                                    <?php foreach ($cryptos as $coin): ?>
                                                        <tr>
                                                            <td><?= htmlspecialchars($coin['name']) ?></td>
                                                            <td class="text-right">$<?= number_format($coin['price'], 2) ?></td>
                                                            <td class="text-right">
                                                                <span class="<?= $coin['change'] >= 0 ? 'text-success' : 'text-danger' ?>">
                                                                    <?= $coin['change'] >= 0 ? '+' : '' ?><?= number_format($coin['change'], 2) ?>%
                                                                </span>
                                                            </td>
                                                        </tr>
                                                    <?php endforeach; ?>
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
                                                <h4 class="card-title">Our Mining Plans</h4>
                                            </div><!--end col-->
                                            <div class="col-auto">
                                                <a href="../signup.php" class="btn btn-sm btn-outline-primary">Get Started</a>
                                            </div><!--end col-->
                                        </div><!--end row-->
                                    </div><!--end card-header-->
                                    <div class="card-body">
                                        <div class="row">
                                            <?php foreach ($mining_plans as $plan): ?>
                                                <div class="col-md-4">
                                                    <div class="card report-card">
                                                        <div class="card-body">
                                                            <h5 class="text-dark mb-2"><?= htmlspecialchars($plan['name']) ?></h5>
                                                            <p class="mb-1"><strong>Minimum Investment:</strong> $<?= number_format($plan['min_investment'], 2) ?></p>
                                                            <p class="mb-1"><strong>Daily Profit:</strong> <?= number_format($plan['daily_profit'], 1) ?>%</p>
                                                            <p class="mb-3"><strong>Duration:</strong> <?= htmlspecialchars($plan['duration']) ?> days</p>
                                                            <a href="../signup.php" class="btn btn-sm btn-primary">Join Plan</a>
                                                        </div><!--end card-body-->
                                                    </div><!--end card-->
                                                </div><!--end col-->
                                            <?php endforeach; ?>
                                        </div><!--end row-->
                                    </div><!--end card-body-->
                                </ Ordinates the card-->
                            </div><!--end col-->
                        </div><!--end row-->
                    </div><!--end col-lg-9-->

                    <div class="col-lg-3">
                        <!-- What's New -->
                        <div class="card">
                            <div class="card-header">
                                <div class="row align-items-center">
                                    <div class="col">
                                        <h4 class="card-title">Crypto News</h4>
                                    </div><!--end col-->
                                </div><!--end row-->
                            </div><!--end card-header-->
                            <div class="card-body">
                                <ul class="list-group custom-list-group mb-n3">
                                    <?php
                                    $index = 1;
                                    foreach ($news as $new):
                                        $tag1 = $index == 1 ? "Crypto News" : ($index == 2 ? "Cryptocurrency" : "Mining");
                                        $tag2 = $index == 1 ? "Market" : "Tech";
                                    ?>
                                        <li class="list-group-item align-items-center d-flex justify-content-between pt-0">
                                            <div class="media">
                                                <img src="../admin/images/<?= htmlspecialchars($new['photo']) ?>" height="30" class="mr-3 align-self-center rounded" alt="...">
                                                <div class="media-body align-self-center">
                                                    <h6 class="m-0"><?= htmlspecialchars(substrwords($new['short_title'], 30)) ?></h6>
                                                    <p class="mb-0 text-muted"><?= htmlspecialchars($tag1) ?>, <?= htmlspecialchars($tag2) ?></p>
                                                </div><!--end media-body-->
                                            </div>
                                            <div class="align-self-center">
                                                <a target="_blank" href="../news-detail.php?id=<?= htmlspecialchars($new['id']) ?>&title=<?= htmlspecialchars($new['slug']) ?>" class="btn btn-sm btn-soft-primary">Read <i class="las la-external-link-alt font-15"></i></a>
                                            </div>
                                        </li>
                                    <?php
                                        $index++;
                                    endforeach;
                                    ?>
                                </ul>
                            </div><!--end card-body-->
                        </div><!--end card-->

                        <!-- Call to Action -->
                        <div class="card">
                            <div class="card-header">
                                <h4 class="card-title">Start Mining Now</h4>
                            </div><!--end card-header-->
                            <div class="card-body">
                                <p class="text-muted">Join the future of cryptocurrency mining with our cutting-edge cloud solutions.</p>
                                <a href="../signup.php" class="btn btn-primary btn-block">Sign Up</a>
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
 رییس: <!-- end page-wrapper -->

    <?php include('inc/scripts.php'); ?>

    <!-- Additional CSS -->
    <style>
        .card-body .row.align-items-center {
            margin-bottom: 10px;
        }
    </style>
</body>
</html>
