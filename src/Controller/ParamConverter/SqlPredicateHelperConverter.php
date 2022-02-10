<?php

namespace App\Controller\ParamConverter;

use App\Enum\CompareControllerPathEnum;
use App\Helper\MysqlPredicateHelper;
use App\Helper\PostgresqlPredicateHelper;
use App\Helper\SqlPredicateHelper;
use JetBrains\PhpStorm\ArrayShape;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface;
use Symfony\Component\HttpFoundation\Request;

class SqlPredicateHelperConverter implements ParamConverterInterface
{
    public function __construct(
        private PostgresqlPredicateHelper $postgresqlPredicateHelper,
        private MysqlPredicateHelper      $mysqlPredicateHelper
    ) {
    }

    #[ParamConverter]
    public function apply(Request $request, ParamConverter $configuration)
    {
        $type = $request->attributes->get('type');
        $helper = match (CompareControllerPathEnum::from($type)) {
            CompareControllerPathEnum::Mysql => $this->mysqlPredicateHelper,
            CompareControllerPathEnum::Postgresql => $this->postgresqlPredicateHelper
        };
        $values = $request->get('values');
        if (is_array($values)) {
            $this->applyValues($values, $helper);
        }

        $request->attributes->set(
            $configuration->getName(),
            $helper
        );
    }

    private function applyValues(
        #[ArrayShape(['int' => 'integer', 'string' => 'string', 'float' => 'float'])]
        array $values,
        SqlPredicateHelper $helper,
    ): void {
        if (array_key_exists('int', $values)) {
            $helper->getValueSetting()->setInt($values['int']);
        }
        if (array_key_exists('float', $values)) {
            $helper->getValueSetting()->setFloat($values['float']);
        }
        if (array_key_exists('string', $values)) {
            $helper->getValueSetting()->setString($values['string']);
        }
    }

    public function supports(ParamConverter $configuration)
    {
        return $configuration->getClass() === SqlPredicateHelper::class;
    }
}