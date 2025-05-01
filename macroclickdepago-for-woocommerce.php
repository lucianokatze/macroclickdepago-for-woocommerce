<?php
/**
 * Plugin Name: Macro Click de Pago for WooCommerce
 * Plugin URI: https://github.com/lucianokatze/macroclickdepago-for-woocommerce
 * Description: Una pasarela de pago personalizada para integrar con WooCommerce y Banco Macro.
 * Version: 1.0.1
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
            $this->icon = apply_filters("woocommerce_pluspagos_icon", plugins_url("img/logos-tarjetas.png", __FILE__));
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
                    "title" => "Activar/Desactivar",
                    "label" => "Activar la pasarela de pagos",
                    "type" => "checkbox",
                    "description" => "Si desea activar Macro Click de pago como medio de cobro el check debe estar activo.",
                    "default" => "no"
                ),
                "title" => array(
                    "title" => "Titulo",
                    "type" => "text",
                    "description" => "Esto controla el título que el usuario ve durante el proceso de pago.",
                    "default" => "Macro Click de Pago",
                    "desc_tip" => true
                ),
                "description" => array(
                    "title" => "Descripción",
                    "type" => "textarea",
                    "description" => "Esto controla la descripción que el usuario ve durante el proceso de pago.",
                    "default" => "Paga con Macro Click de Pago, si tenes Macro aprovecha los beneficios exclusivos."
                ),

                "callbackCancelURL" => array(
                    "title" => "URL para cancelar el pedido",
                    "type" => "text",
                    "description" => "Ingrese la URL en caso de cancelar el pedido",
                    "default" => "https://ckdyd.net/mi-cuenta/orders/"
                ),

// Ambiente de Desarrollo

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

// Ambiente de Producción

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

            $order->reduce_order_stock();

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
            $order = wc_get_order($orderAux[0]);
            $order->add_order_note("Datos enviados por PlusPago" . json_encode($data));
            $estadoPlusPago = $data["EstadoId"];
            if ($estadoPlusPago == 3) {
                $order->payment_complete();
                $order->reduce_order_stock();
                $order->update_status("completed", "Pago Confirmado.");
            } elseif ($estadoPlusPago == 2 || $estadoPlusPago == 10) {
                $order->update_status("processing", "Procesando el pago.");
            } elseif ($estadoPlusPago == 7 || $estadoPlusPago == 8 || $estadoPlusPago == 11 || $estadoPlusPago == 4) {
                $order->update_status("cancelled", "Pago cancelado o expirado.");
            } elseif ($estadoPlusPago == 5 || $estadoPlusPago == 6) {
                $order->update_status("pending", "Error de Hash al realizar el proceso de pago");
            }
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

/*
/**
 * Clase SHA256Encript
 * Esta clase se utiliza para generar un hash SHA256 para el proceso de pago.
 * Toma como entrada la dirección IP, clave secreta, ID de comercio, ID de sucursal y el monto.
 * El hash se genera concatenando estos valores y aplicando el algoritmo SHA256.
 */
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
    add_menu_page(
        'Macro Click de Pago for WooCommerce',      
        'Macro Click de Pago',      
        'manage_options',           
        'macrocdp_admin_menu',      
        'macrocdp_admin_menu_page', 
        'dashicons-money-alt' // Usando un dashicon de WordPress en lugar de SVG
    );
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
                <h2><span class="dashicons dashicons-dashboard"></span> Estado del Sistema</h2>
                <div class="macrocdp-status-grid">
                    <div class="status-item">
                        <span class="label">Versión Plugin:</span>
                        <span class="value">1.0.1</span>
                    </div>
                    <div class="status-item">
                        <span class="label">WooCommerce:</span>
                        <span class="value <?php echo is_plugin_active('woocommerce/woocommerce.php') ? 'active' : 'inactive'; ?>">
                            <?php echo is_plugin_active('woocommerce/woocommerce.php') ? '✓ Activo' : '✗ Inactivo'; ?>
                        </span>
                    </div>
                    <div class="status-item">
                        <span class="label">Modo:</span>
                        <span class="value mode-<?php echo get_option('woocommerce_pluspagos_gateway_settings')['testmode'] === 'yes' ? 'test' : 'live'; ?>">
                            <?php echo get_option('woocommerce_pluspagos_gateway_settings')['testmode'] === 'yes' ? '🔧 Pruebas' : '🚀 Producción'; ?>
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
                <h2><span class="dashicons dashicons-chart-bar"></span> Estadísticas</h2>
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
                        <span class="stat-label">Transacciones Totales</span>
                    </div>
                    <div class="stat-box">
                        <span class="stat-value"><?php echo count($completed_orders); ?></span>
                        <span class="stat-label">Pagos Completados</span>
                    </div>
                    <div class="stat-box">
                        <span class="stat-value"><?php echo wc_price($total_sales); ?></span>
                        <span class="stat-label">Ventas Totales</span>
                    </div>
                </div>
            </div>

            <!-- Acciones Rápidas -->
            <div class="card">
                <h2><span class="dashicons dashicons-admin-tools"></span> Acciones Rápidas</h2>
                <div class="macrocdp-actions">
                    <a href="<?php echo admin_url('admin.php?page=wc-settings&tab=checkout&section=pluspagos_gateway'); ?>" class="button button-primary">
                        <span class="dashicons dashicons-admin-generic"></span> Configurar Gateway
                    </a>
                    <a href="<?php echo admin_url('admin.php?page=wc-status'); ?>" class="button">
                        <span class="dashicons dashicons-analytics"></span> Estado WooCommerce
                    </a>
                    <a href="https://github.com/lucianokatze/macroclickdepago-for-woocommerce" class="button" target="_blank">
                        <span class="dashicons dashicons-book"></span> Documentación
                    </a>
                </div>
            </div>

            <!-- Últimas Transacciones -->
            <div class="card full-width">
                <h2><span class="dashicons dashicons-list-view"></span> Últimas Transacciones</h2>
                <?php 
                $orders = wc_get_orders(array(
                    'payment_method' => 'pluspagos_gateway',
                    'limit' => 10
                ));
                
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
        </div>
    </div>
    <?php
}

// Agregar estilos de admin
function macrocdp_admin_styles() {
    $screen = get_current_screen();
    if ($screen->id === 'toplevel_page_macrocdp_admin_menu') {
        wp_enqueue_style('macrocdp-admin-css', plugins_url('assets/css/admin.css', __FILE__));
    }
}
add_action('admin_enqueue_scripts', 'macrocdp_admin_styles');