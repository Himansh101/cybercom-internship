<?php
namespace App\Models;

use PDO;

class Brand extends BaseModel
{
    public function getTop($limit = 6)
    {
        $stmt = $this->pdo->prepare("SELECT entity_id, name FROM catalog_brand_entity ORDER BY name LIMIT :limit");
        $stmt->bindValue(':limit', (int) $limit, PDO::PARAM_INT);
        $stmt->execute();
        $brandRows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $brands = [];
        foreach ($brandRows as $b) {
            $brands[$b['entity_id']] = ['name' => $b['name']];
        }
        return $brands;
    }
}
