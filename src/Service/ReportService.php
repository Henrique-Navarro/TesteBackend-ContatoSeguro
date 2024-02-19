<?php

namespace Contatoseguro\TesteBackend\Service;

use DateTime;
    
class ReportService
{
    private ProductService $productService;
    private CompanyService $companyService;

    public function __construct()
    {
        $this->productService = new ProductService();
        $this->companyService = new CompanyService();
    }

    public function generate($adminUserId, $queryParams)
    {
        $productId = isset($queryParams['productId']) ? filter_var($queryParams['productId'], FILTER_VALIDATE_INT, FILTER_NULL_ON_FAILURE) : null;
    
        if($productId)
            $stm = $this->productService->getOne($productId);

        else
            $stm = $this->productService->getAll($adminUserId, $queryParams);

        $products = $stm->fetchAll();

        echo " ";
        $data = [];

        foreach ($products as $i => $product) { 
            $stm = $this->productService->getLog($product->id);
            $productLogs = $stm->fetchAll();

            $logString="";
            foreach ($productLogs as $log) {
                $dateTime = new DateTime($log->timestamp);
                $formatedDate = $dateTime->format('d/m/Y H:i:s');

                $logString = $product->title.': (' . $log->admin_user_id . ', ' . $log->action . ', ' . $formatedDate.'), ';
                $data[] = $logString;
            }
        }
        
        $report = "<table style='font-size: 10px;'>";
        foreach ($data as $row) {
            $report .= "<tr>";
            $report .= "<td>" . $row . "</td>";
            $report .= "</tr>";
        }
        $report .= "</table>";
        
        return $report;
    }

    public function validateAdminUserId($adminUserId){
        if (!ctype_digit(strval($adminUserId)) || $adminUserId <= 0) {
            throw new \InvalidArgumentException("Invalid admin user ID");
        }
        return $adminUserId;
    }
}
