<?php

namespace Tests\Feature\Final\Support;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

abstract class FinalFeatureTestCase extends TestCase
{
    use DatabaseTransactions;
    use FinalTestData;

    public function kyVongNgoaiLe(string $exception): void
    {
        $this->expectException($exception);
    }

    protected function vi(string $id, string $icon, string $name): string
    {
        return "{$id} {$icon} {$name}";
    }
}
