<?php

require_once 'model/User.php';
require_once 'model/ItemPicture.php';
require_once 'framework/View.php';
require_once 'framework/Controller.php';
require_once 'framework/Configuration.php';
class ControllerUser extends Controller
{
    public function profile(): void
    {
        $user = $this->get_user_or_redirect();
        (new View("profile"))->show(["user" => $user]);

    }

    public function manage_profile_picture(): void
    {
        $user = $this->get_user_or_redirect();
        (new View("manage_profile_picture"))->show(["user" => $user]);
    }

    public function upload_profile_picture(): void
    {
        if (!isset($_FILES['profile_picture'])) {
            throw new Exception("No file uploaded or upload error.");
        }

        $file = $_FILES['profile_picture'];

        $max_size = Configuration::get('max_image_size', 5242880);
        if ($file['size'] > $max_size) {
            throw new Exception("File too large. Maximum size is 5MB.");
        }


        $user = $this->get_user_or_redirect();


        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

        $uploadDir = 'uploads/users/'.$user->getId().'/';

        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $filename = 'profile.jpg';
        $thumbnail = 'profile_thumbnail.jpg';
        $path = $uploadDir.$filename;
        $thumbnail_path = $uploadDir.$thumbnail;
        move_uploaded_file($file['tmp_name'], $path);
        $max_image_dim = Configuration::get('max_image_dimensions', 1080);
        $max_thumbnail = Configuration::get('max_thumbnail_size', 360);

        ItemPicture::resize_and_save($path, $thumbnail_path, $ext, $max_thumbnail, $max_thumbnail);


        $user->setPicturePath($path);
        $user->update();
    }

    public function upload_pp(): void
    {
        try {
            $this->upload_profile_picture();
            $this->redirect('user/profile');
        } catch (Exception $e) {
            $error = $e->getMessage();
        }
    }

    public function delete_profile_picture(): void
    {
        $user = $this->get_user_or_redirect();
        $id = $user->getId();
        $path = "uploads/users/".$id."/profile_thumbnail.jpg";
        unlink($path);
        unlink($user->getPicturePath());
        $user->setPicturePath(null);
        $user->update();
        $this->redirect('user/profile');
    }

    public function check_edit_and_update(): void
    {
       $user = $this->get_user_or_redirect();
       $full_name = '';
       $email = '';
       $pseudo = '';
       $iban = '';
       $errors = [];

       if (isset($_POST['full_name'])) {
           $full_name = trim(htmlspecialchars($_POST['full_name'], ENT_QUOTES, "UTF-8"));
           if (empty($full_name))
               $errors['full_name'] = "Full name is required";
           elseif (strlen($full_name) < 3)
               $errors['full_name'] = "Full name must be at least 3 characters long";
           else if (!User::is_unique_full_name($user->getId(), $full_name))
               $errors['full_name'] = "Full name is already taken";
       }

       if (isset($_POST['email'])) {
           $email = trim(htmlspecialchars($_POST['email'], ENT_QUOTES, "UTF-8"));
           $tmpUser = User::get_user_by_mail($email);
           if (empty($email)) $errors['email'] = "Email is required";
           elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors['email'] = "Invalid email address";
           elseif ($tmpUser && $tmpUser->getId() != $user->getId()) $errors['email'] = "email is already taken";
       }

       if (isset($_POST['pseudo'])) {
           $pseudo = trim(htmlspecialchars($_POST['pseudo'], ENT_QUOTES, "UTF-8"));
           $tmpUser = User::get_user_by_pseudo($pseudo);
           if (empty($pseudo) || strlen($pseudo) < 3 || strlen($pseudo) > 30) $errors['pseudo'] = "Username must be at least 3 characters and at most 30 characters";
           elseif ($tmpUser && $tmpUser->getId() != $user->getId()) $errors['pseudo'] = "Username if already taken";
       }

       $iban = null;
       if (isset($_POST['iban']) && strlen(Tools::sanitize($_POST['iban'])) > 0) {
           $iban = $_POST['iban'];
           if (!User::isValidBelgianIbanFormat($iban))
               $errors['iban'] = "IBAN must follow BE99 9999 9999 9999 format";
       }

       if (empty($errors)) {
           $user->setFullName($full_name);
           $user->setPseudo($pseudo);
           $user->setEmail($email);
           $user->setIban($iban);
           $user->update();
           $this->profile();
       } else
           (new View("edit_profile"))->show(["user" => $user, "errors" => $errors, "type" => "edit"]);
    }

