<?php
require_once 'framework/Model.php';
require_once 'model/ItemCategory.php';
require_once 'framework/Configuration.php';

class Category extends Model
{
    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): void
    {
        $this->id = $id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getPriority(): int
    {
        return $this->priority;
    }

    public function setPriority(int $priority): void
    {
        $this->priority = $priority;
    }
    public function __construct(
        private ?int $id,
        private string $name,
        private int $priority,
        private int $item_count = 0)
    {

    }

    public function has_items(): bool
    {
        return ItemCategory::empty_category($this->getId());
    }

    public function getItemCount(): int
    {
        return $this->item_count;
    }

    public static function create_category_object_from_db(array $categories) : array
    {
        $result = [];
        foreach ($categories as $category) {
            $category_item = new Category(
                (int)$category["id"],
                $category["name"],
                (int)$category["priority"]
            );
            $category_item->item_count = self::get_item_count_per_category($category_item->getId());
            $result[] = $category_item;
        }
        return $result;
    }

    public static function get_all_categories_by_priority() : array {
        $query = self::execute("SELECT * FROM categories order by priority", []);
        $categories = $query->fetchAll();

        return self::create_category_object_from_db($categories);
    }

    public static function validate_name(string $new_name, ?int $id = null) : array {
        $errors = [];
        if(strlen($new_name) <3 || strlen($new_name) >25){
            $errors[] = "New category name must be between 3 and 25 characters";
        }
        if(!self::is_name_available($new_name, $id)){
            $errors[] = "New category name '$new_name' is not available";
        }
        return $errors;
    }

    //si on veut modifier, il y a un id non null, dont on doit en tenir compte dans la requête pour ne pas comparer le nom mais nullable pour l'insert
    public static function is_name_available(string $name, ?int $id=null) : bool
    {
        $query = "SELECT Count(*) FROM categories WHERE name = :name";
        $params = ['name'=> $name];
        if($id !== null){
            $query .= " AND id != :id";
            $params['id'] = $id;
        }
        $query=self::execute($query, $params);
        $result = $query->fetchColumn();
        return $result == 0;
    }

    public static function get_item_count_per_category(int $id) : int {
        $query = self::execute("select count(*) from item_categories WHERE category = :id", ['id' => $id]);
        return $query->fetchColumn();
    }

    public static function get_category_by_id(int $id) : ?Category {
        $query = self::execute("SELECT * FROM categories WHERE id = :id", ['id' => $id]);
        $result = $query->fetch();

        if(!$result) {
            return null;
        }

        return new Category((int)$result["id"],
            $result["name"],
            (int)$result["priority"]);
    }

    public function rename_category() : void {
        self::execute("UPDATE categories SET name = :name WHERE id = :id", ["name" => $this->name, "id" => $this->id]);

    }

    public static function is_empty(int $id) : bool {
        return self::get_item_count_per_category($id) == 0;
    }

    public function delete_category() : void {
        self::execute("DELETE FROM categories WHERE id = :id", ['id' => $this->id]);
    }

    public function change_priority(int $priority) : void {
        self::execute("UPDATE categories SET priority = :priority WHERE id = :id", ['priority' => $priority, 'id' => $this->id]);
    }

    public static function swap_categories(int $id_current, int $id_neighbour) : void {
        if($id_current != null && $id_neighbour != null) {
            $current = self::get_category_by_id($id_current);
            $neighbour = self::get_category_by_id($id_neighbour);
            $current_priority = $current->getPriority();
            $neighbour_priority = $neighbour->getPriority();

            $current->change_priority(0);
            $neighbour->change_priority($current_priority);
            $current->change_priority($neighbour_priority);

        }
    }

    //up a true si up, false si down
    public function get_neighbour_id(array $categories, bool $up, int $current_id): ?int {
        $neighbour_id = null;
        $current_index = null;

        foreach ($categories as $i => $category) {
            if ($category->getId() === $current_id) {
                $current_index = $i;
            }
        }

        if($current_index === null) {
            return null;
        }

        if ($up && $current_index > 0) {
            $neighbour_id = $categories[$current_index - 1]->getId();

        } elseif (!$up && $current_index < count($categories)-1) {
            $neighbour_id = $categories[$current_index + 1]->getId();
        }


        return $neighbour_id;
    }

    public static function create_category($name): void
    {
        $cats = self::get_all();
        $prio = sizeof($cats) + 1;
        self::execute("INSERT INTO `categories`(`name`, `priority`) VALUES (:name,:prio)", ["name" => $name, "prio" => $prio]);
    }

    public static function add_category(String $name) : int {
        $priority = self::get_max_priority();
        self::execute("INSERT INTO categories (name, priority) VALUES (:name, :priority)", ['name' => $name, 'priority' => $priority]);
        return (int)self::lastInsertId();
    }

    public static function get_max_priority() : int {
        $query = self::execute("SELECT MAX(priority) FROM categories", []);
        return $query->fetchColumn() + 1;
    }


    public static function update_priorities(array $ordrered_categories) : void {

        //on passe toutes les priorités en négatif pour éviter la contrainte d'unicité

        self::execute("UPDATE categories SET priority = -priority", []);

        //on réécrit les priorités en fonction de la position de la catégorie dans ordered_categories

        $newPriority = 1;
        foreach ($ordrered_categories as $category) {
            self::execute("UPDATE categories SET priority = :priority WHERE id = :id", ['priority' => $newPriority, 'id' => $category]);
            $newPriority++;
        }
    }
        private static function create_categories_array(array $cats): array
        {
            $categories = [];
            foreach ($cats as $cat):
                $categories[] = new Category((int)$cat['id'], $cat['name'], (int)$cat['priority']);
            endforeach;
            return $categories;
        }
        public static function get_all(): array
        {
            $query = self::execute("SELECT * FROM `categories` c ORDER by c.priority", []);
            return self::create_categories_array($query->fetchAll());
        }

        public static function get_all_names(): array
        {
            $query = self::execute("SELECT c.name FROM `categories` c", []);
            return $query->fetchAll();
        }

        public static function get_all_names_except(int $id): array
        {
            $query = self::execute("SELECT c.name FROM `categories` c where c.id !=:id", ["id" => $id]);
            return $query->fetchAll();
        }

        public static function delete(int $id): void
        {
            $query = self::execute("select c.priority from categories c where c.id =:id", ["id" => $id]);
            $data = $query->fetch();
            $prio = $data['priority'];
            self::execute("DELETE FROM `categories` WHERE id =:id ", ["id" => $id]);
            self::execute("update categories set priority = priority - 1 where priority > :prio", ["prio" => (int)$prio]);
        }

        public static function save_name(int $id, string $name): void
        {
            self::execute("UPDATE `categories` SET `name`=:name WHERE id =:id", ["name" => $name, "id" => $id]);
        }


        public static function validate_name_unicity(string $name, ?int $id): bool
        {
            $names = ($id == null ? self::get_all_names() : self::get_all_names_except($id));
            foreach ($names as $n) {
                if (strtolower($name) === strtolower($n['name'])) {
                    return false;
                }
            }
            return true;
        }

    }