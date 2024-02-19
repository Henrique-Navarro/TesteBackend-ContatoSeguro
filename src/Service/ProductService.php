<?php

namespace Contatoseguro\TesteBackend\Service;

use Contatoseguro\TesteBackend\Config\DB;

class ProductService
{
    private \PDO $pdo;
    public function __construct()
    {
        $this->pdo = DB::connect();
    }

    public function getAll($adminUserId, $queryParams)
    {
        $activeOnly = isset($queryParams['activeOnly']) ? filter_var($queryParams['activeOnly'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) : null;
        $categoryId = isset($queryParams['categoryId']) ? filter_var($queryParams['categoryId'], FILTER_VALIDATE_INT, FILTER_NULL_ON_FAILURE) : null;
        $orderByDate = isset($queryParams['orderByDate']) ? filter_var($queryParams['orderByDate'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) : null;

        if ($activeOnly === null && isset($queryParams['activeOnly'])) {
            throw new \InvalidArgumentException('activeOnly must be a boolean');
        }

        if ($categoryId === null && isset($queryParams['categoryId'])) {
            throw new \InvalidArgumentException('categoryId must be an integer');
        }

        if ($orderByDate === null && isset($queryParams['orderByDate'])) {
            throw new \InvalidArgumentException('orderByDate must be a boolean');
        }

        $query = "
            SELECT p.*, c.title as category
            FROM product p
            INNER JOIN product_category pc ON pc.product_id = p.id
            INNER JOIN category c ON c.id = pc.cat_id
            WHERE p.company_id = {$adminUserId}
        ";


        if ($activeOnly) 
            $query .= " AND p.active = 1";
    
        if ($categoryId !== null)
            $query .= " AND c.id = {$categoryId}";
        
        if ($orderByDate) 
            $query .= " ORDER BY p.created_at DESC";
        
        $stm = $this->pdo->prepare($query);

        $stm->execute();

        return $stm;
    }

    public function getOne($id)
    {
        $this->validateProductId($id);

        $stm = $this->pdo->prepare("    
            SELECT *
            FROM product
            WHERE id = {$id}
        ");

        $stm->execute();

        return $stm;
    }

    public function insertOne($body, $adminUserId)
    {
        $this->validateProductBody($body);

        $stmCategoryCheck = $this->pdo->prepare
        ("SELECT id FROM category WHERE id = {$body ['category_id']}");
        $stmCategoryCheck->execute();

        if (!$stmCategoryCheck->fetch()) 
            throw new \InvalidArgumentException("Category does not exist");

        $stm = $this->pdo->prepare("
            INSERT INTO product (
                company_id,
                title,
                price,
                active
            ) VALUES (
                {$body['company_id']},
                '{$body['title']}',
                {$body['price']},
                {$body['active']}   
            )
        "); 

        if (!$stm->execute())
            throw new \RuntimeException("Failed to insert product");

        $productId = $this->pdo->lastInsertId();

        $stm = $this->pdo->prepare("
            INSERT INTO product_category (
                product_id,
                cat_id
            ) VALUES (
                {$productId},
                {$body['category_id']}
            );
        ");

        if (!$stm->execute())
            return false;

        $stm = $this->pdo->prepare("
            INSERT INTO product_log (
                product_id,
                admin_user_id,
                `action`
            ) VALUES (
                {$productId},
                {$adminUserId},
                'create'
            )
        ");

        return $stm->execute();
    }

    public function updateOne($id, $body, $adminUserId)
    {
        $this->validateProductBody($body);

        $this->validateProductId($id);

        $stm = $this->pdo->prepare("
            UPDATE product
            SET company_id = {$body['company_id']},
                title = '{$body['title']}',
                price = {$body['price']},
                active = {$body['active']}
            WHERE id = {$id}
        ");

        if (!$stm->execute()) 
            throw new \RuntimeException("Failed to update product");

        if($stm->rowCount() === 0)
            return false;

        $stm = $this->pdo->prepare("
            UPDATE product_category
            SET cat_id = {$body['category_id']}
            WHERE product_id = {$id}
        ");
    
        if (!$stm->execute()) {
            return false;
        }
    
        $stm = $this->pdo->prepare("
            INSERT INTO product_log (
                product_id,
                admin_user_id,
                `action`
            ) VALUES (
                {$id},
                {$adminUserId},
                'update'
            )
        ");
    
        return $stm->execute();
    }

    public function deleteOne($id, $adminUserId)
    {
        $this->validateProductId($id);

        $stm = $this->pdo->prepare("
            DELETE FROM product_category 
            WHERE product_id = {$id}
        ");
        
        if (!$stm->execute()) 
            throw new \RuntimeException("Failed to delete product");

        if($stm->rowCount() === 0)
            return false;

        $stm = $this->pdo->prepare("
            DELETE FROM product 
            WHERE id = {$id}
        ");
        
        if (!$stm->execute())
            return false;

        $stm = $this->pdo->prepare("
            INSERT INTO product_log (
                product_id,
                admin_user_id,
                `action`
            ) VALUES (
                {$id},
                {$adminUserId},
                'delete'
            )
        ");

        return $stm->execute();
    }

    public function getLog($id)
    {
        $stm = $this->pdo->prepare("
            SELECT *
            FROM product_log
            WHERE product_id = {$id}
            ORDER BY timestamp DESC
        ");

        $stm->execute();

        return $stm;
    }

    private function validateProductBody($body)
    {
        $requiredAttributes = ['company_id', 'title', 'price', 'active', 'category_id'];
        
        foreach ($requiredAttributes as $attribute) {
            if (!isset($body[$attribute])) {
                throw new \InvalidArgumentException("Missing required attribute: {$attribute}");
            }
        }
        
        if (!is_int($body['company_id']) || !is_string($body['title']) || !is_numeric($body['price']) || !is_bool($body['active']) || !is_numeric($body['category_id'])) {
            throw new \InvalidArgumentException("Invalid attribute types in the body");
        }
    }

    private function validateProductId($id)
    {
        if (!ctype_digit(strval($id)) || $id <= 0) {
            throw new \InvalidArgumentException("Invalid product ID");
        }
    }

    public function validateAdminUserId($adminUserId){
        if (!ctype_digit(strval($adminUserId)) || $adminUserId <= 0) {
            throw new \InvalidArgumentException("Invalid admin user ID");
        }
        return $adminUserId;
    }
}
