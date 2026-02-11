<?php
namespace App\Controller;

use App\Database;
use App\Lib\Query;
use App\Model\Product\Model_ProductCollection;
use App\Model\Product\Model_Product;
use App\View\View_Product as ProductView; // Alias to avoid conflict if any

class Controller_Product
{
    public function indexAction() // PLP
    {
        // 1. Input (Filters)
        $filters = [
            'categories' => $_GET['categories'] ?? [],
            'brands' => $_GET['brands'] ?? [],
            'stock_status' => $_GET['stock_status'] ?? [],
            'search' => isset($_GET['search']) ? trim($_GET['search']) : '',
            'min_price' => (isset($_GET['min_price']) && $_GET['min_price'] !== '') ? (int) $_GET['min_price'] : null,
            'max_price' => (isset($_GET['max_price']) && $_GET['max_price'] !== '') ? (int) $_GET['max_price'] : null,
            'sort' => $_GET['sort'] ?? 'newest',
            'page' => (isset($_GET['page']) && $_GET['page'] > 0) ? (int) $_GET['page'] : 1
        ];

        // 2. Logic (Collection)
        $collection = new Model_ProductCollection();
        $collection->filterByCategories($filters['categories'])
            ->filterByBrands($filters['brands'])
            ->filterByStock($filters['stock_status'])
            ->filterBySearch($filters['search'])
            ->filterByPrice($filters['min_price'], $filters['max_price'])
            ->sort($filters['sort']);

        // Pagination
        $perPage = 9;
        $total = $collection->getSize();
        $totalPages = ceil($total / $perPage);
        $page = $filters['page'];

        $collection->setPage($page, $perPage);
        $products = $collection->getItems();

        $startItem = ($page - 1) * $perPage + 1;
        $endItem = min($page * $perPage, $total);

        // 3. View Data Preparation
        $viewData = [
            'paginatedProducts' => $products, // View expects this name
            'categories' => $this->getCategories(),
            'brands' => $this->getBrands(),

            // Filter Data for inputs
            'searchQuery' => $filters['search'],
            'minPrice' => $filters['min_price'] ?? 0,
            'maxPrice' => $filters['max_price'] ?? 1000000,
            'selectedStock' => $filters['stock_status'],
            'selectedCats' => $filters['categories'],
            'selectedBrands' => $filters['brands'],
            'sortBy' => $filters['sort'],

            // Pagination Data
            'pageNumber' => $page,
            'totalPages' => $totalPages,
            'totalVisible' => $total,
            'startItem' => $startItem,
            'endItem' => $endItem,

            // Layout & Assets
            'pageTitle' => 'EasyCart | Shop',
            'currentPage' => 'products',
            'extraStyles' => ['plp.css'],
            'extraScripts' => ['plp.js?v=' . time()], // Cache busted as per previous fix
            'isLoggedIn' => $GLOBALS['isLoggedIn'] ?? false,
            'cartQuantity' => $GLOBALS['cartQuantity'] ?? 0,
            'user' => $GLOBALS['user'] ?? null
        ];

        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
            // AJAX Response
            header('Content-Type: application/json');
            // We need to render partials.
            // The View class should handle this.
            $view = new ProductView();
            echo $view->renderJson($viewData);
            exit;
        }

        // 4. Render
        $view = new ProductView();
        echo $view->toHtml('plp', $viewData);
    }

    public function viewAction() // PDP
    {
        // 1. Input
        $slug = $_GET['slug'] ?? $_GET['url_key'] ?? null;
        if (!$slug) {
            header("Location: plp");
            exit();
        }

        // 2. Logic
        $model = new Model_Product();
        $model->loadByUrlKey($slug);

        $product = $model->getData();
        if (!$product) {
            header("Location: plp");
            exit();
        }

        // Cart Quantity
        $currentQtyInCart = 0;
        global $cartId; // Assuming init.php sets this as per legacy behavior
        if ($cartId) {
            $q = new \App\Lib\Query();
            $q->select(['quantity'])
                ->from('sales_cart_product')
                ->where('cart_id = ?', $cartId)
                ->where('product_id = ?', $product['id']);

            $db = new Database();
            $pdo = $db->getConnection();
            $stmt = $pdo->prepare((string) $q);
            $stmt->execute($q->getBinds());
            $currentQtyInCart = (int) $stmt->fetchColumn();
        }

        // 3. View Data
        $viewData = [
            'product' => $product,
            'productId' => $product['id'],
            'brandName' => $this->getBrand($product['brand_id']),
            'categoryName' => $this->getCategoryByProduct($product['id']),
            'currentQtyInCart' => $currentQtyInCart,
            'pageTitle' => $product['name'] . ' | EasyCart',
            'currentPage' => 'shop',
            'extraStyles' => ['pdp.css'],
            'extraScripts' => ['pdp.js'],
            'isLoggedIn' => $GLOBALS['isLoggedIn'] ?? false,
            'cartQuantity' => $GLOBALS['cartQuantity'] ?? 0,
            'user' => $GLOBALS['user'] ?? null
        ];

        // 4. Render
        $view = new ProductView();
        echo $view->toHtml('pdp', $viewData);
    }

    // Helpers to fetch side data (would be in their own Models in full system)
    private function getCategories()
    {
        // Quick & dirty usage of existing model or raw query via Lib to be compliant
        // Since I haven't refactored Category model yet, I'll use a direct Query.
        $q = new \App\Lib\Query();
        $q->select(['entity_id', 'name'])->from('catalog_category_entity')->limit(100);
        $db = new Database(); // Legacy DB class usage is allowed if wrapped? 
        // User said "Uses the centralized query system only". 
        // So I must execute the query using PDO from Database::getConnection()
        $pdo = $db->getConnection();
        $stmt = $pdo->prepare((string) $q);
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_KEY_PAIR);
    }

    private function getBrands()
    {
        $q = new \App\Lib\Query();
        $q->select(['entity_id', 'name'])->from('catalog_brand_entity')->limit(1000);
        $db = new Database();
        $pdo = $db->getConnection();
        $stmt = $pdo->prepare((string) $q);
        $stmt->execute();
        $res = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        $brands = [];
        foreach ($res as $r)
            $brands[$r['entity_id']] = $r;
        return $brands;
    }

    private function getBrand($id)
    {
        $q = new \App\Lib\Query();
        $q->select(['name'])->from('catalog_brand_entity')->where('entity_id = ?', $id);
        $db = new Database();
        $pdo = $db->getConnection();
        $stmt = $pdo->prepare((string) $q);
        $stmt->execute($q->getBinds());
        return $stmt->fetchColumn() ?: 'Generic';
    }

    private function getCategoryByProduct($id)
    {
        // Subquery logic emulation
        $q = new \App\Lib\Query();
        $q->select(['name'])
            ->from('catalog_category_entity', 'c')
            ->join('catalog_category_product cp', 'c.entity_id = cp.category_id')
            ->where('cp.product_id = ?', $id)
            ->limit(1);
        $db = new Database();
        $pdo = $db->getConnection();
        $stmt = $pdo->prepare((string) $q);
        $stmt->execute($q->getBinds());
        return $stmt->fetchColumn() ?: 'Uncategorized';
    }
}
