<?php

namespace App\Setting;

class SqlCompareValueSetting
{
    private int $int;
    private string $string;
    private float $float;

    public function getInt(): int
    {
        return $this->int;
    }

    public function setInt(int $value): self
    {
        $this->int = $value;
        return $this;
    }

    public function getString(): string
    {
        return $this->string;
    }

    public function setString(string $string): self
    {
        $this->string = $string;
        return $this;
    }

    public function getFloat(): float
    {
        return $this->float;
    }

    public function setFloat(float $float): self
    {
        $this->float = $float;
        return $this;
    }
}