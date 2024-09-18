<?php

/**
 * Plugin Name: Macro Click de Pago for WooCommerce
 * Plugin URI: https://github.com/lucianokatze/macroclickdepago-for-woocommerce
 * Description: Una pasarela de pago personalizada para integrar con WooCommerce y Banco Macro.
 * Version: 1.0.0
 * Author: Luciano Katze
 * Author URI: https://github.com/lucianokatze
 * License: GPL-2.0+
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Requires at least: 5.0
 * Tested up to: 6.3
 * Requires PHP: 7.4
 * WC requires at least: 4.0
 * WC tested up to: 7.0
 */

 add_filter("woocommerce_payment_gateways", "macrocdp_add_gateway_class");

 function macrocdp_add_gateway_class($gateways) {
     $gateways[] = "WC_MacroCDP_Gateway";
     return $gateways;
 }

add_action("plugins_loaded", "macrocdp_init_gateway_class");

function macrocdp_init_gateway_class()
{
    class WC_MacroCDP_Gateway extends WC_Payment_Gateway
    {
        public $testmode;
        public $comercio_key;
        public $publishable_key;
        public $comercio_name;

        public function __construct()
        {
            $this->id = "macrocdp_gateway";
            $this->icon = apply_filters("woocommerce_macrocdp_icon", plugins_url("assets/img/cards.png", __FILE__));
            $this->has_fields = true;
            $this->method_title = "Macro Click de Pago";
            $this->method_description = "El Sistema de Macro Click de Pago permite a las empresas cobrar por productos y/o servicios vendidos a través de internet utilizando tarjetas de crédito. La plataforma es compatible con VISA, Mastercard, Diners, American Express, Tarjeta Shopping y Tarjeta Naranja, garantizando el cumplimiento de los estándares internacionales y locales definidos por las marcas de tarjetas mencionadas.";
            $this->supports = array("products");

            $this->init_form_fields();
            $this->init_settings();

            $this->title = $this->get_option("title");
            $this->description = $this->get_option("description");
            $this->enabled = $this->get_option("enabled");
            $this->testmode = "yes" === $this->get_option("testmode");
            $this->comercio_key = $this->testmode ? $this->get_option("test_comercio_key") : $this->get_option("comercio_key");
            $this->publishable_key = $this->testmode ? $this->get_option("test_publishable_key") : $this->get_option("publishable_key");
            $this->comercio_name = $this->get_option("comercio_name");

            add_action("woocommerce_update_options_payment_gateways_" . $this->id, array($this, "process_admin_options"));
            add_action("wp_enqueue_scripts", array($this, "payment_scripts"));
            add_action("woocommerce_api_ckdyd_macroclickpp", array($this, "webhook"));
            add_action("woocommerce_thankyou_" . $this->id, array($this, "thankyou_page"));
        }

        public function init_form_fields()
        {
            $this->form_fields = array(
                "enabled" => array(
                    "title" => "Activar/Desactivar",
                    "label" => "Activar la pasarela de pagos",
                    "type" => "checkbox",
                    "description" => "Si desea activar Macro Click de pago como medio de cobro el check debe estar activo.",
                    "default" => "no"
                ),
                "title" => array(
                    "title" => "Título",
                    "type" => "text",
                    "description" => "Esto controla el título que el usuario ve durante el proceso de pago.",
                    "default" => "Macro Click de Pago",
                    "desc_tip" => true
                ),
                "description" => array(
                    "title" => "Descripción",
                    "type" => "textarea",
                    "description" => "Esto controla la descripción que el usuario ve durante el proceso de pago.",
                    "default" => "Paga con Macro Click de Pago, si tenés Macro aprovechá los beneficios exclusivos."
                ),
                "callbackCancelURL" => array(
                    "title" => "URL para cancelar el pedido",
                    "type" => "text",
                    "description" => "Esta URL se genera automáticamente en función de la página 'Mi cuenta' y 'Pedidos' de WooCommerce.",
                    "default" => wc_get_endpoint_url('orders', '', wc_get_page_permalink('myaccount')),
                ),
                "testmode" => array(
                    "title" => "Modo de Testeo",
                    "label" => "Activar el modo de desarrollo",
                    "type" => "checkbox",
                    "description" => "Coloque la pasarela de pagos en modo de prueba utilizando claves API de prueba.",
                    "default" => "yes",
                    "desc_tip" => true
                ),
                "test_comercio_key" => array(
                    "title" => "Sandbox IDENTIFICADOR DE COMERCIO",
                    "type" => "text"
                ),
                "test_url" => array(
                    "title" => "URL de Test",
                    "type" => "text",
                    "description" => "Ingrese la URL de test para el gateway.",
                    "default" => "https://sandboxpp.asjservicios.com.ar/"
                ),
                "publishable_key" => array(
                    "title" => "Producción - Secret Key",
                    "type" => "text"
                ),
                "comercio_key" => array(
                    "title" => "Producción IDENTIFICADOR DE COMERCIO",
                    "type" => "text"
                ),
                "comercio_name" => array(
                    "title" => "Nombre Comercio",
                    "type" => "text"
                ),
                "live_url" => array(
                    "title" => "URL de Producción",
                    "type" => "text",
                    "description" => "Ingrese la URL de producción para el gateway.",
                    "default" => "https://botonpp.macroclickpago.com.ar/"
                )
            );
        }

        public function payment_fields()
        {
            if ($this->description) {
                if ($this->testmode) {
                    $this->description .= " - <strong>ATENCIÓN!</strong> El modo desarrollo fue activado, por favor revise la documentación para acceder a las tarjetas de prueba.";
                    $this->description = trim($this->description);
                }
                echo wpautop(wp_kses_post($this->description));
            }
        }

        public function payment_scripts()
        {
            if (!is_cart() && !is_checkout() && !isset($_GET["pay_for_order"])) {
                return;
            }

            if ("no" === $this->enabled) {
                return;
            }

            if (empty($this->comercio_key) || empty($this->publishable_key)) {
                return;
            }

            if (!$this->testmode && !is_ssl()) {
                return;
            }

            wp_localize_script("woocommerce_macrocdp", "macrocdp_params", array(
                "publishableKey" => $this->publishable_key
            ));

            wp_enqueue_script("woocommerce_macrocdp");
        }

        public function validate_fields()
        {
            if (empty($_POST["billing_first_name"])) {
                wc_add_notice("El nombre es requerido.", "error");
                return false;
            }
            return true;
        }

        public function process_payment($order_id)
        {
            global $woocommerce;
            $order = wc_get_order($order_id);

            $order->reduce_order_stock();

            WC()->cart->empty_cart();

            return array(
                "result" => "success",
                "redirect" => $this->get_return_url($order)
            );
        }

        public function thankyou_page($order_id)
        {
            if ($this->instructions) {
                echo wpautop(wptexturize($this->instructions));
            }
            $order = wc_get_order($order_id);
            $estadoOrden = $order->get_status();
            if ($estadoOrden == "completed") {
                $order->update_status("processing", __("TRANSACCION OK", "wc-gateway-macrocdp"));
            } else {
                $url = $this->testmode == "no" ? $this->get_option("live_url") : $this->get_option("test_url");
                $macrocdp_MerchOrderIdnewdate = date("his");
                $site_transaction_id = $order_id . "-" . $macrocdp_MerchOrderIdnewdate;
                $psp_Amount = preg_replace('#[^\d.]#', '', $order->order_total);
                $amount = str_replace(".", "", $psp_Amount);
                $hash = new SHA256Encript();
                $ipAddress = "";
                $secretKey = $this->publishable_key;
                $comercio = $this->comercio_key;
                $sucursalComercio = "";
                $hash_ok = $hash->Generate($ipAddress, $secretKey, $site_transaction_id, $sucursalComercio, $amount);
                $iv = "";

                echo "<iframe src='{$url}e-tpv?parametro={$hash_ok}&parametro2={$iv}' width='700px' height='700px'></iframe>";
            }
        }

        public function webhook()
        {
            $order = wc_get_order($_GET["id"]);
            $order->payment_complete();
            $order->reduce_order_stock();
            update_option("webhook_debug", $_GET);
        }
    }
}

