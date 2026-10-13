<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Manage profile picture</title>
    <base href="<?= $web_root ?>"/>
    <link rel="stylesheet" href="css/profile-picture.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body>

<div class="page">

    <header class="top-bar">
        <a href="user/profile" class="back-btn">
            <i class="bi bi-arrow-left"></i>
        </a>
        <h1>Manage Profile Picture <i class="bi bi-cart4"></i></h1>
    </header>
    <section class="card">
        <header class="card-header">Current Profile Picture</header>

        <div class="card-content">
            <div class="profile-center">
                <img
                        src="<?php echo $user->getPicturePath() ?? "no_profile_picture.png" ?>"
                        alt="Profile picture"
                        class="profile-avatar"
                >

                <div class="profile-text">
                    <div class="profile-name"><?php echo $user->getFullName() ?></div>
                    <div class="profile-username">@<?php echo $user->getPseudo() ?></div>
                </div>
            </div>
        </div>
    </section>

    <section class="card">
        <header class="card-header">Upload New Picture</header>

        <div class="card-content">
            <?php if (!empty($error)): ?>
                <div class="error-message">
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="user/upload_pp" enctype="multipart/form-data">

                <label class="form-label">
                    Select Image <span class="required">*</span>
                </label>

                <input
                        type="file"
                        name="profile_picture"
                        accept=".jpg,.jpeg,.png,.gif,.webp"
                        required
                >

                <p class="form-help">
                    Supported formats: JPG, JPEG, PNG, GIF, WebP
                </p>

                <button type="submit" class="btn btn-primary">Upload Picture</button>
            </form>
        </div>
    </section>

    <?php if ($user->getPicturePath() != null): ?>
        <section class="card">
            <header class="card-header">Remove Current Picture</header>

            <div class="card-content">
                <div class="warning">
                    This will permanently delete your current profile picture. You can upload a new one any time.
                </div>

                <form method="POST" action="user/delete_profile_picture">
                    <button type = "submit" class="btn btn-danger">Delete Picture</button>
                </form>
            </div>
        </section>
    <?php endif; ?>

</div>
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
    <a href="user/profile" class="nav-item active">
        <i class="bi bi-gear-fill"></i>
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
