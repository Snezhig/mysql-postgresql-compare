<?php

namespace App\Controller\Api\v1;

use App\Enum\CompareControllerPathEnum;
use App\Helper\SqlPredicateHelper;
use App\Service\ProductSqlSelectService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/api/v1/compare')]
class CompareController extends AbstractController
{
    public function __construct(
        private ProductSqlSelectService $service
    ) {
    }

    #[Route(path: '/select/types')]
    public function getTypes(): JsonResponse
    {
        return new JsonResponse([
            CompareControllerPathEnum::Mysql->name      => CompareControllerPathEnum::Mysql->value,
            CompareControllerPathEnum::Postgresql->name => CompareControllerPathEnum::Postgresql->value,
        ]);
    }

    #[Route(
        path: '/select/{type}',
        name: 'compare_select',
        methods: ['POST']
    )]
    public function executeSelect(Request $request, SqlPredicateHelper $helper): JsonResponse
    {
        $this->service->setPredicateHelper($helper);
        $onlyPredicates = $request->get('only');

        if (is_array($onlyPredicates)) {
            $this->service->only($onlyPredicates);
        }

        return new JsonResponse($this->service->execute());
    }

    #[Route(
        path: '/select/{type}',
        methods: ['GET']
    )]
    public function getSelectPredicates(SqlPredicateHelper $helper): JsonResponse
    {
        return new JsonResponse(
            $helper->getWhereCollection()
                   ->map(static fn(string $predicate) => trim(preg_replace(
                       ["/\n/", "/ {2,}/"],
                       ' ',
                       $predicate)))
                   ->toArray()
        );
    }

}