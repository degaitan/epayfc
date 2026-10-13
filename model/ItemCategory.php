<?php
require_once "framework/Model.php";
class ItemCategory extends Model
{
    public static function get_all_categories_by_item(int $itemId): array
    {
        $query = self::execute("SELECT c.name from categories c join item_categories ic on c.id = ic.category and ic.item =:itemId order by c.name",
            ['itemId' => $itemId]);
        return $query->fetchAll();
    }

    public static function get_categories_per_item(int $item_id): array
    {
        $query = self::execute("SELECT c.name FROM categories c, item_categories ic WHERE c.id = ic.category and  ic.item = :item_id order by c.name", ['item_id' => $item_id]);
        return $query->fetchAll(PDO::FETCH_COLUMN);
    }

    public static function link_to_item(int $item_id, array $category_ids): void
    {
        foreach ($category_ids as $category_id) {
            self::execute(
                "INSERT INTO item_categories (item, category) VALUES (:item, :category)",
                ['item' => $item_id, 'category' => (int)$category_id]
            );
        }
    }

    public static function get_all_cat_id_by_item(int $item_id): array
    {
        $query = self::execute("SELECT ic.category FROM item_categories ic WHERE ic.item =:item_id", ["item_id" => $item_id]);
        return $query->fetchAll();
    }

    public static function get_categories_id_per_item(int $item_id): array
    {
        $query = self::execute("SELECT category FROM item_categories WHERE item = :item_id order by category", ['item_id' => $item_id]);
        return $query->fetchAll(PDO::FETCH_COLUMN);
    }

    public static function delete_categories_for_item(int $item_id): void
    {
        self::execute("DELETE FROM item_categories WHERE item = :item_id", ['item_id' => $item_id]);
    }

    public static function item_has_cat(int $item, int $cat): bool
    {
        $cats = self::get_all_cat_id_by_item($item);

        foreach ($cats as $c) {
            if ((int)$c['category'] == $cat)
                return true;
        }

        return false;
    }

    public static function empty_category(int $cat): bool
    {
        $items = self::get_all_items_for_cat($cat);
        return $items != null;
    }

    public static function delete_all_for_item(int $item_id): void
    {
        self::execute("DELETE FROM item_categories WHERE item =:item_id", ["item_id" => $item_id]);
    }

    public static function set_categories_for_item(int $item_id, array $cats): void
    {
        self::delete_all_for_item($item_id);
        foreach ($cats as $cat):
            self::execute("INSERT INTO `item_categories`(`item`, `category`) VALUES (:item_id, :cat)", ["item_id" => $item_id, "cat" => (int)$cat]);
        endforeach;
    }

    public static function get_all_items_for_cat(int $cat): array
    {
        $query = self::execute("select ic.item from item_categories ic where ic.category =:cat", ["cat" => $cat]);
        return $query->fetchAll();
    }
}