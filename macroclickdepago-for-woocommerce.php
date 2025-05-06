<?php
/**
 * Plugin Name: Macro Click de Pago for WooCommerce
 * Plugin URI: https://github.com/lucianokatze/macroclickdepago-for-woocommerce
 * Description: Una pasarela de pago personalizada para integrar con WooCommerce y Banco Macro.
 * Version: 1.0.2
 * Author: Luciano Katze
 * Author URI: https://github.com/lucianokatze
 * License: GPL-2.0+
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: macroclickdepago-for-woocommerce
 * Domain Path: /languages
 * Requires at least: 6.0
 * Tested up to: 6.3
 * Requires PHP: 7.4
 * WC requires at least: 4.0
 * WC tested up to: 7.0
 * WooCommerce HPOS Compatible: true
 */

// Cargar traducciones
add_action('init', 'macrocdp_load_textdomain');
function macrocdp_load_textdomain() {
    load_plugin_textdomain(
        'macroclickdepago-for-woocommerce',
        false,
        dirname(plugin_basename(__FILE__)) . '/languages'
    );
}

// Agregar soporte HPOS
add_action('before_woocommerce_init', function() {
    if (class_exists(\Automattic\WooCommerce\Utilities\FeaturesUtil::class)) {
        \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility('custom_order_tables', __FILE__, true);
    }
});

add_filter("woocommerce_payment_gateways", "pluspagos_add_gateway_class");

function pluspagos_add_gateway_class($gateways) {
    $gateways[] = "WC_PlusPagos_Gateway";
    return $gateways;
}

add_action("plugins_loaded", "pluspagos_init_gateway_class");

