<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Sales</title>
    <base href="<?= $web_root ?>"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="css/browse.css" rel="stylesheet" type="text/css"/>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body>
    <a href="user/profile" class="back-btn">
        <i class="bi bi-arrow-left"></i>
    </a>
    <div class = "main_title browse_header">Sales<i class="bi bi-cart4"></i></div>

    <div class ="title">
        <h3>Completed sales</h3>
        <p>A snapshot of the deals you've wrapped up.</p>
    </div>

    <div class="sales-stats-row">

            <div class="sales-stats">
                <h2>Total revenue</h2>
                <p class="stats-value">€ <?= number_format($user->get_total_revenue(), 2, ',', '.') ?></p>
                <p class="stats-label">Across <?= count($myItems) ?> sales</p>
            </div>

            <div class="sales-stats">
                <h2>Average ticket</h2>
                <p class="stats-value"> € <?= number_format($user->get_average(), 2, ',', '.') ?></p>
                <p class="stats-label">Median buyer appetite indicator</p>
            </div>
            <div class="sales-stats">
                <h2>Loyal bidder</h2>
                <p class="stats-value"><?= $user->get_most_loyal_bidder() != null ? $user->get_most_loyal_bidder()->getPseudo() : '' ?></p>
                <p class="stats-label">Most recurring winning bidder</p>
            </div>

    </div>


        <div class = "sales-section">

            <ul class="sale-items-list">

                <?php foreach ($myItems as $item) : ?>
                <li class="sale-items-wrapper">
                    <a href="item/open/<?= $item->getId() ?>/from-sales" class="item-card-link">
                        <div class="item-card">
                        <div class="item-label">
                            <?php if ($item->isIsAuction()): ?>
                                <span class="label auction"><i class="fa-solid fa-gavel"></i> Auction</span>
                            <?php endif; ?>
                            <?php if ($item->isIsBuyNow()): ?>
                                <span class="label buy"><i class="bi bi-cart-plus"></i> Buy Now</span>
                            <?php endif; ?>
                        </div>
                        <div class="item-image-container">
                            <img class="item-img" src="<?= $item->get_main_photo() ?: 'no-image.png' ?>" alt="<?= htmlspecialchars($item->getTitle()) ?>">
                            <?php if ($item->getImageCount() > 0): ?>
                                <div class="image-label">
                                    <i class="bi bi-image"></i>
                                    <?= $item->getImageCount() . ' ' . ($item->getImageCount() === 1 ? 'image' : 'images') ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="item-title"><?= $item->getTitle() ?></div>
                        <?php
                        $priceClass = $item->isIsBuyNow() ? 'price-buy' : 'price-auction';
                        ?>
                        <div class="item-price <?=  $priceClass ?>">
                            <?= $item->getPrice() ?>
                        </div>

                        <div class="time-left"><?= $item->getTimeLeft() ?? '' ?></div>
                        </div>
                    </a>
                    <div class="sale-sell-card">
                            <p><i class="bi bi-currency-dollar"></i>Final price <?= $item->get_winning_price()?> €</p>
                            <p><i class="bi bi-trophy"></i> <?= $item->get_buyer()->getPseudo() ?></p>
                            <p><i class="bi bi-clock"></i>Closed at <?= $item->get_end_time()?></p>

                    </div>

                </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>


<footer class="bottom-nav">
    <?php if ($user): ?>
        <a href="item/browse" class="nav-item">
            <i class="bi bi-search"></i>
            <span>Browse</span>
        </a>
        <a href="item/list" class="nav-item">
            <i class="bi bi-box"></i>
            <span>My Items</span>
        </a>
        <a href="item/add" class="nav-item">
            <i class="bi bi-plus-circle"></i>
            <span>Add Offer</span>
        </a>
        <?php if ($user->is_admin()): ?>
            <a href="category/manage" class="nav-item">
                <i class="bi bi-grid"></i>
                <span>Categories</span>
            </a>
        <?php endif; ?>
        <a href="user/profile" class="nav-item active">
            <i class="bi bi-gear-fill"></i>
            <span>Profile</span>
        </a>
    <?php else: ?>
        <a href="item/browse" class="nav-item">
            <i class="bi bi-search"></i>
            <span>Browse</span>
        </a>
        <a href="user/signup" class="nav-item">
            <i class="fa-solid fa-bullhorn"></i>
            <span>Join us!</span>
        </a>
    <?php endif; ?>
</footer>
    <div class="app-time-bar">

        <div class="app-time-left">
            <span class="app-time-label">
                ⏱ <?= AppTime::get_current_datetime() ?>
            </span>
        </div>

        <div class="app-time-actions">
            <form method="post" action="time/advance">
                <button name="unit" value="hour">+1h</button>
            </form>
            <form method="post" action="time/advance">
                <button name="unit" value="day">+1d</button>
            </form>
            <form method="post" action="time/advance">
                <button name="unit" value="week">+1w</button>
            </form>
            <form method="post" action="time/reset">
                <button name="time_action" value="reset" class="reset">Reset</button>
            </form>
        </div>

    </div>
</body>
</html>
