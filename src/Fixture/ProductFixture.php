<?php

namespace App\Fixture;

use App\Entity\Product;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use JsonException;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpKernel\KernelInterface;
use Traversable;

class ProductFixture extends Fixture
{

    public function __construct(
        private KernelInterface $kernel
    ) {
    }

    public function load(ObjectManager $manager): void
    {
        $manager->getConnection()->getConfiguration()->setSQLLogger(null);

        foreach ($this->getData() as $items) {
            foreach ($items as $item) {
                $product = (new Product())
                    ->setName($item['name'])
                    ->setProperties($item['props']);
                $manager->persist($product);
            }
            $manager->flush();
            $manager->clear();
        }

    }

    /**
     * @return Traversable
     * @throws JsonException
     */
    private function getData(): Traversable
    {
        $finder = new Finder();
        $finder->files()->in($this->kernel->getProjectDir() . '/files/fixtures')->sortByName(true);
        foreach ($finder as $file) {
            yield json_decode(
                $file->getContents(),
                true,
                512,
                JSON_THROW_ON_ERROR
            );
        }

    }

}