function pluspagos_init_gateway_class() {
    class WC_PlusPagos_Gateway extends WC_Payment_Gateway {
        // Declaración de las propiedades necesarias
        public $testmode; // Modo de prueba
        public $comercio_key; // Clave de comercio
        public $publishable_key; // Clave pública
        public $comercio_name; // Nombre del comercio
    
        public function __construct() {
            $this->id = "pluspagos_gateway"; // ID del gateway
            // Ruta al ícono de tarjetas que se muestra en el checkout. Puedes personalizar esta ruta según tu necesidad.
            $this->icon = apply_filters("woocommerce_pluspagos_icon", plugins_url("assets/img/logo.png", __FILE__));
            $this->has_fields = true; // Si se requieren campos adicionales en el formulario de pago
            $this->method_title = "Macro Click de Pago"; // Título del método de pago
            $this->method_description = "El Sistema de Macro Click de Pago permite a las empresas cobrar por productos y/o servicios vendidos a través de internet utilizando tarjetas de crédito. La plataforma es compatible con VISA (incluyendo Verified by VISA), Mastercard, Diners, American Express, Tarjeta Shopping y Tarjeta Naranja, garantizando el cumplimiento de los estándares internacionales y locales definidos por las marcas de tarjetas mencionadas.";
            $this->supports = array("products");
    
            $this->init_form_fields();
            $this->init_settings();
    
            // Asignación de propiedades a partir de las opciones
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
        #region Menú de configuración para el usuario
        public function init_form_fields() {
            $this->form_fields = array(

                // Configuración de Avisos del Plugin

                "enabled" => array(
                    "title" => __("Activar/Desactivar", 'macroclickdepago-for-woocommerce'),
                    "label" => __("Activar la pasarela de pagos", 'macroclickdepago-for-woocommerce'),
                    "type" => "checkbox",
                    "description" => __("Si desea activar Macro Click de pago como medio de cobro el check debe estar activo.", 'macroclickdepago-for-woocommerce'),
                    "default" => "no"
                ),
                "title" => array(
                    "title" => __("Titulo", 'macroclickdepago-for-woocommerce'),
                    "type" => "text",
                    "description" => __("Esto controla el título que el usuario ve durante el proceso de pago.", 'macroclickdepago-for-woocommerce'),
                    "default" => __("Macro Click de Pago", 'macroclickdepago-for-woocommerce'),
                    "desc_tip" => true
                ),
                "description" => array(
                    "title" => __("Descripción", 'macroclickdepago-for-woocommerce'),
                    "type" => "textarea",
                    "description" => __("Esto controla la descripción que el usuario ve durante el proceso de pago.", 'macroclickdepago-for-woocommerce'),
                    "default" => __("Paga con Macro Click de Pago, si tenes Macro aprovecha los beneficios exclusivos.", 'macroclickdepago-for-woocommerce')
                ),

                "callbackCancelURL" => array(
                    "title" => __("URL para cancelar el pedido", 'macroclickdepago-for-woocommerce'),
                    "type" => "text",
                    "description" => __("Ingrese la URL en caso de cancelar el pedido", 'macroclickdepago-for-woocommerce'),
                    "default" => get_site_url() . "/mi-cuenta/orders/"
                ),

// Ambiente de Desarrollo

                "testmode" => array(
                    "title" => __("Modo de Testeo", 'macroclickdepago-for-woocommerce'),
                    "label" => __("Activar el modo de desarrollo", 'macroclickdepago-for-woocommerce'),
                    "type" => "checkbox",
                    "description" => __("Coloque la pasarela de pagos en modo de prueba utilizando claves API de prueba.", 'macroclickdepago-for-woocommerce'),
                    "default" => "yes",
                    "desc_tip" => true
                ),
                
                "test_comercio_key" => array(
                    "title" => __("Sandbox IDENTIFICADOR DE COMERCIO", 'macroclickdepago-for-woocommerce'),
                    "type" => "text"
                ),

                "test_url" => array(
                    "title" => __("URL de Test", 'macroclickdepago-for-woocommerce'),
                    "type" => "text",
                    "description" => __("Ingrese la URL de test para el gateway.", 'macroclickdepago-for-woocommerce'),
                    "default" => "https://sandboxpp.asjservicios.com.ar/"
                ),

// Ambiente de Producción

                "publishable_key" => array(
                    "title" => __("Producción - Secret Key", 'macroclickdepago-for-woocommerce'),
                    "type" => "text"
                ),
                "comercio_key" => array(
                    "title" => __("Producción IDENTIFICADOR DE COMERCIO", 'macroclickdepago-for-woocommerce'),
                    "type" => "text"
                ),
                "comercio_name" => array(
                    "title" => __("Nombre Comercio", 'macroclickdepago-for-woocommerce'),
                    "type" => "text"
                ),

                "live_url" => array(
                    "title" => __("URL de Producción", 'macroclickdepago-for-woocommerce'),
                    "type" => "text",
                    "description" => __("Ingrese la URL de producción para el gateway.", 'macroclickdepago-for-woocommerce'),
                    "default" => "https://botonpp.macroclickpago.com.ar/"
                )

            );
        }
#region set y aviso de modo desarrollo.

        public function payment_fields() {
            if ($this->description) {
                if ($this->testmode) {
                    $this->description .= " - <strong>ATENCIÓN!</strong> El modo desarrollo fue activado, por favor revise la documentación para acceder a las tarjetas de prueba.";
                    $this->description = trim($this->description);
                }
                echo wpautop(wp_kses_post($this->description));
            }
        }

        public function payment_scripts() {
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
            
            wp_localize_script("woocommerce_pluspagos", "pluspagos_params", array(
                "publishableKey" => $this->publishable_key
            ));

            wp_enqueue_script("woocommerce_pluspagos");
        }

        public function validate_fields() {
            if (empty($_POST["billing_first_name"])) {
                wc_add_notice("First name is required!", "error");
                return false;
            }
            return true;
        }

        public function process_payment($order_id) {
            global $woocommerce;
            $order = wc_get_order($order_id);

            // Usar los nuevos métodos HPOS
            if (method_exists($order, 'get_data_store')) {
                $order->get_data_store()->reduce_order_stock($order);
            } else {
                $order->reduce_order_stock();
            }

            WC()->cart->empty_cart();

            return array(
                "result" => "success",
                "redirect" => $this->get_return_url($order)
            );
        }

        public function thankyou_page($order_id) {
            if ($this->instructions) {
                echo wpautop(wptexturize($this->instructions));
            }
            $order = wc_get_order($order_id);
            $estadoOrden = $order->get_status();
            if ($estadoOrden == "completed") {
                $order->update_status("processing", __("TRANSACCION OK", "wc-gateway-pluspagos"));
            } else {
                if ($this->settings["testmode"] == "no") {
                    $url = $this->get_option("live_url"); // Cambiado a opción de configuración
                } else {
                    $url = $this->get_option("test_url"); // Cambiado a opción de configuración
                }
                $pluspagos_MerchOrderIdnewdate = date("his");
                $site_transaction_id = $order_id . "-" . $pluspagos_MerchOrderIdnewdate;
                // Se cambian estas partes en caso de que el decimal de WooCommerce falle o este configurado
                // de otra forma, caso contrario configurarlo en WooCoomerce
                // Revisar el README.md para configurar WooCommerce correctamente                    
                // $psp_Amount = preg_replace('#[^\d.]#', '', $order->order_total); // Eliminar caracteres no numéricos y puntos
                // $amount = intval(str_replace(".", "", $psp_Amount)) * 100; // Eliminar el punto y multiplicar por 100
                $psp_Amount = preg_replace('#[^\d.]#', '', $order->order_total);
                $amount = str_replace(".", "", $psp_Amount);
                $hash = new SHA256Encript();
                $ipAddress = "";
                $secretKey = $this->publishable_key;
                $comercio = $this->comercio_key;
                $sucursalComercio = "";
                $hash_ok = $hash->Generate($ipAddress, $secretKey, $comercio, $sucursalComercio, $amount);
                $aes = new AESEncrypter();
                $callbackSuccess = $order->get_checkout_order_received_url();
                $callbackCancel = $this->get_option("callbackCancelURL"); 
                $callbackEncriptada = $aes->EncryptString($callbackSuccess, $secretKey);
                $cancelEncriptada = $aes->EncryptString($callbackCancel, $secretKey);
                $montoEncriptado = $aes->EncryptString($amount, $secretKey);
                $sucursalEncriptada = $aes->EncryptString($sucursalComercio, $secretKey);
                $data = array(
                    "CallbackSuccess" => $callbackEncriptada,
                    "CallbackCancel" => $cancelEncriptada,
                    "Comercio" => $comercio,
                    "SucursalComercio" => $sucursalEncriptada,
                    "Hash" => $hash_ok,
                    "TransaccionComercioId" => $site_transaction_id,
                    "Monto" => $montoEncriptado,
                    "Producto[0]" => "Compra en " . $this->comercio_name . " - Orden #" . $order_id
                );
                $html = "<html><body><form id='form' action='{$url}' method='post'>";
                foreach ($data as $key => $value) {
                    $html .= "<input type='hidden' name='{$key}' value='{$value}'>";
                }
                $html .= "</form><script>document.getElementById('form').submit();</script>";
                $html .= "</body></html>";
                print $html;
        
                // Agregar registro de depuración
                if (defined('WC_LOG_DIR')) {
                    $logger = wc_get_logger();
                    $logger->info('URL de producción: ' . $url);
                    $logger->info('Datos enviados: ' . json_encode($data));
                }
            }
        }

        public function webhook() {
            header("HTTP/1.1 200 OK");
            $data = json_decode(file_get_contents("php://input"), true);
            $orderAux = explode("-", $data["TransaccionComercioId"]);
            
            // Usar el nuevo método de obtención de orden compatible con HPOS
            $order = wc_get_order($orderAux[0]);
            if (!$order) {
                return;
            }

            // Usar métodos compatibles con HPOS para actualizar el estado
            $order->add_order_note("Datos enviados por PlusPago: " . json_encode($data));
            $estadoPlusPago = $data["EstadoId"];
            
            if ($estadoPlusPago == 3) {
                $order->set_status("completed", "Pago Confirmado.");
                $order->payment_complete();
            } elseif ($estadoPlusPago == 2 || $estadoPlusPago == 10) {
                $order->set_status("processing", "Procesando el pago.");
            } elseif ($estadoPlusPago == 7 || $estadoPlusPago == 8 || $estadoPlusPago == 11 || $estadoPlusPago == 4) {
                $order->set_status("cancelled", "Pago cancelado o expirado.");
            } elseif ($estadoPlusPago == 5 || $estadoPlusPago == 6) {
                $order->set_status("pending", "Error de Hash al realizar el proceso de pago");
            }
            
            $order->save();
            update_option("webhook_debug", $data);
        }
    }
}

class AESEncrypter {
    public static function EncryptString($plainText, $phrase) {
        if (strlen($phrase) < 32) {
            while (strlen($phrase) < 32) {
                $phrase .= $phrase;
            }
            $phrase = substr($phrase, 0, 32);
        }
        if (strlen($phrase) > 32) {
            $phrase = substr($phrase, 0, 32);
        }
        $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length('aes-256-cbc'));
        $string = openssl_encrypt($plainText, "aes-256-cbc", $phrase, OPENSSL_RAW_DATA, $iv);
        return base64_encode($iv . $string);
    }
    
    public static function DecryptString($plainText, $phrase) {
        if (strlen($phrase) < 32) {
            while (strlen($phrase) < 32) {
                $phrase .= $phrase;
            }
            $phrase = substr($phrase, 0, 32);
        }
        if (strlen($phrase) > 32) {
            $phrase = substr($phrase, 0, 32);
        }
        
        $plainText = base64_decode($plainText);
        $encodedData = substr($plainText, openssl_cipher_iv_length('aes-256-cbc'));
        $iv = substr($plainText, 0, openssl_cipher_iv_length('aes-256-cbc'));
        $decrypted = openssl_decrypt($encodedData, "aes-256-cbc", $phrase, OPENSSL_RAW_DATA, $iv);
        return $decrypted;
    }
}

