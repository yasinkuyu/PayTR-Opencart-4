<?php
namespace Extension\Paytr;

use \Extension\Paytr\Hash;

class Eft
{
    public $db;
    public $logger;
    public $config;

    private $hash;

    public function __construct()
    {
        $this->hash = new Hash();
    }

    public function getToken($params)
    {
        $response = array();

        $paytr_token = $this->hash->generateHashEftAPI($params);

        $post_val = array(
            'merchant_id' => $params['merchant_id'],
            'user_ip' => $params['user_ip'],
            'merchant_oid' => $params['merchant_oid'],
            'email' => $params['email'],
            'payment_amount' => $params['payment_amount'],
            'payment_type' => 'eft',
            'paytr_token' => $paytr_token,
            'debug_on' => 1
        );

        /*
        * XXX: DİKKAT: lokal makinanızda "SSL certificate problem: unable to get local issuer certificate" uyarısı alırsanız eğer
        * aşağıdaki kodu açıp deneyebilirsiniz. ANCAK, güvenlik nedeniyle sunucunuzda (gerçek ortamınızda) bu kodun kapalı kalması çok önemlidir!
        * curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        * */
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://www.paytr.com/odeme/api/get-token");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_val);
        curl_setopt($ch, CURLOPT_FRESH_CONNECT, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 90);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 90);
        curl_setopt($ch, CURLOPT_SSLVERSION, 6);
        $result = @curl_exec($ch);

        if (curl_errno($ch)) {

            $response['status'] = 'failed';
            $response['status_message'] = 'PAYTR IFRAME connection error. err: ' . curl_error($ch);

            curl_close($ch);
        } else {

            $result = json_decode($result, 1);

            if ($result['status'] == 'success') {

                $response['status'] = 'success';
                $response['eft_token'] = $result['token'];
            } else {

                $response['status'] = 'failed';
                $response['status_message'] = $result['reason'];
            }
        }

        return $response;
    }
}