// AES Encryption Class
class AESEncrypter
{
    public static function EncryptString($plainText, $phrase)
    {
        if (strlen($phrase) < 32) {
            while (strlen($phrase) < 32) {
                $phrase .= $phrase;
            }
            $phrase = substr($phrase, 0, 32);
        }
        $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length('aes-256-cbc'));
        $encrypted = openssl_encrypt($plainText, "aes-256-cbc", $phrase, OPENSSL_RAW_DATA, $iv);
        return base64_encode($iv . $encrypted);
    }

    public static function DecryptString($encryptedText, $phrase)
    {
        if (strlen($phrase) < 32) {
            while (strlen($phrase) < 32) {
                $phrase .= $phrase;
            }
            $phrase = substr($phrase, 0, 32);
        }
        $encryptedText = base64_decode($encryptedText);
        $iv = substr($encryptedText, 0, openssl_cipher_iv_length('aes-256-cbc'));
        $encrypted = substr($encryptedText, openssl_cipher_iv_length('aes-256-cbc'));
        return openssl_decrypt($encrypted, "aes-256-cbc", $phrase, OPENSSL_RAW_DATA, $iv);
    }
}

// SHA256 Encryption Class
class SHA256Encript
{
    public function Generate($ipAddress, $secretKey, $comercio, $sucursal, $amount)
    {
        $ipAddress = $this->getRealIpAddr();
        $input = sprintf("%s*%s*%s*%s*%s", $ipAddress, $comercio, $sucursal, $amount, $secretKey);
        $inputArray = utf8_encode($input);
        $hashedArray = unpack('C*', hash("sha256", $inputArray, true));
        $string = '';
        for ($i = 1; $i <= count($hashedArray); $i++) {
            $string .= str_pad(strtolower(dechex($hashedArray[$i])), 2, '0', STR_PAD_LEFT);
        }
        return $string;
    }

    private function getRealIpAddr()
    {
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            return $_SERVER['HTTP_CLIENT_IP'];
        }
        if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            return $_SERVER['HTTP_X_FORWARDED_FOR'];
        }
        return $_SERVER['REMOTE_ADDR'];
    }
}

add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'macrocdp_add_details_link');

function macrocdp_add_details_link($links)
{
    $links[] = '<a href="https://ckdyd.net/contacto" class="thickbox" title="Contacto con el soporte">Soporte</a>';
    $links[] = '<a href="' . admin_url('admin.php?page=wc-settings&tab=checkout') . '" title="Configurar">Configurar</a>';
    return $links;
}

add_action('admin_menu', 'macrocdp_add_admin_menu');

function macrocdp_add_admin_menu() {
    add_menu_page(
        'Macro Click de Pago for WooCommerce',      
        'Macro Click de Pago',      
        'manage_options',           
        'macrocdp_admin_menu',      
        'macrocdp_admin_menu_page', 
        plugin_dir_url(__FILE__) . 'assets/img/icon.svg'
    );
}



function macrocdp_admin_menu_page() {
    include(plugin_dir_path(__FILE__) . 'views/admin-menu-page.php');
}
