<?php
namespace Core;

class VisualTemplateRenderer
{
    public static function render($configJson, $data)
    {
        $configArray = is_array($configJson) ? $configJson : json_decode($configJson ?? '{}', true);
        
        $blocks = $configArray['blocks'] ?? (isset($configArray[0]) ? $configArray : []);
        $styles = $configArray['styles'] ?? ['primary_color' => '#3b82f6', 'font_family' => 'sans-serif'];
        
        if (!$blocks) return "Error de configuración de plantilla.";

        // Sort by position
        usort($blocks, function($a, $b) {
            return ($a['position'] ?? 99) <=> ($b['position'] ?? 99);
        });

        // Ensure we have fallbacks for styles
        $primaryColor = htmlspecialchars($styles['primary_color'] ?? '#3b82f6');
        $fontFamily = htmlspecialchars($styles['font_family'] ?? 'sans-serif');

        $html = '<div class="visual-document" style="font-family: ' . $fontFamily . '; color: #333; max-width: 800px; margin: auto; padding: 40px; background: white; border: 1px solid #ddd;">';

        foreach ($blocks as $block) {
            if (!($block['enabled'] ?? false)) continue;

            $html .= self::renderBlock($block['id'], $block['options'] ?? [], $data, $primaryColor);
        }

        $html .= '</div>';
        return $html;
    }

