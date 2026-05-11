<?php
namespace Core\Services;

/**
 * Servicio central para cálculos de facturación: subtotal, impuestos y totales.
 * Permite compatibilidad con Invoice, POS y módulos futuros.
 *
 * Compatible con PHP 8+, hosting tradicional, sin dependencias externas.
 */
class BillingService
{
    /**
     * Calcula totales para items de factura
     * @param array $items [ [ qty, price, ... ], ... ]
     * @param float $taxRate (Ej: 0.19 para 19% IVA)
     * @param int $decimales Decimales según moneda
     * @return array [ 'subtotal' => float, 'tax' => float, 'total' => float, 'items' => array ]
     */
    public static function calcularTotales(array $items, float $taxRate = 0.19, int $decimales = 0): array
    {
        $subtotal = 0;
        $detalle = [];
        foreach ($items as $item) {
            $qty = isset($item['qty']) ? (float)$item['qty'] : 0;
            $price = isset($item['price']) ? (float)$item['price'] : 0;
            $lineTotal = round($qty * $price, $decimales);
            $subtotal += $lineTotal;
            $detalle[] = array_merge($item, ['total' => $lineTotal]);
        }
        $subtotal = round($subtotal, $decimales);
        $tax = round($subtotal * $taxRate, $decimales);
        $total = round($subtotal + $tax, $decimales);
        return [
            'subtotal' => $subtotal,
            'tax' => $tax,
            'total' => $total,
            'items' => $detalle,
        ];
    }
}
