<?php

class Catalog_Block_Product_View extends Core_Block_Template
{
    protected $request;
    protected $base;

    public function __construct()
    {
        parent::__construct();
        $this->setTemplate("Catalog/View/Product/view.phtml");
        $this->request = Sdp::getModel("core/request");
        $this->base = $this->request->getBaseUrl();
    }

    public function _construct()
    {

    }

    public function getProduct()
    {
        $product = Sdp::getModel("catalog/product");
        $product->addData(
            [
                "product_id" => 1,
                "sku" => "DELL-001-LAPTOP",
                "name" => "Dell Inspiron 15 3000",
                "url" => "dell-inspiron-15-3000",

                "price" => 55000,
                "special_price" => 49999,
                "cost" => 45000,
                "tax_class" => "electronics",

                "short_description" => "15.6 inch laptop with Intel i5 processor and 8GB RAM.",
                "description" => "The Dell Inspiron 15 3000 features a powerful Intel Core i5 processor, 8GB DDR4 RAM, 512GB SSD storage, and Windows 11. Perfect for students and professionals.",

                "image" => $this->base."images/products/dell001/main.png",
                "gallery_images" => [
                    $this->base."images/products/dell001/image1.png",
                    $this->base."images/products/dell001/image2.png",
                    $this->base."images/products/dell001/image3.png"
                ],

                "brand" => "Dell",
                "weight" => "1.85kg",
                "color" => "Black",
                "processor" => "Intel Core i5 12th Gen",
                "ram" => "8GB",
                "storage" => "512GB SSD",
                "screen_size" => "15.6 inch",

                "quantity" => 25,
                "stock_status" => "in_stock",
                "visibility" => "catalog,search",

                "rating" => 4.5,
                "review_count" => 124,

                "category_id" => 3,
                "is_featured" => 1,
                "is_new" => 1,

                "meta_title" => "Buy Dell Inspiron 15 3000 Laptop Online",
                "meta_description" => "Shop Dell Inspiron 15 3000 with Intel i5, 8GB RAM, 512GB SSD at best price.",
                "meta_keywords" => "dell laptop, inspiron 15, dell i5 laptop",

                "created_at" => "2026-02-01 10:00:00",
                "updated_at" => "2026-02-15 12:30:00"
            ]
        );

        // echo "<pre>";
        // print_r($product);

        return $product;
    }
}
?>