<?php
namespace Core;

class TransbankService
{
    private $commerceCode;
    private $apiKey;
    private $environment;
    
    public function __construct()
    {
        $db = Database::getInstance();
        $stmt = $db->query("SELECT * FROM settings WHERE `key` IN ('webpay_cc', 'webpay_key', 'webpay_env')");
        $settings = [];
        foreach($stmt->fetchAll() as $s) {
            $settings[$s['key']] = $s['value'];
        }

        $this->commerceCode = $settings['webpay_cc'] ?? '597055555532';
        $this->apiKey = $settings['webpay_key'] ?? '579B532A7440BB0C9079DED94D31EA1615BACEB56610332264630D42D0A36B1C';
        $this->environment = $settings['webpay_env'] ?? 'integration';
    }

    private function getBaseUrl()
    {
        return $this->environment === 'production' 
            ? 'https://webpay3g.transbank.cl' 
            : 'https://webpay3gint.transbank.cl';
    }

    private function request($endpoint, $method = 'POST', $data = [])
    {
        $url = $this->getBaseUrl() . $endpoint;
        
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Tbk-Api-Key-Id: ' . $this->commerceCode,
            'Tbk-Api-Key-Secret: ' . $this->apiKey,
            'Content-Type: application/json'
        ]);

        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        } elseif ($method === 'PUT') {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
        }

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return [
            'status' => $httpCode,
            'body' => json_decode($response, true)
        ];
    }

    public function createTransaction($buyOrder, $sessionId, $amount, $returnUrl)
    {
        $data = [
            'buy_order' => $buyOrder,
            'session_id' => $sessionId,
            'amount' => $amount,
            'return_url' => $returnUrl
        ];

        return $this->request('/rswebpaytransaction/api/webpay/v1.2/transactions', 'POST', $data);
    }

    public function commitTransaction($tokenWs)
    {
        return $this->request('/rswebpaytransaction/api/webpay/v1.2/transactions/' . $tokenWs, 'PUT');
    }
}
