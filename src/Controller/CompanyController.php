<?php

namespace Contatoseguro\TesteBackend\Controller;

use Contatoseguro\TesteBackend\Config\DB;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Contatoseguro\TesteBackend\Service\CompanyService;

class CompanyController
{
    private CompanyService $service;    

    public function __construct() 
    {
       $this->service = new CompanyService();
    }

    public function getAll(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        try
        {
            $stm = $this->service->getAll();

            $response->getBody()->write(json_encode($stm->fetchAll()));
            return $response->withStatus(200);
        }
        catch(\Exception $e)
        {
            $response->getBody()->write(json_encode(['error' => $e->getMessage()]));
            return $response->withStatus(400);
        }
    }

    public function getOne(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        try
        {
            $stm = $this->service->getOne($args['id']);
            $result = $stm->fetch();
    
            if($result){
                $response->getBody()->write(json_encode($result));
                return $response->withStatus(200);
            }
            else{
                $response->getBody()->write(json_encode(['error' => "Company not found"]));
                return $response->withStatus(404);
            }
        }
        catch(\Exception $e)
        {
            $response->getBody()->write(json_encode(['error' => $e->getMessage()]));
            return $response->withStatus(400);
        }
    }
    
}