    private static function renderBlock($id, $options, $data, $primaryColor)
    {
        $out = '<div class="block-' . $id . '" style="margin-bottom: 30px;">';

        switch ($id) {
            case 'company':
                $out .= '<div style="display: flex; justify-content: space-between; align-items: flex-start;">';
                if ($options['show_logo'] ?? true) {
                    $logo = $data['settings']['biz_logo'] ?? '';
                    $logoWidth = (int)($options['logo_width'] ?? 150);
                    $logoX = (int)($options['logo_x'] ?? 0);
                    $logoY = (int)($options['logo_y'] ?? 0);
                    
                    $logoPath = !empty($logo) ? 'uploads/' . $logo : 'assets/img/logo.png';
                    
                    $out .= '<div style="margin-left: ' . $logoX . 'px; margin-top: ' . $logoY . 'px;">';
                    $out .= '<img src="' . $logoPath . '" style="max-width: ' . $logoWidth . 'px; display: block;">';
                    $out .= '</div>';
                }
                $out .= '<div style="text-align: right;">';
                $out .= '<h2 style="margin: 0; color: ' . $primaryColor . ';">' . ($data['settings']['biz_name'] ?? 'Empresa') . '</h2>';
                if ($options['show_rut'] ?? true) $out .= '<p style="margin: 5px 0; font-size: 0.9rem;">RUT: ' . ($data['settings']['biz_rut'] ?? '') . '</p>';
                $out .= '<p style="margin: 0; font-size: 0.85rem; color: #666;">' . ($data['settings']['biz_address'] ?? '') . '</p>';
                $out .= '</div></div>';
                break;

            case 'client':
                $out .= '<div style="background: #f9fafb; padding: 15px; border-radius: 8px; font-size: 0.9rem;">';
                $out .= '<h4 style="margin: 0 0 10px 0; border-bottom: 1px solid #ddd; padding-bottom: 5px;">Facturar a:</h4>';
                $out .= '<p style="margin: 3px 0;"><strong>' . ($data['client']['name'] ?? 'Cliente Demo') . '</strong></p>';
                $out .= '<p style="margin: 3px 0;">RUT: ' . ($data['client']['rut'] ?? '') . '</p>';
                $out .= '<p style="margin: 3px 0;">' . ($data['client']['address'] ?? '') . '</p>';
                $out .= '</div>';
                break;

            case 'header':
                $out .= '<div style="display: flex; justify-content: space-between; border-bottom: 2px solid ' . $primaryColor . '; padding-bottom: 10px;">';
                if ($options['show_number'] ?? true) $out .= '<h3 style="margin: 0;">DOCUMENTO #' . ($data['invoice']['number'] ?? '000') . '</h3>';
                if ($options['show_date'] ?? true) $out .= '<div style="text-align: right;">Fecha: ' . date('d/m/Y', strtotime($data['invoice']['created_at'] ?? 'now')) . '</div>';
                $out .= '</div>';
                break;

            case 'items':
                $out .= '<table style="width: 100%; border-collapse: collapse; margin-top: 10px;">';
                $out .= '<thead style="background: ' . $primaryColor . '; color: white;"><tr>';
                if ($options['show_sku'] ?? true) $out .= '<th style="padding: 10px; text-align: left; font-size: 0.85rem;">SKU</th>';
                $out .= '<th style="padding: 10px; text-align: left; font-size: 0.85rem;">Descripción</th>';
                $out .= '<th style="padding: 10px; text-align: right; font-size: 0.85rem;">Cant.</th>';
                $out .= '<th style="padding: 10px; text-align: right; font-size: 0.85rem;">Precio</th>';
                $out .= '<th style="padding: 10px; text-align: right; font-size: 0.85rem;">Total</th>';
                $out .= '</tr></thead><tbody>';

                $items = $data['items'] ?? [['sku' => 'DEMO1', 'name' => 'Producto de Prueba', 'quantity' => 1, 'price' => 10000, 'subtotal' => 10000]];
                foreach ($items as $item) {
                    $out .= '<tr style="border-bottom: 1px solid #eee;">';
                    if ($options['show_sku'] ?? true) $out .= '<td style="padding: 10px; font-size: 0.85rem;">' . ($item['sku'] ?? '') . '</td>';
                    $out .= '<td style="padding: 10px; font-size: 0.85rem;">' . ($item['name'] ?? '') . '</td>';
                    $out .= '<td style="padding: 10px; text-align: right; font-size: 0.85rem;">' . ($item['quantity'] ?? 1) . '</td>';
                    $out .= '<td style="padding: 10px; text-align: right; font-size: 0.85rem;">$' . number_format($item['price'] ?? 0, 0, ',', '.') . '</td>';
                    $out .= '<td style="padding: 10px; text-align: right; font-size: 0.85rem;">$' . number_format($item['subtotal'] ?? 0, 0, ',', '.') . '</td>';
                    $out .= '</tr>';
                }
                $out .= '</tbody></table>';

                $out .= '<div style="display: flex; justify-content: flex-end; margin-top: 20px;">';
                $out .= '<div style="width: 300px;">';
                $out .= '<div style="display: flex; justify-content: space-between; padding: 5px 0; color: #666;"><span>Subtotal:</span><span>$' . number_format($data['invoice']['subtotal'] ?? 0, 0, ',', '.') . '</span></div>';
                if ($options['show_tax'] ?? true) {
                    $out .= '<div style="display: flex; justify-content: space-between; padding: 5px 0; color: #666;"><span>IVA (19%):</span><span>$' . number_format($data['invoice']['tax'] ?? 0, 0, ',', '.') . '</span></div>';
                }
                $out .= '<div style="display: flex; justify-content: space-between; padding: 10px 0; border-top: 2px solid ' . $primaryColor . '; font-weight: 800; font-size: 1.2rem; color: ' . $primaryColor . ';"><span>TOTAL:</span><span>$' . number_format($data['invoice']['total'] ?? 0, 0, ',', '.') . '</span></div>';
                $out .= '</div></div>';
                break;

            case 'notes':
                $out .= '<div style="margin-top: 20px; padding: 15px; border-left: 4px solid ' . $primaryColor . '; background: #f8fafc; font-size: 0.85rem;">';
                $out .= '<h5 style="margin: 0 0 5px 0; color: ' . $primaryColor . ';">Notas y Condiciones:</h5>';
                $out .= nl2br(htmlspecialchars($options['text'] ?? $data['invoice']['notes'] ?? ''));
                $out .= '</div>';
                break;

            case 'footer':
                $out .= '<div style="margin-top: 40px; text-align: center; font-size: 0.75rem; color: #999; border-top: 1px solid #eee; padding-top: 20px;">';
                $out .= htmlspecialchars($options['text'] ?? 'Documento generado por Facturador PCcurico');
                $out .= '</div>';
                break;

            // Bloques para Email
            case 'greeting':
                $out .= '<h3 style="color: #333; margin-bottom: 10px;">' . htmlspecialchars($options['text'] ?? 'Hola,') . '</h3>';
                break;
                
            case 'message':
                $out .= '<p style="color: #555; line-height: 1.6; margin-bottom: 20px;">' . nl2br(htmlspecialchars($options['text'] ?? 'Adjunto encontrará su documento.')) . '</p>';
                break;

            case 'button':
                $btnText = htmlspecialchars($options['text'] ?? 'Ver Documento');
                $out .= '<div style="text-align: center; margin: 30px 0;">';
                $out .= '<a href="#" style="background: ' . $primaryColor . '; color: white; padding: 12px 24px; text-decoration: none; border-radius: 6px; font-weight: bold; display: inline-block;">' . $btnText . '</a>';
                $out .= '</div>';
                break;

            case 'signature':
                $out .= '<p style="color: #888; font-size: 0.85rem; margin-top: 30px; border-top: 1px solid #eee; padding-top: 15px;">' . nl2br(htmlspecialchars($options['text'] ?? 'Atentamente,')) . '</p>';
                break;
        }

        $out .= '</div>';
        return $out;
    }
}
