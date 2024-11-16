# Macro Click de Pago for WooCommerce

**Versión:** 1.0.0  
**Autor:** Luciano Katze  
**Plugin URI:** [https://github.com/lucianokatze/macroclickdepago-for-woocommerce](https://github.com/lucianokatze/macroclickdepago-for-woocommerce)

## Descripción

Macro Click de Pago for WooCommerce es una pasarela de pago personalizada que permite la integración con WooCommerce y Banco Macro, brindando una solución rápida y segura para tus transacciones.

## Características

- Integración fácil con WooCommerce.
- Soporte para pagos con Banco Macro.
- Configuración rápida y amigable.
- Totalmente personalizable según las necesidades del comercio.

## Requisitos

- **WordPress:** 5.0 o superior
- **WooCommerce:** 4.0 o superior
- **PHP:** 7.4 o superior

## Instalación

1. Descarga el archivo `.zip` de la [última versión release](https://github.com/lucianokatze/macroclickdepago-for-woocommerce/releases).
2. Ve al panel de administración de WordPress y dirígete a **Plugins > Añadir nuevo**.
3. Haz clic en **Subir plugin** y selecciona el archivo `.zip` descargado.
4. Activa el plugin desde el menú **Plugins**.

### Instalación Alnternativa por FTP

1. Descomprimí el archivo `.zip` del plugin.
2. Subí la carpeta descomprimida a la ruta `wp-content/plugins/` en tu servidor.
3. Activá el plugin desde **Plugins > Plugins instalados** en el panel de WordPress.

## Configuración del Plugin

Una vez instalado y activado el plugin, seguí estos pasos para configurarlo:

1. **Acceder a la configuración:**
   - Desde el menú de Wordpress navegar hasta la configuración general de WooCommerce
   - En la configuración de la tienda asegurarse que este configurado como separador de miles con un punto, separador decimal con una coma y que el número de decimales sea de 2
   - Desde el menú de WordPress, navegá a **WooCommerce > Ajustes**.
   - Seleccioná la pestaña **Pagos** y hacé clic en **Macro Click de Pago**.

2. **Configurar los campos:**

   - **Activar/Desactivar:** Marcá la casilla para activar Macro Click de Pago como opción de pago en tu tienda.
   - **Título:** Es el texto que el cliente verá durante el proceso de pago (por defecto: "Macro Click de Pago").
   - **Descripción:** Un mensaje adicional que se muestra al cliente (podés incluir beneficios de usar este método de pago).
   - **Modo de Testeo:** Activá esta opción para operar en modo prueba (sandbox). Es útil para hacer pruebas sin afectar las transacciones reales.
   - **Claves de API para Testeo:**
     - **Clave Secreta (Test):** Ingresá la clave secreta de prueba proporcionada por Macro Click de Pagos.
     - **Sandbox IDENTIFICADOR DE COMERCIO (Test):** Ingresá la clave de comercio de prueba.
   - **URL de Test:** URL del entorno de prueba de Macro Click de Pagos.
   - **Claves de API para Producción:**
     - **Clave Secreta (Producción):** Clave para entornos en vivo.
     - **Producción IDENTIFICADOR DE COMERCIO (Producción):** Clave de comercio para entornos en vivo.
   - **URL de Producción:** URL del entorno en vivo de Macro Click de Pagos (generalmente provista por la pasarela de pagos).
   - **Nombre del Comercio:** El nombre de tu comercio que aparecerá en el detalle de pago.
   - **URL para cancelar pedido:** Ingresá la URL a la que los clientes serán redirigidos si cancelan el pago.

3. Guardá los cambios y probá el plugin para verificar que todo funcione correctamente.

## Configuración en la Plataforma de Pagos de Banco Macro

Para integrar correctamente el plugin con la plataforma de pagos de Banco Macro, es necesario realizar una configuración adicional desde el panel de administración de Macro:

### Pasos para Configurar la URL de Notificación:

1. Ingresá al **Ambiente de Pruebas de Macro**:  
   [https://sandboxpp.macroclickpago.com.ar:8081/macroclickdepago/Account/Login](https://sandboxpp.macroclickpago.com.ar:8081/macroclickdepago/Account/Login)

   O al **Ambiente de Producción de Macro**:  
   [https://adminpp.macroclickpago.com.ar/macroclickdepago/Account/Login](https://adminpp.macroclickpago.com.ar/macroclickdepago/Account/Login)

2. Iniciá sesión con tus credenciales.

3. En el menú principal, seleccioná **Comercio**.

4. Agregá la URL de notificación correspondiente a tu dominio en el campo proporcionado:  
   `https://tudominio.com.ar/wc-api/ckdyd_macroclickpp`

   **Importante:** Reemplazá `tudominio.com.ar` con el dominio real de tu tienda.

5. Guardá los cambios.

Esta URL permitirá que la plataforma de pagos se comunique correctamente con tu sitio, notificando el estado de los pagos y actualizando los pedidos en WooCommerce.

## Uso del Plugin

Cuando un cliente seleccione **Macro Click de Pago** al finalizar la compra, será redirigido al formulario de pago de Macro Click de Pagos, donde podrá completar su transacción. El plugin maneja automáticamente la confirmación de pagos y actualiza el estado de los pedidos en WooCommerce.

### Notas Importantes

- **Modo Testeo:** Si activás el modo de desarrollo, los pagos no se procesarán de manera real. Utilizalo solo para realizar pruebas con tarjetas de prueba.
- **Ambiente de Producción:** Antes de activar en producción, asegurate de tener las claves correctas y de que tu sitio esté bajo HTTPS.

## Webhooks y Estado de los Pedidos

El plugin recibe notificaciones de Macro Click de Pagos mediante un webhook. Dependiendo del estado de la transacción, el pedido en WooCommerce se actualizará automáticamente a "Completado", "Procesando", "Cancelado" o "Pendiente".

Podés revisar los logs y detalles adicionales en **WooCommerce > Estado > Logs** si tenés algún problema.
## Soporte

Si encuentras algún problema o tienes alguna consulta, por favor revisa la sección de [Issues](https://github.com/lucianokatze/macroclickdepago-for-woocommerce/issues) en el repositorio.

## Changelog

- **1.0.0:** Versión inicial.

## Licencia

Este plugin está licenciado bajo la [GPL-2.0+](https://www.gnu.org/licenses/gpl-2.0.html).
Si necesitás ayuda adicional o encontrás algún problema, podés contactarnos en [https://ckdyd.net/contacto](https://ckdyd.net/contacto). También estamos disponibles para ofrecer soporte técnico y actualizaciones del plugin.
