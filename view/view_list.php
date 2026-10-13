<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <title>My List</title>
        <base href="<?= $web_root ?>"/>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link href="css/browse.css" rel="stylesheet" type="text/css"/>
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    </head>
    <body data-search-page="list">
        <div class = "main_title browse_header">My items<i class="bi bi-cart4"></i></div>
        <div class="search-bar" id="searchBar" style="display: none;">
            <i class="bi bi-search"></i>
            <input type="text" id="searchInput" placeholder="Search items.."
                   value="<?= htmlspecialchars($search_pattern ?? '') ?>">
            <select name="categories" class="category-dropdown" id="cat-filter" style="display: none">
                <option value="0">All Categories</option>
                <?php foreach ($all_cats as $cat) {?>
                    <?php if ($filter == $cat->getId()) { ?>
                        <option value="<?= $cat->getId() ?>" selected><?= $cat->getName() ?></option>
                    <?php } else { ?>
                        <option value="<?= $cat->getId() ?>"><?= $cat->getName() ?></option>
                    <?php }?>
                <?php } ?>
            </select>
        </div>
        <div id="noItemFound" style="display: none;">
            <p>No item found.</p>
        </div>
        <div class = "myitems item-section">

        <p>Active Items</p>
            <ul class = "myitemsList">
                <?php
                foreach ($active_items as $item) : ?>
                    <li class="item-card" data-item-id="<?= $item->getId() ?>">
                        <a href="item/open/<?= $item->getId() ?>/from-list"
                           data-base-url="item/open/<?= $item->getId() ?>/from-list"
                           class="item-card-link">
                        <div class="user-label">
                            <?php if ($item->isIsHighestBidder()): ?>
                                <span class="label highest"><i class="bi bi-trophy"></i> Highest Bidder</span>
                            <?php elseif ($item->isIsBidder()): ?>
                                <span class="label bidder"><i class="fa-solid fa-gavel"></i> Bidder</span>
                            <?php endif; ?>


                        </div>
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

                        <div class="item-title"><?= htmlspecialchars($item->getTitle()) ?></div>

                        <?php
                        $priceClass = $item->isIsBuyNow() ? 'price-buy' : 'price-auction';
                        $highest_current_bid = $item->get_current_highest_bid();
                        ?>
                            <div class="item_categories">
                                <?php foreach (ItemCategory::get_categories_per_item($item->getId()) as $category): ?>
                                    <span><?= htmlspecialchars($category) ?></span>
                                <?php endforeach; ?>
                            </div>
                        <div class="item-price <?=  $priceClass ?>">
                            <?= $item->getPrice() ?>
                        </div>
                        <?php if ($highest_current_bid !== null): ?>
                            <div class="current-bid">
                                Current bid <?= number_format($highest_current_bid, 2, ',', ' ') ?> €
                            </div>
                        <?php endif; ?>

                        <div class="time-left"><?= htmlspecialchars($item->getTimeLeft() ?? '') ?></div>
                        </a>
                    </li>
                    <?php endforeach; ?>

            </ul>

        </div>
        <div class = "closed_unsold_items item-section">
            <p>Closed Unsold Items</p>
            <ul class = "myitemsList">
                <?php
                foreach ($closed_unsold_items as $item) : ?>




                    <li class="item-card" data-item-id="<?= $item->getId() ?>">
                        <a href="item/open/<?= $item->getId() ?>/from-list"
                           data-base-url="item/open/<?= $item->getId() ?>/from-list"
                           class="item-card-link">

                        <div class="user-label">
                            <?php if ($item->isIsHighestBidder()): ?>
                                <span class="label highest"><i class="bi bi-trophy"></i> Highest Bidder</span>
                            <?php elseif ($item->isIsBidder()): ?>
                                <span class="label bidder"><i class="fa-solid fa-gavel"></i> Bidder</span>
                            <?php endif; ?>


                        </div>
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

                        <div class="item-title"><?= htmlspecialchars($item->getTitle()) ?></div>

                        <?php
                        $priceClass = $item->isIsBuyNow() ? 'price-buy' : 'price-auction';
                        ?>
                            <div class="item_categories">
                                <?php foreach (ItemCategory::get_categories_per_item($item->getId()) as $category): ?>
                                    <span><?= htmlspecialchars($category) ?></span>
                                <?php endforeach; ?>
                            </div>
                        <div class="item-price <?=  $priceClass ?>">
                            <?= $item->getPrice() ?>
                        </div>

                        <div class="time-left"><?= htmlspecialchars($item->getTimeLeft() ?? '') ?></div>
                        </a>
                    </li>
                    <?php endforeach; ?>

            </ul>
        </div>
        <div class = "sold_items item-section">
            <p>Sold Items</p>
            <ul class = "myitemsList">
                <?php foreach ($sold_items as $item) : ?>
                    <li class="item-card" data-item-id="<?= $item->getId() ?>">
                        <a href="item/open/<?= $item->getId() ?>/from-list"
                           data-base-url="item/open/<?= $item->getId() ?>/from-list"
                           class="item-card-link">
                        <div class="user-label">
                            <?php if ($item->isIsHighestBidder()): ?>
                                <span class="label highest"><i class="bi bi-trophy"></i> Highest Bidder</span>
                            <?php elseif ($item->isIsBidder()): ?>
                                <span class="label bidder"><i class="fa-solid fa-gavel"></i> Bidder</span>
                            <?php endif; ?>


                        </div>
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

                        <div class="item-title"><?= htmlspecialchars($item->getTitle()) ?></div>

                        <?php
                        $priceClass = $item->isIsBuyNow() ? 'price-buy' : 'price-auction';
                        ?>

                            <div class="item_categories">
                                <?php foreach (ItemCategory::get_categories_per_item($item->getId()) as $category): ?>
                                    <span><?= htmlspecialchars($category) ?></span>
                                <?php endforeach; ?>
                            </div>
                        <div class="item-price <?=  $priceClass ?>">
                            <?= $item->getPrice() ?>
                        </div>

                        <div class="time-left"><?= htmlspecialchars($item->getTimeLeft() ?? '') ?></div>
                        </a>
                    </li>
                    <?php endforeach; ?>

            </ul>
        </div>
    </div>
<footer class="bottom-nav">
        <a href="item/browse" class="nav-item">
            <i class="bi bi-search"></i>
            <span>Browse</span>
        </a>
        <a href="item/list" class="nav-item active">
            <i class="bi bi-box-fill"></i>
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
        <a href="user/profile" class="nav-item">
            <i class="bi bi-gear"></i>
            <span>Profile</span>
        </a>

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
    <script src="js/search.js"></script>
    </body>
</html>
