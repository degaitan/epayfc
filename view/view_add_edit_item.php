<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title id="page-type" data-type="<?=$type?>"><?php if ($type == "edit") echo "Edit Item"; else echo "Add Item"?></title>
    <base href="<?= $web_root ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/form_pages.css">
</head>

<body class="form-page">
<?php
if ($type === "edit") {
    $back_url = "item/open/" . $item->getId() . "/" . ($origin ?? 'from-browse');
    if (!empty($encoded_state)) {
        $back_url .= "/" . $encoded_state;
    }
} else {
    $back_url = "item/browse";
}
?>
<form method="post" action="<?php
if ($type == "add") echo "item/add";
    else {
        $action = "item/edit/" . $item->getId() . "/" . ($origin ?? 'from-browse');
        if (!empty($encoded_state)) {
            $action .= "/" . $encoded_state;
        }
        echo $action;
    }?>"
      class="edit-form">
    <header class="top-bar">
        <a href="<?= $back_url ?>" class="back-btn">
            <i class="bi bi-arrow-left"></i>
        </a>

        <h1><?php if ($type == "add") echo "Add Item "; else echo "Edit Item "?><i class="bi bi-cart4"></i></h1>

        <button type="submit" class="save-btn">
            <i class="bi bi-floppy"></i>
        </button>
    </header>
    <main class="container">

        <section class="card">
            <div class="card-header">Basic Information</div>
            <div class="card-content">
                <label class="form-label">Item Title *</label>
                <input type="text" name="title" value="<?= $type == "edit" ? $item->getTitle() : ''?>" placeholder="<?= $type == "add" ? "Ex: iPhone 13 Pro Max 256GB" : ''?>" required>
                <div class="invalid-feedback js-error-title"></div>
                <?php if (isset($errors['title'])): ?>
                    <div class="form-error"><i class="bi bi-exclamation-circle"></i> <?= $errors['title'] ?></div>
                <?php endif; ?>

                <label class="form-label">Description</label>
                <textarea class="dark-textarea" name="description" placeholder="<?= $type == "add" ? "Describe your item in detail..." : ''?>"><?php if ($type == "edit") echo $item->getDescription()?></textarea>
                <div class="invalid-feedback js-error-description"></div>
                <div style="margin-top: 16px;">
                    <label class="form-label">Sale Duration (days) *</label>
                    <input type="number" name="duration" value="<?= $type == "edit" ? $item->getDuration() : 7?>" required>
                    <div class="invalid-feedback js-error-duration"></div>
                    <?php if (isset($errors['duration'])): ?>
                        <div class="form-error"><i class="bi bi-exclamation-circle"></i> <?= $errors['duration'] ?></div>
                    <?php endif; ?>
                </div>
            </div>
        </section>

        <section class="card">
            <div class="card-header">Sale Type</div>
            <div class="card-content">

                <div class="sale-option auction">
                    <div class="sale-option-header">
                        <i class="bi bi-hammer"></i> Option 1: Auction
                    </div>
                    <div class="sale-option-body">
                        <label class="form-label">Starting Bid</label>
                        <div class="input-group">
                            <input type="number" step="0.01" name="starting_bid" placeholder="e.g., 50.00" value="<?= $type == "edit" && $item->getStartingBid() !== null && $item->getStartingBid() > 0 ? $item->getStartingBid() : '' ?>">
                            <div class="invalid-feedback js-error-starting_bid"></div>
                            <span class="currency-symbol">€</span>
                        </div>
                        <?php if (isset($errors['starting_bid'])): ?>
                            <div class="form-error">
                                <i class="bi bi-exclamation-circle"></i>
                                <?= $errors['starting_bid'] ?>
                            </div>
                        <?php endif; ?>
                        <label class="form-label">Instant Purchase Price <span class="optional">(optional)</span></label>
                        <div class="input-group">
                            <input type="number" step="0.01" name="buy_now_price" value="<?= $type == "edit" && $item->getBuyNowPrice() !== null && $item->getBuyNowPrice() > 0 && ($item->getStartingBid() !== null && $item->getStartingBid() > 0) ? $item->getBuyNowPrice() : ''?>" placeholder="e.g., 200.00">
                            <div class="invalid-feedback js-error-buy_now_price"></div>
                            <span class="currency-symbol">€</span>
                            <?php if (isset($errors['buy_now_price'])): ?>
                                <div class="form-error">
                                    <i class="bi bi-exclamation-circle"></i>
                                    <?= $errors['buy_now_price']?>
                                </div>
                            <?php endif;?>
                        </div>
                    </div>
                </div>

                <div class="sale-option direct">
                    <div class="sale-option-header">
                        <i class="bi bi-cart2"></i> Option 2: Direct Sale
                    </div>
                    <div class="sale-option-body">
                        <label class="form-label">Sale Price</label>
                        <div class="input-group">
                            <input type="number" step="0.01" name="direct_sale" value="<?= $type == "edit" && ($item->getBuyNowPrice() !== null && $item->getStartingBid() !== null && $item->getStartingBid() == 0) ? $item->getBuyNowPrice() : ''?>" placeholder="e.g., 150.00">
                            <div class="invalid-feedback js-error-direct_sale"></div>
                            <span class="currency-symbol">€</span>
                        </div>
                        <?php if (isset($errors['direct_sale'])): ?>
                            <div class="form-error">
                                <?php if (isset($errors['direct_sale'])): ?>
                                    <i class="bi bi-exclamation-circle"></i>
                                    <?= $errors['direct_sale'] ?>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

            </div>
        </section>

        <section class="card">
            <div class="card-header">Categories</div>
            <div class="card-content">
                <span class="optional">You can select up to 3 categories.<br><br></span>
                <div class="invalid-feedback js-error-categories"></div>
                <?php if (isset($errors['categories'])): ?>
                    <div class="form-error">
                        <?php if (isset($errors['categories'])): ?>
                            <i class="bi bi-exclamation-circle"></i>
                            <?= $errors['categories'] ?>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
                    <?php foreach ($categories as $cat): ?>
                        <div class="form-checklist">
                            <?php if ($type == "edit" && $item->has_category($cat->getId())): ?>
                                <input type="checkbox" class="form-checkbox" id=<?php echo $cat->getPriority()?> name="categories[]" value=<?php echo $cat->getId() ?> checked>
                            <?php else: ?>
                                <input type="checkbox" class="form-checkbox" id=<?php echo $cat->getPriority()?> name="categories[]" value=<?php echo $cat->getId() ?>>
                            <?php endif; ?>
                            <label class="checkbox-label" for=<?php echo $cat->getPriority() ?>><?php echo $cat->getName() ?></label>
                        </div>
                    <?php endforeach; ?>

            </div>
        </section>
    </main>
