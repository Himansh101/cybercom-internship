<?php
namespace App\Models;

use PDO;

class Category extends BaseModel
{
    public function getTop($limit = 6)
    {
        $stmt = $this->pdo->prepare("SELECT entity_id, name FROM catalog_category_entity ORDER BY name LIMIT :limit");
        $stmt->bindValue(':limit', (int) $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
    }
}
