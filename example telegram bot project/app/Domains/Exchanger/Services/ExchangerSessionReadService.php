<?php

namespace App\Domains\Exchanger\Services;

use App\Domains\Exchanger\Models\ExchangerSession;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class ExchangerSessionReadService
{
    /**
     * @param int $id
     * @throws ModelNotFoundException
     * @return ExchangerSession
     */
    public function getById(int $id) : ExchangerSession
    {
        return ExchangerSession::findOrFail($id);
    }

    public function getByIdOrNull(int $id) : ?ExchangerSession
    {
        try {
            return $this->getById($id);
        } catch (ModelNotFoundException) {
            return null;
        }
    }
}