<?php

require_once "framework/Model.php";
require_once "Item.php";

class User extends Model
{

    public function __construct(private ?int   $id, private string $email, private string $hashed_password,
                                private string  $full_name, private string $pseudo, private string $role,
                                private ?string $picture_path, private ?string $iban)
    {
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $new_id): void
    {
        $this->id = $new_id;
    }

    public function getRole(): string
    {
        return $this->role;
    }

    public function getHashedPassword(): string
    {
        return $this->hashed_password;
    }

    public function setHashedPassword(string $hashed_password): void
    {
        $this->hashed_password = $hashed_password;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): void
    {
        $this->email = $email;
    }

    public function getFullName(): string
    {
        return $this->full_name;
    }

    public function setFullName(string $full_name): void
    {
        $this->full_name = $full_name;
    }

    public function getPseudo(): string
    {
        return $this->pseudo;
    }

    public function setPseudo(string $pseudo): void
    {
        $this->pseudo = $pseudo;
    }

    public function getPicturePath(): ?string
    {
        return $this->picture_path;
    }

    public function setPicturePath(?string $picture_path): void
    {
        $this->picture_path = $picture_path;
    }

    public function getIban(): ?string
    {
        return $this->iban;
    }

    public function setIban(?string $iban): void
    {
        $this->iban = $iban;
    }

    public function is_admin(): bool {
        return $this->getRole() === 'admin';
    }




    public static function validate_login(string $email, string $password): array
    {
        $errors = [];
        $user = User::get_user_by_mail($email);

        if ($user) {
            if (!self::check_password($password, $user->hashed_password)) {
                $errors[] = "Wrong password, Try again!";
            }
        } else {
            $errors[] = "No profile linked to this email! Sign up!";
        }
        return $errors;
    }

    public static function get_user_by_mail(string $email): User|false
    {
        $query = self::execute("SELECT * FROM `users` WHERE `email` = :email", ["email" => $email]);
        $data = $query->fetch();
        if ($query->rowCount() == 0) {
            return false;
        } else {
            return new User($data["id"], $data["email"], $data["password"], $data["full_name"], $data["pseudo"],
                $data["role"], $data["picture_path"], $data["iban"]);
        }
    }

    public static function get_user_by_id(int $id): User|false
    {
        $query = self::execute("SELECT * FROM `users` WHERE `id` = :id", ["id" => $id]);
        $user = $query->fetch(PDO::FETCH_ASSOC);

        if (empty($user)) {
            return false;
        }
        return new User((int)$user["id"],
            $user["email"] ?? '',
            $user["password"] ?? '',
            $user["full_name"] ?? '',
            $user["pseudo"] ?? '',
            $user["role"] ?? '',
            $user["picture_path"] ?? '',
            $user["iban"] ?? '');
    }

    //public static function get_user

    public static function check_password(string $password, string $hashed_password)
    {
        return password_verify($password, $hashed_password);
    }

    public function get_items(): array
    {
        return Item::get_items($this->getId());
    }

    public function create(): User
    {
        self::execute("INSERT INTO Users(email, password, full_name, pseudo) VALUES(:email, :password, :full_name, :pseudo)",
            ["email" => $this->email, "password" => $this->hashed_password, "full_name" => $this->full_name,
                "pseudo" => $this->pseudo]);
        $this->setId(self::lastInsertId());
        return $this;
    }

    public function update(): void
    {
        self::execute("UPDATE `users` SET `full_name`=:full_name,`email`=:email,`pseudo`=:pseudo,`password`=:password,
        `role`=:role,`picture_path`=:picture_path,`iban`=:iban WHERE id=:id",
            ["full_name" => $this->full_name, "email" => $this->email, "pseudo" => $this->pseudo, "password" => $this->hashed_password,
                "role" => $this->role, "picture_path" => $this->picture_path, "iban" => $this->iban, "id" => $this->id]);
    }

    public static function validate_unicity_mail(string $email): array
    {
        $errors = [];
        $user = self::get_user_by_mail($email);
        if ($user) {
            $errors[] = "This user already exists.";
        }
        return $errors;
    }

    public static function get_user_by_pseudo(string $pseudo): User|false
    {
        $query = self::execute("SELECT * FROM `users` WHERE `pseudo` = :pseudo", ["pseudo" => $pseudo]);
        if ($query->rowCount() === 0) {
            return false;
        }
        $data = $query->fetch();
        return new User($data["id"], $data["email"], $data["password"], $data["full_name"], $data["pseudo"], $data["role"], $data["picture_path"], $data["iban"]);
    }

