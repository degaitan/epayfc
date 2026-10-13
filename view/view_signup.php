<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <title>Sign up</title>
        <base href="<?= $web_root ?>"/>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link href="css/styles.css" rel="stylesheet" type="text/css"/>
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">

    </head>
    <body>
    <form class = "login_box signup_box" action="user/signup" method="post">
        <div class="form_title">
            <h2>Sign Up</h2>
        </div>
        <div class="input_fields">
            <div class="input_box">
                <label><i class="bi bi-envelope"></i></label>
                <input type="email" name="email" placeholder="Email" value="<?= htmlspecialchars($email ?? '') ?>">
            </div>
            <div class="input_box">
                <label for="full_name"><i class="bi bi-person"></i></label>
                <input type="text" name="full_name" placeholder="Full Name" value="<?= htmlspecialchars($full_name ?? '') ?>">
            </div>
            <div class="input_box">
                <label><i class="bi bi-emoji-smile"></i></label>
                <input type="text" name="pseudo" placeholder="Pseudo" value="<?= htmlspecialchars($pseudo ?? '') ?>">
            </div>
            <div class="input_box">
                <label><i class="bi bi-key"></i></label>
                <input type="password" name="password" placeholder="Password" value="<?= htmlspecialchars($password ?? '') ?>">
            </div>
            <div class="input_box">
                <label><i class="bi bi-key"></i></label>
                <input type="password" name="password_confirm" placeholder="Confirm your password" value="<?= htmlspecialchars($confirm_password ?? '') ?>">
            </div>
            <div>
                <div class="buttons signup_buttons">
                    <button type="submit" name="signup">Sign Up</button>

                    <a href="user/login" class="guest_button">Cancel</a>
                </div>
            </div>

        </div>
    </form>
    <?php if (count($errors) != 0):  ?>
        <div class='errors'>
            <p>Please correct the following error(s)</p>
            <ul>
                <?php foreach($errors as $error): ?>
                    <li><?= $error ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    </body>
</html>