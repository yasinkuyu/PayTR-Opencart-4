<?php
namespace Opencart\Catalog\Controller\Extension\PayTr\Payment;

class PayTrCheckout extends \Opencart\System\Engine\Controller
{
    private $error = array();
    private $oc_version = 'PAYTROC4';
    private $paytr;

	public function __construct($registry) {
		parent::__construct($registry);

        require_once DIR_EXTENSION . 'paytr/system/library/paytr/autoload.php';

        $this->paytr = new \Extension\Paytr\System\Library\PayTr($this->registry);
	}

    public function index()
    {

        $data['page_layout'] = $this->config->get('payment_paytr_checkout_module_layout');

        if ($data['page_layout'] != 'onepage') {
            $data = $this->getToken();
        }

        return $this->load->view('extension/paytr/payment/paytr_checkout', $data);
    }

    public function onepage()
    {
        $json = array();

        if (!isset($this->session->data['order_id'])) {
			$json['error'] = $this->language->get('error_order');
        }

        if (!isset($this->session->data['payment_method'])) {
			$json['error'] = $this->language->get('error_payment_method');
        }

        if (!isset($this->session->data['payment_method']) || $this->session->data['payment_method'] != 'paytr_checkout') {
			$json['error'] = $this->language->get('error_payment_method');
		}

        $json['status'] = 'success';

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    public function form() 
    {
        if (!isset($this->session->data['order_id'])) {
			$data['error'] = $this->language->get('error_order');
        }

        if (!isset($this->session->data['payment_method'])) {
			$data['error'] = $this->language->get('error_payment_method');
        }

        if ($this->session->data['payment_method'] != 'paytr_checkout') {
			$data['error'] = $this->language->get('error_payment_method');
        }

        if ($this->config->get('payment_paytr_checkout_module_layout') != 'onepage') {
			$data['error'] = $this->language->get('error_payment_method') . "xxx";
        }

        $this->document->setTitle($this->config->get('config_meta_title'));
        $this->document->setDescription($this->config->get('config_meta_description'));
        $this->document->setKeywords($this->config->get('config_meta_keyword'));

        $data = $this->getToken();

        $data['column_left'] = $this->load->controller('common/column_left');
        $data['column_right'] = $this->load->controller('common/column_right');
        $data['content_top'] = $this->load->controller('common/content_top');
        $data['content_bottom'] = $this->load->controller('common/content_bottom');
        $data['footer'] = $this->load->controller('common/footer');
        $data['header'] = $this->load->controller('common/header');

        $this->response->setOutput($this->load->view('extension/paytr/payment/paytr_checkout_onepage', $data));
    }

    public function callback(): void
    {
        if (!isset($_POST) || empty($_POST)) {
            echo '';
            exit;
        }

        $this->load->model('checkout/order');

        if ($_POST['payment_type'] == 'eft') {

            $this->paytr->chkHash($_POST, 'eft');

            $this->load->language('extension/paytr/payment/paytr_eft_transfer');

            $this->paytr->eftCallback($_POST, $this->oc_version);
        } else {

            $this->paytr->chkHash($_POST, 'iframe');

            $this->load->language('extension/paytr/payment/paytr_checkout');

            $this->paytr->iframeCallback($_POST, $this->oc_version);
        }
    }

    protected function getToken() 
    {
        $this->load->model('checkout/order');
        $this->load->language('extension/paytr/payment/paytr_checkout');

        if (!isset($this->session->data['order_id'])) {
            return $this->response->redirect($this->url->link('common/home'));
        }

        $paytr_params = array();
        $data = array();

        // Get Order
        $order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);

        // Get Products
        $products = $this->cart->getProducts();

        // Credentials
        $paytr_params['merchant_id'] = $this->config->get('payment_paytr_checkout_merchant_id');
        $paytr_params['merchant_key'] = $this->config->get('payment_paytr_checkout_merchant_key');
        $paytr_params['merchant_salt'] = $this->config->get('payment_paytr_checkout_merchant_salt');

        // User
        $paytr_params['user_ip'] = $this->getIp();
        $paytr_params['email'] = $order_info['email'];

        // Basket && Installments
        $basket_installment = $this->paytr->iframe->getBasketMaxInstallment($products, $this->config->get('payment_paytr_checkout_installment_number'), $this->config);
        $paytr_params['user_basket'] = $basket_installment['user_basket'];
        $paytr_params['max_installment'] = $basket_installment['max_installment'];
        $paytr_params['no_installment'] = ($paytr_params['max_installment'] == 1) ? 1 : 0;

        // User Info
        $paytr_params['user_name'] = $order_info['payment_firstname'] . ' ' . $order_info['payment_lastname'];
        $paytr_params['user_address'] = $order_info['payment_address_1'] . ' ' . $order_info['payment_address_2'] . ' ' . $order_info['payment_postcode'] . ' ' . $order_info['payment_city'] . ' ' . $order_info['payment_zone'] . ' ' . $order_info['payment_iso_code_3'];
        $paytr_params['user_phone'] = $order_info['telephone'];

        // Order
        $paytr_params['merchant_oid'] = uniqid() . $this->oc_version . $order_info['order_id'];
        $paytr_params['currency'] = strtoupper($order_info['currency_code']);
        $paytr_params['payment_amount'] = ($this->currency->format($order_info['total'], $order_info['currency_code'], $order_info['currency_value'], false) * 100);

        // URLs
        $paytr_params['merchant_ok_url'] = $this->url->link('checkout/success', '', true);
        $paytr_params['merchant_fail_url'] = $this->url->link('checkout/cart', '', true);

        // Language
        if ($this->config->get('payment_paytr_checkout_lang') == 0) {
            $lang_arr = array('tr', 'tr-tr', 'tr_tr', 'turkish', 'turk', 'türkçe', 'turkce', 'try', 'tl');

            $langstring = isset($this->session->data['language']) ?? $this->session->data['language'];
            $paytr_params['lang'] = (in_array(strtolower($langstring), $lang_arr) == 1 ? 'tr' : 'en');
        } else {
            $paytr_params['lang'] = ($this->config->get('payment_paytr_checkout_lang') == 2 ? 'en' : 'tr');
        }

        if (function_exists('curl_version')) {

            $getToken = $this->paytr->iframe->getToken($paytr_params);

            if ($getToken['status'] == 'success') {

                // Save Transaction
                $transaction['order_id'] = $order_info['order_id'];
                $transaction['merchant_oid'] = $paytr_params['merchant_oid'];
                $transaction['total'] = $this->currency->format($order_info['total'], $order_info['currency_code'], $order_info['currency_value'], false);
                $transaction['is_failed'] = 0;
                $transaction['is_complete'] = 0;

                try {

                    if ($this->paytr->transaction->addTransaction($transaction, 'iframe')) {

                        $data['iframe_token'] = $getToken['iframe_token'];
                    } else {

                        $this->error['error_paytr_checkout_transaction_save'] = $this->language->get('error_paytr_checkout_transaction_save');
                    }

                } catch (Exception $exception) {
                    $this->error['error_paytr_checkout_transaction_install'] = $this->language->get('error_paytr_checkout_transaction_install');
                }
            } else {
                $this->error['error_paytr_iframe_failed'] = $this->language->get('error_paytr_iframe_failed') . $getToken['status_message'];
            }
        } else {
            $this->error['error_paytr_checkout_curl'] = $this->language->get('error_paytr_checkout_curl');
        }

        if ($this->error) {
            $data['errors'] = $this->error;
        }

        return $data;
    }

    protected function getIp() 
    {
        if (isset($_SERVER["HTTP_CLIENT_IP"])) {
            $ip = $_SERVER["HTTP_CLIENT_IP"];
        } elseif (isset($_SERVER["HTTP_X_FORWARDED_FOR"])) {
            $ip = $_SERVER["HTTP_X_FORWARDED_FOR"];
        } else {
            $ip = $_SERVER["REMOTE_ADDR"];
        }

        return $ip;
    }
}