<?php

require_once "framework/Model.php";
require_once "User.php";
require_once "utils/AppTime.php";
require_once "model/ItemCategory.php";

class Item extends Model
{
    public function getTimeLeft(): ?string
    {
        return $this->time_left;
    }

    public function setTimeLeft(?string $time_left): void
    {
        $this->time_left = $time_left;
    }

    public function getPrice(): ?string
    {
        return $this->price;
    }

    public function setPrice(?string $price): void
    {
        $this->price = $price;
    }

    public function isIsAuction(): bool
    {
        return $this->is_auction;
    }

    public function setIsAuction(bool $is_auction): void
    {
        $this->is_auction = $is_auction;
    }

    public function isIsBuyNow(): bool
    {
        return $this->is_buy_now;
    }

    public function setIsBuyNow(bool $is_buy_now): void
    {
        $this->is_buy_now = $is_buy_now;
    }

    public function getImageCount(): int
    {
        return $this->image_count;
    }

    public function setImageCount(int $image_count): void
    {
        $this->image_count = $image_count;
    }

    public function isIsBidder(): bool
    {
        return $this->is_bidder;
    }

    public function setIsBidder(bool $is_bidder): void
    {
        $this->is_bidder = $is_bidder;
    }

    public function isIsHighestBidder(): bool
    {
        return $this->is_highest_bidder;
    }

    public function setIsHighestBidder(bool $is_highest_bidder): void
    {
        $this->is_highest_bidder = $is_highest_bidder;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): void
    {
        $this->id = $id;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): void
    {
        $this->description = $description;
    }

    public function getOwner(): int
    {
        return $this->owner;
    }

    public function setOwner(int $owner): void
    {
        $this->owner = $owner;
    }

    public function getCreatedAt(): string
    {
        return $this->created_at;
    }

    public function setCreatedAt(string $created_at): void
    {
        $this->created_at = $created_at;
    }

    public function getBuyNowPrice(): ?float
    {
        return $this->buy_now_price;
    }

    public function setBuyNowPrice(?float $buy_now_price): void
    {
        $this->buy_now_price = $buy_now_price;
    }

    public function getDuration(): int
    {
        return $this->duration;
    }

    public function setDuration(int $duration): void
    {
        $this->duration = $duration;
    }

    public function getStartingBid(): float
    {
        return $this->starting_bid;
    }

    public function setStartingBid(float $starting_bid): void
    {
        $this->starting_bid = $starting_bid;
    }
    private ?string $main_photo;
    private ?string $time_left = null;
    private ?string $price = null;
    private bool $is_auction = false;
    private bool $is_buy_now = false;
    private int $image_count = 0;
    private bool $is_bidder = false;
    private bool $is_highest_bidder = false;
    private ?string $creator = null;

    public function __construct(
        private? int    $id,
        private string $title,
        private? string $description,
        private int    $owner,
        private string $created_at,
        private? float  $buy_now_price,
        private int    $duration,
        private float  $starting_bid
    )
    {
    }

