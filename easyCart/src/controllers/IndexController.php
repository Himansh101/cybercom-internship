<?php
namespace App\Controllers;

use App\Models\Product;
use App\Models\Category;
use App\Models\Brand;

class IndexController extends BaseController
{
    public function index()
    {
        // 1. Initialize models
        $productModel = new Product();
        $categoryModel = new Category();
        $brandModel = new Brand();

        // 2. Fetch data
        $pageTitle = 'EasyCart | Home';
        $currentPage = 'home';
        $searchQuery = isset($_GET['search']) ? $_GET['search'] : '';

        $featuredProducts = $productModel->getFeatured(4);
        $categories = $categoryModel->getTop(6);
        $brands = $brandModel->getTop(6);

        $extraStyles = ['plp.css'];
        $extraScripts = ['plp.js'];

        // 3. Render View
        $this->render('index', [
            'pageTitle' => $pageTitle,
            'currentPage' => $currentPage,
            'searchQuery' => $searchQuery,
            'featuredProducts' => $featuredProducts,
            'categories' => $categories,
            'brands' => $brands,
            'extraStyles' => $extraStyles,
            'extraScripts' => $extraScripts
        ]);
    }
}