/**
 * Clase SHA256Encript
 * Esta clase se utiliza para generar un hash SHA256 para el proceso de pago.
 * Toma como entrada la dirección IP, clave secreta, ID de comercio, ID de sucursal y el monto.
 * El hash se genera concatenando estos valores y aplicando el algoritmo SHA256.
 */
class SHA256Encript {
    public function Generate($ipAddress, $secretKey, $comercio, $sucursal, $amount) {
        $ipAddress = $this->getRealIpAddr();
        
        $input = sprintf("%s*%s*%s*%s*%s", $ipAddress, $comercio, $sucursal, $amount, $secretKey);
        $inputArray = utf8_encode($input);
        $hashedArray = unpack('C*', hash("sha256", $inputArray, true));
        $string = null;
        for ($i = 1; $i <= count($hashedArray); $i++) {
            $string .= str_pad(strtolower(dechex($hashedArray[$i])), 2, '0', STR_PAD_LEFT);
        }
        return $string;
    }
    
    private function getRealIpAddr() {
        if (!empty($_SERVER['HTTP_CLIENT_IP']))
        return $_SERVER['HTTP_CLIENT_IP'];
    
    if (!empty($_SERVER['HTTP_X_FORWARDED_FOR']))
            return $_SERVER['HTTP_X_FORWARDED_FOR'];
        
        return $_SERVER['REMOTE_ADDR'];
    }
}

