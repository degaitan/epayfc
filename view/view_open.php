<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <title><?= htmlspecialchars($item->getTitle()) ?></title>
        <base href="<?= $web_root ?>"/>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
        <link href="css/open_item.css" rel="stylesheet" type="text/css"/>
    </head>
    <body>
    <?php
    $back_url = match ($origin) {
        'from-browse'    => 'item/browse',
        'from-list'      => 'item/list',
        'from-sales'     => 'item/sales',
        'from-purchases' => 'item/purchases',
        default          => 'item/browse',  // fallback for null or unknown
    };
    //le mtoif de recherche
    if ($encoded_state) {
        $back_url .= '/' . $encoded_state;
    }
    ?>
    <a href="<?= $back_url ?>" class="back-btn">
        <i class="bi bi-arrow-left"></i>
    </a>
    <div class = "main_title browse_header">Item Open<i class="bi bi-cart4"></i></div>

    <main class = "item-info">
        <div class="main-content">
        <section class = "item-card">
            <div id="item-carousel" data-item-id="<?= $item->getId() ?>">
                <div id="carousel-nojs">
            <?php if ($main_photo): ?>
                <div class="main-image">
                    <img src="<?= htmlspecialchars($main_photo) ?>"
                         alt="Main photo"
                         class="big-img" />
                </div>
            <?php endif; ?>
                </div>
            </div>

            <div class="item-title-with-label">
                <span class="item-title"><?= $item->getTitle(); ?></span>
                <?php if ($item->isIsAuction()): ?>
                    <span class="item-label">Auction</span>
                <?php endif; ?>
            </div>

            <div class = "item_description">
                <?= $item->getDescription(); ?>

                <?php if (!empty($categories)):?>
                    <p>
                        <strong>
                            <br>
                            Categories:</strong>
                        <ul>
                            <?php foreach ($categories as $cat): ?>
                                <li><?= $cat['name'] ?></li>
                            <?php endforeach;?>
                        </ul>
                    </p>
                <?php endif;?>
            </div>

            <div class = "time">
                Start:
                <div class="start">
                    <?= $item->getCreatedAt() ?>
                </div>
                <div class="end">
                    Ends:
                    <?= $item->get_end_date()?>
                </div>
            </div>

        </section>



        <section class = "extra_images" id="extra-images-nojs">
                    <p>Additional images</p>
                    <?php if(empty($pictures)) : ?>
                        <p>No images yet.</p>
                    <?php else: ?>
                        <?php foreach ($pictures as $picture): ?>

                            <form method="POST" action="item/open/<?= $item->getId() ?>" class="miniature-form">
                                <input type="hidden" name="selected_picture" value="<?= htmlspecialchars(basename($picture)) ?>">
                                <button type="submit" class="miniature-button">
                                    <img src="<?= htmlspecialchars($picture) ?>"
                                         alt="Miniature"
                                         class="miniature" />
                                </button>
                            </form>

                        <?php endforeach; ?>

                    <?php if (count($pictures) === 1): ?>
                    <h3>No additional images</h3>

                    <?php endif; ?>
            <?php endif; ?>


        <section class = "bid-history">
            <h3>Bid History
                <span class="bid-count"><?= count($bids) ?> entries
                </span>
                </h3>

            <ul>
                <?php foreach ($bids as $bid): ?>
                    <li>
                        <span class="bid-user"><?= $bid['pseudo'] ?></span>
                        <span class="bid-date"><?= $bid['created_at'] ?></span>
                        <span class="bid-amount"><?= $bid['amount'] ?>€</span>
                    </li>
                <?php endforeach; ?>
            </ul>
        </section>
        </div>


        <div class="pricing">

            <section class="pricing-container">
                <?php
                $current_bid = $item->getPrice();
                if (!empty($bids)) {
                    $current_bid = max(array_column($bids, 'amount'));
                } else {
                    $current_bid = $item->getStartingBid();
                }
                $pricing_type = $item->get_pricing_type($user);
                $buyer = $item->get_buyer();
                ?>

                <div class="pricing-header">
                    <div class="pricing-title">Pricing</div>
                    <div class="pricing-current">
                        <span class="current-label">Current Bid</span>
                        <span class="current-amount"><?= number_format($current_bid, 2, ',', ' ') ?> €</span>
                    </div>
                </div>

                <div class="buy-now-section">
                    <div class="buy-now-label">Buy Now</div>
                    <div class="buy-now-price"><?= number_format($item->getBuyNowPrice(), 2, ',', ' ') ?> €</div>
                </div>

                <?php if ($pricing_type === 'purchased'): ?>

                    <?php if ($user->getId() === $buyer->getId()): ?>
                        <div class="message">
                            Congratulations! You purchased this item for <?= number_format($item->get_winning_price(), 2, ',', ' ') ?> €
                        </div>
                    <?php else: ?>
                        <div class="message">
                            <?= htmlspecialchars($buyer->getPseudo()) ?> won this item for <?= number_format($item->get_winning_price(), 2, ',', ' ') ?> €
                        </div>
                    <?php endif; ?>

                <?php elseif ($pricing_type === 'not sold'): ?>
                    <div class="message">
                        This listing ended without a buyer.
                    </div>

                <?php elseif ($pricing_type === 'owner' || $pricing_type === 'owner ended'): ?>
                    <div class="message">
                        You cannot bid on your own listing.
                    </div>

                <?php elseif ($pricing_type === 'starting' || $pricing_type === 'bid buy'): ?>
                    <?php $min_bid = $item->get_min_bid_amount(); ?>
                    <form method="POST" action="item/open/<?= $item->getId() ?>/<?= $origin ?? 'from-browse' ?>/<?= $encoded_state ?? '' ?>" class="bid-input-form">
                        <input type="hidden" name="item_id" value="<?= $item->getId() ?>">
                        <div class="bid-input-container">
                            <span class="currency-symbol">€</span>
                            <input type="number" name="amount" min="<?= $min_bid ?>"
                                   class="bid-input-field" value="<?= number_format($min_bid, 2, '.', '') ?>" required>
                        </div>
                        <button type="submit" name="place_bid" class="place-bid-btn">Place Bid</button>
                        <?php if ($bidSuccess): ?>
                            <div class="popup success">Bid placed!</div>
                        <?php endif; ?>
                    </form>
                <?php endif; ?>
                <?php if (($pricing_type === 'bid buy' || $pricing_type === 'buy now only') && $item->getBuyNowPrice() > 0 && $item->is_available()): ?>
                    <form method="POST" action="item/open/<?= $item->getId() ?>/<?= $origin ?? 'from-browse' ?>/<?= $encoded_state ?? '' ?>">
                        <input type="hidden" name="item_id" value="<?= $item->getId() ?>">
                        <button type="submit" name="buy_now" class="buy-now-button">
                            Buy Now at <?= number_format($item->getBuyNowPrice(), 2, ',', ' ') ?> €
                        </button>
                        <?php if ($buySuccess): ?>
                            <div class="popup success">Congrats for your new purchase!</div>
                        <?php endif; ?>
                    </form>
                <?php endif; ?>

            </section>

            <section class="seller-info">
                <h3>Seller Information</h3>
                <p><?= $creator ?></p>
            </section>

            <?php
            if($creator===$user->getPseudo()) { ?>
                <section class="manage-panel">
                    <?php
                    if($can_edit){
                    ?>
                    <p>Manage Your Item</p>
                        <a href="item/edit/<?= $item->getId() ?>/<?= $origin ?>/<?= $encoded_state ?? '' ?>" class="edit-item-button">
                            <i class="bi bi-pencil"></i> Edit Item
                        </a>
                    <form method="POST" action="item/manage_images/<?= $item->getId() ?>/<?= $origin?>/<?= $encoded_state ?? '' ?>">
                        <button type="submit" name="manage_images" class="manage-images-button">
                            <i class="bi bi-image"></i> Manage Images
                        </button>
                    </form>
                    <form method="POST" action="item/delete/<?= $item->getId() ?>/<?= $origin?>/<?= $encoded_state ?? '' ?>">
                        <button type="submit" name="delete_item" class="delete-button">
                            <i class="bi bi-trash"></i> Delete Item
                        </button>
                    </form>

                    <?php } else {?>
                        You cannot edit this item due to existing bids or the listing has ended.
                    <?php } ?>
                </section>
            <?php
            }
            ?>




        </div>


    </main>


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
            <a href="user/profile" class="nav-item">
                <i class="bi bi-gear"></i>
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
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/open_item.js"></script>
</body>
</html>