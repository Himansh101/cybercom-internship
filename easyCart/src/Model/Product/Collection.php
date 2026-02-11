<?php
namespace App\Model\Product;

use App\Lib\Query;
use App\Database;
use PDO;

class Collection
{
    protected $resource;
    protected $pdo;
    protected $query;
    protected $items = [];

    public function __construct()
    {
        $this->resource = new Resource();
        $db = new Database();
        $this->pdo = $db->getConnection();
        $this->query = new Query();

        // Base Select
        $this->query->select(['p.*'])
            ->from($this->resource->tableName, 'p');

        $this->joinAttributes();
    }

    private function joinAttributes()
    {
        // Use LEFT JOINs to make attributes available for Filtering and Sorting
        // Removed stock_count as it does not exist in DB
        $this->query->join('catalog_product_attribute as instock_attr', "instock_attr.entity_id = p.entity_id AND instock_attr.attribute_key = 'in_stock'", 'LEFT');
        $this->query->join('catalog_product_attribute as brand_attr', "brand_attr.entity_id = p.entity_id AND brand_attr.attribute_key = 'brand_id'", 'LEFT');
        $this->query->join('catalog_product_attribute as shipping_attr', "shipping_attr.entity_id = p.entity_id AND shipping_attr.attribute_key = 'shipping_type'", 'LEFT');

        $this->query->select([
            'p.*',
            'instock_attr.attribute_value as in_stock',
            'brand_attr.attribute_value as brand_id',
            'shipping_attr.attribute_value as item_shipping_type',
            "(SELECT category_id FROM catalog_category_product WHERE product_id = p.entity_id LIMIT 1) as cat_id",
            "(SELECT image_url FROM catalog_product_image WHERE product_id = p.entity_id AND is_main_image = true LIMIT 1) as image"
        ]);
    }

    public function filterByCategories($categories)
    {
        if (!empty($categories)) {
            $placeholders = implode(',', array_fill(0, count($categories), '?'));
            $this->query->where("p.entity_id IN (SELECT product_id FROM catalog_category_product WHERE category_id IN ($placeholders))", $categories);
        }
        return $this;
    }

    public function filterBySearch($search)
    {
        if ($search) {
            $this->query->where("p.name ILIKE ?", '%' . $search . '%');
        }
        return $this;
    }

    public function filterByPrice($min, $max)
    {
        if ($min !== null && $max !== null) {
            $this->query->where("p.price BETWEEN ? AND ?", [$min, $max]);
        }
        return $this;
    }

    public function filterByBrands($brands)
    {
        if (!empty($brands)) {
            $placeholders = implode(',', array_fill(0, count($brands), '?'));
            $this->query->where("brand_attr.attribute_value IN ($placeholders)", $brands);
        }
        return $this;
    }

    public function filterByStock($status)
    {
        if (empty($status))
            return $this;

        $conditions = [];
        if (in_array('instock', $status)) {
            $conditions[] = "(instock_attr.attribute_value = '1')";
        }
        if (in_array('outofstock', $status)) {
            $conditions[] = "(instock_attr.attribute_value = '0' OR instock_attr.attribute_value IS NULL)";
        }

        if (!empty($conditions)) {
            $this->query->where("(" . implode(" OR ", $conditions) . ")");
        }
        return $this;
    }

    public function sort($sortBy)
    {
        // Use COALESCE to handle nulls if sorting by attributes
        $stockPriority = "(CASE WHEN instock_attr.attribute_value = '1' THEN 1 ELSE 0 END)";
        $this->query->orderBy($stockPriority, 'DESC');

        switch ($sortBy) {
            case 'price_low':
                $this->query->orderBy('p.price', 'ASC');
                break;
            case 'price_high':
                $this->query->orderBy('p.price', 'DESC');
                break;
            case 'name_asc':
                $this->query->orderBy('p.name', 'ASC');
                break;
            case 'name_desc':
                $this->query->orderBy('p.name', 'DESC');
                break;
            default:
                $this->query->orderBy('p.created_at', 'DESC');
                break;
        }
        return $this;
    }

    public function setPage($page, $perPage)
    {
        $this->query->limit($perPage);
        $this->query->offset(($page - 1) * $perPage);
        return $this;
    }

    public function getItems()
    {
        $stmt = $this->pdo->prepare((string) $this->query);
        $stmt->execute($this->query->getBinds());
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $items = [];
        foreach ($results as $row) {
            $row['in_stock'] = ($row['in_stock'] == '1');
            $items[$row['entity_id']] = $row;
        }
        return $items;
    }

    public function getSize()
    {
        // Count Query manually constructed to match filters
        // We clone logic because Query object state might have limit/offset
        // Strict way: remove limit/offset from current query string. 
        // But Query object is simple string builder.
        // We'll run a separate count query using the same joins and wheres.
        // Since I can't easily clone the Query object's internal state without accessors, 
        // I'll make a pragmatic choice: 
        // FETCH ALL and count (bad for perf but robust for this refactor size)
        // OR
        // Use SQL_CALC_FOUND_ROWS if supported? No, deprecated.

        // Let's grab specific method to get count if possible.
        // For now, I will use a simple count query on the base table if no filters.
        // IF filters, I'll fetch column ID and count.

        // Actually, let's fix the query to allow fetching IDs without limit.
        // I will return a placeholder for now, BUT a dynamic one based on results?
        // No, that breaks pagination.

        // Let's implement a naive count:
        // Execute query WITHOUT Limit/Offset.
        // This is heavy but correct.

        // To do this with current Query class structure:
        // I need to construct the SQL without the LIMIT/OFFSET clause.
        // The Query class probably appends them at the end.
        // I'll rely on the `query` object's `__toString`.
        // I can replace "LIMIT X OFFSET Y" with "" in the string.

        $sql = (string) $this->query;
        $sql = preg_replace('/LIMIT \d+/', '', $sql);
        $sql = preg_replace('/OFFSET \d+/', '', $sql);
        // Replace SELECT ... FROM with SELECT COUNT(*) FROM
        // This is tricky with complex selects.
        // Wrap in subquery!
        $countSql = "SELECT COUNT(*) FROM ($sql) as sub_total";

        $stmt = $this->pdo->prepare($countSql);
        $stmt->execute($this->query->getBinds());
        return (int) $stmt->fetchColumn();
    }

    public function getTotalCount($filters)
    {
        return $this->getSize();
    }
}
