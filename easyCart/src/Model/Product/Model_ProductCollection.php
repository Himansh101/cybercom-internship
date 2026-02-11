<?php
namespace App\Model\Product;

use App\Lib\Query;
use App\Database;
use PDO;

class Model_ProductCollection
{
    protected $resource;
    protected $pdo;
    protected $query;
    protected $items = [];

    public function __construct()
    {
        $this->resource = new Model_ProductResource();
        $db = new Database();
        $this->pdo = $db->getConnection();
        $this->query = new Query();
        $this->query->select([
            'p.*',
            "(SELECT category_id FROM catalog_category_product WHERE product_id = p.entity_id LIMIT 1) as cat_id",
            "(SELECT attribute_value FROM catalog_product_attribute WHERE entity_id = p.entity_id AND attribute_key = 'brand_id' LIMIT 1) as brand_id",
            "(SELECT attribute_value FROM catalog_product_attribute WHERE entity_id = p.entity_id AND attribute_key = 'in_stock' LIMIT 1) as in_stock",
            "(SELECT attribute_value FROM catalog_product_attribute WHERE entity_id = p.entity_id AND attribute_key = 'shipping_type' LIMIT 1) as item_shipping_type",
            "(SELECT image_url FROM catalog_product_image WHERE product_id = p.entity_id AND is_main_image = true LIMIT 1) as image"
        ])->from($this->resource->tableName, 'p');
    }

    public function filterByCategories($categories)
    {
        if (empty($categories))
            return $this;
        $placeholders = array_fill(0, count($categories), '?');
        $this->query->where('p.entity_id IN (SELECT product_id FROM catalog_category_product WHERE category_id IN (' . implode(',', $placeholders) . '))', $categories);
        return $this;
    }

    public function filterByBrands($brands)
    {
        if (empty($brands))
            return $this;
        $placeholders = array_fill(0, count($brands), '?');
        $this->query->where('p.entity_id IN (SELECT entity_id FROM catalog_product_attribute WHERE attribute_key = \'brand_id\' AND attribute_value IN (' . implode(',', $placeholders) . '))', $brands);
        return $this;
    }

    public function filterBySearch($search)
    {
        if (empty($search))
            return $this;
        $this->query->where("(p.name LIKE ? OR p.sku LIKE ?)", ["%$search%", "%$search%"]);
        return $this;
    }

    public function filterByPrice($min, $max)
    {
        if ($min !== null)
            $this->query->where('p.price >= ?', $min);
        if ($max !== null)
            $this->query->where('p.price <= ?', $max);
        return $this;
    }

    public function sort($sort)
    {
        switch ($sort) {
            case 'price_low':
                $this->query->orderBy('p.price', 'ASC');
                break;
            case 'price_high':
                $this->query->orderBy('p.price', 'DESC');
                break;
            case 'newest':
                $this->query->orderBy('p.entity_id', 'DESC');
                break;
            case 'name_asc':
                $this->query->orderBy('p.name', 'ASC');
                break;
            case 'name_desc':
                $this->query->orderBy('p.name', 'DESC');
                break;
            default:
                $this->query->orderBy('p.entity_id', 'DESC');
        }
        return $this;
    }

    public function getSize()
    {
        $countQuery = clone $this->query;
        $countQuery->select(['COUNT(*)'])
            ->resetOrder()
            ->resetLimit()
            ->resetOffset();

        $stmt = $this->pdo->prepare((string) $countQuery);
        $stmt->execute($countQuery->getBinds());
        return (int) $stmt->fetchColumn();
    }

    public function filterByStock($stocks)
    {
        if (empty($stocks))
            return $this;
        $conditions = [];
        foreach ($stocks as $s) {
            if ($s == 'instock') {
                $conditions[] = "EXISTS (SELECT 1 FROM catalog_product_attribute WHERE entity_id = p.entity_id AND attribute_key = 'in_stock' AND attribute_value = '1')";
            } elseif ($s == 'outofstock') {
                $conditions[] = "EXISTS (SELECT 1 FROM catalog_product_attribute WHERE entity_id = p.entity_id AND attribute_key = 'in_stock' AND attribute_value = '0')";
            }
        }
        if (!empty($conditions)) {
            $this->query->where('(' . implode(' OR ', $conditions) . ')');
        }
        return $this;
    }

    public function setPage($page, $pageSize)
    {
        $offset = ($page - 1) * $pageSize;
        $this->query->limit($pageSize)->offset($offset);
        return $this;
    }

    public function getItems()
    {
        $stmt = $this->pdo->prepare((string) $this->query);
        $stmt->execute($this->query->getBinds());
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
