<?php

namespace Contatoseguro\TesteBackend\Service;

use Contatoseguro\TesteBackend\Config\DB;

class CategoryService
{
    private \PDO $pdo;
    public function __construct()
    {
        $this->pdo = DB::connect();
    }

    public function getAll($adminUserId)
    {
        $query = "
            SELECT *
            FROM category c
            WHERE (c.company_id = {$this->getCompanyFromAdminUser($adminUserId)}
            OR c.company_id IS NULL)
        ";

        $stm = $this->pdo->prepare($query);
        
        if (!$stm->execute())
            throw new \RuntimeException("Failed to retrieve categories.");

        return $stm;
    }

    public function getOne($adminUserId, $categoryId)
    {
        $this->validateCategoryId($categoryId);

        $query = "
            SELECT *
            FROM category c
            WHERE c.active = 1
            AND (c.company_id = {$this->getCompanyFromAdminUser($adminUserId)}
            OR c.company_id IS NULL) 
            AND c.id = {$categoryId}
        ";

        $stm = $this->pdo->prepare($query);

        $stm->execute();

        return $stm;
    }   

    public function getProductCategory($productId)
    {
        $query = "
            SELECT c.*
            FROM category c
            INNER JOIN product_category pc
                ON pc.cat_id = c.id
            WHERE pc.product_id = {$productId}
        ";

        $stm = $this->pdo->prepare($query);

        if (!$stm->execute())
            throw new \RuntimeException("Failed to retrieve categories from product");
        
        return $stm;
    }

    public function insertOne($body, $adminUserId)
    {
        $this->validateCategoryBody($body);

        $stm = $this->pdo->prepare("
            INSERT INTO category (
                company_id,
                title,
                active
            ) VALUES (
                {$this->getCompanyFromAdminUser($adminUserId)},
                '{$body['title']}',
                {$body['active']}
            )
        ");

        if (!$stm->execute())
            throw new \RuntimeException("Failed to insert category");

        return $stm;
    }

    public function updateOne($id, $body, $adminUserId)
    {
        $this->validateCategoryBody($body);

        $this->validateCategoryId($id);

        $active = (int)$body['active'];

        $stm = $this->pdo->prepare("
            UPDATE category
            SET title = '{$body['title']}', 
                active = {$active}
            WHERE id = {$id}
            AND (company_id = {$this->getCompanyFromAdminUser($adminUserId)} 
            OR company_id IS NULL)
        ");

        if (!$stm->execute()) 
            throw new \RuntimeException("Failed to update category");

        if($stm->rowCount() === 0)
            return false;

        return $stm->execute();
    }

    public function deleteOne($id, $adminUserId)
    {
        $this->validateCategoryId($id);

        $stm = $this->pdo->prepare("
            DELETE
            FROM category
            WHERE id = {$id}
            AND (company_id = {$this->getCompanyFromAdminUser($adminUserId)}
            OR company_id IS NULL)
        ");

        if (!$stm->execute()) 
            throw new \RuntimeException("Failed to delete category");

        if($stm->rowCount() === 0)
            return false;

        return $stm->execute();
    }

    private function getCompanyFromAdminUser($adminUserId)
    {
        $query = "
            SELECT company_id
            FROM admin_user
            WHERE id = {$adminUserId}
        ";

        $stm = $this->pdo->prepare($query);

        $stm->execute();

        return $stm->fetch()->company_id;
    }

    private function validateCategoryBody($body)
    {
        $requiredAttributes = ['title', 'active'];
    
        foreach ($requiredAttributes as $attribute) {
            if (!isset($body[$attribute])) {
                throw new \InvalidArgumentException("Missing required attribute: {$attribute}");
            }
        }
    
        if (!is_string($body['title']) || !is_bool($body['active'])) {
            throw new \InvalidArgumentException("Invalid attribute types in the body");
        }
    }

    private function validateCategoryId($id)
    {
        if (!ctype_digit(strval($id)) || $id <= 0) {
            throw new \InvalidArgumentException("Invalid category ID");
        }
    }

    public function validateAdminUserId($adminUserId){
        if (!ctype_digit(strval($adminUserId)) || $adminUserId <= 0) {
            throw new \InvalidArgumentException("Invalid admin user ID");
        }
        return $adminUserId;
    }
}
