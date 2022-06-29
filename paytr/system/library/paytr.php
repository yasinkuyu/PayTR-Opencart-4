<?php
namespace Extension\Paytr\System\Library;

use \Extension\Paytr\Transaction;
use \Extension\Paytr\Refund;
use \Extension\Paytr\Iframe;
use \Extension\Paytr\Eft;

final class Paytr
{
    private $registry;
    private $logger;
    private $db;
    private $config;
    private $load;
    private $currency;
    private $language;

    public $transaction;
    public $refund;
    public $iframe;
    public $eft;

    public function __construct($registry)
    {
        $this->registry = $registry;

        $this->logger = $registry->get('log');
        $this->db = $registry->get('db');
        $this->config = $registry->get('config');
        $this->load = $registry->get('load');
        $this->currency = $registry->get('currency');
        $this->language = $registry->get('language');

        $this->transaction = new Transaction();
        $this->transaction->db = $this->db;
        $this->transaction->logger = $this->logger;

        $this->refund = new Refund();
        $this->refund->db = $this->db;
        $this->refund->logger = $this->logger;

        $this->iframe = new Iframe();
        $this->iframe->db = $this->db;
        $this->iframe->logger = $this->logger;
        $this->iframe->config = $this->config;

        $this->eft = new Eft();
        $this->eft->db = $this->db;
        $this->eft->logger = $this->logger;
        $this->eft->config = $this->config;
    }

    public static function installmentOptions($lang, $category_based = false)
    {
        $options = array();

        if ($lang == 'tr') {
            $options = array(
                0 => "Tüm Taksit Seçenekleri",
                1 => "Tek Çekim (Taksit Yok)",
                2 => "2 Taksit'e kadar",
                3 => "3 Taksit'e kadar",
                4 => "4 Taksit'e kadar",
                5 => "5 Taksit'e kadar",
                6 => "6 Taksit'e kadar",
                7 => "7 Taksit'e kadar",
                8 => "8 Taksit'e kadar",
                9 => "9 Taksit'e kadar",
                10 => "10 Taksit'e kadar",
                11 => "11 Taksit'e kadar",
                12 => "12 Taksit'e kadar"
            );

            if ($category_based) {
                $options[13] = 'KATEGORİ BAZLI';
            }
        } else {
            $options = array(
                0 => 'All Installment Options',
                1 => 'One Shot (No Installment)',
                2 => 'Up to 2 Installments',
                3 => 'Up to 3 Installments',
                4 => 'Up to 4 Installments',
                5 => 'Up to 5 Installments',
                6 => 'Up to 6 Installments',
                7 => 'Up to 7 Installments',
                8 => 'Up to 8 Installments',
                9 => 'Up to 9 Installments',
                10 => 'Up to 10 Installments',
                11 => 'Up to 11 Installments',
                12 => 'Up to 12 Installments'
            );

            if ($category_based) {
                $options[13] = 'CATEGORY BASED';
            }
        }

        return $options;
    }

    public function categoryParser($lang_id)
    {
        $query = $this->db->query("SELECT c.category_id AS 'id',  c.parent_id AS 'parent_id', cd.name AS 'name' FROM " . DB_PREFIX . "category c LEFT JOIN " . DB_PREFIX . "category_description cd ON (c.category_id = cd.category_id) WHERE cd.language_id = '" . (int)$lang_id . "' ORDER BY c.sort_order, cd.name ASC");
        $categories = $query->rows;
        $category_tree = array();
        foreach ($categories as $key => $item) {
            if ($item['parent_id'] == 0) {
                $category_tree[$item['id']] = array('id' => $item['id'], 'name' => $item['name']);
                $this->parentCategoryParser($categories, $category_tree[$item['id']]);
            }
        }

        return $category_tree;
    }

