<?php
namespace Extension\Paytr;
 
class Hash
{
    public function generateHashIframeAPI($params)
    {
        $hash_str = $params['merchant_id'] . $params['user_ip'] . $params['merchant_oid'] . $params['email'] . $params['payment_amount'] . $params['user_basket'] . $params['no_installment'] . $params['max_installment'] . $params['currency'];
        return base64_encode(hash_hmac('sha256', $hash_str . $params['merchant_salt'], $params['merchant_key'], true));
    }

    public function generateHashEftAPI($params)
    {
        $hash_str = $params['merchant_id'] . $params['user_ip'] . $params['merchant_oid'] .  $params['email'] . $params['payment_amount'] . 'eft';
        return base64_encode(hash_hmac('sha256', $hash_str . $params['merchant_salt'], $params['merchant_key'], true));
    }

    public function generateHashRefundApi($params)
    {
        return base64_encode(hash_hmac('sha256', $params['merchant_id'] . $params['merchant_oid'] . $params['amount'] . $params['merchant_salt'], $params['merchant_key'], true));
    }
}