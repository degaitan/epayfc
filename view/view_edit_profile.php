<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php if ($type == "edit") echo "Edit profile"; else echo "Change Password"?></title>
    <base href="<?= $web_root ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="css/form_pages.css">
</head>

<body class="form-page">

<form method="post" action="<?php if ($type == "edit") echo "user/edit_profile"; else echo "user/change_password"?>" class="edit-form">

    <header class="top-bar">
        <a href="user/profile" class="back-btn">
            <i class="bi bi-arrow-left"></i>
        </a>

        <h1><?php if ($type == "edit") echo "Edit profile "; else echo "Change Password "?><i class="bi bi-cart4"></i></h1>

        <button type="submit" class="save-btn">
            <i class="bi bi-floppy"></i>
        </button>
    </header>

    <main class="container">

        <section class="card">
            <div class="card-header"><?php if ($type == "edit") echo "Personal Information"; else echo "Current Password"?></div>
            <div class="card-content">

                <?php if ($type == "edit"): ?>
                <label class="form-label">Full Name *</label>
                <input type="text" name="full_name" value="<?php echo $user->getFullName()?>"
                       class="<?= isset($errors['full_name']) ? 'input-error' : '' ?>" required>
                    <?php if (isset($errors['full_name'])): ?>
                        <div class="form-error">
                            <i class="bi bi-exclamation-circle"></i>
                            <?= $errors['full_name'] ?>
                        </div>
                    <?php endif; ?>
                <div class="form-help">
                    Your complete name as it should appear on your profile
                </div>

                <label class="form-label">Username *</label>
                <input type="text" name="pseudo" value="<?php echo $user->getPseudo()?>"
                       class="<?= isset($errors['pseudo']) ? 'input-error' : '' ?>" required>
                    <?php if (isset($errors['pseudo'])): ?>
                        <div class="form-error">
                            <i class="bi bi-exclamation-circle"></i>
                            <?= $errors['pseudo'] ?>
                        </div>
                    <?php endif; ?>
                <div class="form-help">
                    This will be your public display name on the platform
                </div>
                <?php endif;?>

                <?php if ($type == "pass"): ?>
                <label class="form-label">Current Password *</label>
                <input type="password" name="password" placeholder="Enter your current password"
                       class="<?= isset($errors['current']) ? 'input-error' : '' ?>" required>
                    <?php if (isset($errors['current'])): ?>
                        <div class="form-error">
                            <i class="bi bi-exclamation-circle"></i>
                            <?= $errors['current'] ?>
                        </div>
                    <?php endif; ?>
                <div class="form-help">
                    Enter your current password to verify your identity
                </div>
                <?php endif;?>

            </div>
        </section>

        <section class="card">
            <div class="card-header"><?php if ($type == "edit") echo "Contact & Payment"; else echo "New Password"?></div>
            <div class="card-content">

                <?php if ($type == "edit"): ?>
                <label class="form-label">Email Address *</label>
                <input type="email" name="email" value="<?php echo $user->getEmail()?>"
                       class="<?= isset($errors['email']) ? 'input-error' : '' ?>" required>
                    <?php if (isset($errors['email'])): ?>
                        <div class="form-error">
                            <i class="bi bi-exclamation-circle"></i>
                            <?= $errors['email'] ?>
                        </div>
                    <?php endif; ?>
                <div class="form-help">
                    We'll use this email to send you notifications about your sales and purchases
                </div>

                <label class="form-label">
                    IBAN <span class="optional">(Optional)</span>
                </label>
                <input type="text" name="iban" value="<?php echo $user->getIban() ?>"
                       class="<?= isset($errors['iban']) ? 'input-error' : '' ?>" placeholder="BE99 9999 9999 9999">
                    <?php if (isset($errors['iban'])): ?>
                        <div class="form-error">
                            <i class="bi bi-exclamation-circle"></i>
                            <?= $errors['iban']?>
                        </div>
                    <?php endif; ?>
                <div class="form-help">
                    For receiving payments from your sales. Leave empty if you don't want to receive payments directly.
                </div>
                <?php endif; ?>

                <?php if ($type == "pass"): ?>
                <label class="form-label">New Password *</label>
                <input type="password" name="new_password" placeholder="Enter your new password"
                       class="<?= isset($errors['new_password']) ? 'input-error' : '' ?>" required>
                    <?php if (isset($errors['new_password'])): ?>
                        <div class="form-error">
                            <i class="bi bi-exclamation-circle"></i>
                            <?= $errors['new_password'] ?>
                        </div>
                    <?php endif; ?>
                <div class="form-help">
                    Password must be 8-16 characters with uppercase, number and punctuation
                </div>

                <label class="form-label">Confirm New Password *</label>
                <input type="password" name="confirm" placeholder="Confirm your new password" required>
                <div class="form-help">
                    Re-enter your new password to confirm it matches
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