    public function categoryParserClear($tree, $level = 0, $arr = array(), &$finish_him = array())
    {
        foreach ($tree as $id => $item) {
            if ($level == 0) {
                unset($arr);
                $arr = array();
                $arr[] = $item['name'];
            } elseif ($level == 1 or $level == 2) {
                if (count($arr) == ($level + 1)) {
                    $deleted = array_pop($arr);
                }
                $arr[] = $item['name'];
            }
            if ($level < 3) {
                $nav = null;
                foreach ($arr as $key => $val) {
                    $nav .= $val . ($level != 0 ? ' > ' : null);
                }
                $finish_him[$item['id']] = rtrim($nav, ' > ') . '<br>';
                if (!empty($item['parent'])) {
                    $this->categoryParserClear($item['parent'], $level + 1, $arr, $finish_him);
                }
            }
        }
    }

    protected function parentCategoryParser(&$categories = array(), &$category_tree = array())
    {
        foreach ($categories as $key => $item) {
            if ($item['parent_id'] == $category_tree['id']) {
                $category_tree['parent'][$item['id']] = array('id' => $item['id'], 'name' => $item['name']);
                $this->parentCategoryParser($categories, $category_tree['parent'][$item['id']]);
            }
        }
    }

    public function eftCallback($params, $version)
    {
        $model_checkout_order = $this->registry->get('model_checkout_order');

        $order_id = explode($version, $params['merchant_oid']);
        $order = $model_checkout_order->getOrder($order_id[1]);

        if (!$order) {

            echo 'OK';
            exit;
        }

        $paytr_transaction = $this->transaction->getTransactionByMerchantOID($params['merchant_oid'], 'eft');

        if (!$paytr_transaction || !$paytr_transaction['is_order']) {

            echo 'OK';
            exit;
        }

        $merchant['id'] = $this->config->get('payment_paytr_eft_transfer_merchant_id');
        $merchant['key'] = $this->config->get('payment_paytr_eft_transfer_merchant_key');
        $merchant['salt'] = $this->config->get('payment_paytr_eft_transfer_merchant_salt');

        $completedStatus = $this->config->get('payment_paytr_eft_transfer_order_completed_id');
        $canceledStatus = $this->config->get('payment_paytr_eft_transfer_order_canceled_id');
        $notifyStatus = $this->config->get('payment_paytr_eft_transfer_notify');

        $transaction = array();
        $transaction['merchant_oid'] = $params['merchant_oid'];

        if ($params['status'] == 'success') {

            $total_amount = round($params['total_amount'] / 100, 2);

            $transaction['status'] = $params['status'];
            $transaction['status_message'] = 'completed';
            $transaction['total_paid'] = $total_amount;

            $note_params = array(
                'status' => $params['status'],
                'merchant_oid' => $params['merchant_oid'],
                'total_amount' => $total_amount,
                'currency_code' => $order['currency_code'],
                'currency_value' => $order['currency_value'],
            );

            try {
                $note = $this->callbackNote($note_params, 0, 'eft');

                // Update Transaction Table
                $this->transaction->updateTransactionForCallback($transaction, 'eft');

                // Add Order History
                $model_checkout_order->addOrderHistory($order['order_id'], $completedStatus, $note, $notifyStatus);
            } catch (Exception $exception) {
                echo $exception->getMessage();
                exit;
            }

            echo 'OK';
            exit;
        } else {

            // Transaction
            $transaction['status'] = $params['status'];
            $transaction['status_message'] = $params['failed_reason_code'] . " - " . $params['failed_reason_msg'];
            $transaction['is_complete'] = 1;
            $transaction['total_paid'] = 0;

            if (array_key_exists('failed_reason_code', $params) and $params['failed_reason_code'] != 6) {

                if ($paytr_transaction['status'] == 'success') {

                    // Two attempts have been made with the incoming merchant_oid. 1st attempt failed. Run here if the failed notification comes after the successful notification.
                    $addTransaction['order_id'] = $order['order_id'];
                    $addTransaction['merchant_oid'] = $params['merchant_oid'];
                    $addTransaction['total'] = $this->currency->format($order['total'], $order['currency_code'], $order['currency_value'], false);
                    $addTransaction['is_failed'] = 1;
                    $addTransaction['is_complete'] = 1;
                    $addTransaction['status'] = $transaction['status'];
                    $addTransaction['status_message'] = $transaction['status_message'];

                    $this->transaction->addTransactionForCallback($addTransaction, 'eft');
                } else {

                    // Update Transaction Table
                    $this->transaction->updateTransactionForCallback($transaction, 'eft');
                }

                $note = $this->callbackNote($params, 0, 'eft');

                // Add Order History
                $model_checkout_order->addOrderHistory($order['order_id'], $canceledStatus, $note, 0);
            }

            echo 'OK';
            exit;
        }
    }

