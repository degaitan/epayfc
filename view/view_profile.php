<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Profile</title>
    <base href="<?= $web_root ?>"/>
    <link rel="stylesheet" href="css/profile.css" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body class="profile-page">

<header class="top-bar">
    <h1>Profile <span class="cart"><i class="bi bi-cart4"></i></span></h1>
</header>

<main class="container">

    <section class="card profile-card">
        <img src="<?php echo (($user->getPicturePath() == null) ? "no_profile_picture.png" : $user->getPicturePath()) ?>"
             alt="profile-pic" class="profile-pic" />
        <h2><?php echo $user->getFullName() ?></h2>
        <p class="username">@<?php echo $user->getPseudo() ?></p>
        <p class="email"><?php echo $user->getEmail() ?></p>
    </section>

    <section class="section">
        <h3 class="section-title">My Activities</h3>

        <a href="item/sales" class="list-item">
            <i class="bi bi-receipt icon"></i>
            <div class="text">
                <strong>Sales</strong>
                <span>Items you have sold</span>
            </div>
        </a>

        <a href="item/purchases" class="list-item">
            <i class="bi bi-cart-check icon"></i>
            <div class="text">
                <strong>Purchases</strong>
                <span>Items you have purchased</span>
            </div>
        </a>
    </section>

    <section class="section">
        <h3 class="section-title">Account Settings</h3>

        <a href="user/edit_profile" class="list-item">
            <i class="bi bi-person icon"></i>
            <div class="text">
                <strong>Edit Profile</strong>
                <span>Update your personal information</span>
            </div>
        </a>

        <a href="user/change_password" class="list-item">
            <i class="bi bi-lock icon"></i>
            <div class="text">
                <strong>Change Password</strong>
                <span>Update your account security</span>
            </div>
        </a>

        <a href="user/manage_profile_picture" class="list-item">
            <i class="bi bi-image icon"></i>
            <div class="text">
                <strong>Profile Picture</strong>
                <span>Upload or change your profile picture</span>
            </div>
        </a>

        <a href="user/signout" class="list-item logout">
            <i class="bi bi-box-arrow-right icon"></i>
            <div class="text">
                <strong>Logout</strong>
                <span>Sign out of your account</span>
            </div>
        </a>
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