    public function create_or_update(): void {
        if ($this->id == null)
        {
            self::execute("Insert into items(title, description, owner, created_at, buy_now_price, duration_days, starting_bid)
            values (:title, :description, :owner, :created_at, :buy_now_price, :duration, :starting_bid)", ["title" => $this->title,
                "description" => $this->description, "owner" => $this-> owner, "created_at" => $this->created_at, "buy_now_price" => $this->buy_now_price == 0 ? null : $this->buy_now_price,
                "duration" => $this->duration, "starting_bid" => $this->starting_bid]);
            $this->id = self::lastInsertId();
        } else {
            self::execute("UPDATE `items` SET `title`=:title,`description`=:description,`owner`=:owner,
            `created_at`=:created_at,`buy_now_price`=:buy_now_price, `duration_days`=:duration,`starting_bid`=:starting_bid 
            WHERE `id`=:id", ["title" => $this->title, "description" => $this->description, "owner" => $this->owner,
                "created_at" => $this->created_at, "buy_now_price" => $this->buy_now_price != 0 ? $this->buy_now_price : null, "duration" => $this->duration,
                "starting_bid" => $this->starting_bid, "id" => $this->id]);
        }
}
//retourne tous les items auxquels on participe (si on a fait des bids dessus)
    public static function get_items(int $id): array
    {
        $query = self::execute("SELECT i.id, i.title, i.description, i.buy_now_price, 
               i.starting_bid, i.created_at, i.owner, i.duration_days
        FROM items i
        WHERE i.id IN (
            SELECT DISTINCT b.item 
            FROM bids b 
            WHERE b.owner = :owner
        )
    ", ["owner" => $id]);
        $rows = $query->fetchAll();

        return self::create_item_from_db($rows, $id);
    }

    public static function get_item_by_id(int $id): ?Item
    {  // Return Item|null
        $query = self::execute("SELECT * FROM items WHERE id = :id", ["id" => $id]);
        $result = $query->fetch();

        if (!$result) {
            return null;
        }

        return new self(
            (int)$result['id'],
            $result['title'],
            $result['description'] ?? '',
            (int)$result['owner'],
            $result['created_at'],
            (float)($result['buy_now_price'] ?? 0),
            (int)($result['duration_days'] ?? 0),
            (float)($result['starting_bid'] ?? 0)
        );
    }


    public static function get_other_items(int $id): array
    {
        $query = self::execute("
        SELECT i.*
         FROM items i
         WHERE i.owner != :owner
         AND i.id NOT IN (
             SELECT b.item
             FROM bids b
             WHERE b.owner = :owner
         )",
            ["owner" => $id]);

        $rows = $query->fetchAll();

        return self::create_item_from_db($rows, $id);
    }

    public function is_available(): bool
    {
        $now = AppTime::get_current_datetime();
        $query = self::execute(
            "SELECT end_at FROM v_items_status WHERE id = :item_id AND end_at > :now
                AND buy_now_reached = 0 and
            (is_auction = 1 OR not_purchased_direct_sale = 1) ", //si buy_now_reached, l'offre se cloture immédiatement
            ["item_id" => $this->id, "now" => $now]
        );
        return $query->rowCount() > 0;
    }

    public function time_left(): string
    {
        $status = self::execute(
            "SELECT end_at, buy_now_reached, bid_count FROM v_items_status WHERE id = :item_id",
            ["item_id" => $this->id]
        )->fetch();

        if (!$status) {
            return 'Unknown';
        }

        $now = new DateTime(AppTime::get_current_datetime());
        $end = new DateTime($status['end_at']);

        if ($status['buy_now_reached'] == 1 || ($end <= $now && $status['bid_count'] > 0)) {
            return 'Sold';
        }
        if ($end <= $now) {
            return 'Closed';
        }

        // Only show countdown for truly active items
        return self::time_left_string($status['end_at']);
    }


    public static function time_left_string(string $datetime): string
    {
        $now = new DateTime(AppTime::get_current_datetime());
        $end = new DateTime($datetime);

        if ($end <= $now) {
            return 'Ended';
        }

        $diff = $now->diff($end);

        $total_hours = ($diff->days * 24) + $diff->h;

        $days = $diff->days;
        $hours = $diff->h;
        $minutes = $diff->i;


        if ($days > 0) {
            return "{$days} d {$hours} h left";
        } elseif ($hours > 0) {
            return "{$hours} h {$minutes} min left";
        } else {
            return "{$minutes} min left";
        }
    }

    public function get_end_time() : string
    {
        $status = $this->get_item_status();
        if($status['buy_now_reached'] == 1){
            $query = self::execute("SELECT created_at from bids where item = :item_id AND amount >= :buy_now_price 
                            ORDER BY amount DESC LIMIT 1",["item_id"=>$this->id, "buy_now_price"=>$this->buy_now_price]);
            $result = $query->fetchColumn();
            if ($result) return $result;
        }
        $query = self::execute("SELECT end_at FROM v_items_status WHERE id = :item_id", ["item_id" => $this->id]);
        return $query->fetchColumn();
    }

    public static function get_other_items_complete(int $userId): array
    {
        return $items = self::get_other_items($userId) ?: [];
//        return self::create_item_from_db($items, $userId);
    }


    public function get_main_photo(): string|false
    {
        $query = self::execute("SELECT picture_path FROM `item_pictures` WHERE `priority` = 1 AND `item` = :item_id",
            ["item_id" => $this->id]);
        $picture_path = $query->fetchColumn();

        if (empty($picture_path)) {
            return false;
        };

        return 'uploads/items/' . $this->id . '/' . basename($picture_path);
    }

    public function is_main_photo(string $picture_path): bool
    {
        $main_photo = $this->get_main_photo();
        return $main_photo !== false && $picture_path === $main_photo;
    }


    public function get_pictures(): array|false
    {
        $query = self::execute("SELECT picture_path FROM `item_pictures` WHERE `item` = :item_id", ["item_id" => $this->id]);
        $paths = $query->fetchAll(PDO::FETCH_COLUMN);

        if (empty($paths)) {
            return false;
        }

        $result = [];
        foreach ($paths as $path) {
            $result[] = 'uploads/items/' . $this->id . '/' . basename($path);
        }
        return $result;
    }

    public function get_creator(): string|false
    {
        $query = self::execute("SELECT users.pseudo 
                FROM `items`
                JOIN users on items.owner = users.id
              WHERE items.id = :item_id", ["item_id" => $this->id]);
        return $query->fetchColumn() ?: false;
    }

    private function image_count(): int
    {
        $query = self::execute("SELECT COUNT(*) FROM `item_pictures` WHERE `item` = :item_id", ["item_id" => $this->id]);
        $count = $query->fetchColumn();
        return $count;
    }

    public function is_auction(): bool
    {
        return $this->starting_bid > 0;

    }

    public function is_buy_now(): bool
    {
        return $this->buy_now_price > 0;
    }

    public function is_bidder(int $user_id): bool
    {
        $query = self::execute("SELECT COUNT(*) FROM `bids` WHERE `item` = :item_id and `owner` = :user_id",
            ["item_id" => $this->id, "user_id" => $user_id]);
        $result = $query->fetchColumn();
        return $result > 0;
    }

    public function get_user_bid(int $user_id): ?float
    {
        $query = self::execute(
            "SELECT MAX(amount)
         FROM bids
         WHERE item = :item_id
           AND owner = :user_id",
            [
                "item_id" => $this->id,
                "user_id" => $user_id
            ]
        );

        $amount = $query->fetchColumn();
        return $amount !== false ? (float)$amount : null;
    }

    public function get_end_date(): string
    {
        $query = self::execute("SELECT end_at FROM v_items_status WHERE `id` = :item_id", ["item_id" => $this->id]);
        $result = $query->fetchColumn();
        return $result;
    }

    public function is_highest_bidder(int $user_id): bool
    {
        $query = self::execute("SELECT owner FROM `bids` 
                            WHERE `item` = :item_id ORDER BY amount DESC
         LIMIT 1",
            ["item_id" => $this->id]);
        $highest_bid = $query->fetchColumn();
        return (int)$highest_bid === $user_id;
    }

    public static function get_items_complete(int $userId): array
    {
        return $items = self::get_items($userId) ?: [];
//        return self::create_item_from_db($items, $userId);
    }


    public function computePrice(): string
    {
        if ($this->buy_now_price > 0) {
            return number_format($this->buy_now_price, 2, ',', ' ') . ' €';
        }

        return number_format($this->starting_bid, 2, ',', ' ') . ' €';
    }

    //Construit un Item avec les informations provenant de la db + bool pour inclure les items non disponible pour sold_items
    //et closed_unsold_items
    public static function create_item_from_db(array $rows, ?int $userId = null, bool $include_unavailable = false): array
    {
        $result = [];
        foreach ($rows as $row) {
            $item = new Item(
                (int)$row['id'],
                $row['title'],
                $row['description'] ?? '',
                (int)$row['owner'],
                $row['created_at'],
                (float)($row['buy_now_price'] ?? 0),
                (int)($row['duration'] ?? 0),
                (float)($row['starting_bid'] ?? 0)
            );
            if (!$include_unavailable && !$item->is_available()) {
                continue; //si include est sur true, on affiche aussi les items non disponibles
            }

            $item->main_photo = $item->get_main_photo();
            $item->time_left = $item->time_left();
            $item->price = $item->computePrice();
            $item->is_auction = $item->is_auction();
            $item->is_buy_now = $item->is_buy_now();
            $item->image_count = $item->image_count();
            $item->is_bidder = $userId && $item->is_bidder($userId);
            $item->is_highest_bidder = $userId && $item->is_highest_bidder($userId);
            $item->creator = $item->get_creator();

            $result[] = $item;
        }
        return $result;
    }

    //Pour les guests
    public static function get_all_items(): array
    {
        $query = self::execute("SELECT * FROM `items`", []);
        $items = $query->fetchAll();

        return self::create_item_from_db($items, null);

    }

    public static function get_active_items(int $userId): array
    {
        $now = AppTime::get_current_datetime();
        $query = self::execute("SELECT * FROM `v_items_status` WHERE owner = :user_id
            and ((is_auction = 1 and end_at>:now) or (is_direct_sale = 1 and buy_now_reached = 0 AND end_at > :now) )
            ORDER BY end_at ASC", ['user_id' => $userId, 'now' => $now]);
        $active_items = $query->fetchAll();

        return self::create_item_from_db($active_items, $userId, false);
    }

    /*
     * Enchère simple : doit aller jusqu'à la date de fin et on prend l'offre la plus >
     * Achat direct : buy_now_price is reached
     * Les deux : soit le buy_now_price is reached soit la date de fin s'il y a des enchères
     */
    public static function get_closed_unsold_items(int $userId): array
    {
        $closed_unsold_items = [];
        $now = AppTime::get_current_datetime();
        $query = self::execute("SELECT * FROM `v_items_status` WHERE owner = :user_id
        AND (
            (is_auction = 1 AND bid_count = 0 AND end_at <= :now) 
            OR 
            (is_direct_sale = 1 AND buy_now_reached = 0 AND end_at <= :now
                )) ORDER BY end_at ASC 
        ",
            ['user_id' => $userId, 'now' => $now]
        );
        $items = $query->fetchAll();
        return self::create_item_from_db($items, $userId, true);
    }

    public static function get_sold_items(int $userId): array
    {
        $now = AppTime::get_current_datetime();
        $query = self::execute(
            "SELECT * FROM v_items_status 
         WHERE owner = :user_id 
         AND (
             buy_now_reached = 1 
             OR (is_auction = 1 AND end_at <= :now AND bid_count > 0)
         )",
            ['user_id' => $userId, 'now' => $now]
        );

        $sold_items = self::create_item_from_db($query->fetchAll(), $userId, true);

        usort($sold_items, function($a, $b) {
            return strcmp($b->get_end_time(), $a->get_end_time());
        });

        return $sold_items;
    }

    public function get_bids(): array
    {
        $query = self::execute("SELECT pseudo, created_at, amount
        FROM bids, users
        WHERE users.id = bids.owner and item = :id ORDER BY amount DESC", ['id' => $this->id]);
        $bids = $query->fetchAll();

        return $bids;
    }


    /*
     * non createur :
     *      1.annonce ouverte, pas de buy_now, pas de bid
     *      2.annonce ouverte, buy_now_not reached et bids > 0
     *      3.annonce ouverte, buy_now only
     * non createur mais gagnant: annonce fermee vendue a l'user : Congrats! You purchased this item for +
     * createur :
     *      1.annonce ouverte -> You cannot bid on your own listing
     *      2.annonce fermee vendue -> X won this item + prix
     *      3.annonce fermee non vendue -> This listing ended w/o a buyer
     */

    public function get_item_status() : array
    {
        $query = self::execute(
            "SELECT bid_count, buy_now_reached FROM v_items_status WHERE id = :id",
            ['id' => $this->id]
        );
        return $status= $query->fetch();
    }
    public function get_pricing_type(?User $user = null): string
    {
        $userId = $user->getId();
        $isOwner = $this->owner === $userId;
        $isAvailable = $this->is_available();

        $status = $this::get_item_status();


        $hasBids = ($status['bid_count'] ?? 0) > 0;
        $buyNowReached = ($status['buy_now_reached'] ?? 0) == 1;
        $isBuyNowOnly = $this->is_buy_now() && !$this->is_auction();
        $isBuyNow = $this->is_buy_now();

        $isSold = !$isAvailable && ($buyNowReached || $hasBids);

        if($isSold){
            return 'purchased';
        }

        //annonce dispo
        if ($isAvailable) {
            if ($isOwner) {
                return 'owner';  // Annonce ouverte, utilisateur créateur
            }

            if (!$hasBids && !$isBuyNow) {
                return 'starting';  // Ouverte mais pas d'enchères, pas de prix d'achat immédiat
            }
            if ($hasBids && !$buyNowReached) {
                return 'bid buy';   // Enchères existantes, prix d'achat non atteint
            }
            if ($isBuyNowOnly) {
                return 'buy now only';   // Vente directe uniquement
            }
        }

        // annonce cloturee
        if ($isOwner) {
            if ($buyNowReached || $hasBids) {
                return 'owner ended';  // Annonce fermée vendue, utilisateur créateur
            }
            return 'not sold';         // Annonce fermée non vendue, utilisateur créateur
        }

        return 'starting';
    }

    public function get_min_bid_amount(): float
    {
        $query = self::execute("SELECT max(amount) from bids where item = :id", ['id' => $this->id]);
        $current_highest = $query->fetchColumn();

        //si pas encore d'enchere alors le min bid c'est le starting bid
        if($current_highest === false || $current_highest === null){
            return $this->starting_bid;
        }

        //sinon, min bid c'est la plus grande offre existante +1
        return $current_highest + 1.00;

    }

    public function get_buyer(): ?User
    {
        //soit par buy_now
        //soit par highest_bidder a la fin du temps


        $status = $this->get_item_status();

        if ($this->is_available() > 0 && $status['buy_now_reached'] != 1) {
            return null;
        }
        $buyer_id = null;

        if($status['buy_now_reached']==1){
            $query = self::execute("SELECT owner FROM bids WHERE item =:id and amount>=:buy_now_price",
                ['buy_now_price' => $this->buy_now_price, 'id'=>$this->id]);
            $buyer_id = $query->fetchColumn();
        }else if($status['buy_now_reached']==0 && !$this->is_available()){
            $query = self::execute("SELECT owner FROM bids WHERE item =:id ORDER BY amount DESC LIMIT 1", ['id'=>$this->id]);
            $buyer_id = $query->fetchColumn();
        }

        if (!$buyer_id) {
            return null;
        }
        return User::get_user_by_id($buyer_id);
    }

    public function get_winning_price() : int
    {
        $query = self::execute("SELECT amount FROM bids WHERE item =:id ORDER BY amount DESC LIMIT 1", ['id'=>$this->id]);
        return $winning_price = $query->fetchColumn();
    }


    public function place_bid(int $userId, float $amount) : bool
    {
        if($this->owner === $userId){
            return false;
        }
        if(!$this->is_available()){
            return false;
        }
        $min_bid = $this->get_min_bid_amount();

        if($min_bid > $amount){
            return false;
        }

        self::execute(
            "INSERT INTO bids (owner, item, created_at, amount)
                VALUES (:owner, :item, NOW(), :amount)",
            ['owner' => $userId, 'item' => $this->id, 'amount' => $amount]
        );
        return true;
    }

    public function buy_now(int $userId) : bool
    {
        if($this->owner === $userId){
            return false;
        }

        if(!$this->is_available()){
            return false;
        }

        $buy_now_price = $this->buy_now_price;

        if($buy_now_price == 0){
            return false;
        }
        self::execute(
            "INSERT INTO bids (owner, item, created_at, amount)
                VALUES (:owner, :item, NOW(), :amount)",
            ['owner' => $userId, 'item' => $this->id, 'amount' => $buy_now_price]
        );

        return true;
    }

    public function has_bid() : bool
    {
        $query = self::execute("SELECT COUNT(*) FROM bids WHERE item =:id", ['id'=>$this->id]);
        return $query->fetchColumn()>0;
    }

    public function can_edit() : bool
    {
        //aucune offre d'achat (enchère) n'a été faite sur celle-ci
        if(!$this->is_available()){
            return false;
        }
        if($this->is_buy_now() && !$this->is_auction()){
            return true;
        }

        if($this->is_auction() && $this->has_bid()){
            return false;
        }

        return true;
    }

    public function delete(): void
    {
        ItemCategory::delete_all_for_item($this->id);
        self::execute("Delete from items where id=:id", ["id" => $this->id]);
    }
    public static function get_purchases($userId): array
    {
        $now = AppTime::get_current_datetime();
        $my_purchases = [];

        $query = self::execute("
        SELECT v.* FROM v_items_status v
        WHERE v.id IN (SELECT item FROM bids WHERE owner = :user_id)
          AND (v.buy_now_reached = 1 OR v.end_at <= :now)
        ", ["user_id" => $userId, "now" => $now]);
        //tous les items clotures ou le user a fait des offres puis ensuite on ne garde que celles où il a été gagnant

        $all_purchases = self::create_item_from_db($query->fetchAll(), $userId, true);

        foreach ($all_purchases as $purchase) {
            $buyer = $purchase->get_buyer()->getId();
            if($buyer === $userId){
                $my_purchases[] = $purchase;
            }
        }
        //ordre décroissant, strcmp = string compare, compare 2 items en fonction de leur date de fin effective
        usort($my_purchases, function($a, $b) {
            return strcmp($b->get_end_time(), $a->get_end_time());
        });

        return $my_purchases;
    }

    public function get_current_highest_bid() : ?float
    {
        $query = self::execute("SELECT max(amount) FROM bids WHERE item =:id", ['id'=>$this->id]);

        $current_highest_bid =  $query->fetchColumn();

        if ($current_highest_bid  === false || $current_highest_bid  === null) {
            return null;
        }
        return $current_highest_bid;
    }

    public static function validate(array $user_input, int $user_id, ?int $item_id = null) : array
    {
        $errors = [];
        $title = $user_input['title'] ?? null;
        $description = $user_input['description'] ?? null;
        $duration = $user_input['duration'] ?? null;
        $starting_bid = $user_input['starting_bid'] ?? null;
        $direct_sale = $user_input['direct_sale'] ?? null;
        $buy_now_price = $user_input['buy_now_price'] ?? null;
        $categories = $user_input['categories'] ?? null;

        if ($categories && count($categories) > 3)
            $errors['categories'] = "You cannot select more than 3 categories";

        if (!$starting_bid && !$direct_sale) {
            $errors['starting_bid'] = "You must choose an option";
            $errors['direct_sale'] = "You must choose an option";
        } elseif (self::title_exists_for_user($user_id, $title, $item_id)) {
            $errors['title'] = "You already have an item with this title";
        }
        if (!$title || strlen($title) < 3)
            $errors['title'] = "Title must be at least 3 characters";
        if ($description && strlen(trim($description)) < 3)
            $errors['description'] = "Description must be at least 3 characters long";
        if ($duration && ($duration < 1 || $duration > 365))
            $errors['duration'] = "Duration must be between 1 and 365 days";
        if ($starting_bid && $direct_sale) {
            $errors['starting_bid'] = "If you choose the direct sale option, please leave this field blank";
            $errors['direct_sale'] = "If you choose the auction option, please leave this field blank";
        } elseif ($starting_bid) {
            if ($starting_bid <= 0)
                $errors['starting_bid'] = "Starting bid must be > 0";
            if ($buy_now_price) {
                if ($buy_now_price < 0)
                    $errors['buy_now_price'] = "Purchase price must be > 0";
                elseif ($buy_now_price <= $starting_bid)
                    $errors['buy_now_price'] = "Purchase price must be greater than starting bid";
            }
        } elseif ($direct_sale) {
            if ($direct_sale < 0)
                $errors['direct_sale'] = "Sale price must be > 0";
        }
        return $errors;
    }

    //Retourne une grande liste avec tous les items sur la page my_items
    public static function get_all_my_items(int $user_id) : array
    {
        return array_merge(
            self::get_active_items($user_id),
            self::get_closed_unsold_items($user_id),
            self::get_sold_items($user_id)
        );
    }

    //Retourne une grande liste avec tous les items sur la page browse
    public static function get_all_browse_items(int $user_id) : array
    {
        return array_merge(
          self::get_items_complete($user_id),
            self::get_other_items_complete($user_id)
        );
    }

    public static function search(array $items, ?string $pattern, int $filter): array
    {
        if (empty($pattern)) {
            if ($filter == 0)
                return array_values($items);
            $result = [];
            foreach ($items as $item) {
                if (ItemCategory::item_has_cat($item->getId(), $filter)) {
                    $result[] = $item;
                }
            }
            return $result;
        }

        $pattern = mb_strtolower($pattern);

        $result = [];
        foreach ($items as $item) {
            $title = mb_strtolower($item->getTitle());
            $description = mb_strtolower($item->getDescription() ?? '');
            $creator = mb_strtolower($item->get_creator() ?? '');

            if ((str_contains($title, $pattern)
                || str_contains($description, $pattern)
                || str_contains($creator, $pattern))) {
                if ($filter == 0 || ItemCategory::item_has_cat($item->getId(), $filter)) {
                    $result[] = $item;
                }
            }
        }
        return $result;
    }

    public static function title_exists_for_user(int $userId, string $title, ?int $excludeItemId = null): bool
    {
        $sql = "SELECT COUNT(*) FROM items WHERE owner = :owner AND title = :title";
        $params = ['owner' => $userId, 'title' => $title];

        if ($excludeItemId) {
            $sql .= " AND id != :exclude_id";
            $params['exclude_id'] = $excludeItemId;
        }

        $query = self::execute($sql, $params);
        return $query->fetchColumn() > 0;
    }

    public function has_category(int $cat): bool
    {
        $cats = ItemCategory::get_all_cat_id_by_item($this->id);
        foreach ($cats as $c) {
            if ($c['category'] == $cat) {
                return true;
            }
        }
        return false;
    }
}