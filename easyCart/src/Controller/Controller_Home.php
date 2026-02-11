<?php
namespace App\Controller;

use App\View\View_Home as HomeView;

class Controller_Home
{
    public function indexAction()
    {
        // Logic to fetch featured products, categories, brands?
        // Legacy `index.view.php` seems to have hardcoded links or fetched data?
        // Let's check `index.php` legacy content effectively.
        // Usually `index.php` included `index.view.php`.
        // `index.view.php` logic needs to be checked if it pulled data from DB directly.
        // Assuming it's mostly static or we need to pass data.

        // Fetch Data
        $db = new \App\Database();
        $pdo = $db->getConnection();

        // 1. Featured Products (Limit 8)
        $productCollection = new \App\Model\Product\Model_ProductCollection();
        $productCollection->filterByStock(['instock'])->setPage(1, 8);
        $featuredProducts = $productCollection->getItems();

        // 2. Categories
        $q = new \App\Lib\Query();
        $q->select(['entity_id', 'name'])->from('catalog_category_entity')->limit(8);
        $stmt = $pdo->prepare((string) $q);
        $stmt->execute();
        $categories = $stmt->fetchAll(\PDO::FETCH_KEY_PAIR);

        // 3. Brands
        $q = new \App\Lib\Query();
        $q->select(['entity_id', 'name'])->from('catalog_brand_entity')->limit(8);
        $stmt = $pdo->prepare((string) $q);
        $stmt->execute();
        $rawBrands = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        $brands = [];
        foreach ($rawBrands as $b) {
            $brands[$b['entity_id']] = $b;
        }

        global $isLoggedIn, $cartQuantity, $user;

        $view = new HomeView();
        echo $view->toHtml('index', [
            'pageTitle' => 'EasyCart | Home',
            'currentPage' => 'home',
            'isLoggedIn' => $isLoggedIn,
            'cartQuantity' => $cartQuantity,
            'user' => $user,
            'searchQuery' => '', // Default empty for home
            'featuredProducts' => $featuredProducts,
            'categories' => $categories,
            'brands' => $brands,
            'extraStyles' => ['plp.css'],
            'extraScripts' => ['plp.js', 'main.js'],
        ]);
    }
}
