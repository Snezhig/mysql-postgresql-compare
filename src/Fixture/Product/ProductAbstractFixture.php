<?php

namespace App\Fixture\Product;

use App\Entity\Product;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpKernel\KernelInterface;

abstract class ProductAbstractFixture extends Fixture
{
    private $faker;

    public function __construct(
        private KernelInterface $kernel
    ) {
        $this->faker = Factory::create();
    }

    public function load(ObjectManager $manager)
    {
        $manager->getConnection()->getConfiguration()->setSQLLogger(null);

        foreach ($this->getData() as $items) {
            foreach ($items as $item) {
                $product = $this->getEntity();
                $product->setName($item['name'])
                        ->setProperties($item['props']);
                $manager->persist($product);
            }
            $manager->flush();
            $manager->clear();
        }

    }

    private function getData(): \Traversable
    {
        $finder = new Finder();
        $finder->files()->in($this->kernel->getProjectDir() . '/files/fixtures')->sortByName(true);
        foreach ($finder as $file) {
            yield json_decode($file->getContents(), true, 512, JSON_THROW_ON_ERROR);
        }

    }

    abstract protected function getEntity(): Product;
}