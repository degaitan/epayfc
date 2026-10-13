<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= $item->getTitle()?></title>
    <base href="<?= $web_root ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="css/form_pages.css">
</head>

<body class="form-page">
<?php
$back_url = "item/open/" . $item->getId() . "/" . ($origin ?? 'from-browse');
if (!empty($encoded_state)) {
    $back_url .= "/" . $encoded_state;
}
?>
<form method="post" action="" class="edit-form" enctype="multipart/form-data">
    <header class="top-bar">
        <a href="<?= $back_url ?>" class="back-btn">
            <i class="bi bi-arrow-left"></i>
        </a>
        <h1>Manage Images <i class="bi bi-cart4"></i></h1>
        <span class="back-btn" style="visibility: hidden;">
    </header>

    <main class="container">
        <h1>Manage Images for "<?= $item->getTitle()?>"</h1>
        <section class="card">
            <div class="card-header">Add New images</div>
            <div class="card-content">
                <label class="form-label">Select Images</label>
                <input type="file" name="new_pictures[]" accept=".jpg,.jpeg,.png,.gif,.webp" multiple>
                <div class="form-help">
                    You can select multiple images (JPG, PNG, GIF, WEBP). Images will be added to the end of your current list. (Max Size = 5MB)
                </div>
                <button type="submit" name="action" value="upload" class="upload-btn upload-btn-primary">Upload Images</button>
            </div>
        </section>

        <section class="card">
            <div class="card-header">Current Images</div>
            <div class="card-content">
                <p id="drag-drop-hint" class="form-help" style="display: none;">
                    <i class="bi bi-arrows-move"></i> Drag and drop images to reorder them.
                </p>
                <?php if (empty($pictures)): ?>
                    <p class="form-help">No images uploaded yet.</p>
                <?php else: ?>
                    <div class="image-grid">
                        <?php
                        $count = count($pictures);
                        foreach ($pictures as $pic):
                            ?>
                            <div class="image-card" data-path="<?= $pic->getPicturePath() ?>">
                                <div class="img-wrapper">
                                    <img src="<?= $pic->getPicturePath() ?>" alt="Item image">
                                </div>
                                <div class="image-actions">
                                    <button type="submit" name="move_left" value="<?= $pic->getPriority() ?>"
                                            class="action-btn" <?= $pic->getPriority() == 1 ? 'disabled' : '' ?>
                                            title="Move Left">
                                        <i class="bi bi-arrow-left"></i>
                                    </button>

                                    <button type="submit" name="move_right" value="<?= $pic->getPriority() ?>"
                                            class="action-btn" <?= $pic->getPriority() == $count ? 'disabled' : '' ?>
                                            title="Move Right">
                                        <i class="bi bi-arrow-right"></i>
                                    </button>

                                    <button type="submit" name="delete" value="<?= $pic->getPriority() ?>"
                                            class="action-btn btn-delete"
                                            title="Delete">
                                        <i class="bi bi-x-lg"></i>
                                    </button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
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
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui-touch-punch/0.2.3/jquery.ui.touch-punch.min.js"></script>

<script src="js/drag_drop.js"></script>
<script>
    const currentItemId = <?= $item->getId() ?>;
</script>
</body>
</html>