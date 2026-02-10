<?php
namespace App\Controllers;

use App\Models\Product;
use App\Models\Brand;
use App\Models\Category;

class PdpController extends BaseController
{
    public function index()
    {
        global $pdo, $cartId;

        $slug = $_GET['slug'] ?? $_GET['url_key'] ?? null;

        if ($slug === null) {
            header("Location: plp");
            exit();
        }

        $productModel = new Product();
        $brandModel = new Brand();

        $product = $productModel->findByUrlKey($slug);

        if (!$product) {
            header("Location: plp");
            exit();
        }

        $productId = $product['id'];

        // Fetch display names
        $stmtCat = $pdo->prepare("SELECT name FROM catalog_category_entity WHERE entity_id = (SELECT category_id FROM catalog_category_product WHERE product_id = :id LIMIT 1)");
        $stmtCat->execute([':id' => $productId]);
        $categoryName = $stmtCat->fetchColumn() ?: 'Uncategorized';

        $stmtBrand = $pdo->prepare("SELECT name FROM catalog_brand_entity WHERE entity_id = :b_id");
        $stmtBrand->execute([':b_id' => $product['brand_id']]);
        $brandName = $stmtBrand->fetchColumn() ?: 'Generic';

        // Cart Quantity from DB
        $currentQtyInCart = 0;
        if ($cartId) {
            $stmtQty = $pdo->prepare("SELECT quantity FROM sales_cart_product WHERE cart_id = ? AND product_id = ?");
            $stmtQty->execute([$cartId, $productId]);
            $currentQtyInCart = (int) $stmtQty->fetchColumn();
        }

        $pageTitle = 'EasyCart | Product Detail';
        $currentPage = 'products';
        $extraStyles = ['pdp.css'];
        $extraScripts = ['pdp.js'];

        $this->render('pdp', [
            'product' => $product,
            'productId' => $productId,
            'categoryName' => $categoryName,
            'brandName' => $brandName,
            'currentQtyInCart' => $currentQtyInCart,
            'pageTitle' => $pageTitle,
            'currentPage' => $currentPage,
            'extraStyles' => $extraStyles,
            'extraScripts' => $extraScripts
        ]);
    }
}