    public function eftNotification($params)
    {
        $merchant['merchant_key'] = $this->config->get('payment_paytr_eft_transfer_merchant_key');
        $merchant['merchant_salt'] = $this->config->get('payment_paytr_eft_transfer_merchant_salt');

        $hash = base64_encode(hash_hmac('sha256', $params['merchant_oid'] . $params['bank'] . $merchant['merchant_salt'], $merchant['merchant_key'], true));

        if ($hash != $_POST['hash']) {
            die('PAYTR notification failed: bad hash');
        }

        $transaction = array();
        $note = '';

        // Note Start
        $note .= '<div class="paytr-note">';
        $note .= '<div class="paytr-note_title">PAYTR SİSTEM NOTU - <span class="paytr-note_status_title status-info">Bildirim Yapıldı</span></div>';
        $note .= '<div class="paytr-note_sub_title"><span>Banka</span>: ' . $params['bank'] . '</div>';
        $note .= '<div class="paytr-note_sub_title"><span>Ad Soyad</span>: ' . $params['user_name'] . '</div>';
        $note .= '<div class="paytr-note_sub_title"><span>Telefon</span>: ' . $params['user_phone'] . '</div>';
        $note .= '<div class="paytr-note_sub_title"><span>Tarih</span>: ' . $params['payment_sent_date'] . '</div>';
        $note .= '<div class="paytr-note_sub_title"><span>PayTR Sipariş No</span>: ' . $params['merchant_oid'] . '</div>';
        $note .= '</div>';
        // Note End

        $transaction['status'] = $params['status'];
        $transaction['merchant_oid'] = $params['merchant_oid'];
        $transaction['notify_message'] = $note;

        $this->transaction->updateTransactionForEftNotify($transaction);

        echo 'OK';
        exit;
    }

