<?php

namespace App\Entity;
interface Product
{
    public function getId(): ?int;

    public function getName(): ?string;

    public function setName(string $name): self;

    public function getProperties(): ?array;

    public function setProperties(array $properties): self;
}