add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'pluspagos_add_details_link');

function pluspagos_add_details_link($links) {
    $links[] = '<a href="https://ckdyd.net/contacto" class="thickbox" title="Contacto con el soporte">Soporte</a>';
    $links[] = '<a href="' . admin_url('admin.php?page=wc-settings&tab=checkout') . '" title="Configurar">Configurar</a>';
    return $links;
}

add_action('admin_menu', 'macrocdp_add_admin_menu');

function macrocdp_add_admin_menu() {
    $icon_url = plugins_url('assets/img/icon.svg', __FILE__);
    
    // Menú principal
    add_menu_page(
        __('Macro Click de Pago for WooCommerce', 'macroclickdepago-for-woocommerce'),
        __('Macro Click de Pago', 'macroclickdepago-for-woocommerce'),
        'manage_options',
        'macrocdp_admin_menu',
        'macrocdp_admin_menu_page',
        $icon_url
    );

    // Submenú "Detalles" (mismo que la página principal)
    add_submenu_page(
        'macrocdp_admin_menu',
        __('Detalles', 'macroclickdepago-for-woocommerce'),
        __('Detalles', 'macroclickdepago-for-woocommerce'),
        'manage_options',
        'macrocdp_admin_menu'
    );

    // Submenú "Documentación"
    add_submenu_page(
        'macrocdp_admin_menu',
        __('Documentación', 'macroclickdepago-for-woocommerce'),
        __('Documentación', 'macroclickdepago-for-woocommerce'),
        'manage_options',
        'macrocdp_documentation',
        'macrocdp_documentation_page'
    );
}

