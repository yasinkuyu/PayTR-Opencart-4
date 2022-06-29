<?php
namespace Extension\Paytr;
  
class Transaction
{
    public $db;
    public $logger;

    public function addTransaction($array, $api_name)
    {
        $this->db->query("INSERT INTO " . DB_PREFIX . "paytr_" . $this->db->escape($api_name) . "_transaction SET order_id = '" . (int)$array['order_id'] . "', merchant_oid = '" . $this->db->escape($array['merchant_oid']) . "', total = '" . (float)$array['total'] . "', is_order = 1, is_complete = '" . (int)$array['is_complete'] . "', is_failed = '" . (int)$array['is_failed'] . "', is_refunded = 0, date_added = NOW()");

        return $this->db->getLastId();
    }

    public function addTransactionForCallback($array, $api_name)
    {
        $this->db->query("INSERT INTO " . DB_PREFIX . "paytr_" . $this->db->escape($api_name) . "_transaction SET order_id = '" . (int)$array['order_id'] . "', merchant_oid = '" . $this->db->escape($array['merchant_oid']) . "', total = '" . (float)$array['total'] . "', status = '" . $this->db->escape($array['status']) . "', status_message = '" . $this->db->escape($array['status_message']) . "', is_order = 1, is_complete = '" . (int)$array['is_complete'] . "', is_failed = '" . (int)$array['is_failed'] . "', is_refunded = 0, date_added = NOW(), date_updated = NOW()");

        return $this->db->getLastId();
    }

    public function updateTransactionForCallback($array, $api_name)
    {
        $this->db->query("UPDATE " . DB_PREFIX . "paytr_" . $this->db->escape($api_name) . "_transaction SET total_paid = '" . (float)$array['total_paid'] . "', status = '" . $this->db->escape($array['status']) . "', status_message = '" . $this->db->escape($array['status_message']) . "', is_complete = 1, date_updated = NOW() WHERE merchant_oid = '" . $this->db->escape($array['merchant_oid']) . "'");
    }

    public function updateTransactionForRefund($array, $api_name)
    {
        $this->db->query("UPDATE " . DB_PREFIX . "paytr_" . $this->db->escape($api_name) . "_transaction SET is_refunded = 1, refund_status = '" . $this->db->escape($array['refund_status']) . "', refund_amount = '" . (float)$array['refund_amount'] . "', date_updated = NOW() WHERE merchant_oid = '" . $this->db->escape($array['merchant_oid']) . "'");
    }

    public function updateTransactionForEftNotify($array)
    {
        $this->db->query("UPDATE " . DB_PREFIX . "paytr_eft_transaction SET status = '" . $this->db->escape($array['status']) . "', notify_message = '" . $this->db->escape($array['notify_message']) . "', is_notify = 1, date_updated = NOW() WHERE merchant_oid = '" . $this->db->escape($array['merchant_oid']) . "'");
    }

    public function getListTransactionsForRefund($order_id, $lang, $api_name)
    {
        $response = array();

        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "paytr_" . $this->db->escape($api_name) . "_transaction WHERE order_id = '" . (int)$order_id . "' AND is_complete = 1 ORDER BY status");
        $transactions = $query->rows;

        if ($transactions) {

            $count = 0;

            $response['status'] = true;
            $response['content'] = array();
            $content = array();

            foreach ($transactions as $transaction) {
                $content = array(
                    'merchant_oid' => '<a href="https://www.paytr.com/magaza/islemler?merchant_oid=' . $transaction['merchant_oid'] . '" target="_blank">' . $transaction['merchant_oid'] . '</a>',
                    'total' => $transaction['total'],
                    'total_paid' => $transaction['total_paid'],
                    'refund_amount' => $transaction['refund_amount']
                );

                // Status
                if ($transaction['status'] == 'success') {

                    $count++;
                    $content['status'] = '<span class="label label-success">' . $lang->get('text_refund_success') . '</span>';
                } elseif ($transaction['status'] == 'info') {

                    $content['status'] = '<span class="label label-info">' . $lang->get('text_refund_info') . '</span>';
                } else {

                    $content['status'] = '<span class="label label-danger">' . $lang->get('text_refund_failed') . '</span>';
                }

                // Status Message
                if ($transaction['status_message'] == 'completed') {

                    $content['status_message'] = '<span class="label label-success">' . $lang->get('text_refund_completed') . '</span>';
                } else {

                    $content['status_message'] = '<span class="label label-danger">' . $transaction['status_message'] . '</span>';
                }

                // Notify Message
                if ($api_name == 'eft') {

                    $content['notify_message'] = $transaction['notify_message'];
                }

                // Refunded?
                if ($transaction['is_refunded']) {

                    $content['is_refunded'] = $lang->get('text_refund_yes');
                } else {

                    $content['is_refunded'] = $lang->get('text_refund_no');
                }

                // Refund Status
                if ($transaction['refund_status'] == 'partial') {

                    $content['refund_status'] = $lang->get('text_refund_partial');
                } elseif ($transaction['refund_status'] == 'full') {

                    $content['refund_status'] = $lang->get('text_refund_full');
                } else {

                    $content['refund_status'] = '';
                }

                if ($transaction['total_paid'] >= 1 && $transaction['refund_status'] != 'full' && $transaction['status_message'] == 'completed') {
                    $content['refund_form'] = true;

                    $amount = $transaction['total'];
                    if ($transaction['refund_amount'] < $transaction['total']) {
                        $amount = $transaction['total'] - $transaction['refund_amount'];
                    }

                    $content['input_refund'] = '<input type="text" name="txtPaytrRefundAmount" data-oid="' . $transaction['merchant_oid'] . '" class="form-control" style="width: 90px;float:left;margin-right:3px;" value="' . round($amount, 2) . '" />';

                    $content['button_refund'] = '<button class="btn btn-primary" data-oid="' . $transaction['merchant_oid'] . '" onclick="doPaytrRefund(this)">' . $lang->get('text_refund_refund') . '</button>';

                } else {
                    $content['refund_form'] = false;
                }

                $content['date_added'] = $transaction['date_added'];
                $content['date_updated'] = $transaction['date_updated'];

                $response['content'][] = $content;
            }

            $response['count'] = $count;

        } else {
            $response['status'] = false;
        }

        return $response;
    }

    public function getTransactionByMerchantOID($merchant_oid, $api_name, $fetch_all = false)
    {
        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "paytr_" . $this->db->escape($api_name) . "_transaction WHERE merchant_oid = '" . $this->db->escape($merchant_oid) . "'");

        if ($fetch_all) {
            $query->rows;
        }

        return $query->row;
    }

    public function getTransactionByMerchantOIDByFailed($merchant_oid, $api_name, $fetch_all = false)
    {
        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "paytr_" . $this->db->escape($api_name) . "_transaction WHERE merchant_oid = '" . $this->db->escape($merchant_oid) . "' AND is_failed = 0");

        if ($fetch_all) {
            $query->rows;
        }

        return $query->row;
    }

    public function getTransactionByOrderId($order_id, $api_name, $fetch_all = false)
    {
        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "paytr_" . $this->db->escape($api_name) . "_transaction WHERE order_id = '" . (int)$order_id . "'");

        if ($fetch_all) {
            return $query->rows;
        }

        return $query->row;
    }
}