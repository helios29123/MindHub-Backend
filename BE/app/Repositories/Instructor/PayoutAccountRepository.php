<?php

namespace App\Repositories\Instructor;

use App\Models\PayoutAccount;

class PayoutAccountRepository
{
    public function create(array $data): PayoutAccount
    {
        return PayoutAccount::create($data);
    }
}
