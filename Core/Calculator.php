<?php

namespace Core;

/**
 * Motor de Cálculo Desacoplado
 * Sección 5 de la Biblia
 */
class Calculator
{
    public static function calculate($items, $taxRate = 0.19)
    {
        $subtotal = 0;

        foreach ($items as $item) {
            $subtotal += (float)$item['qty'] * (float)$item['price'];
        }

        $tax = round($subtotal * $taxRate, 0); // Redondeo según normativa chilena (pesos)
        $total = $subtotal + $tax;

        return [
            'subtotal' => $subtotal,
            'tax' => $tax,
            'total' => $total,
            'tax_rate' => $taxRate
        ];
    }
}
