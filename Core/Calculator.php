<?php

namespace Core;

class Calculator
{
    public static function calculate($items, $taxRate = 0.19, $decimals = 0)
    {
        $subtotal = 0;

        foreach ($items as $item) {
            $subtotal += (float)$item['qty'] * (float)$item['price'];
        }

        $decimals = max(0, (int)$decimals);
        $subtotal = round($subtotal, $decimals);
        $tax = round($subtotal * $taxRate, $decimals);
        $total = round($subtotal + $tax, $decimals);

        return [
            'subtotal' => $subtotal,
            'tax' => $tax,
            'total' => $total,
            'tax_rate' => $taxRate,
        ];
    }
}
