<?php
namespace App\Models;

use PDO;

class Product extends BaseModel
{
    public function getFeatured($limit = 4)
    {
        $stmt = $this->pdo->prepare("SELECT p.*, i.image_url as image,
            (SELECT attribute_value FROM catalog_product_attribute WHERE entity_id = p.entity_id AND attribute_key = 'in_stock') as in_stock,
            (SELECT attribute_value FROM catalog_product_attribute WHERE entity_id = p.entity_id AND attribute_key = 'shipping_type') as shipping_type,
            (SELECT category_id FROM catalog_category_product WHERE product_id = p.entity_id LIMIT 1) as cat_id
            FROM catalog_product_entity p
            LEFT JOIN catalog_product_image i ON p.entity_id = i.product_id AND i.is_main_image = true
            WHERE EXISTS (SELECT 1 FROM catalog_product_attribute WHERE entity_id = p.entity_id AND attribute_key = 'is_featured' AND attribute_value = '1')
            ORDER BY (CASE WHEN p.stock_count > 0 THEN 1 ELSE 0 END) DESC
            LIMIT :limit");

        $stmt->bindValue(':limit', (int) $limit, PDO::PARAM_INT);
        $stmt->execute();
        $dbFeatured = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $featuredProducts = [];
        foreach ($dbFeatured as $row) {
            $featuredProducts[$row['entity_id']] = [
                'id' => $row['entity_id'],
                'name' => $row['name'],
                'price' => $row['price'],
                'image' => $row['image'],
                'in_stock' => ($row['in_stock'] === '1' && (int) $row['stock_count'] > 0),
                'cat_id' => $row['cat_id'],
                'item_shipping_type' => $row['shipping_type']
            ];
        }
        return $featuredProducts;
    }

    public function findById($id)
    {
        $stmt = $this->pdo->prepare("SELECT p.*, 
            (SELECT attribute_value FROM catalog_product_attribute WHERE entity_id = p.entity_id AND attribute_key = 'description') as description,
            (SELECT attribute_value FROM catalog_product_attribute WHERE entity_id = p.entity_id AND attribute_key = 'in_stock') as in_stock,
            (SELECT attribute_value FROM catalog_product_attribute WHERE entity_id = p.entity_id AND attribute_key = 'brand_id') as brand_id,
            (SELECT attribute_value FROM catalog_product_attribute WHERE entity_id = p.entity_id AND attribute_key = 'shipping_type') as shipping_type
            FROM catalog_product_entity p WHERE p.entity_id = :id");
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row)
            return null;

        // Fetch images
        $stmtImg = $this->pdo->prepare("SELECT image_url, is_main_image FROM catalog_product_image WHERE product_id = :id ORDER BY is_main_image DESC");
        $stmtImg->execute([':id' => $id]);
        $dbImages = $stmtImg->fetchAll(PDO::FETCH_ASSOC);

        return [
            'id' => $row['entity_id'],
            'name' => $row['name'],
            'price' => $row['price'],
            'image' => $dbImages[0]['image_url'] ?? '',
            'images' => array_map(fn($i) => $i['image_url'], $dbImages),
            'description' => $row['description'],
            'in_stock' => ($row['in_stock'] === '1'),
            'stock_count' => (int) $row['stock_count'],
            'brand_id' => $row['brand_id'],
            'item_shipping_type' => $row['shipping_type']
        ];
    }

    public function getFiltered($filters = [], $limit = 9, $offset = 0)
    {
        $whereClauses = ["1=1"];
        $params = [];

        if (!empty($filters['categories'])) {
            $placeholders = implode(',', array_fill(0, count($filters['categories']), '?'));
            $whereClauses[] = "p.entity_id IN (SELECT product_id FROM catalog_category_product WHERE category_id IN ($placeholders))";
            foreach ($filters['categories'] as $cat)
                $params[] = (int) $cat;
        }

        if (!empty($filters['search'])) {
            $whereClauses[] = "p.name ILIKE ?";
            $params[] = '%' . $filters['search'] . '%';
        }

        if (isset($filters['min_price'], $filters['max_price'])) {
            $whereClauses[] = "p.price BETWEEN ? AND ?";
            $params[] = $filters['min_price'];
            $params[] = $filters['max_price'];
        }

        if (!empty($filters['brands'])) {
            $placeholders = implode(',', array_fill(0, count($filters['brands']), '?'));
            $whereClauses[] = "p.entity_id IN (SELECT entity_id FROM catalog_product_attribute WHERE attribute_key = 'brand_id' AND attribute_value IN ($placeholders))";
            foreach ($filters['brands'] as $brandId)
                $params[] = $brandId;
        }

        if (!empty($filters['stock_status'])) {
            $subConditions = [];
            if (in_array('instock', $filters['stock_status'])) {
                $subConditions[] = "(p.entity_id IN (SELECT entity_id FROM catalog_product_attribute WHERE attribute_key = 'in_stock' AND attribute_value = '1') AND p.stock_count > 0)";
            }
            if (in_array('outofstock', $filters['stock_status'])) {
                $subConditions[] = "(p.entity_id IN (SELECT entity_id FROM catalog_product_attribute WHERE attribute_key = 'in_stock' AND attribute_value = '0') OR p.stock_count <= 0)";
            }
            if (!empty($subConditions)) {
                $whereClauses[] = "(" . implode(" OR ", $subConditions) . ")";
            }
        }

        $sortBy = $filters['sort'] ?? 'newest';
        $stockPrioritySql = "(CASE WHEN p.stock_count > 0 THEN 1 ELSE 0 END) DESC";
        switch ($sortBy) {
            case 'price_low':
                $sortSql = "p.price ASC";
                break;
            case 'price_high':
                $sortSql = "p.price DESC";
                break;
            case 'name_asc':
                $sortSql = "p.name ASC";
                break;
            case 'name_desc':
                $sortSql = "p.name DESC";
                break;
            default:
                $sortSql = "p.created_at DESC";
                break;
        }

        $sql = "SELECT p.*, i.image_url as image, 
            (SELECT attribute_value FROM catalog_product_attribute WHERE entity_id = p.entity_id AND attribute_key = 'in_stock') as in_stock,
            (SELECT attribute_value FROM catalog_product_attribute WHERE entity_id = p.entity_id AND attribute_key = 'shipping_type') as shipping_type,
            (SELECT attribute_value FROM catalog_product_attribute WHERE entity_id = p.entity_id AND attribute_key = 'brand_id') as brand_id,
            (SELECT attribute_value FROM catalog_product_attribute WHERE entity_id = p.entity_id AND attribute_key = 'shipping_type') as shipping_type,
            (SELECT category_id FROM catalog_category_product WHERE product_id = p.entity_id LIMIT 1) as cat_id
            FROM catalog_product_entity p
            LEFT JOIN catalog_product_image i ON p.entity_id = i.product_id AND i.is_main_image = true
            WHERE " . implode(" AND ", $whereClauses) . "
            ORDER BY $stockPrioritySql, $sortSql
            LIMIT ? OFFSET ?";

        $stmt = $this->pdo->prepare($sql);
        $paramIndex = 1;
        foreach ($params as $val) {
            $stmt->bindValue($paramIndex++, $val);
        }
        $stmt->bindValue($paramIndex++, (int) $limit, PDO::PARAM_INT);
        $stmt->bindValue($paramIndex++, (int) $offset, PDO::PARAM_INT);
        $stmt->execute();

        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $products = [];
        foreach ($results as $row) {
            $products[$row['entity_id']] = [
                'id' => $row['entity_id'],
                'name' => $row['name'],
                'price' => $row['price'],
                'image' => $row['image'],
                'in_stock' => ($row['in_stock'] === '1' && (int) $row['stock_count'] > 0),
                'brand_id' => $row['brand_id'],
                'cat_id' => $row['cat_id'],
                'item_shipping_type' => $row['shipping_type']
            ];
        }

        // Also get total count for pagination
        $countSql = "SELECT COUNT(*) FROM catalog_product_entity p WHERE " . implode(" AND ", $whereClauses);
        $stmtCount = $this->pdo->prepare($countSql);
        $stmtCount->execute($params);
        $total = (int) $stmtCount->fetchColumn();

        return ['products' => $products, 'total' => $total];
    }
}
