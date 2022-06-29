<?php
namespace Extension\Paytr;

use \Extension\Paytr\Hash;

class Refund
{
    public $db;
    public $logger;

    private $hash;

    const BASE_URL = 'https://www.paytr.com/odeme/iade';

    public function __construct()
    {
        $this->hash = new Hash();
    }

    public function doRefund($params)
    {
        $response = array();

        $curl_params = array(
            'merchant_id' => $params['merchant_id'],
            'merchant_oid' => $params['merchant_oid'],
            'return_amount' => $params['amount'],
            'paytr_token' => $this->hash->generateHashRefundApi($params)
        );

        /*
        * XXX: DİKKAT: lokal makinanızda "SSL certificate problem: unable to get local issuer certificate" uyarısı alırsanız eğer
        * aşağıdaki kodu açıp deneyebilirsiniz. ANCAK, güvenlik nedeniyle sunucunuzda (gerçek ortamınızda) bu kodun kapalı kalması çok önemlidir!
        * curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        */
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, self::BASE_URL);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $curl_params);
        curl_setopt($ch, CURLOPT_FRESH_CONNECT, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 90);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 90);
        $result = @curl_exec($ch);

        if (curl_error($ch)) {

            curl_close($ch);

            $response['status'] = 'error';
            $response['err_no'] = '';
            $response['err_msg'] = 'cURL connection error';

            // Add Log
            $this->logger->write('PayTR: cURL connection problem on the refund.');
        } else {

            curl_close($ch);

            $result = json_decode($result, 1);
            $response = $result;
        }

        return $response;
    }

}