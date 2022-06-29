<?php
namespace Opencart\Admin\Controller\Extension\PayTr\Payment;

class PayTrCheckout extends \Opencart\System\Engine\Controller
{
    private $error = array();
	private $paytr;

	public function __construct($registry) {
		parent::__construct($registry);
        
        require_once DIR_EXTENSION . 'paytr/system/library/paytr/autoload.php';

        $this->paytr = new \Extension\Paytr\System\Library\PayTr($this->registry);

	}

    public function index(): void 
    {
        $this->load->language('extension/paytr/payment/paytr_checkout');
        $this->load->model('setting/setting');
        $this->load->model('localisation/order_status');

        $this->document->setTitle($this->language->get('heading_title'));
 
        $data['heading_title'] = $this->language->get('heading_title');

        $data['text_settings'] = $this->language->get('text_settings');
        $data['text_general'] = $this->language->get('text_general');
        $data['text_order_status'] = $this->language->get('text_order_status');
        $data['text_module_settings'] = $this->language->get('text_module_settings');
        $data['text_enabled'] = $this->language->get('text_enabled');
        $data['text_disabled'] = $this->language->get('text_disabled');
        $data['text_select'] = $this->language->get('text_select');
        $data['text_ins_total'] = $this->language->get('text_ins_total');

        $data['entry_merchant_id'] = $this->language->get('entry_merchant_id');
        $data['entry_merchant_key'] = $this->language->get('entry_merchant_key');
        $data['entry_merchant_salt'] = $this->language->get('entry_merchant_salt');
        $data['entry_language'] = $this->language->get('entry_language');
        $data['entry_total'] = $this->language->get('entry_total');
        $data['entry_module_layout'] = $this->language->get('entry_module_layout');
        $data['entry_status'] = $this->language->get('entry_status');
        $data['entry_sort_order'] = $this->language->get('entry_sort_order');
        $data['entry_payment_complete'] = $this->language->get('entry_payment_complete');
        $data['entry_payment_failed'] = $this->language->get('entry_payment_failed');
        $data['entry_notify_status'] = $this->language->get('entry_notify_status');
        $data['entry_ins_total'] = $this->language->get('entry_ins_total');
        $data['entry_order_total'] = $this->language->get('entry_order_total');
        $data['entry_max_installments'] = $this->language->get('entry_max_installments');

        $data['help_paytr_checkout'] = $this->language->get('help_paytr_checkout');
        $data['help_total'] = $this->language->get('help_total');
        $data['help_notify'] = $this->language->get('help_notify');
        $data['help_ins_total'] = $this->language->get('help_ins_total');
        $data['help_order_total'] = $this->language->get('help_order_total');

        $data['button_save'] = $this->language->get('button_save');
        $data['button_cancel'] = $this->language->get('button_cancel');

        $data['errors_message'] = array(
            'warning' => $this->language->get('error_warning'),
            'paytr_checkout_merchant_id' => $this->language->get('error_paytr_checkout_merchant_id'),
            'paytr_checkout_merchant_id_val' => $this->language->get('error_paytr_checkout_merchant_id_val'),
            'paytr_checkout_merchant_key' => $this->language->get('error_paytr_checkout_merchant_key'),
            'paytr_checkout_merchant_key_len' => $this->language->get('error_paytr_checkout_merchant_key_len'),
            'paytr_checkout_merchant_salt' => $this->language->get('error_paytr_checkout_merchant_salt'),
            'paytr_checkout_merchant_salt_len' => $this->language->get('error_paytr_checkout_merchant_salt_len'),
            'paytr_checkout_order_completed_id' => $this->language->get('error_paytr_checkout_order_completed_id'),
            'paytr_checkout_order_canceled_id' => $this->language->get('error_paytr_checkout_order_canceled_id'),
            'paytr_checkout_merchant_general' => $this->language->get('error_paytr_checkout_merchant_general'),
            'paytr_checkout_installment_number' => $this->language->get('error_paytr_checkout_installment_number'),
        );

        $data['breadcrumbs'] = array();
        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true),
        );
        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_extensions'),
            'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'], true),
        );
        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('extension/paytr/payment/paytr_checkout', 'user_token=' . $this->session->data['user_token'], true),
        );

        $data['save'] = $this->url->link('extension/paytr/payment/paytr_checkout|save', 'user_token=' . $this->session->data['user_token']);
        $data['back'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=payment');

        //$data['action'] = $this->url->link('extension/paytr/payment/paytr_checkout', 'user_token=' . $this->session->data['user_token'], true);
        $data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'], true);

        $data['user_token'] = $this->request->get['user_token'];
 
        $data['installment_arr'] = $this->paytr->installmentOptions($this->language->get('code'), true);
        $data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();

        if ($this->language->get('code') == "tr") {
            $data['language_arr'] = array(0 => 'Otomatik', 1 => 'Türkçe', 2 => 'İngilizce');
        } else {
            $data['language_arr'] = array(0 => 'Automatic', 1 => 'Turkish', 2 => 'English');
        }

        $data['module_layout'] = array(
            'standard' => $this->language->get('text_module_layout_standard'),
            'onepage' => $this->language->get('text_module_layout_one')
        );

      
        $data['payment_paytr_checkout_merchant_id'] = $this->config->get('payment_paytr_checkout_merchant_id');
        $data['payment_paytr_checkout_merchant_key'] = $this->config->get('payment_paytr_checkout_merchant_key');
        $data['payment_paytr_checkout_merchant_salt'] = $this->config->get('payment_paytr_checkout_merchant_salt');
        $data['payment_paytr_checkout_lang'] = $this->config->get('payment_paytr_checkout_lang');
        $data['payment_paytr_checkout_total'] = $this->config->get('payment_paytr_checkout_total');
        $data['payment_paytr_checkout_module_layout'] = $this->config->get('payment_paytr_checkout_module_layout');
        $data['payment_paytr_checkout_status'] = $this->config->get('payment_paytr_checkout_status');
        $data['payment_paytr_checkout_sort_order'] = $this->config->get('payment_paytr_checkout_sort_order');
        $data['payment_paytr_checkout_order_completed_id'] = $this->config->get('payment_paytr_checkout_order_completed_id');
        $data['payment_paytr_checkout_order_canceled_id'] = $this->config->get('payment_paytr_checkout_order_canceled_id');
        $data['payment_paytr_checkout_notify'] = $this->config->get('payment_paytr_checkout_notify');
        $data['payment_paytr_checkout_ins_total'] = $this->config->get('payment_paytr_checkout_ins_total');
        $data['payment_paytr_checkout_order_total'] = $this->config->get('payment_paytr_checkout_order_total');
  
        if (!$this->config->get('payment_paytr_checkout_installment_number') or $this->config->get('payment_paytr_checkout_installment_number') == null) {
            $data['payment_paytr_checkout_installment_number'] = 0;
        } else {
            $data['payment_paytr_checkout_installment_number'] = $this->config->get('payment_paytr_checkout_installment_number');
        }

        $data['errors'] = $this->error;

        $data['paytr_icon_loader'] = 'view/javascript/paytr/paytr_loader.gif';

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('extension/paytr/payment/paytr_checkout', $data));
    }
    
    public function save(): void {
 
        $this->load->language('extension/paytr/payment/paytr_checkout');
        
        $this->load->model('extension/paytr/payment/paytr_checkout');
        
        if (!$this->user->hasPermission('modify', 'extension/paytr/payment/paytr_checkout')) {
            $this->error['warning'] = $this->language->get('error_warning');
        }
        
        $this->validate();

        if (!$this->error) {
            $this->load->model('setting/setting');

            $this->model_setting_setting->editSetting('payment_paytr_checkout', $this->request->post);
            
            $data['success'] = $this->language->get('text_success');
        }
        
        $data['error'] = $this->error;
        
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($data));

    }
         
    public function install()
    {
        $this->load->model('setting/setting');
        $this->load->model('extension/paytr/payment/paytr_checkout');

        $data['payment_paytr_checkout_lang'] = '0';
        $data['payment_paytr_checkout_notify'] = '0';
        $data['payment_paytr_checkout_ins_total'] = '0';
        $data['payment_paytr_checkout_order_total'] = '0';
        $data['payment_paytr_checkout_geo_zone_id'] = '0';
        $data['payment_paytr_checkout_total'] = '1';
        $data['payment_paytr_checkout_order_completed_id'] = '1';
        $data['payment_paytr_checkout_order_canceled_id'] = '10';
        $data['payment_paytr_checkout_module_layout'] = 'standard';
        $data['payment_paytr_checkout_sort_order'] = '1';

        $this->model_extension_paytr_payment_paytr_checkout->install();
        $this->model_setting_setting->editSetting('payment_paytr_checkout', $data);
    }

    public function uninstall()
    {
        $this->load->model('setting/setting');
        $this->load->model('extension/paytr/payment/paytr_checkout');

        $this->model_extension_paytr_payment_paytr_checkout->uninstall();
        $this->model_setting_setting->deleteSetting('payment_paytr_checkout');
    }

    public function order()
    {
        if ($this->config->get('payment_paytr_checkout_status')) {

            $this->load->model('sale/order');
            $this->load->model('localisation/currency');
            $this->load->language('extension/paytr/payment/paytr_checkout');

            $order = $this->model_sale_order->getOrder($this->request->get['order_id']);

            if ($order['payment_code'] != 'paytr_checkout') {
                return false;
            }

            $data['entry_refund_transaction'] = $this->language->get('entry_refund_transaction');
            $data['entry_refund_total'] = $this->language->get('entry_refund_total');
            $data['entry_refund_total_paid'] = $this->language->get('entry_refund_total_paid');
            $data['entry_refund_status'] = $this->language->get('entry_refund_status');
            $data['entry_refund_status_message'] = $this->language->get('entry_refund_status_message');
            $data['entry_refund_refund'] = $this->language->get('entry_refund_refund');
            $data['entry_refund_refund_status'] = $this->language->get('entry_refund_refund_status');
            $data['entry_refund_refund_amount'] = $this->language->get('entry_refund_refund_amount');
            $data['entry_refund_refund_date'] = $this->language->get('entry_refund_refund_date');

            $data['paytr_icon_loader'] = 'view/javascript/paytr/paytr_loader.gif';
            $data['user_token'] = $this->request->get['user_token'];
            $data['order_id'] = $order['order_id'];

            $this->document->addStyle('view/javascript/paytr/paytr.css');

            return $this->load->view('extension/paytr/payment/paytr_checkout_order', $data);
        }
    }

    public function ajaxTransactions()
    {
        $json = array();
        $content = '';

        if (isset($this->request->get['order_id'])) {
            $this->load->model('sale/order');
            $this->load->language('extension/paytr/payment/paytr_checkout');

            $order_info = $this->model_sale_order->getOrder($this->request->get['order_id']);

            if ($order_info) {

                $paytr_transactions = $this->paytr->transaction->getListTransactionsForRefund($order_info['order_id'], $this->language, 'iframe');

                if ($paytr_transactions['status']) {
                    foreach ($paytr_transactions['content'] as $transaction) {

                        $content .= '<tr>';
                        $content .= '<td>' . $transaction['merchant_oid'] . '</td>';
                        $content .= '<td>' . $this->currency->format($transaction['total'], $order_info['currency_code'], $order_info['currency_value']) . '</td>';
                        $content .= '<td>' . $this->currency->format($transaction['total_paid'], $order_info['currency_code'], $order_info['currency_value']) . '</td>';
                        $content .= '<td>' . $transaction['status'] . '</td>';
                        $content .= '<td>' . $transaction['status_message'] . '</td>';
                        $content .= '<td>' . $transaction['is_refunded'] . '</td>';
                        $content .= '<td>' . $transaction['refund_status'] . '</td>';
                        $content .= '<td>' . $this->currency->format($transaction['refund_amount'], $order_info['currency_code'], $order_info['currency_value']) . '</td>';

                        $content .= '<td>' . date('d-m-y H:i', strtotime($transaction['date_added'])) . '</td>';

                        if ($transaction['refund_form']) {
                            $content .= '<td>' . $transaction['input_refund'] . ' ' . $transaction['button_refund'] . '</td>';
                        } else {
                            $content .= '<td></td>';
                        }

                        $content .= '</tr>';
                    }
                } else {

                    $content .= '<tr><td colspan="9" class="text-center">' . $this->language->get('error_paytr_checkout_refund_incomplete') . '</td></tr>';
                }

                if (isset($paytr_transactions['count']) && $paytr_transactions['count'] >= 2) {
                    $json['count_msg'] = $this->language->get('error_paytr_checkout_refund_recurring');
                } else {
                    $json['count_msg'] = false;
                }

                $json['table'] = $content;
            }
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    public function ajaxRefundApi()
    {
        $json = array();

        if (isset($this->request->get['order_id']) && isset($this->request->get['amount']) && isset($this->request->get['moid'])) {

            $this->load->model('sale/order');
            $this->load->language('extension/paytr/payment/paytr_checkout');

            $order_info = $this->model_sale_order->getOrder($this->request->get['order_id']);

            if (!$order_info) {
                $json['status'] = 'error';
                $json['status_message'] = $this->language->get('error_paytr_checkout_refund_order_not_found');
                die(json_encode($json));
            }

            $paytr_transaction = $this->paytr->transaction->getTransactionByMerchantOID($this->request->get['moid'], 'iframe');

            if (!$paytr_transaction) {
                $json['status'] = 'error';
                $json['status_message'] = $this->language->get('error_paytr_checkout_refund_not_found');
                die(json_encode($json));
            }

            $amount = str_replace('+', '', $this->request->get['amount']);
            $amount = str_replace(',', '.', $amount);

            if (empty($amount) || !is_numeric($amount)) {
                $json['status'] = 'error';
                $json['status_message'] = $this->language->get('error_paytr_checkout_refund_amount_null');
                die(json_encode($json));
            }

            if ($amount <= 0) {
                $json['status'] = 'error';
                $json['status_message'] = $this->language->get('error_paytr_checkout_refund_amount_zero');
                die(json_encode($json));
            }

            if ($paytr_transaction['is_refunded'] && $paytr_transaction['refund_status'] == 'partial') {

                $actually_total = $paytr_transaction['total'] - $paytr_transaction['refund_amount'];

                if (round($actually_total, 2) < $amount) {
                    $json['status'] = 'error';
                    $json['status_message'] = $this->language->get('error_paytr_checkout_refund_amount_more');
                    die(json_encode($json));
                }
            } else {
                if ($paytr_transaction['total'] < $amount) {
                    $json['status'] = 'error';
                    $json['status_message'] = $this->language->get('error_paytr_checkout_refund_amount_more');
                    die(json_encode($json));
                }
            }

            try {
                $refund_params = array();
                $refund_params['merchant_id'] = $this->config->get('payment_paytr_checkout_merchant_id');
                $refund_params['merchant_key'] = $this->config->get('payment_paytr_checkout_merchant_key');
                $refund_params['merchant_salt'] = $this->config->get('payment_paytr_checkout_merchant_salt');
                $refund_params['merchant_oid'] = $this->request->get['moid'];
                $refund_params['amount'] = $amount;

                // Do Refund
                $refund_response = $this->paytr->refund->doRefund($refund_params);

                $paytr_tr_refund_status = 'partial';

                if ($refund_response['status'] == 'success') {
                    if ($paytr_transaction['total'] == $amount && $paytr_transaction['total'] == $refund_response['return_amount']) {
                        $paytr_tr_refund_status = 'full';
                        $paytr_tr_refund_amount = $refund_response['return_amount'];
                    } else {
                        if ($paytr_transaction['is_refunded'] && $paytr_transaction['refund_status'] == 'partial') {
                            $paytr_tr_refund_amount = $paytr_transaction['refund_amount'] + $refund_response['return_amount'];

                            if ($paytr_tr_refund_amount == $paytr_transaction['total_paid']) {
                                $paytr_tr_refund_status = 'full';
                                $paytr_tr_refund_amount = $paytr_transaction['total_paid'];
                            }
                        } else {
                            $paytr_tr_refund_amount = $refund_response['return_amount'];
                        }
                    }

                    $update_paytr_tr_params = array();
                    $update_paytr_tr_params['merchant_oid'] = $paytr_transaction['merchant_oid'];
                    $update_paytr_tr_params['refund_status'] = $paytr_tr_refund_status;
                    $update_paytr_tr_params['refund_amount'] = $paytr_tr_refund_amount;

                    $this->paytr->transaction->updateTransactionForRefund($update_paytr_tr_params, 'iframe');

                    $json['status'] = 'success';
                    $json['status_message'] = $this->language->get('text_refund_refund_success');
                } else {
                    $json['status'] = $refund_response['status'];
                    $json['status_message'] = $refund_response['err_no'] . ' - ' . $refund_response['err_msg'];
                }

            } catch (Exception $exception) {
                $json['status'] = $refund_response['status'];
                $json['status_message'] = $refund_response['err_no'] . ' - ' . $refund_response['err_msg'];
            }
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    public function ajaxCategoryBased()
    {
        $json = array();

        $tree = $this->paytr->categoryParser($this->config->get('config_language_id'));
        $finish = array();
        $this->paytr->categoryParserClear($tree, 0, array(), $finish);

        $options = $data['payment_paytr_checkout_category_installment'] = $this->config->get('payment_paytr_checkout_category_installment');;

        $json['categories'] = $finish;
        $json['result'] = $options;
        $json['installments'] = $this->paytr->installmentOptions($this->language->get('code'));

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    protected function validate()
    {

        if (!$this->request->post['payment_paytr_checkout_merchant_id']) {
            $this->error['paytr-checkout-merchant-id'] = $this->language->get('error_paytr_checkout_merchant_id');
        } else {
            if (!is_numeric($this->request->post['payment_paytr_checkout_merchant_id'])) {
                $this->error['paytr-checkout-merchant-id'] = $this->language->get('error_paytr_checkout_merchant_id_val');
            }
        }

        if (!$this->request->post['payment_paytr_checkout_merchant_key']) {
            $this->error['paytr-checkout-merchant-key'] = $this->language->get('error_paytr_checkout_merchant_key');
        } else {
            if (strlen($this->request->post['payment_paytr_checkout_merchant_key']) < 16 || strlen($this->request->post['payment_paytr_checkout_merchant_key']) > 16) {
                $this->error['paytr-checkout-merchant-key'] = $this->language->get('error_paytr_checkout_merchant_key_len');
            }
        }

        if (!$this->request->post['payment_paytr_checkout_merchant_salt']) {
            $this->error['paytr-checkout-merchant-salt'] = $this->language->get('error_paytr_checkout_merchant_salt');
        } else {
            if (strlen($this->request->post['payment_paytr_checkout_merchant_salt']) < 16 || strlen($this->request->post['payment_paytr_checkout_merchant_salt']) > 16) {
                $this->error['paytr-checkout-merchant-salt'] = $this->language->get('error_paytr_checkout_merchant_salt_len');
            }
        }

        if (!$this->request->post['payment_paytr_checkout_order_completed_id']) {
            $this->error['paytr-checkout-order_completed-id'] = $this->language->get('error_paytr_checkout_order_completed_id');
        }

        if (!$this->request->post['payment_paytr_checkout_order_canceled_id']) {
            $this->error['paytr-checkout-order-canceled-id'] = $this->language->get('error_paytr_checkout_order_canceled_id');
        }
 
    }
}