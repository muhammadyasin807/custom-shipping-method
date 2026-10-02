<?php

declare(strict_types=1);

namespace Doit\CustomShipping\Model\Rate;

class Calculator
{
    public function calculateStandard(
        float $unitPrice,
        float $quantity
    ): float {

        return $unitPrice * $quantity;
    }

    public function calculateExpress(
        float $standardPrice,
        float $surcharge
    ): float {
        return $standardPrice + $surcharge;
    }
}