// Agregar la función para la página de documentación
function macrocdp_documentation_page() {
    ?>
    <div class="wrap macrocdp-admin">
        <h1><img src="<?php echo plugins_url('assets/img/macro-logo.png', __FILE__); ?>" class="macrocdp-logo" /> Documentación de Macro Click de Pago</h1>
        
        <div class="documentation-grid">
            <!-- Guía de Estado del Sistema -->
            <div class="card full-width">
                <h2><span class="dashicons dashicons-info-outline"></span> <?php _e('Guía de Estado del Sistema', 'macroclickdepago-for-woocommerce'); ?></h2>
                <div class="doc-section">
                    <h3><?php _e('Indicadores de Estado', 'macroclickdepago-for-woocommerce'); ?></h3>
                    <dl class="status-guide">
                        <dt><?php _e('Versión Plugin', 'macroclickdepago-for-woocommerce'); ?></dt>
                        <dd><?php _e('Muestra la versión actual instalada del plugin Macro Click de Pago.', 'macroclickdepago-for-woocommerce'); ?></dd>
                        
                        <dt><?php _e('WooCommerce', 'macroclickdepago-for-woocommerce'); ?></dt>
                        <dd><?php _e('Indica si WooCommerce está activo en el sistema. Es necesario para el funcionamiento del plugin.', 'macroclickdepago-for-woocommerce'); ?></dd>
                        
                        <dt><?php _e('Estado WooCommerce', 'macroclickdepago-for-woocommerce'); ?></dt>
                        <dd><?php _e('Muestra si la tienda está en modo desarrollo o producción según la configuración de WooCommerce.', 'macroclickdepago-for-woocommerce'); ?></dd>
                        
                        <dt><?php _e('Gateway', 'macroclickdepago-for-woocommerce'); ?></dt>
                        <dd><?php _e('Indica si la pasarela de pago está activada y lista para recibir pagos.', 'macroclickdepago-for-woocommerce'); ?></dd>
                        
                        <dt><?php _e('Modo Gateway', 'macroclickdepago-for-woocommerce'); ?></dt>
                        <dd><?php _e('Muestra si la pasarela está en modo pruebas (desarrollo) o producción.', 'macroclickdepago-for-woocommerce'); ?></dd>
                        
                        <dt><?php _e('SSL', 'macroclickdepago-for-woocommerce'); ?></dt>
                        <dd><?php _e('Verifica si el sitio tiene un certificado SSL activo, necesario para procesar pagos en producción.', 'macroclickdepago-for-woocommerce'); ?></dd>
                    </dl>
                </div>
            </div>

            <!-- Documentación del README -->
            <div class="card full-width">
                <h2><span class="dashicons dashicons-book"></span> Manual de Usuario</h2>
                <div class="doc-section">
                    <?php
                    $readme_path = plugin_dir_path(__FILE__) . 'README.md';
                    if (file_exists($readme_path)) {
                        // Requiere el parseador de Markdown
                        require_once plugin_dir_path(__FILE__) . 'includes/Parsedown.php';
                        $parsedown = new Parsedown();
                        echo $parsedown->text(file_get_contents($readme_path));
                    } else {
                        echo '<p>La documentación no está disponible.</p>';
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>
    <?php
}

// Agregar estilos para el icono del menú
add_action('admin_head', 'macrocdp_admin_icon_style');
function macrocdp_admin_icon_style() {
    echo '<style>
        #adminmenu .toplevel_page_macrocdp_admin_menu img {
            width: 20px;
            height: 20px;
            padding-top: 7px;
        }
    </style>';
}

// Agregar soporte para estadísticas
add_action('wp_ajax_macrocdp_get_stats', 'macrocdp_get_stats_ajax');
function macrocdp_get_stats_ajax() {
    check_ajax_referer('macrocdp_stats', 'nonce');
    
    $period = $_GET['period'] ?? 'week';
    $stats = macrocdp_get_period_stats($period);
    
    wp_send_json_success($stats);
}