    public static function validate_unicity_pseudo(string $pseudo): bool
    {
        $errors = [];
        $errors = self::get_user_by_pseudo($pseudo);
        if (!empty($errors)) {
            return false;
        }
        return true;

    }

    private static function validate_password(string $password): array
    {
        $errors = [];
        if (strlen($password) < 8) {
            $errors[] = "Passwords must be at least 8 characters long!";
        }
        if (!preg_match('/[A-Z]/', $password)) {
            $errors[] = "Passwords must contain at least one uppercase letter!";
        }
        if (!preg_match('/[a-z]/', $password)) {
            $errors[] = "Passwords must contain at least one lowercase letter!";
        }
        if (!preg_match('/[0-9]/', $password)) {
            $errors[] = "Passwords must contain at least one number!";
        }
        if (!preg_match('/[^a-zA-Z0-9]/', $password)) {
            $errors[] = "Passwords must contain at least one special character!";
        }

        return $errors;
    }

    public static function validate_passwords(string $password, string $password_confirm): array
    {
        $errors = User::validate_password($password);
        if ($password != $password_confirm) {
            $errors[] = "Passwords do not match!";
        }
        return $errors;
    }


    public function validate(): array
    {
        $errors = [];
        if (strlen($this->full_name) < 3) {
            $errors[] = "Full name must be at least 3 characters long.";
        }
        if (!filter_var($this->email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = "Invalid email address.";
        }
        if (strlen($this->pseudo) < 3 || strlen($this->pseudo) > 30) {
            $errors[] = "Pseudo must be at least 3 and maximum 30 characters long.";
        }
        if (!self::validate_unicity_pseudo($this->pseudo)) {
            $errors[] = "Pseudo is already taken.";
        }
        if (!self::is_unique_full_name(0, $this->full_name)) {
            $errors[] = "Full name is already taken.";
        }
        return $errors;
    }

    public static function is_unique_full_name(int $id, string $name): bool
    {
        $query = "";
        if ($id == 0)
            $query = self::execute("SELECT * FROM `users` WHERE `full_name` = :name", ["name" => $name]);
        else
            $query = self::execute("SELECT * FROM `users` WHERE `full_name` = :name and `id` !=:id", ["name" => $name, "id" => $id]);
        return ($query->rowCount() === 0);
    }


    public function get_total_revenue(): float
    {
        $sold_items = Item::get_sold_items($this->id);
        $revenue = 0;

        foreach ($sold_items as $item) {
            $revenue += $item->get_winning_price();
        }
        return $revenue;
    }

    public function get_average(): float
    {
        $sold_items = Item::get_sold_items($this->id);
        if(count($sold_items) == 0){
            return 0;
        }
        return $this->get_total_revenue() / count($sold_items);
    }

    public function get_most_loyal_bidder(): ?User
    {
        $soldItems = Item::get_sold_items($this->id);

        $buyerCounts = [];
        foreach ($soldItems as $item) {
            $buyer = $item->get_buyer();
            if ($buyer) {
                $buyerCounts[$buyer->id] = ($buyerCounts[$buyer->id] ?? 0) + 1;
            }
        }

        if(empty($buyerCounts)) return null;
        $topBuyerId = array_keys($buyerCounts, max($buyerCounts))[0];
        return User::get_user_by_id($topBuyerId);
    }


    public function get_total_spent(): float
    {
        $my_purchases = Item::get_purchases($this->id);
        $spent = 0;

        foreach ($my_purchases as $purchase) {
            $spent += $purchase->get_winning_price();
        }
        return $spent;
    }

    public function get_average_spent(): float
    {
        $my_purchases = Item::get_purchases($this->id);
        $count = count($my_purchases);

        if($count===0) return 0.0;

        return $this->get_total_spent() / $count;
    }

    public function get_top_seller(): ?User
    {
        $my_purchases = Item::get_purchases($this->id);

        $sellerCount = [];
        foreach ($my_purchases as $item) {
            $seller = $item->getOwner();
            $sellerCount[$seller] = ($sellerCount[$seller] ?? 0) + 1;
        }

        if(empty($sellerCount)){
            return null;
        }
        $topSeller = array_keys($sellerCount, max($sellerCount))[0];
        return User::get_user_by_id($topSeller);
    }

    public static function isValidBelgianIbanFormat(string $iban): bool
    {
        $pattern = '/^BE\s?\d{2}(?:\s?\d{4}){3}$/i';

        return preg_match($pattern, $iban) === 1;
    }

    public function isAdmin(): bool {
        $role = $this->getRole();
        return $role === "admin";
    }

}