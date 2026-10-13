<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <title>Log In</title>
        <base href="<?= $web_root ?>"/>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link href="css/styles.css" rel="stylesheet" type="text/css"/>
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    </head>
    <body>
        <div class = "main_title"><i class="bi bi-cart4"></i>EPayFC</div>
        <form class = "login_box" action="user/login" method="post">
            <h2>Sign In</h2>
                <div class="fields_buttons">
                    <div class="input_fields">
                        <div class="input_box">
                            <label><i class="bi bi-person"></i></label>
                            <input type="email" name="email" value="<?= $email ?>"/>
                        </div>
                        <div class="input_box">
                            <label><i class="bi bi-key"></i></label>
                            <input type="password" name="password" value="<?= $password ?>"/>
                        </div>
                    </div>
                    <div class="buttons">
                        <button type="submit">Login</button>
                        <a href="item/browse" class="guest_button">Continue as guest</a>
                    </div>
                    <div class="subscribe">
                        <a href="user/signup" class="register_link">New here? Click here to subscribe !</a>
                    </div>
                </div>
            <?php if ($debug_users):?>
            <div class="debug">
                <p>For Debug Purpose</p>
                <ul class="debug_list">
                    <?php if(isset($debug_users)) : ?>
                        <?php foreach($debug_users as $u) : ?>
                            <li>
                                <a href="<?= $web_root ?>user/login/<?= urlencode($u->getPseudo()) ?>" class="debug_link">
                                    Login as <?= htmlspecialchars($u->getEmail()) ?>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    <li><a href="setup/install" class="debug_link original_data">Restore original data</a></li>
                    <li><a href="setup/install" class="debug_link personal_data">Backup personal data</a></li>
                    <li><a href="setup/install" class="debug_link personal_data">Restore personal data</a></li>

                </ul>
            </div>
            <?php endif;?>
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
