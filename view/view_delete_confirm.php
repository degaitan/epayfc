<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Delete "<?= $item->getTitle()?>"</title>
    <base href="<?= $web_root ?>"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="css/delete-confirm.css" rel="stylesheet" type="text/css"/>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
</head>
<body>

<div class="confirm-container">
    <div class="confirm-card">
        <div class="icon-wrapper">
            <i class="bi bi-trash3"></i>
        </div>

        <h2 class="confirm-title">Are you sure?</h2>

        <hr class="divider">

        <p class="confirm-message">
            Do you really want to delete item "<strong><?= $item->getTitle() ?></strong>"
            by <?= $owner->getFullName() ?> and all of its dependencies ?
        </p>

        <p class="confirm-warning">
            This process cannot be undone.
        </p>

        <div class="confirm-actions">
            <a href="item/open/<?= $item->getId() ?>" class="btn btn-cancel">Cancel</a>

            <form method="post" action="item/delete_all/<?= $item->getId() ?>"> <input type="hidden" name="id" value="<?= $item->getId() ?>">
                <button type="submit" class="btn btn-delete">Delete</button>
            </form>
        </div>
    </div>
</div>

</body>
</html>