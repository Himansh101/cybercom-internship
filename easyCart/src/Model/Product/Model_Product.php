<?php
namespace App\Model\Product;

use App\Lib\Query;
use App\Database;
use PDO;

class Model_Product
{
    protected $resource;
    protected $pdo;
    protected $data = [];

    public function __construct()
    {
        $this->resource = new Model_ProductResource();
        $db = new Database();
        $this->pdo = $db->getConnection();
    }

    public function load($id)
    {
        $query = new Query();
        $query->select(['*'])
            ->from($this->resource->tableName)
            ->where("{$this->resource->primaryKey} = ?", $id);

        $stmt = $this->pdo->prepare((string) $query);
        $stmt->execute($query->getBinds());
        $this->data = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($this->data) {
            $this->data['id'] = $this->data['entity_id'];
            $this->loadAttributes($id);
            $this->loadImages($id);

            if (!isset($this->data['stock_count']) || $this->data['stock_count'] === '' || $this->data['stock_count'] === null) {
                $this->data['stock_count'] = (!empty($this->data['in_stock']) && $this->data['in_stock'] == 1) ? 1000 : 0;
            }
        }

        return $this;
    }

    public function loadByUrlKey($urlKey)
    {
        $query = new Query();
        $query->select(['*'])
            ->from($this->resource->tableName)
            ->where("url_key = ?", $urlKey);

        $stmt = $this->pdo->prepare((string) $query);
        $stmt->execute($query->getBinds());
        $this->data = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($this->data) {
            $this->data['id'] = $this->data['entity_id'];
            $this->loadAttributes($this->data[$this->resource->primaryKey]);
            $this->loadImages($this->data[$this->resource->primaryKey]);

            if (!isset($this->data['stock_count']) || $this->data['stock_count'] === '' || $this->data['stock_count'] === null) {
                $this->data['stock_count'] = (!empty($this->data['in_stock']) && $this->data['in_stock'] == 1) ? 1000 : 0;
            }
        }
        return $this;
    }

    private function loadAttributes($id)
    {
        $query = new Query();
        $query->select(['attribute_key', 'attribute_value'])
            ->from('catalog_product_attribute')
            ->where('entity_id = ?', $id);

        $stmt = $this->pdo->prepare((string) $query);
        $stmt->execute($query->getBinds());
        $attrs = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

        foreach ($attrs as $key => $val) {
            $this->data[$key] = $val;
        }
    }

    private function loadImages($id)
    {
        $query = new Query();
        $query->select(['image_url', 'is_main_image'])
            ->from('catalog_product_image')
            ->where('product_id = ?', $id)
            ->orderBy('is_main_image', 'DESC');

        $stmt = $this->pdo->prepare((string) $query);
        $stmt->execute($query->getBinds());
        $images = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $this->data['image'] = $images[0]['image_url'] ?? '';
        $this->data['images'] = array_column($images, 'image_url');
    }

    public function getData($key = null)
    {
        if ($key === null)
            return $this->data;
        return $this->data[$key] ?? null;
    }
}
