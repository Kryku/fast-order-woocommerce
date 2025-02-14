<?php
/**
 * Plugin Name: Quick Buy Button
 * Description: Quick order in WooCommerce.
 * Version: 1.0
 * Author: V.Krykun
 */

if (!defined('ABSPATH')) exit;

function qbb_check_woocommerce() {
    if (!class_exists('WooCommerce')) {
        add_action('admin_notices', function() {
            echo '<div class="error"><p><strong>Quick Buy Button</strong> requires active WooCommerce!</p></div>';
        });
        return;
    }

    require_once plugin_dir_path(__FILE__) . 'admin/admin-menu.php';
}
add_action('plugins_loaded', 'qbb_check_woocommerce');

add_action('wp_enqueue_scripts', 'fast_order_enqueue_scripts');
function fast_order_enqueue_scripts() {
    wp_enqueue_script('fast-order-script', plugins_url('/js/fast-order.js', __FILE__), array('jquery'), null, true);
    wp_localize_script('fast-order-script', 'fastOrder', array('ajaxurl' => admin_url('admin-ajax.php')));
}

add_action('woocommerce_after_add_to_cart_button', 'qbb_add_quick_buy_button');

function qbb_add_quick_buy_button() {
    $use_default_class = get_option('qbb_use_default_class', 'yes');
    $custom_class = get_option('qbb_custom_class', '');
    $button_text = get_option('qbb_button_custom_text', '');
    $button_text_color = get_option('qbb_button_text_color');
    $button_color = get_option('qbb_button_color');
    $use_integration = get_option('qbb_use_integration', 'yes');
    $keycrm_key = get_option('qbb_keycrm_key');
    $keycrm_source = get_option('qbb_keycrm_source');
    
    
    $target_class = $use_default_class === 'yes' ? '.single_add_to_cart_button' : esc_attr($custom_class);

    ?>
    <style>.btn{display:-webkit-box;display:-ms-flexbox;display:flex;text-decoration:none;outline:none;border:none;cursor:pointer;text-align:center}.btn--fastOrder{-webkit-box-pack:center;-ms-flex-pack:center;justify-content:center;-webkit-box-align:center;-ms-flex-align:center;align-items:center;width:231px;height:55px;font-size:25.42px;color:#000;background: <?php echo $button_color; ?> ;-webkit-border-radius:3px;border-radius:3px;margin-top:10px;margin-bottom:10px}span{margin:0;padding:0;border:0;font-size:100%;font:inherit;vertical-align:baseline}.btn__title{font-family:"Roboto";font-style:normal;font-weight:600;font-size:16px;line-height:20px;color: <?php echo $button_text_color; ?> ;border-bottom:1px solid  <?php echo $button_text_color; ?> }.btn__title:hover{opacity:.9}input{font-family:"Roboto";font-size:16px;color: <?php echo $button_text_color; ?> ;-webkit-appearance:none}input::placeholder{font-family:"Roboto";color: <?php echo $button_text_color; ?> }input:focus{outline:none}.btn--fastOrder .input{max-width:130px;height:23px}.btn--fastOrder .input--tel{font-family:"Montserrat";font-style:normal;font-weight:500;font-size:16px;line-height:20px;border:none;background: <?php echo $button_color; ?> ;margin-right:10px;border-right:1px solid rgb(166 166 166 / .8)}</style>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.maskedinput/1.4.1/jquery.maskedinput.min.js" integrity="sha512-d4KkQohk+HswGs6A1d6Gak6Bb9rMWtxjOa0IiY49Q3TeFd5xAzjWXDCBW9RS7m86FQ4RzM2BdHmdJnnKRYknxw==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            let targetButton = document.querySelector('<?php echo $target_class; ?>');
            if (targetButton) {
                let fastOrderBtn = document.querySelector('.js-fast-order-btn');
                if (fastOrderBtn) {
                } else {
                    let fastOrderDiv = document.createElement('div');
                    fastOrderDiv.classList.add('btn', 'btn--fastOrder');
        
                    fastOrderDiv.innerHTML = 
                        `<div class="fast-order">
                            <input type="text" name="telephone" onpaste="return false" autocomplete="off" 
                                   class="input input--tel js-control js-telMask" placeholder="380 (__) ___-__-__">
                        </div>
                        <span class="btn__title js-fast-order-btn js-open-modal"><?php echo $button_text; ?></span>`
                    ;
        
                    targetButton.insertAdjacentElement('afterend', fastOrderDiv);
                }
            }
        });
    </script>
    <?php
}
    
add_action('wp_ajax_create_fast_order', 'create_fast_order');
add_action('wp_ajax_nopriv_create_fast_order', 'create_fast_order');

function create_fast_order() {
    $phone = preg_replace('/\D/', '', sanitize_text_field($_POST['phone']));
    $product_id = intval($_POST['product_id']);
    $product_quantity = intval($_POST['quantity']) ?: 1;

    if (empty($phone)) {
        wp_send_json(array('message' => 'Enter a phone number'));
        return;
    }

    if (!WC()->cart) {
        wc_load_cart();
    }

    $product = wc_get_product($product_id);
    if (!$product) {
        wp_send_json(array('message' => 'Product not found'));
        return;
    }

    WC()->cart->add_to_cart($product_id, $product_quantity);

    $order = wc_create_order();
    foreach (WC()->cart->get_cart() as $cart_item) {
        $order->add_product($cart_item['data'], $cart_item['quantity']);
    }

    $hide_email = "no-email@" . rand(10000, 99999) . ".com";

    $order->set_billing_phone($phone);
    $order->set_billing_first_name('-');
    $order->set_billing_last_name('-');
    $order->set_billing_email($hide_email);
    $order->calculate_totals();
    $order->save();

    $use_integration = get_option('qbb_use_integration', 'yes');
    $keycrm_key = get_option('qbb_keycrm_key');
    $keycrm_source = get_option('qbb_keycrm_source');

    if ($use_integration == "yes" && $keycrm_key && $keycrm_source) {
        $api_data = array(
            "source_id" => $keycrm_source,
            "source_uuid" => $order->get_id(),
            "buyer" => array(
                "full_name" => "-",
                "email" => $hide_email,
                "phone" => $phone
            ),
            "products" => array(
                array(
                    "sku" => $product->get_sku(),
                    "price" => $product->get_price(),
                    "quantity" => $product_quantity,
                    "name" => $product->get_name(),
                    "picture" => wp_get_attachment_url($product->get_image_id())
                )
            ),
        );

        $api_url = "https://openapi.keycrm.app/v1/order";
        
        $response = wp_remote_post($api_url, array(
            'headers' => array(
                'Authorization' => 'Bearer ' . $keycrm_key,
                'Content-Type' => 'application/json',
            ),
            'body' => json_encode($api_data),
            'timeout' => 30,
        ));

        if (is_wp_error($response)) {
            wp_send_json(array('message' => 'API error: ' . $response->get_error_message()));
            return;
        }

        $response_code = wp_remote_retrieve_response_code($response);
        if ($response_code != 200) {
            wp_send_json(array('message' => 'API error: ' . wp_remote_retrieve_body($response)));
            return;
        }
    }

    WC()->cart->empty_cart();
    wp_send_json(array('message' => 'Thank you for your order!', 'order_id' => $order->get_id()));
}
