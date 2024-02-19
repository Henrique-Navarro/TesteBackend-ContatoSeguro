<?php

namespace Contatoseguro\TesteBackend\Controller;

use Contatoseguro\TesteBackend\Model\Product;
use Contatoseguro\TesteBackend\Service\CategoryService;
use Contatoseguro\TesteBackend\Service\ProductService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class ProductController
{
    private ProductService $service;
    private CategoryService $categoryService;

    public function __construct()
    {
        $this->service = new ProductService();
        $this->categoryService = new CategoryService();
    }

    public function getAll(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        try
        {
            $adminUserId = $this->service->validateAdminUserId($request->getHeader('admin_user_id')[0]);
            $queryParams = $request->getQueryParams();

            $stm = $this->service->getAll($adminUserId, $queryParams);
            $response->getBody()->write(json_encode($stm->fetchAll()));
            return $response->withStatus(200);  
        }
        catch (\Exception $e)
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
            
            $productFetched = $stm->fetch();
            if ($productFetched == null) {
                $response->getBody()->write(json_encode(['error' => "Product not found"]));
                return $response->withStatus(400); 
            }

            $product = Product::hydrateByFetch($productFetched);
            
            $adminUserId = $this->service->validateAdminUserId($request->getHeader('admin_user_id')[0]);
            $productCategories = $this->categoryService->getProductCategory($product->id);
            
            $categories = [];
            foreach ($productCategories as $productCategory) {
                $fetchedCategory = $this->categoryService->getOne($adminUserId, $productCategory->id)->fetch();
                $categories[] = $fetchedCategory->title;
            }
            $product->setCategory($categories);

            $response->getBody()->write(json_encode($product));
            return $response->withStatus(200);
        }
        catch (\Exception $e)
        {
            $response->getBody()->write(json_encode(['error' => $e->getMessage()]));
            return $response->withStatus(400);
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
                $response->getBody()->write(json_encode(['error' => "Product not found"]));
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
                $response->getBody()->write(json_encode(['error' => "Product not found"]));
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
