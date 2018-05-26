<?php

if( ! defined( 'ABSPATH' ) ) {
    die( 'This file cannot be accessed directly' );
}

function PW_Load_Payir_Gateway() {

    if( class_exists( 'WC_Payment_Gateway' ) && ! class_exists( 'WC_Payir_Gateway' ) && ! function_exists( 'Woocommerce_Add_Payir_Gateway' ) ) {

        add_filter( 'woocommerce_payment_gateways', 'Woocommerce_Add_Payir_Gateway' );

        function Woocommerce_Add_Payir_Gateway( $methods ) {
            $methods[] = 'WC_Payir_Gateway';

            return $methods;
        }

        add_filter( 'woocommerce_currencies', 'add_payir_currency' );

        function add_payir_currency( $currencies ) {
            $currencies['IRR'] = __( 'ریال', 'woocommerce' );
            $currencies['IRT'] = __( 'تومان', 'woocommerce' );
            $currencies['IRHR'] = __( 'هزار ریال', 'woocommerce' );
            $currencies['IRHT'] = __( 'هزار تومان', 'woocommerce' );

            return $currencies;
        }

        add_filter( 'woocommerce_currency_symbol', 'add_payir_currency_symbol', 10, 2 );

        function add_payir_currency_symbol( $currency_symbol, $currency ) {
            switch( $currency ) {

                case 'IRR':
                    $currency_symbol = 'ریال';
                    break;

                case 'IRT':
                    $currency_symbol = 'تومان';
                    break;

                case 'IRHR':
                    $currency_symbol = 'هزار ریال';
                    break;

                case 'IRHT':
                    $currency_symbol = 'هزار تومان';
                    break;
            }

            return $currency_symbol;
        }

        class WC_Payir_Gateway extends WC_Payment_Gateway {

            public function __construct() {
                $this->id = 'payir';
                $this->method_title = __( 'درگاه پرداخت و کیف پول الکترونیک Pay.ir', 'woocommerce' );
                $this->method_description = __( 'تنظیمات درگاه پرداخت و کیف پول الکترونیک Pay.ir برای افزونه WooCommerce', 'woocommerce' );
                $this->icon = apply_filters( 'WC_Payir_logo', PW()->plugin_url( 'assets/images/payir.png' ) );
                $this->has_fields = false;

                $this->init_form_fields();
                $this->init_settings();

                $this->title = $this->settings['title'];
                $this->description = $this->settings['description'];

                $this->api_key = $this->settings['api_key'];

                $this->success_massage = $this->settings['success_massage'];
                $this->failed_massage = $this->settings['failed_massage'];

                if( version_compare( WOOCOMMERCE_VERSION, '2.0.0', '>=' ) ) {

                    add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array(
                        $this,
                        'process_admin_options'
                    ) );

                } else {

                    add_action( 'woocommerce_update_options_payment_gateways', array(
                        $this,
                        'process_admin_options'
                    ) );
                }

                add_action( 'woocommerce_receipt_' . $this->id, array( $this, 'Send_to_Payir_Gateway' ) );
                add_action( 'woocommerce_api_' . strtolower( get_class( $this ) ) . '', array(
                    $this,
                    'Return_from_Payir_Gateway'
                ) );
            }

            public function admin_options() {
                parent::admin_options();
            }

            public function init_form_fields() {
                $this->form_fields = apply_filters( 'WC_Payir_Config', array(

                    'base_confing'    => array(

                        'title'       => __( 'تنظیمات پایه', 'woocommerce' ),
                        'type'        => 'title',
                        'description' => null,
                        'desc_tip'    => false
                    ),
                    'enabled'         => array(

                        'title'       => __( 'فعال سازی / غیر فعال سازی', 'woocommerce' ),
                        'type'        => 'checkbox',
                        'label'       => __( 'فعال سازی درگاه پرداخت Pay.ir', 'woocommerce' ),
                        'description' => __( 'برای فعال سازی درگاه پرداخت Pay.ir باید چک باکس را تیک بزنید', 'woocommerce' ),
                        'default'     => 'no',
                        'desc_tip'    => true
                    ),
                    'title'           => array(

                        'title'       => __( 'عنوان درگاه', 'woocommerce' ),
                        'type'        => 'text',
                        'description' => __( 'عنوانی که برای درگاه در طی مراحل خرید به مشتری نمایش داده میشود', 'woocommerce' ),
                        'default'     => __( 'درگاه پرداخت و کیف پول الکترونیک Pay.ir', 'woocommerce' ),
                        'desc_tip'    => true
                    ),
                    'description'     => array(

                        'title'       => __( 'توضیحات درگاه', 'woocommerce' ),
                        'type'        => 'text',
                        'description' => __( 'توضیحاتی که در مرحله پرداخت برای درگاه نمایش داده خواهد شد', 'woocommerce' ),
                        'default'     => __( 'پرداخت امن با استفاده از کلیه کارت های عضو شبکه شتاب از طریق درگاه پرداخت و کیف پول الکترونیک Pay.ir', 'woocommerce' ),
                        'desc_tip'    => true
                    ),
                    'account_confing' => array(

                        'title'       => __( 'تنظیمات حساب Pay.ir', 'woocommerce' ),
                        'type'        => 'title',
                        'description' => null,
                        'desc_tip'    => false
                    ),
                    'api_key'         => array(

                        'title'       => __( 'کلید API', 'woocommerce' ),
                        'type'        => 'text',
                        'description' => null,
                        'default'     => null,
                        'desc_tip'    => false
                    ),
                    'payment_confing' => array(

                        'title'       => __( 'تنظیمات عملیات پرداخت', 'woocommerce' ),
                        'type'        => 'title',
                        'description' => null,
                        'desc_tip'    => false
                    ),
                    'success_massage' => array(

                        'title'       => __( 'پیام پرداخت موفق', 'woocommerce' ),
                        'type'        => 'textarea',
                        'description' => __( 'متن پیامی که می خواهید پس از پرداخت موفق به کاربر نمایش داده شود را وارد نمایید.<br/>شما می توانید از شورت کد {transaction_id} برای نمایش شماره پیگیری تراکنش استفاده نمایید.', 'woocommerce' ),
                        'default'     => __( 'از شما سپاسگزاریم، سفارش شما با موفقیت پرداخت و تایید شد. شماره پیگیری: {transaction_id}', 'woocommerce' ),
                        'desc_tip'    => false
                    ),
                    'failed_massage'  => array(

                        'title'       => __( 'پیام پرداخت ناموفق', 'woocommerce' ),
                        'type'        => 'textarea',
                        'description' => __( 'متن پیامی که می خواهید پس از پرداخت ناموفق به کاربر نمایش داده را وارد نمایید.<br/>شما می توانید از شورت کد {fault} برای نمایش دلیل خطای رخ داده استفاده نمایید. این دلیل خطا از سایت Pay.ir ارسال میگردد.', 'woocommerce' ),
                        'default'     => __( 'متاسفانه پرداخت شما ناموفق بوده است، لطفا مجددا تلاش نمایید یا در صورت بروز اشکال با مدیر سایت تماس بگیرید. شرح خطا: {fault}', 'woocommerce' ),
                        'desc_tip'    => false
                    )
                ) );
            }

            public function process_payment( $order_id ) {
                $order = new WC_Order( $order_id );

                return array(

                    'result'   => 'success',
                    'redirect' => $order->get_checkout_payment_url( true )
                );
            }

            public function Send_to_Payir_Gateway( $order_id ) {
                global $woocommerce;

                $woocommerce->session->order_id_payir = $order_id;

                $order = new WC_Order( $order_id );

                $currency = $order->get_currency();
                $currency = apply_filters( 'WC_Payir_Currency', $currency, $order_id );

                $form = '<form id="payir-checkout-form" name="payir-checkout-form" method="post" class="payir-checkout-form">';
                $form .= '<input id="payir-payment-button" name="payir_submit" type="submit" class="button alt" value="' . __( 'پرداخت', 'woocommerce' ) . '"/>';
                $form .= '<a href="' . $woocommerce->cart->get_checkout_url() . '" class="button cancel">' . __( 'بازگشت', 'woocommerce' ) . '</a>';
                $form .= '</form><br/>';

                $form = apply_filters( 'WC_Payir_Form', $form, $order_id, $woocommerce );

                do_action( 'WC_Payir_Gateway_Before_Form', $order_id, $woocommerce );

                echo $form;

                do_action( 'WC_Payir_Gateway_After_Form', $order_id, $woocommerce );

                $amount = intval( $order->get_total() );
                $amount = apply_filters( 'woocommerce_order_amount_total_IRANIAN_gateways_before_check_currency', $amount, $currency );

                if( strtolower( $currency ) == strtolower( 'IRT' ) || strtolower( $currency ) == strtolower( 'TOMAN' ) ) {

                    $amount = $amount * 10;
                }

                if( strtolower( $currency ) == strtolower( 'Iran TOMAN' ) || strtolower( $currency ) == strtolower( 'Iranian TOMAN' ) ) {

                    $amount = $amount * 10;
                }

                if( strtolower( $currency ) == strtolower( 'Iran-TOMAN' ) || strtolower( $currency ) == strtolower( 'Iranian-TOMAN' ) ) {

                    $amount = $amount * 10;
                }

                if( strtolower( $currency ) == strtolower( 'Iran_TOMAN' ) || strtolower( $currency ) == strtolower( 'Iranian_TOMAN' ) ) {

                    $amount = $amount * 10;
                }

                if( strtolower( $currency ) == strtolower( 'تومان' ) || strtolower( $currency ) == strtolower( 'تومان ایران' ) ) {

                    $amount = $amount * 10;
                }

                if( strtolower( $currency ) == strtolower( 'IRHT' ) ) {

                    $amount = $amount * 1000 * 10;
                }

                if( strtolower( $currency ) == strtolower( 'IRHR' ) ) {

                    $amount = $amount * 1000;
                }

                if( strtolower( $currency ) == strtolower( 'IRR' ) ) {

                    $amount = $amount;
                }

                $amount = apply_filters( 'woocommerce_order_amount_total_IRANIAN_gateways_after_check_currency', $amount, $currency );
                $amount = apply_filters( 'woocommerce_order_amount_total_IRANIAN_gateways_irt', $amount, $currency );
                $amount = apply_filters( 'woocommerce_order_amount_total_Payir_gateway', $amount, $currency );

                if( extension_loaded( 'curl' ) ) {

                    $products = array();
                    $order_items = $order->get_items();

                    foreach( (array) $order_items as $product ) {

                        $products[] = $product['name'] . ' (' . $product['qty'] . ') ';
                    }

                    $products = implode( ' - ', $products );

                    $description = 'خرید به شماره سفارش: ' . $order->get_order_number();
                    $mobile = get_post_meta( $order_id, '_billing_phone', true ) ? get_post_meta( $order_id, '_billing_phone', true ) : null;

                    $description = apply_filters( 'WC_Payir_Description', $description, $order_id );
                    $mobile = apply_filters( 'WC_Payir_Mobile', $mobile, $order_id );
                    $email = apply_filters( 'WC_Payir_Email', $order->get_billing_email(), $order_id );
                    $paymenter = apply_filters( 'WC_Payir_Paymenter', $order->get_billing_first_name() . ' ' . $order->get_billing_last_name(), $order_id );
                    $res_number = apply_filters( 'WC_Payir_ResNumber', intval( $order->get_order_number() ), $order_id );

                    do_action( 'WC_Payir_Gateway_Payment', $order_id, $description, $mobile );

                    $api_key = $this->api_key;
                    $callback = add_query_arg( 'wc_order', $order_id, WC()->api_request_url( 'WC_Payir_Gateway' ) );
					$payresid = '1000000800';
                    $params = array(

                        'api'          => $api_key,
                        'amount'       => $amount,
                        'redirect'     => urlencode( $callback ),
                        'mobile'       => $mobile,
                        'resellerId'   => $payresid,
                        'factorNumber' => $order_id,
                        'description'  => $description
                    );

                    $result = $this->common( 'https://pay.ir/payment/send', $params );

                    if( $result && isset( $result->status ) && $result->status == 1 ) {

                        $gateway_url = 'https://pay.ir/payment/gateway/' . $result->transId;

                        if( ! headers_sent() ) {

                            header( 'Location: ' . $gateway_url );
                            exit;

                        } else {

                            echo 'در حال انتقال به درگاه بانکی...';
                            echo '<script type="text/javascript">window.onload = function(){top.location.href = "' . $gateway_url . '";};</script>';
                            exit;
                        }

                    } else {

                        $message = 'در ارتباط با وب سرویس Pay.ir خطایی رخ داده است';

                        $fault = isset( $result->errorCode ) ? $result->errorCode : 'Send';
                        $message = isset( $result->errorMessage ) ? $result->errorMessage : $message;
                    }

                } else {

                    $fault = 'cUrl';
                    $message = 'تابع cURL در سرور فعال نمی باشد';
                }

                if( ! empty( $message ) && $message ) {

                    $note = sprintf( __( 'خطایی رخ داده است: %s', 'woocommerce' ), $message );
                    $note = apply_filters( 'WC_Payir_Send_to_Gateway_Failed_Note', $note, $order_id, $fault );

                    $order->add_order_note( $note );

                    $notice = sprintf( __( 'خطایی رخ داده است:<br/>%s', 'woocommerce' ), $message );
                    $notice = apply_filters( 'WC_Payir_Send_to_Gateway_Failed_Notice', $notice, $order_id, $fault );

                    if( $notice ) {

                        wc_add_notice( $notice, 'error' );
                    }

                    do_action( 'WC_Payir_Send_to_Gateway_Failed', $order_id, $fault );
                }
            }

            public function Return_from_Payir_Gateway() {
                global $woocommerce;

                $order_id = null;

                $factor_number = isset( $_POST['factorNumber'] ) ? sanitize_text_field( $_POST['factorNumber'] ) : null;
                $trans_id = isset( $_POST['transId'] ) ? sanitize_text_field( $_POST['transId'] ) : null;

                if( $trans_id ) {

                    update_post_meta( $order_id, '_transaction_id', $trans_id );
                }

                if( isset( $_GET['wc_order'] ) ) {

                    $order_id = sanitize_text_field( $_GET['wc_order'] );

                } elseif( $factor_number ) {

                    $order_id = $factor_number;

                } else {

                    $order_id = $woocommerce->session->order_id_payir;

                    unset( $woocommerce->session->order_id_payir );
                }

                if( $order_id ) {

                    $order = new WC_Order( $order_id );
                    $currency = $order->get_currency();
                    $currency = apply_filters( 'WC_Payir_Currency', $currency, $order_id );

                    if( $order->get_status() != 'completed' && $order->get_status() != 'processing' ) {

                        if( isset( $_POST['status'] ) && isset( $_POST['transId'] ) && isset( $_POST['factorNumber'] ) ) {

                            $api_key = $this->api_key;

                            $payir_status = sanitize_text_field( $_POST['status'] );
                            $payir_trans_id = sanitize_text_field( $_POST['transId'] );
                            $payir_factor_number = sanitize_text_field( $_POST['factorNumber'] );
                            $payir_message = sanitize_text_field( $_POST['message'] );

                            if( isset( $payir_status ) && $payir_status == 1 ) {

                                $params = array(

                                    'api'     => $api_key,
                                    'transId' => $payir_trans_id
                                );

                                $result = $this->common( 'https://pay.ir/payment/verify', $params );

                                if( $result && isset( $result->status ) && $result->status == 1 ) {

                                    $amount = intval( $order->get_total() );
                                    $amount = apply_filters( 'woocommerce_order_amount_total_IRANIAN_gateways_before_check_currency', $amount, $currency );

                                    if( strtolower( $currency ) == strtolower( 'IRT' ) || strtolower( $currency ) == strtolower( 'TOMAN' ) ) {

                                        $amount = $amount * 10;
                                    }

                                    if( strtolower( $currency ) == strtolower( 'Iran TOMAN' ) || strtolower( $currency ) == strtolower( 'Iranian TOMAN' ) ) {

                                        $amount = $amount * 10;
                                    }

                                    if( strtolower( $currency ) == strtolower( 'Iran-TOMAN' ) || strtolower( $currency ) == strtolower( 'Iranian-TOMAN' ) ) {

                                        $amount = $amount * 10;
                                    }

                                    if( strtolower( $currency ) == strtolower( 'Iran_TOMAN' ) || strtolower( $currency ) == strtolower( 'Iranian_TOMAN' ) ) {

                                        $amount = $amount * 10;
                                    }

                                    if( strtolower( $currency ) == strtolower( 'تومان' ) || strtolower( $currency ) == strtolower( 'تومان ایران' ) ) {

                                        $amount = $amount * 10;
                                    }

                                    if( strtolower( $currency ) == strtolower( 'IRHT' ) ) {

                                        $amount = $amount * 1000 * 10;
                                    }

                                    if( strtolower( $currency ) == strtolower( 'IRHR' ) ) {

                                        $amount = $amount * 1000;
                                    }

                                    if( strtolower( $currency ) == strtolower( 'IRR' ) ) {

                                        $amount = $amount;
                                    }

                                    $card_number = isset( $_POST['cardNumber'] ) ? sanitize_text_field( $_POST['cardNumber'] ) : 'Null';

                                    if( $amount == $result->amount ) {

                                        $status = 'completed';
                                        $fault = null;
                                        $message = null;

                                    } else {

                                        $status = 'failed';
                                        $fault = 'Invalid Amount';
                                        $message = 'رقم تراكنش با رقم پرداخت شده مطابقت ندارد';
                                    }

                                } else {

                                    $message = 'در ارتباط با وب سرویس Pay.ir خطایی رخ داده است';

                                    $status = 'failed';
                                    $fault = isset( $result->errorCode ) ? $result->errorCode : 'Verify';
                                    $message = isset( $result->errorMessage ) ? $result->errorMessage : $message;
                                }

                            } else {

                                if( $payir_message ) {

                                    $status = 'failed';
                                    $fault = 'Invalid Payment';
                                    $message = $payir_message;

                                } else {

                                    $status = 'failed';
                                    $fault = 'Invalid Payment';
                                    $message = 'تراكنش با خطا مواجه شد و یا توسط پرداخت کننده کنسل شده است';
                                }
                            }

                        } else {

                            $status = 'failed';
                            $fault = 'Invalid Data';
                            $message = 'اطلاعات ارسال شده مربوط به تایید تراکنش ناقص و یا غیر معتبر است';
                        }

                        if( $status == 'completed' ) {

                            $order->payment_complete( $trans_id );
                            $woocommerce->cart->empty_cart();

                            $note = sprintf( __( 'پرداخت موفقیت آمیز بود.<br/>شماره پیگیری: %s', 'woocommerce' ), $trans_id );
                            $note = apply_filters( 'WC_Payir_Return_from_Gateway_Success_Note', $note, $order_id, $trans_id );

                            if( $note ) {

                                $order->add_order_note( $note, 1 );
                            }

                            $notice = wpautop( wptexturize( $this->success_massage ) );
                            $notice = str_replace( '{transaction_id}', $trans_id, $notice );
                            $notice = apply_filters( 'WC_Payir_Return_from_Gateway_Success_Notice', $notice, $order_id, $trans_id );

                            if( $notice ) {

                                wc_add_notice( $notice, 'success' );
                            }

                            do_action( 'WC_Payir_Return_from_Gateway_Success', $order_id, $trans_id );

                            wp_redirect( add_query_arg( 'wc_status', 'success', $this->get_return_url( $order ) ) );
                            exit;

                        } else {

                            $note = sprintf( __( 'خطایی رخ داده است:<br/>شرح خطا: %s<br/>شماره پیگیری: %s', 'woocommerce' ), $message, $trans_id );
                            $note = apply_filters( 'WC_Payir_Return_from_Gateway_Failed_Note', $note, $order_id, $trans_id, $fault );

                            if( $note ) {

                                $order->add_order_note( $note, 1 );
                            }

                            $notice = wpautop( wptexturize( $this->failed_massage ) );
                            $notice = str_replace( '{transaction_id}', $trans_id, $notice );
                            $notice = str_replace( '{fault}', $message, $notice );
                            $notice = apply_filters( 'WC_Payir_Return_from_Gateway_Failed_Notice', $notice, $order_id, $trans_id, $fault );

                            if( $notice ) {
                                wc_add_notice( $notice, 'error' );
                            }

                            do_action( 'WC_Payir_Return_from_Gateway_Failed', $order_id, $trans_id, $fault );

                            wp_redirect( $woocommerce->cart->get_checkout_url() );
                            exit;
                        }

                    } else {

                        $notice = wpautop( wptexturize( $this->success_massage ) );
                        $notice = str_replace( '{transaction_id}', $trans_id, $notice );
                        $notice = apply_filters( 'WC_Payir_Return_from_Gateway_ReSuccess_Notice', $notice, $order_id, $trans_id );

                        if( $notice ) {

                            wc_add_notice( $notice, 'success' );
                        }

                        do_action( 'WC_Payir_Return_from_Gateway_ReSuccess', $order_id, $trans_id );

                        wp_redirect( add_query_arg( 'wc_status', 'success', $this->get_return_url( $order ) ) );
                        exit;
                    }

                } else {

                    $fault = __( 'شماره سفارش وجود ندارد و یا سفارش منقضی شده است', 'woocommerce' );

                    $notice = wpautop( wptexturize( $this->failed_massage ) );
                    $notice = str_replace( '{fault}', $fault, $notice );
                    $notice = apply_filters( 'WC_Payir_Return_from_Gateway_No_Order_ID_Notice', $notice, $order_id, $fault );

                    if( $notice ) {

                        wc_add_notice( $notice, 'error' );
                    }

                    do_action( 'WC_Payir_Return_from_Gateway_No_Order_ID', $order_id, null, $fault );

                    wp_redirect( $woocommerce->cart->get_checkout_url() );
                    exit;
                }
            }

            private function common( $url, $params ) {
                $ch = curl_init();

                curl_setopt( $ch, CURLOPT_URL, $url );
                curl_setopt( $ch, CURLOPT_POST, true );
                curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
                curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false );
                curl_setopt( $ch, CURLOPT_SSL_VERIFYHOST, false );
                curl_setopt( $ch, CURLOPT_POSTFIELDS, http_build_query( $params ) );

                $response = curl_exec( $ch );
                $error = curl_errno( $ch );

                curl_close( $ch );

                $output = $error ? false : json_decode( $response );

                return $output;
            }
        }
    } else {

        if( ! function_exists( 'is_plugin_active' ) ) {
            include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
        }

        $plugin = "payir-woocommerce/index.php";

        if( is_plugin_active( $plugin ) ) {
            deactivate_plugins( $plugin );
        }

    }
}

add_action( 'plugins_loaded', 'PW_Load_Payir_Gateway', 0 );

function pw_after_payir_plugin( $plugin_file, $plugin_data, $status ) {
  echo '<tr class="inactive"><td>&nbsp;</td><td colspan="2">
        <div class="notice inline notice-warning notice-alt"><p>افزونه «<strong>درگاه پرداخت و کیف پول الکترونیک Pay.ir - افزونه WooCommerce</strong>» درون بسته ووکامرس فارسی وجود دارد و نیاز به فعال سازی نیست. به صفحه <a href="' . admin_url('admin.php?page=wc-settings&tab=checkout') . '">پیکربندی تسویه حساب</a> مراجعه کنید.</p></div>
        </td></tr>';
}

add_action( 'after_plugin_row_payir-woocommerce/index.php', 'pw_after_payir_plugin', 10, 3 );