<?php

namespace Contatoseguro\TesteBackend\Controller;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Contatoseguro\TesteBackend\Service\ReportService;

class ReportController
{
    private ReportService $reportService;

    public function __construct()
    {
        $this->reportService = new ReportService();
    }
    
    public function generate(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        try{
            $adminUserId = $this->reportService->validateAdminUserId($request->getHeader('admin_user_id')[0]);
            
            $queryParams = $request->getQueryParams();
    
            $report = $this->reportService->generate($adminUserId, $queryParams);

            $response->getBody()->write($report);
            return $response->withStatus(200)->withHeader('Content-Type', 'text/html');
        }
        catch(\Exception $e)
        {
            $response->getBody()->write(json_encode(['error' => $e->getMessage()]));
            return $response->withStatus(400);
        }
    }
}