    public function iframeCallback($params, $version)
    {
        $model_checkout_order = $this->registry->get('model_checkout_order');

        $order_id = explode($version, $params['merchant_oid']);
        $order = $model_checkout_order->getOrder($order_id[1]);

        if (!$order) {

            echo 'OK';
            exit;
        }

        $paytr_transaction = $this->transaction->getTransactionByMerchantOIDByFailed($params['merchant_oid'], 'iframe');

        if (!$paytr_transaction || !$paytr_transaction['is_order']) {

            echo 'OK';
            exit;
        }

        $merchant['id'] = $this->config->get('payment_paytr_checkout_merchant_id');
        $merchant['key'] = $this->config->get('payment_paytr_checkout_merchant_key');
        $merchant['salt'] = $this->config->get('payment_paytr_checkout_merchant_salt');

        $completedStatus = $this->config->get('payment_paytr_checkout_order_completed_id');
        $canceledStatus = $this->config->get('payment_paytr_checkout_order_canceled_id');

        $transaction = array();
        $transaction['merchant_oid'] = $params['merchant_oid'];

        if ($params['status'] == 'success') {

            $notifyStatus = $this->config->get('payment_paytr_checkout_notify');
            $installment_total = $this->config->get('payment_paytr_checkout_ins_total');
            $order_total = $this->config->get('payment_paytr_checkout_order_total');

            $total_amount = round($params['total_amount'] / 100, 2);
            $installment_dif = $params['total_amount'] - $params['payment_amount'];

            $transaction['status'] = $params['status'];
            $transaction['status_message'] = 'completed';
            $transaction['is_complete'] = 1;
            $transaction['total_paid'] = $total_amount;

            $note_params = array(
                'status' => $params['status'],
                'merchant_oid' => $params['merchant_oid'],
                'total_amount' => $total_amount,
                'currency_code' => $order['currency_code'],
                'currency_value' => $order['currency_value'],
            );

            if (array_key_exists('installment_count', $params)) {
                $note_params['installment_count'] = $params['installment_count'];
            }

            if ($order['order_status_id'] == 0 && !$paytr_transaction['is_complete'] && $installment_dif > 0) {
                if ($order_total) {

                    // Update order total in order table.
                    $this->editOrderTotal($order['order_id'], $total_amount);
                }

                if ($installment_total == 1) {

                    // Edit Total Amount and Add Installment Amount to the invoice.
                    $this->editTotalItem($order['order_id'], $total_amount, round($installment_dif / 100, 2), $this->language->get('text_total'));
                } else if ($installment_total == 2) {

                    // Change Total Amount in the invoice.
                    $this->editTotalValue($order['order_id'], $total_amount);
                }
            }

            $note = $this->callbackNote($note_params, $installment_dif, 'iframe');

            // Update Transaction Table
            $this->transaction->updateTransactionForCallback($transaction, 'iframe');

            // Add Order History
            $model_checkout_order->addOrderHistory($order['order_id'], $completedStatus, $note, $notifyStatus);

            echo 'OK';
            exit;
        } else {

            // Transaction
            $transaction['status'] = $params['status'];
            $transaction['status_message'] = $params['failed_reason_code'] . " - " . $params['failed_reason_msg'];
            $transaction['is_complete'] = 1;
            $transaction['total_paid'] = 0;

            if ($order['order_status_id'] != 0) {
                if (array_key_exists('failed_reason_code', $params) and $params['failed_reason_code'] != 6) {
                    if ($paytr_transaction['status'] == 'success') {

                        // Two attempts have been made with the incoming merchant_oid. 1st attempt failed. Run here if the failed notification comes after the successful notification.
                        $addTransaction['order_id'] = $order['order_id'];
                        $addTransaction['merchant_oid'] = $params['merchant_oid'];
                        $addTransaction['total'] = $this->currency->format($order['total'], $order['currency_code'], $order['currency_value'], false);
                        $addTransaction['is_failed'] = 1;
                        $addTransaction['is_complete'] = 1;
                        $addTransaction['status'] = $transaction['status'];
                        $addTransaction['status_message'] = $transaction['status_message'];

                        $this->transaction->addTransactionForCallback($addTransaction, 'iframe');
                    } else {

                        // The transaction was made with the incoming merchant oid, but it was not completed successfully.
                        // Unsuccessful transaction. Just mirror it to the paytr_transaction table.

                        // Update Transaction Table
                        $this->transaction->updateTransactionForCallback($transaction, 'iframe');
                    }
                }

                echo 'OK';
                exit;
            } else {

                if (array_key_exists('failed_reason_code', $params) and $params['failed_reason_code'] != 6) {

                    $note = $this->callbackNote($params, 0, 'iframe');

                    // Update Transaction Table
                    $this->transaction->updateTransactionForCallback($transaction, 'iframe');

                    // Add Order History
                    $model_checkout_order->addOrderHistory($order['order_id'], $canceledStatus, $note, 0);
                }

                echo 'OK';
                exit;
            }
        }
    }

