<?php
namespace App\Controllers;

use App\Models\Product;
use App\Models\Category;
use App\Models\Brand;

class PlpController extends BaseController
{
    public function index()
    {
        $productModel = new Product();
        $categoryModel = new Category();
        $brandModel = new Brand();

        // 1. Initialize variables from GET parameters
        $filters = [
            'categories' => $_GET['categories'] ?? [],
            'brands' => $_GET['brands'] ?? [],
            'stock_status' => $_GET['stock_status'] ?? [],
            'search' => isset($_GET['search']) ? trim($_GET['search']) : '',
            'min_price' => (isset($_GET['min_price']) && $_GET['min_price'] !== '') ? (int) $_GET['min_price'] : 0,
            'max_price' => (isset($_GET['max_price']) && $_GET['max_price'] !== '') ? (int) $_GET['max_price'] : 1000000,
            'sort' => $_GET['sort'] ?? 'newest'
        ];

        $pageNumber = (isset($_GET['page']) && $_GET['page'] > 0) ? (int) $_GET['page'] : 1;
        $productsPerPage = 9;
        $offset = ($pageNumber - 1) * $productsPerPage;

        // 2. Fetch data
        $categories = $categoryModel->getTop(100); // Fetch all for filter
        $brands = $brandModel->getTop(100);

        $result = $productModel->getFiltered($filters, $productsPerPage, $offset);
        $paginatedProducts = $result['products'];
        $totalVisible = $result['total'];

        // 3. Pagination Calculation
        $totalPages = ceil($totalVisible / $productsPerPage);
        $startItem = $totalVisible > 0 ? $offset + 1 : 0;
        $endItem = min($pageNumber * $productsPerPage, $totalVisible);

        $pageTitle = 'EasyCart | Shop';
        $currentPage = 'products';
        $extraStyles = ['plp.css'];
        $extraScripts = ['plp.js?v=' . time()];

        // 4. Handle AJAX Response
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
            $this->render('plp', [
                'categories' => $categories,
                'brands' => $brands,
                'paginatedProducts' => $paginatedProducts,
                'totalVisible' => $totalVisible,
                'totalPages' => $totalPages,
                'pageNumber' => $pageNumber,
                'startItem' => $startItem,
                'endItem' => $endItem,
                'searchQuery' => $filters['search'],
                'selectedCats' => $filters['categories'],
                'selectedBrands' => $filters['brands'],
                'selectedStock' => $filters['stock_status'],
                'minPrice' => $filters['min_price'],
                'maxPrice' => $filters['max_price'],
                'sortBy' => $filters['sort'],
                'pageTitle' => $pageTitle,
                'currentPage' => $currentPage,
                'extraStyles' => $extraStyles,
                'extraScripts' => $extraScripts
            ]);
            exit;
        }

        $this->render('plp', [
            'categories' => $categories,
            'brands' => $brands,
            'paginatedProducts' => $paginatedProducts,
            'totalVisible' => $totalVisible,
            'totalPages' => $totalPages,
            'pageNumber' => $pageNumber,
            'startItem' => $startItem,
            'endItem' => $endItem,
            'searchQuery' => $filters['search'],
            'selectedCats' => $filters['categories'],
            'selectedBrands' => $filters['brands'],
            'selectedStock' => $filters['stock_status'],
            'minPrice' => $filters['min_price'],
            'maxPrice' => $filters['max_price'],
            'sortBy' => $filters['sort'],
            'pageTitle' => $pageTitle,
            'currentPage' => $currentPage,
            'extraStyles' => $extraStyles,
            'extraScripts' => $extraScripts
        ]);
    }
}
