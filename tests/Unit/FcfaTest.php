<?php

namespace Tests\Unit;

use App\Support\Fcfa;
use PHPUnit\Framework\TestCase;

class FcfaTest extends TestCase
{
    public function test_amounts_are_grouped_by_thousands(): void
    {
        $this->assertSame("500\u{00A0}FCFA", Fcfa::format(500));
        $this->assertSame("25\u{202F}000\u{00A0}FCFA", Fcfa::format(25000));
        $this->assertSame("1\u{202F}250\u{202F}000\u{00A0}FCFA", Fcfa::format(1250000));
    }
}
