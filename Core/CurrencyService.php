<?php
namespace Core;

class CurrencyService
{
    private static $apiUrl = 'https://mindicador.cl/api';

    /**
     * Get exchange rates, using DB cache if possible
     */
    public static function getRates()
    {
        $db = Database::getInstance();
        
        // Try to get from DB first (valid for 1 hour)
        $stmt = $db->query("SELECT * FROM exchange_rates WHERE updated_at > DATE_SUB(NOW(), INTERVAL 1 HOUR)");
        $cached = $stmt->fetchAll();
        
        if (!empty($cached)) {
            $rates = [];
            foreach ($cached as $row) {
                $rates[$row['currency']] = (float)$row['value'];
            }
            return $rates;
        }

        // If not in DB or expired, fetch from API
        return self::fetchAndCacheRates();
    }

    private static function fetchAndCacheRates()
    {
        $db = Database::getInstance();
        $rates = ['CLP' => 1.0];

        try {
            $ch = curl_init(self::$apiUrl);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 5);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            $response = curl_exec($ch);
            curl_close($ch);

            if ($response) {
                $data = json_decode($response, true);
                if (isset($data['uf']['valor'])) $rates['UF'] = (float)$data['uf']['valor'];
                if (isset($data['dolar']['valor'])) $rates['USD'] = (float)$data['dolar']['valor'];

                // Update DB
                $stmt = $db->prepare("INSERT INTO exchange_rates (currency, value, updated_at) VALUES (?, ?, NOW()) ON DUPLICATE KEY UPDATE value = VALUES(value), updated_at = NOW()");
                foreach ($rates as $curr => $val) {
                    if ($curr === 'CLP') continue;
                    $stmt->execute([$curr, $val]);
                }
            }
        } catch (\Exception $e) {
            Logger::error("Currency API Error: " . $e->getMessage());
            
            // If API fails, try to get the last known rates from DB even if expired
            $stmt = $db->query("SELECT * FROM exchange_rates");
            $lastKnown = $stmt->fetchAll();
            foreach ($lastKnown as $row) {
                $rates[$row['currency']] = (float)$row['value'];
            }
        }

        return $rates;
    }

    /**
     * MANDATORY Rule: Convert to CLP using CEIL()
     */
    public static function toCLP($amount, $currency, $rate = null)
    {
        if ($currency === 'CLP') {
            return ceil((float)$amount);
        }

        if (!$rate) {
            $rates = self::getRates();
            $rate = $rates[$currency] ?? 1.0;
        }

        return ceil((float)$amount * (float)$rate);
    }
}
