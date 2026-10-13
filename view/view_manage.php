<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <title>Manage Categories</title>
        <base href="<?= $web_root ?>"/>
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
        <link href="css/browse.css" rel="stylesheet" type="text/css"/>
    </head>
    <body>
    <div class = "main_title browse_header">Categories<i class="bi bi-cart4"></i></div>
    <?php if ($user->isAdmin()) : ?>
    <div class = "category-list">
        <?php if(!empty($categories)) : ?>
            <?php foreach ($categories as $index => $category): ?>
                <form method="post" action="category/manage" class = "category-row" data-id="<?= $category->getId() ?>">
                    <span id="drag-drop-hint" class="drag-handle js-only">
                        <i class="bi bi-grip-vertical"></i>
                    </span>
                    <input type="hidden" name="id" value="<?= $category->getId() ?>">
                    <span class="category-name js-only" data-count="<?= $category->getItemCount() ?>"><?= $category->getName() ?> (<?= $category->getItemCount() ?>)</span>
                    <input type="text" name="name" value="<?= $category->getName() ?>">
                    <?php if (isset($errors[$category->getId()])): ?>
                        <div class="form-error">
                            <?php foreach ($errors[$category->getId()] as $error): ?>
                                <?= $error ?>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                    <button type="button" class="js-only" name="edit">Edit</button>
                    <button type="submit" class="nojs-only" name="up" value="1" <?= $index == 0 ? 'disabled' : ''?>>Up</button>
                    <button type="submit" class="nojs-only" name="down" value="1" <?= $index == count($categories) - 1 ? 'disabled' : ''?>>Down</button>
                    <button type="submit" class="nojs-only" name="rename" value="1">Save</button>
                    <button type="submit" name="ask_delete" data-id="<?= $category->getId() ?>" value="1" <?= $category->getItemCount() > 0 ? 'disabled' : ''?>>Delete</button>
                    <span class="form-error"></span>

                </form>
            <?php endforeach; ?>
            <?php endif; ?>

        <form method="post" action="category/manage" class="category-new nojs-only">
            <input type="text" name="name" value="<?= htmlspecialchars($new_name ?? '') ?>" placeholder="New category name">
                        <?php if (isset($errors['new'])): ?>
                            <div class="form-error">
                                <?php foreach ($errors['new'] as $error): ?>
                                    <?= htmlspecialchars($error) ?>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                        <button type="submit" name="add" value="1">+</button>
                    </form>
        <div class="js-only">
            <div id="addCategoryForm" class="hidden">
                <input type="text" id="newCategoryName" placeholder="New category name">
                <button id="cancelAddBtn">Cancel</button>
                <span class="add-error"></span>
            </div>
            <button type="button" id="showAddBtn" class="js-only">+</button>
        </div>

    </div>
    <?php endif; ?>

    <!-- Bottom navigation -->
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

            <?php if ($user->isAdmin()): ?>

                <a href="category/manage" class="nav-item active">
                    <i class="bi bi-gem"></i>
                    <span>Categories</span>
                </a>
            <?php endif; ?>

            <a href="user/profile" class="nav-item">
                <i class="bi bi-gear"></i>
                <span>Profile</span>
            </a>
        <?php else: ?>
            <a href="item/browse" class="nav-item active">
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

    <div class="modal fade" id="categoryDeleteModal" tabindex="-1" aria-labelledby="categoryDeleteModalLabel" aria-hidden="true" data-bs-theme="dark">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content" style="background-color: #1a1f26; border: 1px solid #334155;">
                <div class="modal-header border-bottom-0">
                    <h5 class="modal-title text-light" id="categoryDeleteModalLabel">Supprimer la catégorie</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body border-bottom-0 text-light">
                    Voulez-vous vraiment supprimer cette catégorie ?
                </div>
                <div class="modal-footer border-top-0">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" style="background-color: #4b5563; border: none;">Cancel</button>
                    <button type="button" class="btn btn-primary" id="confirmDeleteBtn" style="background-color: #3b82f6; border: none;">Delete</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/manage_categories.js"></script>
    </body>
</html>