<?php

namespace App\Services\FFC\ValueObjects;

use App\Services\ValueObject\Exceptions\InvalidSchemaException;
use Illuminate\Support\Collection;

class AccountInfo extends User
{
    private Collection $addresses;
    private Collection $graves;

    /**
     * @return Collection
     */
    public function getAddresses(): Collection
    {
        return $this->addresses;
    }

    /**
     * @return Collection
     */
    public function getGraves(): Collection
    {
        return $this->graves;
    }

    protected function getSchema(): array
    {
        return array_merge(parent::getSchema(), [
            '?addresses',
            '?graves',
        ]);
    }

    /**
     * @throws InvalidSchemaException
     */
    protected function map(): void
    {
        parent::map();

        $this->addresses = $this->fillAddresses();
        $this->graves = $this->fillGraves();
    }

    /**
     * @throws InvalidSchemaException
     */
    private function fillAddresses(): Collection
    {
        $results = collect();

        if (empty($this->attributes['addresses'])) {
            return $results;
        }

        foreach ($this->attributes['addresses'] as $addressData) {
            $results->push(new AccountAddress($addressData));
        }

        return $results;
    }

    /**
     * @throws InvalidSchemaException
     */
    private function fillGraves(): Collection
    {
        $results = collect();

        if (empty($this->attributes['graves'])) {
            return $results;
        }

        foreach ($this->attributes['graves'] as $graveData) {
            $results->push(AccountGrave::makeFromData($graveData));
        }

        return $results;
    }
}
