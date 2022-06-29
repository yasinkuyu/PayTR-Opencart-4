<?
namespace Opencart\Catalog\Model\Extension\Paytr\Total;
class PaytrCheckout extends \Opencart\System\Engine\Model {
	public function getTotal(array &$totals, array &$taxes, float &$total): void {
		$this->load->language('extension/paytr/payment/paytr_checkout');

		$totals[] = array(
			'extension'  => 'paytr',
			'code'       => 'paytr_checkout',
			'title'      => $this->language->get('text_total'),
			'value'      => max(0, $total['total']),
			'sort_order' => (int)$this->config->get('total_total_sort_order')
		);
	}
}