<?php
require_once 'model/Item.php';
require_once 'model/ItemPicture.php';
require_once 'model/ItemCategory.php';
require_once 'model/Category.php';
class ControllerItem extends Controller
{

    public function index(): void
    {
        $myItems = [];
        $otherItems = [];
        $user = $this->get_user_or_false();

        if ($this->user_logged()) {
            $myItems = Item::get_items($this->user_logged());
            $otherItems = Item::get_other_items($this->user_logged());
        }
        //else {} ici pour les guests

        (new View("browse"))->show([
            "user" => $user,
            "myItems" => $myItems,
            "otherItems" => $otherItems
        ]);
    }

    public function add(): void
    {
        $user = $this->get_user_or_redirect();
        $errors = [];
        $categories = Category::get_all();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $errors = Item::validate($_POST, $user->getId());

            if (empty($errors)) {
                $title = $_POST['title'];
                $description = $_POST['description'] ?? null;
                $duration = $_POST['duration'];
                $starting_bid = $_POST['starting_bid'] ?? null;
                $direct_sale = $_POST['direct_sale'] ?? null;
                $buy_now_price = $_POST['buy_now_price'] ?? null;

                $owner = $user->getId();
                $created_at = AppTime::get_current_datetime();

                if ($direct_sale) {
                    $item = new Item(null, $title, $description, $owner, $created_at, $direct_sale, $duration, 0);
                } else {
                    $item = new Item(null, $title, $description, $owner, $created_at, $buy_now_price ?: 0, $duration, $starting_bid ?? 0);
                }

                $item->create_or_update();
                if ($_POST['categories'])
                    ItemCategory::set_categories_for_item($item->getId(), $_POST['categories']);
                $this->redirect("item/open/" . $item->getId(), "");
            }
        }

