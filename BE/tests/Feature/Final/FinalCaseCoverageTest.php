<?php

namespace Tests\Feature\Final;

use Tests\Feature\Final\Support\FinalCaseCatalog;
use Tests\TestCase;

final class FinalCaseCoverageTest extends TestCase
{
    public function test_khong_duoc_sot_bat_ky_case_nao_trong_ke_hoach(): void
    {
        $cases = FinalCaseCatalog::all();

        $this->assertCount(474, $cases, '🔴 Phải có đúng 474 case active từ kế hoạch.');
        $this->assertCount(474, array_unique(array_keys($cases)), '🔴 Có mã case bị trùng.');
    }
}
