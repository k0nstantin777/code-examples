<?php

namespace App\Services\FFC\ValueObjects;

use App\Services\ValueObject\BaseValueObject;

class FlowerProgramMonument extends BaseValueObject
{
    private string $monumentType;
    private string $memorialType;
    private ?string $vaseType;
    private ?string $vaseSize;
    private ?bool $vaseHasPlastic;
    private ?string $vaseDiameter;

    /**
     * @return string
     */
    public function getMonumentType() : string
    {
        return $this->monumentType;
    }

    /**
     * @return string
     */
    public function getMemorialType() : string
    {
        return $this->memorialType;
    }

    /**
     * @return ?string
     */
    public function getVaseType() : ?string
    {
        return $this->vaseType;
    }

    /**
     * @return ?string
     */
    public function getVaseSize() : ?string
    {
        return $this->vaseSize;
    }

    /**
     * @return bool|null
     */
    public function getVaseHasPlastic() : ?bool
    {
        return $this->vaseHasPlastic;
    }

    /**
     * @return ?string
     */
    public function getVaseDiameter() : ?string
    {
        return $this->vaseDiameter;
    }

	protected function getSchema(): array
	{
		return [
            'monument_type',
            'memorial_type',
            '?vase_size',
            '?vase_type',
            '?vase_has_plastic',
            '?vase_diameter',
		];
	}

    protected function map(): void
	{
		$this->monumentType = $this->attributes['monument_type'];
		$this->memorialType = $this->attributes['memorial_type'];
		$this->vaseType = $this->attributes['vase_type'] ?? null;
		$this->vaseSize = $this->attributes['vase_size'] ?? null;
		$this->vaseHasPlastic = $this->attributes['vase_has_plastic'] ?? null;
		$this->vaseDiameter = $this->attributes['vase_diameter'] ?? null;
	}
}