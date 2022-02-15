<?php

namespace App\Controller\Api\v1;

use App\Enum\CompareControllerPathEnum;
use App\Helper\SqlPredicateHelper;
use App\Service\ProductSqlInsertService;
use App\Service\ProductSqlSelectService;
use App\Service\ProductSqlUpdateService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/api/v1/compare')]
class CompareController extends AbstractController
{

    #[Route(path: '/types')]
    public function getTypes(): JsonResponse
    {
        return new JsonResponse([
            CompareControllerPathEnum::Mysql->name      => CompareControllerPathEnum::Mysql->value,
            CompareControllerPathEnum::Postgresql->name => CompareControllerPathEnum::Postgresql->value,
        ]);
    }

    #[Route(
        path: '/{type}/select',
        name: 'compare_select',
        methods: ['POST']
    )]
    public function executeSelect(Request $request, SqlPredicateHelper $helper, ProductSqlSelectService $service): JsonResponse
    {
        $service->setPredicateHelper($helper);
        $onlyPredicates = $request->get('only');

        if (is_array($onlyPredicates)) {
            $service->only($onlyPredicates);
        }

        return new JsonResponse($service->execute());
    }

    #[Route(
        path: '/{type}/select',
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

    #[Route(
        path: '/{type}/update',
        methods: ['POST']
    )]
    public function executeUpdate(
        Request                 $request,
        SqlPredicateHelper      $helper,
        ProductSqlUpdateService $service
    ): JsonResponse {
        $defaultLimit = 100;
        $body = $request->toArray();
        $stack = $service->setPredicateHelper($helper)
                         ->setPropertyKeys(
                             $body['data'],
                             $body['limit'] ?? $defaultLimit
                         );
        return new JsonResponse([
            'connection' => $helper->getConnectionName(),
            'sql'        => current($stack->queries)['sql'],
            'time'       => current($stack->queries)['executionMS'],
        ]);
    }

    #[Route(
        path: '/{type}/insert',
        methods: ['POST']
    )]
    public function executeInsert(Request $request, string $type, ProductSqlInsertService $service)
    {
        $service->setConnection($type);
        ['data' => $data, 'batch' => $batch] = $request->toArray();
        $results = [];
        $data = array_map(static function (array $item) {
            $item['properties'] = json_encode($item['properties'], JSON_THROW_ON_ERROR);
            return $item;
        }, $data);

        if ($batch) {
            $results[] = $service->insertBatch($data);
        } else {
            foreach ($data as $datum) {
                $results[] = $service->insert($datum['name'], $datum['properties']);
            }
        }

        foreach ($results as $index => $item) {
            $results[$index] = [
                'sql'  => $item['sql'],
                'time' => $item['executionMS']
            ];
        }

        return new JsonResponse([
            'type'    => $type,
            'queries' => $results
        ]);
    }
}