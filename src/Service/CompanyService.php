<?php

namespace Contatoseguro\TesteBackend\Service;

use Contatoseguro\TesteBackend\Config\DB;

class CompanyService
{
    private \PDO $pdo;
    public function __construct()
    {
        $this->pdo = DB::connect();
    }

    public function getAll()
    {
        $stm = $this->pdo->prepare("
            SELECT *    
            FROM company
        ");

        if (!$stm->execute())
            throw new \RuntimeException("Failed to retrieve companies.");

        return $stm;
    }

    public function getOne($id)
    {
        $companyId = $this->validateCompanyId($id);

        $stm = $this->pdo->prepare("
            SELECT *
            FROM company
            WHERE id = {$id}
        ");

        if (!$stm->execute())
            throw new \RuntimeException("Failed to retrieve company.");

        return $stm;
    }

    public function getNameById($id)
    {
        $stm = $this->pdo->prepare("
            SELECT name 
            FROM company
            WHERE id = {$id}
        ");

        $stm->execute();
        
        return $stm;
    }

    private function validateCompanyId($companyId){
        if (!ctype_digit(strval($companyId)) || $companyId <= 0) {
            throw new \InvalidArgumentException("Invalid company ID");
        }
        return $companyId;
    }
}
