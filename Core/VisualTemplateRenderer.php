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
        $fontFamily = ($styles['font_family'] ?? 'sans-serif') === 'serif' ? 'serif' : 'helvetica, Arial, sans-serif';

        $html = '<div class="visual-document" style="font-family: ' . $fontFamily . '; color: #333; max-width: 800px; margin: auto; padding: 0; background: white;">';

        foreach ($blocks as $block) {
            if (!($block['enabled'] ?? false)) continue;

            $html .= self::renderBlock($block['id'], $block['options'] ?? [], $data, $primaryColor);
        }

        $html .= '</div>';
        return $html;
    }

    private static function renderBlock($id, $options, $data, $primaryColor)
    {
        // Normalize checkbox options (FormData sends "on" for checked)
        if (is_array($options)) {
            foreach ($options as $key => $val) {
                if ($val === 'on') $options[$key] = true;
            }
        } else {
            $options = [];
        }

        $out = '<div class="block-' . $id . '" style="margin-bottom: 20px;">';

        switch ($id) {
            case 'company':
                $out .= '<table style="width: 100%; border-collapse: collapse; margin-bottom: 15px;"><tr>';
                if ($options['show_logo'] ?? true) {
                    $logo = $data['settings']['biz_logo'] ?? '';
                    $logoWidth = (int)($options['logo_width'] ?? 150);
                    $logoX = (int)($options['logo_x'] ?? 0);
                    $logoY = (int)($options['logo_y'] ?? 0);
                    
                    $logoPath = !empty($logo) ? 'uploads/' . $logo : 'assets/img/logo.png';
                    
                    // Use padding for positioning (safer for PDF engines)
                    $paddingLeft = $logoX > 0 ? $logoX : 0;
                    $paddingTop = $logoY > 0 ? $logoY : 0;

                    $out .= '<td style="vertical-align: top; width: 50%; padding-left: ' . $paddingLeft . 'px; padding-top: ' . $paddingTop . 'px;">';
                    $out .= '<img src="' . $logoPath . '" width="' . $logoWidth . '" style="display: block;">';
                    $out .= '</td>';
                } else {
                    $out .= '<td style="width: 50%;"></td>';
                }
                $out .= '<td style="text-align: right; vertical-align: top; width: 50%;">';
                $out .= '<h2 style="margin: 0; color: ' . $primaryColor . '; font-size: 20px;">' . ($data['settings']['biz_name'] ?? 'Empresa') . '</h2>';
                if ($options['show_rut'] ?? true) $out .= '<p style="margin: 2px 0; font-size: 11px;">RUT: ' . ($data['settings']['biz_rut'] ?? '') . '</p>';
                if (!empty($data['settings']['biz_giro'])) $out .= '<p style="margin: 2px 0; font-size: 10px;">' . $data['settings']['biz_giro'] . '</p>';
                $out .= '<p style="margin: 2px 0; font-size: 10px; color: #666666;">' . ($data['settings']['biz_address'] ?? '') . '</p>';
                if (!empty($data['settings']['biz_phone']) || !empty($data['settings']['biz_email'])) {
                    $contact = implode(' | ', array_filter([$data['settings']['biz_phone'] ?? '', $data['settings']['biz_email'] ?? '']));
                    $out .= '<p style="margin: 2px 0; font-size: 10px; color: #777777;">' . $contact . '</p>';
                }
                $out .= '</td></tr></table>';
                break;

            case 'client':
                $out .= '<table style="width: 100%; background-color: #f9fafb; padding: 15px; border: 1px solid #eeeeee;"><tr><td>';
                $out .= '<h4 style="margin: 0 0 10px 0; border-bottom: 1px solid #dddddd; padding-bottom: 5px;">Facturar a:</h4>';
                $out .= '<p style="margin: 3px 0;"><strong>' . ($data['client']['name'] ?? 'Cliente Demo') . '</strong></p>';
                $out .= '<p style="margin: 3px 0;">RUT: ' . ($data['client']['rut'] ?? '') . '</p>';
                $out .= '<p style="margin: 3px 0;">' . ($data['client']['address'] ?? '') . '</p>';
                $out .= '</td></tr></table>';
                break;

            case 'header':
                $out .= '<table style="width: 100%; border-bottom: 2px solid ' . $primaryColor . '; padding-bottom: 5px; margin-bottom: 10px;"><tr>';
                if ($options['show_number'] ?? true) $out .= '<td style="vertical-align: bottom;"><h3 style="margin: 0; color: #333333; font-size: 16px;">DOCUMENTO #' . ($data['invoice']['number'] ?? '000') . '</h3></td>';
                $out .= '<td style="text-align: right; vertical-align: bottom; font-size: 11px;">';
                if ($options['show_date'] ?? true) $out .= '<div>Fecha: ' . date('d/m/Y', strtotime($data['invoice']['created_at'] ?? 'now')) . '</div>';
                if (!empty($data['invoice']['due_date'])) $out .= '<div style="color: #666666;">Vencimiento: ' . date('d/m/Y', strtotime($data['invoice']['due_date'])) . '</div>';
                $out .= '</td></tr></table>';
                break;

            case 'items':
                $out .= '<table style="width: 100%; border-collapse: collapse; margin-top: 10px;">';
                $out .= '<thead><tr>';
                if ($options['show_sku'] ?? true) $out .= '<th style="padding: 10px; text-align: left; font-size: 12px; background-color: ' . $primaryColor . '; color: #ffffff;">SKU</th>';
                $out .= '<th style="padding: 10px; text-align: left; font-size: 12px; background-color: ' . $primaryColor . '; color: #ffffff;">Descripción</th>';
                $out .= '<th style="padding: 10px; text-align: right; font-size: 12px; background-color: ' . $primaryColor . '; color: #ffffff;">Cant.</th>';
                $out .= '<th style="padding: 10px; text-align: right; font-size: 12px; background-color: ' . $primaryColor . '; color: #ffffff;">Precio</th>';
                $out .= '<th style="padding: 10px; text-align: right; font-size: 12px; background-color: ' . $primaryColor . '; color: #ffffff;">Total</th>';
                $out .= '</tr></thead><tbody>';

                $items = $data['items'] ?? [['sku' => 'DEMO1', 'name' => 'Producto de Prueba', 'quantity' => 1, 'price' => 10000, 'subtotal' => 10000]];
                foreach ($items as $item) {
                    $out .= '<tr style="border-bottom: 1px solid #eee;">';
                    if ($options['show_sku'] ?? true) $out .= '<td style="padding: 10px; font-size: 11px;">' . ($item['sku'] ?? '') . '</td>';
                    $out .= '<td style="padding: 10px; font-size: 11px;">' . ($item['name'] ?? '') . '</td>';
                    $out .= '<td style="padding: 10px; text-align: right; font-size: 11px;">' . ($item['quantity'] ?? 1) . '</td>';
                    $out .= '<td style="padding: 10px; text-align: right; font-size: 11px;">$' . number_format($item['price'] ?? 0, 0, ',', '.') . '</td>';
                    $out .= '<td style="padding: 10px; text-align: right; font-size: 11px;">$' . number_format($item['subtotal'] ?? 0, 0, ',', '.') . '</td>';
                    $out .= '</tr>';
                }
                $out .= '</tbody></table>';

                $out .= '<table style="width: 100%; margin-top: 15px;"><tr><td style="width: 60%;"></td><td style="width: 40%;">';
                $out .= '<table style="width: 100%; border-collapse: collapse;">';
                $out .= '<tr><td style="padding: 4px 0; color: #666666; font-size: 11px;">Subtotal:</td><td style="text-align: right; padding: 4px 0; color: #666666; font-size: 11px;">$' . number_format($data['invoice']['subtotal'] ?? 0, 0, ',', '.') . '</td></tr>';
                if ($options['show_tax'] ?? true) {
                    $out .= '<tr><td style="padding: 4px 0; color: #666666; font-size: 11px;">IVA (19%):</td><td style="text-align: right; padding: 4px 0; color: #666666; font-size: 11px;">$' . number_format($data['invoice']['tax'] ?? 0, 0, ',', '.') . '</td></tr>';
                }
                $out .= '<tr><td style="padding: 8px 0; border-top: 2px solid ' . $primaryColor . '; font-weight: 800; font-size: 16px; color: ' . $primaryColor . ';">TOTAL:</td><td style="text-align: right; padding: 8px 0; border-top: 2px solid ' . $primaryColor . '; font-weight: 800; font-size: 16px; color: ' . $primaryColor . ';">$' . number_format($data['invoice']['total'] ?? 0, 0, ',', '.') . '</td></tr>';
                $out .= '</table></td></tr></table>';
                break;

            case 'notes':
                $out .= '<table style="width: 100%; margin-top: 20px; border-left: 4px solid ' . $primaryColor . '; background-color: #f8fafc;"><tr><td style="padding: 15px;">';
                $out .= '<h5 style="margin: 0 0 5px 0; color: ' . $primaryColor . ';">Notas y Condiciones:</h5>';
                $out .= nl2br(htmlspecialchars($options['text'] ?? $data['invoice']['notes'] ?? ''));
                $out .= '</td></tr></table>';
                break;

            case 'webpay_payment':
                $settingsData = $data['settings'] ?? [];
                $buttonText = htmlspecialchars($settingsData['webpay_button_text'] ?? $options['text'] ?? 'Pagar con Webpay Plus');
                $buttonImage = !empty($settingsData['webpay_button_image'])
                    ? 'uploads/' . htmlspecialchars($settingsData['webpay_button_image'])
                    : 'assets/img/transbank-webpay.svg';
                $publicUrl = htmlspecialchars($data['invoice']['public_url'] ?? '#', ENT_QUOTES, 'UTF-8');
                $buttonWidth = (int)($options['button_width'] ?? 200);
                $out .= '<table style="width: 100%; margin: 25px 0; border: 1px solid #f3c2d2; background-color: #fff7fb;"><tr><td style="padding: 18px; text-align: center;">';
                $out .= '<img src="' . $buttonImage . '" alt="Transbank Webpay Plus" width="' . $buttonWidth . '" style="display: block; margin: 0 auto 12px auto;"><br>';
                $out .= '<a href="' . $publicUrl . '" style="background-color: #e0245e; color: #ffffff; text-decoration: none; padding: 12px 20px; font-weight: 800; display: inline-block;">' . $buttonText . '</a>';
                $out .= '</td></tr></table>';
                break;

            case 'footer':
                $out .= '<div style="margin-top: 40px; text-align: center; font-size: 10px; color: #999999; border-top: 1px solid #eeeeee; padding-top: 20px;">';
                $out .= htmlspecialchars($options['text'] ?? 'Documento generado por PCCurico');
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
