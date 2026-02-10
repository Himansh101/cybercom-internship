<?php
namespace App\Models;

use PDO;

class Customer extends BaseModel
{
    public function find($id)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM customer_entity WHERE entity_id = :id");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function findByEmail($email)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM customer_entity WHERE email = :email");
        $stmt->execute([':email' => $email]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function exists($email)
    {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM customer_entity WHERE email = :email");
        $stmt->execute([':email' => $email]);
        return $stmt->fetchColumn() > 0;
    }

    public function create($data)
    {
        $stmt = $this->pdo->prepare("INSERT INTO customer_entity (name, email, mobile, password, created_at) VALUES (:name, :email, :mobile, :password, :created)");
        return $stmt->execute([
            ':name' => $data['name'],
            ':email' => $data['email'],
            ':mobile' => $data['mobile'],
            ':password' => $data['password'],
            ':created' => date('Y-m-d H:i:s')
        ]);
    }
}