function macrocdp_get_period_stats($period) {
    $end_date = current_time('mysql');
    
    switch($period) {
        case 'day':
            $start_date = date('Y-m-d 00:00:00', strtotime('-1 day'));
            $group_by = 'HOUR';
            break;
        case 'week':
            $start_date = date('Y-m-d 00:00:00', strtotime('-7 days'));
            $group_by = 'DAY';
            break;
        case 'month':
            $start_date = date('Y-m-d 00:00:00', strtotime('-30 days'));
            $group_by = 'DAY';
            break;
        case 'semester':
            $start_date = date('Y-m-d 00:00:00', strtotime('-6 months'));
            $group_by = 'MONTH';
            break;
        case 'year':
            $start_date = date('Y-m-d 00:00:00', strtotime('-1 year'));
            $group_by = 'MONTH';
            break;
        default:
            $start_date = date('Y-m-d 00:00:00', strtotime('-7 days'));
            $group_by = 'DAY';
    }
    
    $orders = wc_get_orders(array(
        'payment_method' => 'pluspagos_gateway',
        'date_created' => $start_date . '...' . $end_date,
        'status' => array('completed', 'processing'),
    ));

    // Procesar datos para el gráfico
    $stats = array(
        'labels' => array(),
        'values' => array(),
        'totalSales' => 0,
        'totalOrders' => count($orders),
        'average' => 0
    );
    
    foreach($orders as $order) {
        $date = $order->get_date_created()->date_i18n('Y-m-d');
        if (!isset($stats['values'][$date])) {
            $stats['values'][$date] = 0;
        }
        $stats['values'][$date] += $order->get_total();
        $stats['totalSales'] += $order->get_total();
    }
    
    $stats['average'] = $stats['totalOrders'] > 0 ? 
        $stats['totalSales'] / $stats['totalOrders'] : 0;
    
    return $stats;
}