    protected function callbackNote($params, $installment_dif, $api_name)
    {
        $note = '';
        $title = '';
        $amount_title = '';
        $amount_status = '';
        $installment_title = '';

        if ($params['status'] == 'success') {
            $title = '<span class="paytr-note_status_title status-success">Ödeme Onaylandı.</span>';
            $amount_title = '<div class="paytr-note_sub_title"><span>Ödeme Tutarı</span>: ' . $this->currency->format($params['total_amount'], $params['currency_code'], $params['currency_value']) . '</div>';

            if ($api_name == 'iframe') {

                if ($installment_dif > 0) {

                    $installment_title .= '<div class="paytr-note_sub_title"><span>Vade Farkı</span>: ' . $this->currency->format(round($installment_dif / 100, 2), $params['currency_code'], $params['currency_value']) . '</div>';
                }

                if (array_key_exists('installment_count', $params)) {

                    $installment_title .= '<div class="paytr-note_sub_title"><span>Taksit Sayısı</span>: ' . ($params['installment_count'] == 1 ? 'Tek Çekim' : $params['installment_count']) . '</div>';
                }
            }
        }

        if ($params['status'] == 'failed') {
            $title = '<span class="paytr-note_status_title status-danger">Ödeme Başarısız.</span>';
            $amount_title = '<div class="paytr-note_sub_title"><span>Ödeme Durumu</span>: Başarısız.</div>';
            $amount_status = '<div class="paytr-note_sub_title"><span>Ödeme Hatası</span>: ' . $params['failed_reason_msg'] . '</div>';
        }

        // Note Start
        $note .= '<div class="paytr-note">';
        $note .= '<div class="paytr-note_title">PAYTR SİSTEM NOTU - ' . $title . '</div>';
        $note .= $amount_title . '';
        $note .= $amount_status;
        $note .= $installment_title;
        $note .= '<div class="paytr-note_sub_title"><span>PayTR Sipariş No</span>: ' . $params['merchant_oid'] . '</div>';
        $note .= '</div>';
        // Note End

        return $note;
    }

    public function chkHash($params, $api_name)
    {
        if ($api_name == 'iframe') {
            $key = $this->config->get('payment_paytr_checkout_merchant_key');
            $salt = $this->config->get('payment_paytr_checkout_merchant_salt');
        }

        if ($api_name == 'eft') {
            $key = $this->config->get('payment_paytr_eft_transfer_merchant_key');
            $salt = $this->config->get('payment_paytr_eft_transfer_merchant_salt');
        }

        $created_hash = base64_encode(hash_hmac('sha256', $params['merchant_oid'] . $salt . $params['status'] . $params['total_amount'], $key, true));

        if ($created_hash != $params['hash']) {
            die('PAYTR notification failed: bad hash.');
        }

        return true;
    }

    protected function getOrderHistory($order_id, $order_status_id)
    {
        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "order_history WHERE order_id = '" . (int)$order_id . "' AND order_status_id = '" . (int)$order_status_id . "'");

        return $query->rows;
    }

    protected function editOrderTotal($order_id, $total)
    {
        // Edit total value in orders table.
        $this->db->query("UPDATE `" . DB_PREFIX . "order` SET total = '" . (float)$total . "', date_modified = NOW() WHERE order_id = '" . (int)$order_id . "'");
    }

    protected function editTotalItem($order_id, $total, $amount, $title)
    {
        // Edit total value in order_total table
        $this->db->query("UPDATE `" . DB_PREFIX . "order_total` SET value = '" . (float)$total . "' WHERE order_id = '" . (int)$order_id . "' AND code = 'total' ");

        // Add total value in order_total table
        $this->db->query("INSERT INTO `" . DB_PREFIX . "order_total` SET order_id = '" . (int)$order_id . "', code = 'paytr_checkout', title = '" . $this->db->escape($title) . "', value = '" . (float)$amount . "', sort_order = '4' ");
    }

    protected function editTotalValue($order_id, $total)
    {
        // Edit total value in order_total table
        $this->db->query("UPDATE `" . DB_PREFIX . "order_total` SET value = '" . (float)$total . "' WHERE order_id = '" . (int)$order_id . "' AND code = 'total' ");
    }
}