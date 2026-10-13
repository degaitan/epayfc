<?php

require_once "framework/Controller.php";
require_once "utils/AppTime.php";
require_once "model/Category.php";
require_once "model/User.php";
require_once "framework/Tools.php";
class ControllerCategory extends Controller
{

    public function index(): void
    {
        if ($this->user_logged()) {
            $this->redirect("item", "browse");
        } else {
            $this->redirect("user", "login");
        }
    }

    public function manage() : void {

        $user = $this->get_user_or_redirect();
        $categories = [];

        if (!$user->isAdmin()){
            $this->redirect("item", "browse");
        }

        $errors = [];

        if($_SERVER['REQUEST_METHOD'] === 'POST'){
            if(isset($_POST['rename'])){
                $errors = $this->rename();
            }elseif(isset($_POST['ask_delete'])){
                $this->ask_delete();
                return;
            }elseif(isset($_POST['delete_confirm'])){
                $this->delete();
            }elseif(isset($_POST['up'])){
                $this->up();
            }elseif (isset($_POST['down'])){
                $this->down();
            }elseif (isset($_POST['add'])){
                $errors = $this->add();
            }
        }

        $categories = Category::get_all_categories_by_priority();

        (new View("manage"))->show([
            "user" => $user,
            "categories" => $categories,
            "errors" => $errors
        ]);

    }

    private function rename() : array
    {
        $id = (int) ($_POST['id'] ?? 0);
        $name = $_POST['name'] ?? '';
        $errorMessages = Category::validate_name($name, $id);


        if (count($errorMessages) === 0) {
            $category = Category::get_category_by_id($id);
            if($category) {
                $category->setName($name);
                $category->rename_category();
            }
            $this->redirect("category", "manage");
            return [];
        }

            return [$id => $errorMessages];
    }

    private function delete() : void
    {
        $id = (int) ($_POST['id'] ?? 0);
        $category = Category::get_category_by_id($id);
        if($category){
            if(Category::is_empty($id)) {
                $category->delete_category();
            }
        }
        $this->redirect("category", "manage");
    }

    private function ask_delete() : void {
        $id = (int) ($_POST['id'] ?? 0);
        $category = Category::get_category_by_id($id);
        if($category){
            if(Category::is_empty($id)) {
                (new View("delete_confirm_category"))->show(["category" => $category]);
                return;
            }
        }
        $this->redirect("category", "manage");

    }

    private function up() : void {
        $this->move(true);
    }

    private function down() : void {
        $this->move(false);
    }

    private function move(bool $up) : void {
        $id = (int) ($_POST['id'] ?? 0);
        $categories = Category::get_all_categories_by_priority();
        $category = Category::get_category_by_id($id);
        if($id !== 0 && $category != null) {
            $neighbour_id = $category->get_neighbour_id($categories, $up, $id);
            if($neighbour_id != null) {
                Category::swap_categories($id, $neighbour_id);
            }
        }
        $this->redirect("category", "manage");
    }

    private function add() : array {
        $name = $_POST['name'] ?? '';
        $errorMessages = Category::validate_name($name);

        if (count($errorMessages) === 0) {
            Category::add_category($name);
            $this->redirect("category", "manage");
            return [];
        }

        //ici pad d'id pour le tableau associatif mais on doit savoir sur quel champs on affiche les erreurs
        return ['new' => $errorMessages];
    }

    public function delete_service() : void {
        header('Content-Type: application/json');

        $user = $this->get_user_or_false();
        if(!$user || !$user->isAdmin()){
            http_response_code(403);
            echo json_encode(['error' => 'You do not have permission to delete this category']);
            return;
        }

        $id = (int) ($_POST['id'] ?? 0);

        if($id === 0) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid page parameter']);
            return;
        }

        $category = Category::get_category_by_id($id);

        if(!$category) {
            http_response_code(404);
            echo json_encode(['error' => 'Category not found']);
            return;
        }

        if(!Category::is_empty($id)) {
            http_response_code(400);
            echo json_encode(['error' => 'Category is not empty.']);
            return;
        }
        $category->delete_category();
        echo json_encode(['success' => true]);
    }

    public function reorder_categories_service() : void {
        header('Content-Type: application/json');

        $user = $this->get_user_or_false();
        if(!$user || !$user->isAdmin()){
            http_response_code(403);
            echo json_encode(['error' => 'You do not have permission to reorder this category']);
            return;
        }
        if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ordered_categories'])) {
            $ordered_categories = $_POST['ordered_categories'];

            if(!is_array($ordered_categories)){
                http_response_code(400);
                echo json_encode(['error' => 'Ordered category must be an array.']);
                return;
            }
            Category::update_priorities($ordered_categories);
            echo json_encode(['success' => true]);
            return;

        }
        http_response_code(400);
        echo json_encode(['error' => 'Bad Request or Unauthorized']);
    }

    public function add_service() : void {
        header('Content-Type: application/json');
        $user = $this->get_user_or_false();
        if(!$user || !$user->isAdmin()){
            http_response_code(403);
            echo json_encode(['error' => 'You do not have permission to add this category']);
            return;
        }
        if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['new_name'])) {
            $new_name = $_POST['new_name'];

            $errors = Category::validate_name($new_name);

            //ici on a besoin de l'id car une fois la catégorie créée il faut que la vue l'affiche sans reload

            if(count($errors) === 0) {
                $new_id = Category::add_category($new_name);
                echo json_encode(['success' => true, 'id' => $new_id, 'name' => $new_name]);
                return;
            }else{
                http_response_code(400);
                echo json_encode(['error' => $errors]);
                return;
            }
        }
        http_response_code(400);
        echo json_encode(['error' => 'Bad Request or Unauthorized']);
    }

    public function check_name_unique() : void {
        header('Content-Type: application/json');
        $user = $this->get_user_or_false();
        if(!$user || !$user->isAdmin()){
            http_response_code(403);
            echo json_encode(['error' => 'You do not have permission to check this category']);
            return;
        }
        if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['new_name'])) {
            $new_name = $_POST['new_name'];

            $exclude_id = $_POST['id'] ?? null;
            $is_valid = Category::is_name_available($new_name, $exclude_id);

            if($is_valid) {
                echo json_encode(['is_valid' => true]);
                return;
            }
            echo json_encode(['is_valid' => false]);
            return;
        }
        http_response_code(400);
        echo json_encode(['error' => 'Bad Request or Unauthorized']);
    }


    public function rename_service(): void
    {
        header('Content-Type: application/json');
        $user = $this->get_user_or_false();
        if (!$user || !$user->isAdmin()) {
            http_response_code(403);
            echo json_encode(['error' => 'You do not have permission to edit this category']);
            return;
        }

        if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['new_name']) && isset($_POST['id'])) {
            $new_name = $_POST['new_name'];
            $id = (int) ($_POST['id']);

            $errors = Category::validate_name($new_name, $id);

            if(count($errors) === 0) {
                $category = Category::get_category_by_id($id);
                $category->setName($new_name); $category->rename_category();
                echo json_encode(['success' => true, 'name' => $new_name]);
                return;
            }else{
                http_response_code(400);
                echo json_encode(['error' => $errors]);
                return;
            }
        }
        http_response_code(400);
        echo json_encode(['error' => 'Bad Request or Unauthorized']);
    }
}