</form>

<footer class="bottom-nav">
    <a href="item/browse" class="nav-item">
        <i class="bi bi-search"></i>
        <span>Browse</span>
    </a>

    <a href="item/list" class="nav-item">
        <i class="bi bi-box"></i>
        <span>My Items</span>
    </a>

    <a href="item/add" class="nav-item active">
        <i class="bi bi-plus-circle-fill"></i>
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

<div class="modal fade" id="leaveModal" tabindex="-1" aria-labelledby="leaveModalLabel" aria-hidden="true" data-bs-theme="dark">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="background-color: #1a1f26; border: 1px solid #334155;">
            <div class="modal-header border-bottom-0">
                <h5 class="modal-title text-light" id="leaveModalLabel">Unsaved changes</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body border-bottom-0 text-light">
                You have unsaved changes. Leave anyway?
            </div>
            <div class="modal-footer border-top-0">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" style="background-color: #4b5563; border: none;">Cancel</button>
                <button type="button" class="btn btn-primary" id="confirmLeaveBtn" style="background-color: #3b82f6; border: none;">Leave</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="categoryModal" tabindex="-1" aria-labelledby="categoryModalLabel" aria-hidden="true" data-bs-theme="dark">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="background-color: #1a1f26; border: 1px solid #334155;">
            <div class="modal-header border-bottom-0">
                <h5 class="modal-title text-light" id="categoryModalLabel">Category limit</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body border-bottom-0 text-light">
                You can select at most 3 categories.
            </div>
            <div class="modal-footer border-top-0">
                <button type="button" class="btn btn-primary" data-bs-dismiss="modal" style="background-color: #3b82f6; border: none;">OK</button>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script>
    // Pass PHP variables to JS safely
    const currentItemId = "<?= $type == 'edit' ? $item->getId() : '' ?>";
</script>
<script src="js/validation.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="js/add_edit_modal.js"></script>
<script src="js/category_limit_modal.js"></script>
</body>
</html>