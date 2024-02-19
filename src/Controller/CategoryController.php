<?php

namespace Contatoseguro\TesteBackend\Controller;

use Contatoseguro\TesteBackend\Service\CategoryService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class CategoryController
{
    private CategoryService $service;

    public function __construct()
    {
        $this->service = new CategoryService();
    }

    public function getAll(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        try
        {
            $adminUserId = $this->service->validateAdminUserId($request->getHeader('admin_user_id')[0]);
            $stm = $this->service->getAll($adminUserId);
    
            $response->getBody()->write(json_encode($stm->fetchAll()));
            return $response->withStatus(200);
        }
        catch (\Exception $e)
        {
            $response->getBody()->write(json_encode(['error' => $e->getMessage()]));
            return $response->withStatus(500);
        }
    }

    public function getOne(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        try
        {
            $adminUserId = $this->service->validateAdminUserId($request->getHeader('admin_user_id')[0]);
            $stm = $this->service->getOne($adminUserId, $args['id']);
            
            $categoryFetched = $stm->fetchAll();
            if ($categoryFetched == null) {
                throw new \Exception("Category not found");
            }

            $response->getBody()->write(json_encode($categoryFetched));
            return $response->withStatus(200);   
        }
        catch(\Exception $e){
            $response->getBody()->write(json_encode(['error' => $e->getMessage()]));
            return $response->withStatus(404);
        }
    }

    public function insertOne(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        try
        {
            $body = $request->getParsedBody();
            $adminUserId = $this->service->validateAdminUserId($request->getHeader('admin_user_id')[0]);

            if ($this->service->insertOne($body, $adminUserId)) {
                return $response->withStatus(200);
            } else {
                return $response->withStatus(404);
            }
        }
        catch(\Exception $e)
        {
            $response->getBody()->write(json_encode(['error' => $e->getMessage()]));
            return $response->withStatus(400);
        }
    }

    public function updateOne(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        try
        {
            $body = $request->getParsedBody();
            $adminUserId = $this->service->validateAdminUserId($request->getHeader('admin_user_id')[0]);
    
            if ($this->service->updateOne($args['id'], $body, $adminUserId)) {
                return $response->withStatus(200);
            } else {
                $response->getBody()->write(json_encode(['error' => "Category not found"]));
                return $response->withStatus(404);
            }
        }
        catch(\Exception $e)
        {
            $response->getBody()->write(json_encode(['error' => $e->getMessage()]));
            return $response->withStatus(400);
        }
    }

    public function deleteOne(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        try
        {
            $adminUserId = $this->service->validateAdminUserId($request->getHeader('admin_user_id')[0]);

            if ($this->service->deleteOne($args['id'], $adminUserId)) {
                return $response->withStatus(200);
            } else {
                $response->getBody()->write(json_encode(['error' => "Category not found"]));
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
