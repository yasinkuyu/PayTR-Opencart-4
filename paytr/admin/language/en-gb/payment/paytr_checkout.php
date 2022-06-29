<?php
// Heading
$_['heading_title']                                     = 'PayTR Virtual POS - iFrame API';

// Text
$_['text_extensions']                                   = 'Extensions';
$_['text_success']                                      = 'Success: PayTR module has been updated successfully!';
$_['text_settings']                                     = 'Settings';
$_['text_general']                                      = 'General';
$_['text_order_status']                                 = 'Order Status';
$_['text_module_settings']                              = 'Installment Settings';
$_['text_paytr_checkout']                               = '<a href="https://www.paytr.com/" target="_blank"><img src="view/image/payment/paytr.png" alt="PayTR" title="PayTR"/></a>';
$_['text_module_layout_one']                            = 'One Page';
$_['text_module_layout_standard']                       = 'Standard';
$_['text_select']                                       = 'Select';
$_['text_ins_total']                                    = 'Change Total Value';
$_['text_refund_success']                               = 'Success';
$_['text_refund_failed']                                = 'Failed';
$_['text_refund_completed']                             = 'Completed';
$_['text_refund_partial']                               = 'Partial';
$_['text_refund_full']                                  = 'Full';
$_['text_refund_yes']                                   = 'Yes';
$_['text_refund_no']                                    = 'No';
$_['text_refund_refund']                                = 'Refund';
$_['text_refund_refund_success']                        = 'The refund process is completed successfully.';

// Entry
$_['entry_merchant_id']                                 = 'Merchant ID';
$_['entry_merchant_key']                                = 'Merchant Key';
$_['entry_merchant_salt']                               = 'Merchant Salt';
$_['entry_language']                                    = 'Language';
$_['entry_total']                                       = 'Total';
$_['entry_module_layout']	 				            = 'Checkout Page';
$_['entry_status']                                      = 'Status';
$_['entry_sort_order']                                  = 'Sort Order';
$_['entry_payment_complete']                            = 'Completed Status';
$_['entry_payment_failed']                              = 'Failed Status';
$_['entry_notify_status']                               = 'Notify Status';
$_['entry_ins_total']                                   = 'Installment Amount';
$_['entry_order_total']                                 = 'Change Order Total';
$_['entry_max_installments']                            = 'Maximum Number of Installments';
$_['entry_refund_transaction']                          = 'PayTR Order ID';
$_['entry_refund_total']                                = 'Total';
$_['entry_refund_total_paid']                           = 'Total Paid';
$_['entry_refund_status']                               = 'Status';
$_['entry_refund_status_message']                       = 'Status Message';
$_['entry_refund_refund']                               = 'Refunded';
$_['entry_refund_refund_status']                        = 'Refund Status';
$_['entry_refund_refund_amount']                        = 'Refunded Amount';
$_['entry_refund_refund_date']                          = 'Date';

// Help
$_['help_total']                                        = 'The checkout total the order must reach before this payment method becomes active';
$_['help_notify']                                       = 'Notify the status of the order to the customer via email when the order is completed.';
$_['help_ins_total']                                    = '<u>Enabled</u>; Adds a <strong>Installment Difference</strong> row to the order subtotal and replaces <strong>Total Amount of Invoice</strong>. <u>Change Total Amount</u>; it only changes <strong>Total Amount of Invoice</strong>.';
$_['help_order_total']                                  = 'The order total changes when customers paid with the installment option.';
$_['help_paytr_checkout']                               = 'Store information can be found at <a href="https://www.paytr.com/magaza/bilgi
" target="_blank">this address</a>.';

// Error
$_['error_permission']                                  = 'You dont have authorization for this module!';
$_['error_paytr_checkout_merchant_id']                  = '<strong>Merchant ID</strong> must be enter!';
$_['error_paytr_checkout_merchant_id_val']              = '<strong>Merchant ID</strong> must be numeric!';
$_['error_paytr_checkout_merchant_key']                 = '<strong>Merchant Key</strong> must be enter!';
$_['error_paytr_checkout_merchant_key_len']             = '<strong>Merchant Key</strong> length must be 16 character!';
$_['error_paytr_checkout_merchant_salt']                = '<strong>Merchant Salt</strong> must be enter!';
$_['error_paytr_checkout_merchant_salt_len']            = '<strong>Merchant Salt</strong> length must be 16 character!';
$_['error_paytr_checkout_order_status_id']              = 'You have to choose what status will be assigned during the <strong>Payment Process!</strong>';
$_['error_paytr_checkout_order_completed_id']           = 'You have to choose what status will be assigned when the <strong>Payment Approved!</strong>';
$_['error_paytr_checkout_order_canceled_id']            = 'You have to choose what status will be assigned when the <strong>Payment Dont Approved!</strong>';
$_['error_paytr_checkout_installment_number']           = 'You can edit <strong>Maximum Installment Count</strong> according to your preference';
$_['error_paytr_checkout_refund_not_found']             = 'Transaction not found.';
$_['error_paytr_checkout_refund_incomplete']            = 'Transaction is not yet complete.';
$_['error_paytr_checkout_refund_amount_null']           = 'The amount is null or wrong!';
$_['error_paytr_checkout_refund_amount_zero']           = 'Amount must be bigger than 0!!';
$_['error_paytr_checkout_refund_amount_more']           = 'The amount cannot be greater than the remaining amount!';
$_['error_paytr_checkout_refund_order_not_found']       = 'Order not found!';
$_['error_paytr_checkout_refund_recurring']             = 'Your customer may have made more than one payment for this order. Please check the order history and contact your customer.';