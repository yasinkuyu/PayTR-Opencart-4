<?php
// Heading
$_['heading_title']                                     = 'PayTR Sanal POS - iFrame API';

// Text
$_['text_extensions']                                   = 'Eklentiler';
$_['text_success']                                      = 'Başarılı: PayTR modülü başarıyla güncellendi!';
$_['text_settings']                                     = 'Ayarlar';
$_['text_general']                                      = 'Genel';
$_['text_order_status']                                 = 'Sipariş Durumları';
$_['text_module_settings']                              = 'Taksit Ayarları';
$_['text_paytr_checkout']                               = '<a href="https://www.paytr.com/" target="_blank">PayTR</a>';
$_['text_module_layout_one']                            = 'Tek Sayfa';
$_['text_module_layout_standard']                       = 'Standart';
$_['text_select']                                       = 'Seçiniz';
$_['text_ins_total']                                    = 'Toplam Tutarı Değiştir';
$_['text_refund_success']                               = 'Başarılı';
$_['text_refund_failed']                                = 'Hata';
$_['text_refund_completed']                             = 'Tamamlandı';
$_['text_refund_partial']                               = 'Kısmi';
$_['text_refund_full']                                  = 'Tam';
$_['text_refund_yes']                                   = 'Evet';
$_['text_refund_no']                                    = 'Hayır';
$_['text_refund_refund']                                = 'İade Yap';
$_['text_refund_refund_success']                        = 'İade işlemi başarıyla gerçekleştirildi.';

// Entry
$_['entry_merchant_id']                                 = 'Mağaza No';
$_['entry_merchant_key']                                = 'Mağaza Parola';
$_['entry_merchant_salt']                               = 'Mağaza Gizli Anahtar';
$_['entry_language']                                    = 'Dil';
$_['entry_total']                                       = 'Toplam';
$_['entry_module_layout']	 				            = 'Ödeme Sayfası';
$_['entry_status']                                      = 'Durum';
$_['entry_sort_order']                                  = 'Sıralama';
$_['entry_payment_complete']                            = 'Başarılı Ödeme';
$_['entry_payment_failed']                              = 'Hatalı Ödeme';
$_['entry_notify_status']                               = 'Sipariş Notunu Müşteriye Gönder';
$_['entry_ins_total']                                   = 'Vade Farkını Göster';
$_['entry_order_total']                                 = 'Sipariş Tutarını Değiştir';
$_['entry_max_installments']                            = 'Maksimum Taksit Sayısı';
$_['entry_refund_transaction']                          = 'PayTR Sipariş No';
$_['entry_refund_total']                                = 'Toplam';
$_['entry_refund_total_paid']                           = 'Toplam Ödenen';
$_['entry_refund_status']                               = 'Bildirim';
$_['entry_refund_status_message']                       = 'Bildirim Durumu';
$_['entry_refund_refund']                               = 'İade Edildi';
$_['entry_refund_refund_status']                        = 'İade Durumu';
$_['entry_refund_refund_amount']                        = 'İade Edilen Tutar';
$_['entry_refund_refund_date']                          = 'Tarih';

// Help
$_['help_total']                                        = 'Ödeme methodunun aktif olması için siparişin ulaşması gereken toplam tutar.';
$_['help_notify']                                       = 'Ödeme işlemli sonunda müşteriye sipariş notunu gönderir. Bu seçenek aktif edilirse, doğru çalışması için e-posta ayarlarınızın düzgün yapılandırıldığından emin olun.';
$_['help_ins_total']                                    = '<u>Açık seçeneği</u>; sipariş alt toplamına <strong>Vade Farkı</strong> satırı ekler ve <strong>Faturanın Toplam Tutarını</strong> değiştirir. <u>Toplam Tutarı Değiştir seçeneği</u>; sadece <strong>Faturanın Toplam Tutarı</strong>nı değiştirir.';
$_['help_order_total']                                  = 'Taksitli ödemelerde <strong>Ödenen Toplam Tutarı (vade farkı dahil)</strong> siparişin <strong>Toplam Tutarı</strong> ile değiştirir. Bu seçenek faturayı etkilemez.';
$_['help_paytr_checkout']                               = 'Mağaza bilgilerinize <a href="https://www.paytr.com/magaza/bilgi
" target="_blank">bu adresten</a> oturum açarak ulaşabilirsiniz.';

// Error
$_['error_warning']                                     = 'Bu modül için yetkiniz bulunmamaktadır!';
$_['error_paytr_checkout_merchant_id']                  = '<strong>Mağaza No</strong> girilmesi zorunludur!';
$_['error_paytr_checkout_merchant_id_val']              = '<strong>Mağaza No</strong> numerik olmalıdır!';
$_['error_paytr_checkout_merchant_key']                 = '<strong>Mağaza Parolası</strong> girilmesi zorunludur!';
$_['error_paytr_checkout_merchant_key_len']             = '<strong>Mağaza Parolası</strong> 16 karakter olmalıdır. Fazla veya eksik girdiniz!';
$_['error_paytr_checkout_merchant_salt']                = '<strong>Mağaza Gizli Anahtarı</strong> girilmesi zorunludur!';
$_['error_paytr_checkout_merchant_salt_len']            = '<strong>Mağaza Gizli Anahtarı</strong> 16 karakter olmalıdır. Fazla veya eksik girdiniz!';
$_['error_paytr_checkout_order_completed_id']           = '<strong>Başarılı Ödeme</strong> durumunu seçmediniz!';
$_['error_paytr_checkout_order_canceled_id']            = '<strong>Hatalı Ödeme</strong> durumunu seçmediniz!';
$_['error_paytr_checkout_installment_number']           = '<strong>Maksimum Taksit Sayısı</strong>\'nı tercihinize göre düzenleyebilirsiniz.';
$_['error_paytr_checkout_refund_not_found']             = 'İşlem bulunamadı.';
$_['error_paytr_checkout_refund_incomplete']            = 'Bildirim işlemi henüz tamamlanmamış.';
$_['error_paytr_checkout_refund_amount_null']           = 'Tutar boş yada hatalı!';
$_['error_paytr_checkout_refund_amount_zero']           = 'Tutar 0(sıfır)\'dan büyük olmalıdır!!';
$_['error_paytr_checkout_refund_amount_more']           = 'Girilen tutar, kalan tutardan daha büyük olamaz!';
$_['error_paytr_checkout_refund_order_not_found']       = 'Sipariş bulunamadı.';
$_['error_paytr_checkout_refund_recurring']             = 'Bu siparişe müşteriniz birden fazla ödeme yapmış olabilir. Lütfen sipariş geçmişini kontrol edin ve müşteriniz ile iletişime geçin.';