    public function edit_profile(): void
    {
        $user = $this->get_user_or_redirect();
        $type = "edit";
        $full_name = '';
        $email = '';
        $pseudo = '';
        $iban = '';
        $errors = [];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (isset($_POST['full_name'])) {
                $full_name = trim(htmlspecialchars($_POST['full_name'], ENT_QUOTES, "UTF-8"));
                if (empty($full_name))
                    $errors['full_name'] = "Full name is required";
                elseif (strlen($full_name) < 3)
                    $errors['full_name'] = "Full name must be at least 3 characters long";
                else if ($full_name !== $user->getFullName() && !User::is_unique_full_name($user->getId(), $full_name))
                    $errors['full_name'] = "Full name is already taken";
            }

            if (isset($_POST['email'])) {
                $email = trim(htmlspecialchars($_POST['email'], ENT_QUOTES, "UTF-8"));
                $tmpUser = User::get_user_by_mail($email);
                if (empty($email)) $errors['email'] = "Email is required";
                elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors['email'] = "Invalid email address";
                elseif ($tmpUser && $tmpUser->getId() != $user->getId()) $errors['email'] = "email is already taken";
            }

            if (isset($_POST['pseudo'])) {
                $pseudo = trim(htmlspecialchars($_POST['pseudo'], ENT_QUOTES, "UTF-8"));
                $tmpUser = User::get_user_by_pseudo($pseudo);
                if (empty($pseudo) || strlen($pseudo) < 3 || strlen($pseudo) > 30) $errors['pseudo'] = "Username must be at least 3 characters and at most 30 characters";
                elseif ($tmpUser && $tmpUser->getId() != $user->getId()) $errors['pseudo'] = "Username if already taken";
            }

            $iban = null;
            if (isset($_POST['iban']) && strlen(Tools::sanitize($_POST['iban'])) > 0) {
                $iban = $_POST['iban'];
                if (!User::isValidBelgianIbanFormat($iban))
                    $errors['iban'] = "IBAN must follow BE99 9999 9999 9999 format";
            }

            if (empty($errors)) {
                $user->setFullName($full_name);
                $user->setPseudo($pseudo);
                $user->setEmail($email);
                $user->setIban($iban);
                $user->update();
                $this->redirect("user", "profile");
            }
        }
        (new View("edit_profile"))->show(["user" => $user, "errors" => $errors, "type" => "edit"]);
    }

    public function change_password(): void
    {
        $user = $this->get_user_or_redirect();
        $errors = [];
         if (isset($_POST['password']) && isset($_POST['new_password']) && isset($_POST['confirm'])) {
            $password = $_POST['password'];
            $new_password = $_POST['new_password'];
            $confirm = $_POST['confirm'];
            if (!User::check_password($password, $user->getHashedPassword()))
                $errors['current'] = "Current password input does not match current password";
            $err_pass = User::validate_passwords($new_password, $confirm);
            if ($err_pass)
                $errors['new_password'] = "You have to enter twice the same password and password must match requirements listed below";
            if (empty($errors)) {
                $user->setHashedPassword(password_hash($new_password, PASSWORD_BCRYPT));
                $user->update();
                $this->redirect("user", "profile");
                return;
            }
         }
        (new View("edit_profile"))->show(["user" => $user, "type" => "pass", "errors" => $errors]);
    }


    public function signout(): void
    {
        $_SESSION = array();
        session_destroy();
        $this->redirect("user", "login");
    }


    public function index(): void
    {
        if ($this->user_logged()) {
            $this->redirect("item", "browse");
        } else {
            $this->redirect("user", "login");
        }
    }

    public function login() : void {
        $mail = '';
        $password = '';
        $errors = [];
        $debug_users = [
            User::get_user_by_pseudo("bob"),
            User::get_user_by_pseudo("emma"),
            User::get_user_by_pseudo("frank"),
            User::get_user_by_pseudo("grace"),
            User::get_user_by_pseudo("admin")
        ];

        if(isset($_GET['param1'])){
            $pseudo = $_GET['param1'];
            $user = User::get_user_by_pseudo($pseudo);
            if($user){
                $this->log_user($user, "item", "browse");
                return;

            } else {
                $errors[] = "Debug user not found";
            }

        }

        if (isset($_POST['email']) && isset($_POST['password'])) {
            $mail = $_POST['email'];
            $password = $_POST['password'];

            $errors = User::validate_login($mail, $password);
            if (empty($errors)) {
                $this->log_user(User::get_user_by_mail($mail), "item", "browse");
            }
        }
        (new View("login"))->show(["email" => $mail, "password" => $password,
            "errors" => $errors, "debug_users"=>(Configuration::is_dev() ? $debug_users : null)]);
    }

    public function signup() : void {
        $email = '';
        $full_name = '';
        $pseudo = '';
        $password = '';
        $password_confirm = '';
        $errors = [];
        $id = '';

        if (isset($_POST['email']) && isset($_POST['password'])) {
            $email = trim($_POST['email']);
            $full_name = trim($_POST['full_name']);
            $pseudo = trim($_POST['pseudo']);
            $password = $_POST['password'];
            $password_confirm = $_POST['password_confirm'];

            if (empty($email)) $errors[] = "Email is required";
            if (empty($full_name)) $errors[] = "Full name is required";
            if (empty($pseudo)) $errors[] = "Pseudo is required";
            if (empty($password)) $errors[] = "Password is required";
            if (empty($password_confirm)) $errors[] = "Password confirmation is required";



            $email_errors = User::validate_unicity_mail($email);
            $errors = array_merge($errors, $email_errors);



            $password_errors = User::validate_passwords($password, $password_confirm);
            $errors = array_merge($errors, $password_errors);



            $user = new User(null, $email, password_hash($password, PASSWORD_BCRYPT),
                $full_name, $pseudo, 'user', null, null);

            $user_errors = $user->validate();
            $errors = array_merge($errors, $user_errors);

            if (empty($errors)) {
                if ($user->create()) {
                    $this->log_user($user, "item", "browse");
                    return;
                } else {
                    $errors[] = "Database error - user not created";
                }
            }


        }

        (new View("signup"))->show(["email" => $email, "full_name" => $full_name, "pseudo" => $pseudo,
            "password" => $password, "password_confirm" => $password_confirm,
            "id" => null, "errors" => $errors]);


    }
}