<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Catgeories</title>
    <base href="<?= $web_root ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="css/form_pages.css">
</head>

<body class="form-page">
    <header class="top-bar">
        <a href="" class="back-btn">
            <i class="bi bi-arrow-left"></i>
        </a>
        <h1>Categories <span class="cart"><i class="bi bi-cart4"></i></span></h1>
        <a href="" class="invisi-btn">
            <i class="bi bi-floppy"></i>
        </a>
    </header>

    <main class="container">
        <section class="card">

            <?php foreach ($categories as $cat): ?>
                <form action="" method="post">
                    <div class="category-box">
                        <input type="text" name="cat_name" value="<?= $cat->getName()?>" class="category-input">
                        <button type="submit" name="up" value="<?= $cat->getPriority()?>"
                                class="action-btn category-btn" title="up" <?= $cat->getPriority() == 1 ? "disabled" : "" ?> >Up</button>
                        <button type="submit" name="down" value="<?= $cat->getPriority()?>"
                                class="action-btn category-btn" title="Down" <?= $cat->getPriority() == sizeof($categories) ? "disabled" : ""?> >Down</button>
                        <button type="submit" name="save" value="<?= $cat->getId()?>"
                                class="action-btn btn-save category-btn" title="Save">Save</button>
                        <button type="submit" name="delete" value="<?= $cat->getId()?>"
                                class="action-btn btn-delete category-btn" title="Delete" <?= $cat->has_items() ? "disabled" : ""?> >Delete</button>
                    </div>
                </form>
                <?php if (isset($errors[$cat->getId()])): ?>
                    <div class="form-error">
                        <i class="bi bi-exclamation-circle"></i>
                        <?= $errors[$cat->getId()] ?>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
            <form action="" method="post">
                <div class="category-box">
                    <input type="text" name="new_cat" value="Category name" class="new-cat-input">
                    <button type="submit" name="add" title="add" class="action-btn category-btn new-cat-btn">+</button>
                </div>
            </form>
            <?php if (isset($errors['add'])): ?>
            <div class="form-error">
                <i class="bi bi-exclamation-circle"></i>
                <?= $errors['add'] ?>
                <?php endif; ?>
        </section>
    </main>

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
        <a href="category/manage" class="nav-item active">
            <i class="bi bi-grid-fill"></i>
            <span>Categories</span>
        </a>
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
</body>
</html>