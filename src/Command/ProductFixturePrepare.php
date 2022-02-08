<?php

namespace App\Command;

use App\Enum\JsonColumnEnum;
use Faker\Factory;
use Faker\Generator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpKernel\KernelInterface;

class ProductFixturePrepare extends Command
{

    protected static $defaultName = 'app:product:fixture:prepare';

    private Generator $faker;

    public function __construct(
        private KernelInterface $kernel
    ) {
        parent::__construct();
        $this->faker = Factory::create();
    }

    public function getDescription(): string
    {
        return 'Создаёт json файл с готовым данными, что заполнить обе базы идентичными строками';
    }

    protected function configure()
    {
        $this->addOption('icount', '', InputOption::VALUE_OPTIONAL, 'Items count', 10000);
        $this->addOption('pcount', '',InputArgument::OPTIONAL, 'Props count', 2000);
    }


    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $count = (int)$input->getOption('icount');
        $props = $this->getProps((int)$input->getOption('pcount'));
        $items = [];
        $part = 1;
        for ($i = 1; $i <= $count; $i++) {
            $items[] = [
                'name'  => $this->faker->word,
                'props' => array_map(
                    static fn(\Closure $closure) => $closure(),
                    $props
                )
            ];
            if (count($items) % 1000 === 0) {
                $this->dump($items, $part);
                ++$part;
                $items = [];
            }
        }
        $this->dump($items, $part);

        return 0;
    }

    private function getProps(int $count): array
    {
        static $props = null;

        if (is_null($props)) {
            $props = [
                JsonColumnEnum::Int->getProperty()    => fn() => $this->faker->numberBetween(1, 100000),
                JsonColumnEnum::Float->getProperty()  => fn() => $this->faker->randomFloat(2, 1, 10000),
                JsonColumnEnum::String->getProperty() => fn() => $this->faker->word
            ];

            for ($i = 1; $i <= $count; $i++) {
                $props["p_${i}"] = $this->faker->randomElement([
                    fn() => $this->faker->randomFloat(2, 1, 100000),
                    fn() => $this->faker->numberBetween(1, 10000),
                    fn() => $this->faker->word
                ]);
            }
        }

        return $props;
    }

    private function dump(array $items, int $part): void
    {
        if (!empty($items)) {
            $fs = new Filesystem();
            $fs->dumpFile(
                $this->kernel->getProjectDir() . "/files/fixtures/product_$part.json",
                json_encode($items, JSON_THROW_ON_ERROR)
            );
        }
    }
}