function macrocdp_admin_menu_page() {
    // Agregar estilos de admin
    wp_enqueue_style('macrocdp-admin-style', plugins_url('assets/css/admin.css', __FILE__));
    ?>
    <div class="wrap macrocdp-admin">
        <h1><img src="<?php echo plugins_url('assets/img/macro-logo.png', __FILE__); ?>" class="macrocdp-logo" /> <?php echo esc_html(get_admin_page_title()); ?></h1>
        
        <div class="macrocdp-grid">
            <!-- Estado del Sistema -->
            <div class="card">
                <h2><span class="dashicons dashicons-dashboard"></span> <?php _e('Estado del Sistema', 'macroclickdepago-for-woocommerce'); ?></h2>
                <div class="macrocdp-status-grid">
                    <div class="status-item">
                        <span class="label"><?php _e('Versión Plugin:', 'macroclickdepago-for-woocommerce'); ?></span>
                        <span class="value">1.0.1</span>
                    </div>
                    <div class="status-item">
                        <span class="label"><?php _e('WooCommerce:', 'macroclickdepago-for-woocommerce'); ?></span>
                        <span class="value <?php echo is_plugin_active('woocommerce/woocommerce.php') ? 'active' : 'inactive'; ?>">
                            <?php echo is_plugin_active('woocommerce/woocommerce.php') ? 
                                _e('✓ Activo', 'macroclickdepago-for-woocommerce') : 
                                _e('✗ Inactivo', 'macroclickdepago-for-woocommerce'); ?>
                        </span>
                    </div>
                    <div class="status-item">
                        <span class="label"><?php _e('Estado WooCommerce:', 'macroclickdepago-for-woocommerce'); ?></span>
                        <?php 
                        $wc_status = get_option('woocommerce_store_address') ? 'live' : 'test';
                        ?>
                        <span class="value mode-<?php echo $wc_status; ?>">
                            <?php echo $wc_status === 'test' ? 
                                _e('🔧 Desarrollo', 'macroclickdepago-for-woocommerce') : 
                                _e('🏪 Tienda Activa', 'macroclickdepago-for-woocommerce'); ?>
                        </span>
                    </div>
                    <div class="status-item">
                        <span class="label">Modo:</span>
                        <?php 
                        $gateway = new WC_PlusPagos_Gateway();
                        $testmode = $gateway->testmode;
                        ?>
                        <span class="value mode-<?php echo $testmode ? 'test' : 'live'; ?>">
                            <?php echo $testmode ? '🔧 Modo Desarrollo' : '🚀 Producción'; ?>
                        </span>
                    </div>
                    <div class="status-item">
                        <span class="label">Gateway:</span>
                        <?php 
                        $enabled = $gateway->enabled === 'yes';
                        ?>
                        <span class="value <?php echo $enabled ? 'active' : 'inactive'; ?>">
                            <?php echo $enabled ? '✓ Activado' : '✗ Desactivado'; ?>
                        </span>
                    </div>
                    <div class="status-item">
                        <span class="label">Modo Gateway:</span>
                        <?php 
                        $testmode = $gateway->testmode;
                        ?>
                        <span class="value mode-<?php echo $testmode ? 'test' : 'live'; ?>">
                            <?php echo $testmode ? '🔧 Desarrollo' : '🚀 Producción'; ?>
                        </span>
                    </div>
                    <div class="status-item">
                        <span class="label">SSL:</span>
                        <span class="value <?php echo is_ssl() ? 'active' : 'inactive'; ?>">
                            <?php echo is_ssl() ? '🔒 Activo' : '⚠️ Inactivo'; ?>
                        </span>
                    </div>
                </div>
            </div>

            <!-- Estadísticas -->
            <div class="card">
                <h2><span class="dashicons dashicons-chart-bar"></span> <?php _e('Estadísticas', 'macroclickdepago-for-woocommerce'); ?></h2>
                <?php
                $total_orders = wc_get_orders(array(
                    'payment_method' => 'pluspagos_gateway',
                    'return' => 'ids',
                ));
                
                $completed_orders = wc_get_orders(array(
                    'payment_method' => 'pluspagos_gateway',
                    'status' => 'completed',
                    'return' => 'ids',
                ));

                $total_sales = array_reduce($completed_orders, function($carry, $order_id) {
                    $order = wc_get_order($order_id);
                    return $carry + $order->get_total();
                }, 0);
                ?>
                <div class="macrocdp-stats-grid">
                    <div class="stat-box">
                        <span class="stat-value"><?php echo count($total_orders); ?></span>
                        <span class="stat-label"><?php _e('Transacciones Totales', 'macroclickdepago-for-woocommerce'); ?></span>
                    </div>
                    <div class="stat-box">
                        <span class="stat-value"><?php echo count($completed_orders); ?></span>
                        <span class="stat-label"><?php _e('Pagos Completados', 'macroclickdepago-for-woocommerce'); ?></span>
                    </div>
                    <div class="stat-box">
                        <span class="stat-value"><?php echo wc_price($total_sales); ?></span>
                        <span class="stat-label"><?php _e('Ventas Totales', 'macroclickdepago-for-woocommerce'); ?></span>
                    </div>
                </div>
            </div>

            <!-- Acciones Rápidas -->
            <div class="card">
                <h2><span class="dashicons dashicons-admin-tools"></span> <?php _e('Acciones Rápidas', 'macroclickdepago-for-woocommerce'); ?></h2>
                <div class="macrocdp-actions">
                    <a href="<?php echo admin_url('admin.php?page=wc-settings&tab=checkout&section=pluspagos_gateway'); ?>" class="button button-primary">
                        <span class="dashicons dashicons-admin-generic"></span> Configurar Gateway
                    </a>
                    <a href="<?php echo admin_url('admin.php?page=wc-status'); ?>" class="button">
                        <span class="dashicons dashicons-analytics"></span> Estado WooCommerce
                    </a>
                    <a href="<?php echo admin_url('admin.php?page=macrocdp_documentation'); ?>" class="button">
                        <span class="dashicons dashicons-book"></span> Documentación
                    </a>
                </div>
            </div>

            <!-- Estadísticas Mejoradas -->
            <div class="full-width stats-detailed">
                <h2><span class="dashicons dashicons-chart-bar"></span> Estadísticas Detalladas</h2>
                <div class="stats-controls">
                    <select id="macrocdp-stats-period">
                        <option value="day">Hoy</option>
                        <option value="week" selected>Esta semana</option>
                        <option value="month">Este mes</option>
                        <option value="semester">Último semestre</option>
                        <option value="year">Este año</option>
                    </select>
                </div>
                <div class="stats-container">
                    <?php if (empty($total_orders)) : ?>
                        <div class="placeholder-chart">
                            <div class="placeholder-message">
                                <span class="dashicons dashicons-chart-line"></span>
                                <p>Aún no hay datos para mostrar</p>
                                <p class="placeholder-subtitle">Aquí se mostrarán las estadísticas una vez que comiences a recibir pagos</p>
                            </div>
                            <div class="placeholder-graph">
                                <!-- Gráfico de ejemplo -->
                                <svg viewBox="0 0 300 100" class="placeholder-svg">
                                    <polyline
                                        fill="none"
                                        stroke="#ddd"
                                        stroke-width="2"
                                        points="0,80 50,60 100,70 150,50 200,40 250,30 300,20"
                                    />
                                </svg>
                            </div>
                        </div>
                    <?php else : ?>
                        <canvas id="macrocdp-sales-chart"></canvas>
                    <?php endif; ?>
                </div>
            </div>

            <div class="flex-container">
                <!-- Últimas Transacciones -->
                <div class="card half-width">
                    <h2><span class="dashicons dashicons-list-view"></span> Últimas Transacciones</h2>
                    <?php 
                    // Actualizar las consultas de órdenes para HPOS
                    $query_args = array(
                        'payment_method' => 'pluspagos_gateway',
                        'limit' => 10,
                        'type' => 'shop_order',
                    );
                    
                    $orders = wc_get_orders($query_args);
                    
                    if (!empty($orders)) : ?>
                        <table class="widefat striped">
                            <thead>
                                <tr>
                                    <th>Order ID</th>
                                    <th>Cliente</th>
                                    <th>Estado</th>
                                    <th>Total</th>
                                    <th>Fecha</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($orders as $order) : ?>
                                    <tr>
                                        <td>#<?php echo $order->get_id(); ?></td>
                                        <td><?php echo $order->get_billing_first_name() . ' ' . $order->get_billing_last_name(); ?></td>
                                        <td><span class="order-status status-<?php echo $order->get_status(); ?>"><?php echo wc_get_order_status_name($order->get_status()); ?></span></td>
                                        <td><?php echo $order->get_formatted_order_total(); ?></td>
                                        <td><?php echo $order->get_date_created()->date_i18n('Y-m-d H:i'); ?></td>
                                        <td>
                                            <a href="<?php echo $order->get_edit_order_url(); ?>" class="button button-small">Ver</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else : ?>
                        <p>No hay transacciones recientes.</p>
                    <?php endif; ?>
                </div>

                <!-- Disclaimer -->
                <div class="card half-width">
                    <h2><span class="dashicons dashicons-info"></span> Aviso Legal</h2>
                    <div class="disclaimer-content" style="padding: 15px;">
                        <p><strong>Advertencia:</strong> Este plugin no es un producto oficial del Banco Macro y no está afiliado, asociado, autorizado, respaldado por, o de ninguna manera oficialmente conectado con el Banco Macro S.A.</p>
                        <p>Los nombres de productos, logos y marcas son propiedad de sus respectivos dueños. Los nombres de empresas, productos y servicios utilizados en este plugin son solo para propósitos de identificación.</p>
                        <p>Este es un proyecto de código abierto desarrollado por <?php echo 'Luciano Katze'; ?> y está disponible bajo la <a href="https://www.gnu.org/licenses/gpl-2.0.html" target="_blank">Licencia Pública General de GNU v2.0</a>.</p>
                        <p>Para más información o contribuciones, visite: <a href="<?php echo esc_url('https://github.com/lucianokatze/macroclickdepago-for-woocommerce'); ?>" target="_blank">GitHub Repository</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php
    // Encolar scripts necesarios
    wp_enqueue_script('chart-js', 'https://cdn.jsdelivr.net/npm/chart.js', array(), '3.7.0', true);
    wp_enqueue_script('macrocdp-admin-stats', plugins_url('assets/js/admin-stats.js', __FILE__), array('jquery', 'chart-js'), '1.0.0', true);
    wp_localize_script('macrocdp-admin-stats', 'macrocdpStats', array(
        'nonce' => wp_create_nonce('macrocdp_stats')
      ));
}