<?php
require_once 'framework/Model.php';
require_once 'framework/Configuration.php';

class ItemPicture extends Model {


    public function __construct(private int $item, private int $priority, private string $picture_path)
    {
    }

    public function getItem(): int
    {
        return $this->item;
    }

    public function setItem(int $item): void
    {
        $this->item = $item;
    }

    public function getPriority(): int
    {
        return $this->priority;
    }

    public function setPriority(int $priority): void
    {
        $this->priority = $priority;
    }

    public function getPicturePath(): string
    {
        return $this->picture_path;
    }

    public function setPicturePath(string $picture_path): void
    {
        $this->picture_path = $picture_path;
    }


    public static function get_by_item(int $itemId): array
    {
        $query = self::execute("SELECT * FROM item_pictures WHERE item = :item ORDER BY priority ASC", ['item' => $itemId]);
        return self::create_pictures_array($query->fetchAll());
    }

    public static function get_path_by_item(int $itemId) : array
    {

        $query = self::execute("SELECT picture_path FROM `item_pictures` WHERE `item` = :item_id",
            ["item_id" => $itemId]);
        $rows = $query->fetchAll();

        $picture_paths = [];

        foreach ($rows as $row){
            if(!empty($row['picture_path'])){
                $picture_paths[] = 'uploads/items/' . $itemId . '/' . basename($row['picture_path']);
            }
        }
        return $picture_paths;

    }


    public static function create_pictures_array($rows): array
    {
        $pictures = [];
        foreach ($rows as $row) {
            $pic = new ItemPicture((int)$row['item'], (int)$row['priority'], $row['picture_path']);
            $pictures[] = $pic;
        }
        return $pictures;
    }

    public static function add_pictures(int $itemId, array $files): void {

        $query = self::execute("SELECT MAX(priority) FROM item_pictures WHERE item = :item", ['item' => $itemId]);
        $max_prio = $query->fetchColumn();
        $next_prio = $max_prio ? $max_prio + 1 : 1;

        $max_size = Configuration::get('max_image_size', 5242880);
        $max_dimensions = Configuration::get('max_image_dimensions', 1080);
        $max_thumbnail = Configuration::get('max_thumbnail_dimensions', 360);

        $count = count($files['name']);
        for ($i = 0; $i < $count; $i++) {
            if ($files['error'][$i] === UPLOAD_ERR_OK) {
                $tmp_name = $files['tmp_name'][$i];
                $ext = strtolower(pathinfo($files['name'][$i], PATHINFO_EXTENSION));

                $image_id = uniqid();
                $new_filename = $image_id . '.jpg';
                $thumbnail = $image_id . "_thumbnail" . '.jpg';
                $upload_dir = 'uploads/items/'.$itemId.'/';
                $picture_path = $upload_dir. $new_filename;
                $thumbnail_path = $upload_dir . $thumbnail;

                if (!is_dir($upload_dir)) {
                    mkdir($upload_dir, 0755, true);
                }

                if (self::resize_and_save($tmp_name, $picture_path, $ext, $max_dimensions, $max_dimensions)) {
                    $query = self::execute("INSERT INTO item_pictures (item, priority, picture_path) VALUES (:item, :prio, :path)",
                        [
                        'item' => $itemId,
                        'prio' => $next_prio++,
                        'path' => $picture_path
                    ]);
                }

                self::resize_and_save($tmp_name, $thumbnail_path, $ext, $max_thumbnail, $max_thumbnail);
            }
        }
    }

    public static function resize_and_save($source, $destination, $ext, $max_width, $max_height): bool {
        list($width, $height) = getimagesize($source);
        $ratio = $width / $height;
        $new_height = $height;
        $new_width = $width;

        if ($width > $max_width) {
            $new_width = $max_width;
            $new_height = $new_width / $ratio;
        }

        if ($height > $max_height) {
            $new_height = $max_height;
            $new_width = $ratio * $new_height;
        }

        $dstImg = imagecreatetruecolor($new_width, $new_height);

        switch($ext) {
            case 'jpg': case 'jpeg': $srcImg = imagecreatefromjpeg($source); break;
            case 'png': $srcImg = imagecreatefrompng($source); break;
            case 'gif': $srcImg = imagecreatefromgif($source); break;
            case 'webp': $srcImg = imagecreatefromwebp($source); break;
            default: return false;
        }

        imagecopyresampled($dstImg, $srcImg, 0, 0, 0, 0, $new_width, $new_height, $width, $height);

        $result = imagejpeg($dstImg, $destination);


        imagedestroy($srcImg);
        imagedestroy($dstImg);

        return $result;
    }

    public static function move_picture(int $itemId, int $priority, string $direction): void {

        $targetPrio = ($direction === 'left') ? $priority - 1 : $priority + 1;

        $query = self::execute("SELECT priority FROM item_pictures WHERE item = :item AND priority = :prio",
            ['item' => $itemId, 'prio' => $targetPrio]);

        if ($query->fetch()) {
            $query = self::execute("UPDATE item_pictures SET priority = -1 WHERE item = :item AND priority = :prio",
                    ['item' => $itemId, 'prio' => $targetPrio]);

            $query = self::execute("UPDATE item_pictures SET priority = :target WHERE item = :item AND priority = :source",
                        ['item' => $itemId, 'target' => $targetPrio, 'source' => $priority]);

            $query = self::execute("UPDATE item_pictures SET priority = :source WHERE item = :item AND priority = -1",
                        ['item' => $itemId, 'source' => $priority]);

        }
    }

    public static function update_priorities(int $itemId, array $ordered_paths): void
    {
        // 1. Temporarily flip all priorities to negative to avoid UNIQUE constraint errors in SQL
        self::execute(
            "UPDATE item_pictures SET priority = -priority WHERE item = :item",
            ['item' => $itemId]
        );

        // 2. Loop through the array and assign new positive priorities (1, 2, 3...)
        $newPriority = 1;
        foreach ($ordered_paths as $path) {
            self::execute(
                "UPDATE item_pictures SET priority = :priority WHERE item = :item AND picture_path = :path",
                [
                    'priority' => $newPriority,
                    'item' => $itemId,
                    'path' => $path
                ]
            );
            $newPriority++;
        }
    }

    public static function delete_picture(int $itemId, int $priority): void {

        $query = self::execute("SELECT picture_path FROM item_pictures WHERE item = :item AND priority = :prio",
            ['item' => $itemId, 'prio' => $priority]);
        $path = $query->fetchColumn();

        if ($path && file_exists($path)) {
            unlink($path);
            $path = explode(".", $path)[0];
            unlink($path."_thumbnail.jpg");
        }

        $query = self::execute("DELETE FROM item_pictures WHERE item = :item AND priority = :prio",
        ['item' => $itemId, 'prio' => $priority]);

        $query = self::execute("UPDATE item_pictures SET priority = priority - 1 WHERE item = :item AND priority > :prio",
                    ['item' => $itemId, 'prio' => $priority]);

    }

    public static function delete_all(int $item): void
    {
        $pictures = self::get_by_item($item);
        foreach ($pictures as $pic) {
            $path = $pic->getPicturePath();
            unlink($path);
            $path = explode(".", $path)[0];
            unlink($path."_thumbnail.jpg");
        }

        self::execute("Delete from item_pictures where item=:item", ["item" => $item]);
    }
}