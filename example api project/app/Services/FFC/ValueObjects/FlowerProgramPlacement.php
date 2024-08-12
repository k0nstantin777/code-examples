<?php

namespace App\Services\FFC\ValueObjects;

use App\Services\ValueObject\BaseValueObject;
use App\Services\ValueObject\Exceptions\InvalidSchemaException;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class FlowerProgramPlacement extends BaseValueObject
{
	private int $id;
	private string $price;
	private string $tax;
	private ?Carbon $readyDate;
    private Carbon $placementDate;

    /**
     * @var Collection|FlowerProgramPlacementExtra[]
     */
    private Collection $extras;
	private Product $product;

	/**
	 * @return int
	 */
	public function getId(): int
	{
		return $this->id;
	}

	/**
	 * @return string|null
	 */
	public function getPrice(): ?string
	{
		return $this->price;
	}

	/**
	 * @return Product
	 */
	public function getProduct(): Product
	{
		return $this->product;
	}

    /**
     * @return string
     */
    public function getTax() : string
    {
        return $this->tax;
    }

    /**
     * @return null|Carbon
     */
    public function getReadyDate() : ?Carbon
    {
        return $this->readyDate;
    }

    /**
     * @return Carbon
     */
    public function getPlacementDate() : Carbon
    {
        return $this->placementDate;
    }

    /**
     * @return Collection|FlowerProgramPlacementExtra[]
     */
    public function getExtras() : Collection
    {
        return $this->extras;
    }

	protected function getSchema(): array
	{
		return [
			'id',
			'product',
			'placement_date',
			'extras',
			'price',
			'tax',
            '?ready_date',
		];
	}

    /**
     * @throws InvalidSchemaException
     */
    protected function map(): void
	{
		$this->id = $this->attributes['id'];
		$this->product = new Product($this->attributes['product']);
		$this->tax = $this->attributes['tax'];
		$this->price = $this->attributes['price'];
		$this->placementDate = Carbon::parse($this->attributes['placement_date']);
		$this->readyDate = filled($this->attributes['ready_date']) ? Carbon::parse($this->attributes['ready_date']) : null;
		$this->extras = $this->fillExtras();
	}

    /**
     * @throws InvalidSchemaException
     */
    private function fillExtras() : Collection
    {
        $results = collect();

        foreach (Arr::wrap($this->attributes['extras'] ?? []) as $placementExtraData) {
            $results->push(new FlowerProgramPlacementExtra($placementExtraData));
        }

        return $results;
    }
}