        (new View("add_edit_item"))->show(["user" => $user, "type" => "add", "item" => null, "errors" => $errors, "categories" => $categories]);
    }

    public function edit(): void
    {
        error_log("METHOD: " . $_SERVER['REQUEST_METHOD']);
        error_log("POST: " . print_r($_POST, true));

        $id = $_GET['param1'];
        $origin = $_GET['param2'] ?? null;
        $encoded_state = $_GET['param3'] ?? null;

        if (!$id) {
            die("No valid id");
        }

        $item = Item::get_item_by_id($id);
        $categories = Category::get_all();

        if (!$item) {
            die("Item not found");
        }

        $user = $this->get_user_or_redirect();
        if ($user->getId() != $item->getOwner()) {
            $this->redirect();
        }

        $errors = [];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $errors = Item::validate($_POST, $user->getId(), $item->getId());

            if (empty($errors)) {
                $starting_bid = $_POST['starting_bid'] ?? null;
                $direct_sale = $_POST['direct_sale'] ?? null;

                $item->setTitle($_POST['title']);
                $item->setDescription($_POST['description'] ?? null);
                $item->setDuration($_POST['duration']);

                if ($direct_sale) {
                    $item->setBuyNowPrice($direct_sale);
                    $item->setStartingBid(0);
                } else {
                    $item->setBuyNowPrice($_POST['buy_now_price'] ?? null);
                    $item->setStartingBid($starting_bid);
                }

                $item->create_or_update();
                ItemCategory::set_categories_for_item($item->getId(), $_POST['categories']);
                $this->redirect("item", "open", $item->getId(), $origin ?? 'from-browse', $encoded_state ?? '');
            }
        }
        (new View("add_edit_item"))->show(["user" => $user, "type" => "edit", "item" => $item, "errors" => $errors, "origin" => $origin, "encoded_state" => $encoded_state,
            "categories" => $categories]);
    }

    public function manage_images(): void
    {
        $id = $_GET['param1'];
        $origin = $_GET['param2'] ?? null;
        $encoded_state = $_GET['param3'] ?? null;

        if (!$id) {
            $this->redirect("item/browse");
        }

        $item = Item::get_item_by_id($id);
        if (!$item) {
            die("Item not found");
        }

        $user = $this->get_user_or_redirect();
        if ($user->getId() !== $item->getOwner()) {
            $this->redirect("item/browse");
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            if (isset($_POST['action']) && $_POST['action'] === 'upload') {
                if (isset($_FILES['new_pictures'])) {
                    ItemPicture::add_pictures($item->getId(), $_FILES['new_pictures']);
                }
            }

            elseif (isset($_POST['move_left'])) {
                $priority = (int)$_POST['move_left'];
                ItemPicture::move_picture($item->getId(), $priority, 'left');
            }

            elseif (isset($_POST['move_right'])) {
                $priority = (int)$_POST['move_right'];
                ItemPicture::move_picture($item->getId(), $priority, 'right');
            }

            elseif (isset($_POST['delete'])) {
                $priority = (int)$_POST['delete'];
                ItemPicture::delete_picture($item->getId(), $priority);
            }

            $this->redirect("item", "manage_images", $item->getId(), $origin ?? 'from-browse', $encoded_state ?? '');
        }

        $pictures = ItemPicture::get_by_item($item->getId());

        (new View("manage_images"))->show([
            "user" => $user,
            "item" => $item,
            "pictures" => $pictures,
            "origin" => $origin,
            "encoded_state" => $encoded_state
        ]);
    }

    public function reorder_images_service(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ordered_paths']) && isset($_POST['item_id'])) {

            $item_id = (int)$_POST['item_id'];
            $ordered_paths = $_POST['ordered_paths'];

            $item = Item::get_item_by_id($item_id);
            $user = $this->get_user_or_false();

            // Security Check
            if ($user && $item && $user->getId() === $item->getOwner()) {

                ItemPicture::update_priorities($item_id, $ordered_paths);

                header('Content-Type: application/json');
                echo json_encode(['success' => true]);
                return;
            }
        }

        http_response_code(400);
        echo json_encode(['error' => 'Bad Request or Unauthorized']);
    }


    public function browse(): void
    {
        //l'input entré par l'utilisateur encodé par javascript
        $encoded_state = $_GET['param1'] ?? null;
        $search_pattern = null;
        $filter = 0;

        //on decode que s'il ya un parametre
        if($encoded_state) {
            $decoded = Tools::url_safe_decode($encoded_state);

            //ulr_safe_decode retourne un tableau associatif avec une seule valeur?
            $search_pattern = $decoded['pattern'] ?? null;
            $filter = (int)($decoded['filter'] ?? 0);
        }

        $user = $this->get_user_or_false();
        $myItems = [];
        $otherItems = [];

        if ($user) {
            $myItems = Item::get_items_complete($user->getId());
            $otherItems = Item::get_other_items_complete($user->getId());
        }else {
            $otherItems = Item::get_all_items(); //pour les guests
        }
        $cats_per_item = [];
        foreach ($myItems as $my) {
            $cats_per_item[$my->getId()] = ItemCategory::get_all_categories_by_item($my->getId());
        }
        foreach ($otherItems as $other) {
            $cats_per_item[$other->getId()] = ItemCategory::get_all_categories_by_item($other->getId());
        }

        $all_cats = Category::get_all();

        (new View("browse"))->show([
            "myItems" => $myItems,
            "otherItems" => $otherItems,
            "user" => $user,
            "search_pattern" => $search_pattern,
            "encoded_state" => $encoded_state,
            "cats_per_item" => $cats_per_item,
            "all_cats" => $all_cats,
            "filter" => $filter
        ]);

    }

    public function sales(): void
    {
        $user = $this->get_user_or_false();
        $myItems = [];

        if($user) {
            $myItems = Item::get_sold_items($user->getId());
        }

        (new View("sales"))->show([
            "user" => $user,
            "myItems" => $myItems
        ]);
    }

    public function purchases(): void
    {
        $user = $this->get_user_or_false();
        $my_purchases = [];

        if($user) {
            $my_purchases = Item::get_purchases($user->getId());
        }

        (new View("purchases"))->show([
            "user" => $user,
            "my_purchases" => $my_purchases
        ]);
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


    public function open(): void
    {
        $id = $_GET['param1'];
        $origin = $_GET['param2'] ?? null;
        $encoded_state = $_GET['param3'] ?? null;
        $user = $this->get_user_or_false();

        if (!$id) {
            die("No valid id");
        }

        $item = Item::get_item_by_id($id);
        $pictures = $item->get_pictures();
        $main_photo = $item->get_main_photo();
        $bids = $item->get_bids();


        $pricingType = $item->get_pricing_type($user);
        $min_bid = $item->get_min_bid_amount();
        $creator = $item->get_creator();
        $can_edit = $item->can_edit();

        $bidSuccess = false;
        $buySuccess = false;

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['amount'])) {
            $amount = (float)$_POST['amount'];
            $bidSuccess = $item->place_bid($user->getId(), $amount);
            $this->redirect("item", "open", $item->getId(), $origin ?? 'from-browse', $encoded_state ?? '');
        }

        elseif (isset($_POST['buy_now'])) {
            $buySuccess = $item->buy_now($user->getId());
            $this->redirect("item", "open", $item->getId(), $origin ?? 'from-browse', $encoded_state ?? '');
        }

        if (isset($_POST['selected_picture'])) {
            $main_photo = 'uploads/items/' . $item->getId() . '/' . basename($_POST['selected_picture']);
        }

        $categories = ItemCategory::get_all_categories_by_item($id);

        (new View("open"))->show([
            "item" => $item,
            "pictures" => $pictures,
            "bids" => $bids,
            "user" => $user,
            "pricing_type" => $pricingType,
            "min_bid" => $min_bid,
            "creator" => $creator,
            "bidSuccess" => $bidSuccess,
            "buySuccess" => $buySuccess,
            "can_edit" => $can_edit,
            "main_photo" => $main_photo,
            "origin" => $origin,
            "categories" => $categories,
            "encoded_state" => $encoded_state
        ]);
    }


    public function list(): void
    {

        //l'input entré par l'utilisateur encodé par javascript depuis l'url
        $encoded_state = $_GET['param1'] ?? null;
        $search_pattern = null;
        $filter = 0;

        //on decode que s'il ya un parametre
        if($encoded_state) {
            $decoded = Tools::url_safe_decode($encoded_state);

            //ulr_safe_decode retourne un tableau associatif avec une seule valeur?
            $search_pattern = $decoded['pattern'] ?? null;
            $filter = (int)($decoded['filter'] ?? 0);
        }

        $user = $this->get_user_or_false();

        if (!$user) {
            $this->redirect();
            return;
        }

        $active_items = Item::get_active_items($user->getId());
        $closed_unsold_items = Item::get_closed_unsold_items($user->getId());
        $sold_items = Item::get_sold_items($user->getId());
        $all_cats = Category::get_all();


        (new View("list"))->show([
            "user" => $user,
            "active_items" => $active_items,
            "closed_unsold_items" => $closed_unsold_items,
            "sold_items" => $sold_items,
            "encoded_state" => $encoded_state,
            "search_pattern" => $search_pattern,
            "filter" => $filter,
            "all_cats" => $all_cats
        ]);
    }

    public function delete_all(): void
    {
        $user = $this->get_user_or_redirect();
        $id = $_GET['param1'];

        if (!$id) {
            $this->redirect("item/browse");
        }

        $item = Item::get_item_by_id($id);
        if (!$item) {
            die("Item not found");
        }

        ItemPicture::delete_all($id);
        $item->delete();
        $this->redirect("item/list");
    }
    public function delete(): void
    {
        $user = $this->get_user_or_redirect();
        $id = $_GET['param1'];

        if (!$id) {
            $this->redirect("item/browse");
        }

        $item = Item::get_item_by_id($id);
        if (!$item) {
            die("Item not found");
        }

        $user = $this->get_user_or_redirect();
        if ($user->getId() !== $item->getOwner()) {
            $this->redirect("item/browse");
        }

        (new View("delete_confirm"))->show(["item" => $item, "owner" => $user]);
    }

    public function carousel_service() : void
    {
        $id = $_GET['param1'];
        $user = $this->get_user_or_false();

        if(!$id || !is_numeric($id)) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid item id']);
            return;
        }

        $item = Item::get_item_by_id($id);

        if(!$item) {
            http_response_code(404);
            echo json_encode(['error' => 'Item not found']);
            return;
        }

        echo json_encode(["images" => ItemPicture::get_path_by_item($item -> getId())]);

    }

    public function fulltext_service() : void
    {
        $page = $_GET['param1'];
        $pattern = isset($_GET['param2']) ? urldecode($_GET['param2']) : null;
        $filter = $_GET['param3'] ?? '';

        if ($filter === '') {
            $filter = $pattern;
            $pattern = '';
        }
        $user = $this->get_user_or_false();

        if (!in_array($page, ['browse', 'list'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid page parameter']);
            return;
        }

        if ($page === 'list' && !$user) {
            http_response_code(401);
            echo json_encode(['error' => 'Authentication required']);
            return;
        }

        if($page === 'list') {
            $items = $user ? Item::get_all_my_items($user->getId()) : [];
        } else {
            if ($user) {
                $items = Item::get_all_browse_items($user->getId());
            } else {
                $items = Item::get_all_items();
            }
        }

        $filtered = Item::search($items, $pattern, (int)$filter);


        //le résultat de la recherche
        $items_info = [];
        foreach ($filtered as $item) {
            $items_info[] = [
              'id' => $item -> getId(),
              'title' => $item -> getTitle(),
                'creator' => $item -> get_creator()
            ];
        }

        //encode l'état pour l'url
        $encoded_state = $pattern !== '' || $filter != 0 ? Tools::url_safe_encode(['pattern' => $pattern, 'filter' => $filter]) : '';

        //produit un json pret pour le js
        echo json_encode(
            [
                'items' => $items_info,
                'encoded_state' => $encoded_state
            ]
        );
    }

    public function get_validation_config_service(): void
    {
        // Fetch rules from dev.ini / prod.ini
        header('Content-Type: application/json');
        echo json_encode([
            'title_min_length' => (int)Configuration::get('title_min_length', 3),
            'title_max_length' => (int)Configuration::get('title_max_length', 255)
        ]);
    }

    public function check_title_unique_service(): void
    {
        $user = $this->get_user_or_false();
        if (!$user || !isset($_POST['title'])) {
            http_response_code(400);
            return;
        }

        $title = $_POST['title'];
        $item_id = isset($_POST['item_id']) && $_POST['item_id'] !== '' ? (int)$_POST['item_id'] : null;

        $exists = Item::title_exists_for_user($user->getId(), $title, $item_id);

        header('Content-Type: application/json');
        // Return true if valid (unique), false if invalid (exists)
        echo json_encode(['is_valid' => !$exists]);
    }

}