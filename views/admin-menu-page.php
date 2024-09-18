<!-- views/admin-menu-page.php -->
<div class="wrap">
  <h1>Bienvenido a Macro Click de Pago</h1>
  <p>¡Gracias por usar Macro Click de Pago para WooCommerce! Aquí encontrarás información útil para configurar y
    utilizar el plugin.</p>

  <!-- Sección de Configuración del Plugin -->
  <section id="config">
    <h2>Configuración del Plugin</h2>
    <p>Una vez instalado y activado el plugin, sigue estos pasos para configurarlo:</p>
    <ol>
      <li>
        <strong>Acceder a la configuración:</strong>
        <ul>
          <li>Desde el menú de WordPress, navega hasta WooCommerce > Ajustes.</li>
          <li>En la configuración de la tienda, asegúrate de configurar el separador de miles con un punto, el separador
            decimal con una coma y el número de decimales en 2.</li>
          <li>Selecciona la pestaña Pagos y haz clic en Macro Click de Pago.</li>
        </ul>
      </li>
      <li>
        <strong>Configurar los campos:</strong>
        <ul>
          <li><strong>Activar/Desactivar:</strong> Marca la casilla para activar Macro Click de Pago como opción de pago
            en tu tienda.</li>
          <li><strong>Título:</strong> Es el texto que el cliente verá durante el proceso de pago (por defecto: "Macro
            Click de Pago").</li>
          <li><strong>Descripción:</strong> Un mensaje adicional que se muestra al cliente (puedes incluir beneficios de
            usar este método de pago).</li>
          <li><strong>Modo de Testeo:</strong> Activa esta opción para operar en modo prueba (sandbox). Es útil para
            hacer pruebas sin afectar las transacciones reales.</li>
          <li><strong>Claves de API para Testeo:</strong> Ingresa las claves proporcionadas por Macro Click de Pagos
            para pruebas.</li>
          <li><strong>Claves de API para Producción:</strong> Ingresa las claves para entornos en vivo.</li>
          <li><strong>Nombre del Comercio:</strong> El nombre de tu comercio que aparecerá en el detalle de pago.</li>
          <li><strong>URL para cancelar pedido:</strong> Ingresa la URL a la que los clientes serán redirigidos si
            cancelan el pago.</li>
        </ul>
      </li>
      <li>
        <strong>Configuración en la Plataforma de Pagos de Banco Macro:</strong>
        <p>Para integrar correctamente el plugin con la plataforma de pagos de Banco Macro, sigue estos pasos:</p>
        <ul>
          <li>Ingresa al enlace <a href="https://sandboxpp.macroclickpago.com.ar:8081/macroclickdepago/Account/Login"
              target="_blank" rel="noreferrer">Ambiente de Pruebas</a> o al <a
              href="https://adminpp.macroclickpago.com.ar/macroclickdepago/Account/Login" target="_blank"
              rel="noreferrer">Ambiente de Producción</a>, dependiendo de la etapa de la integración.</li>
          <li>Inicia sesión con tus credenciales.</li>
          <li>En el menú principal, selecciona "Comercio".</li>
          <li>Agrega la siguiente URL de notificación a tu plataforma de pagos:</li>
          <li>
            <label for="macroclick-url">URL de Notificación:</label>
            <input type="text" id="macroclick-url" value="<?php echo home_url('/wc-api/ckdyd_macroclickpp'); ?>"
              readonly style="width: 100%; padding: 5px;" />
            <button onclick="copyMacroclickUrl()" style="margin-top: 5px;">Copiar Enlace de Notifiación Para Macro Click
              de Pago</button>
          </li>
          <li>Guarda los cambios en la plataforma de pagos.</li>
        </ul>
      </li>

      <script>
        function copyMacroclickUrl() {
          var copyText = document.getElementById("macroclick-url");
          copyText.select();
          copyText.setSelectionRange(0, 99999); // Para dispositivos móviles
          document.execCommand("copy");
          alert("URL copiada: " + copyText.value);
        }
      </script>

    </ol>
    <p>Cuando un cliente seleccione Macro Click de Pago al finalizar la compra, será redirigido al formulario de pago de
      Macro Click de Pagos, donde podrá completar su transacción.</p>
    <p><strong>Notas Importantes:</strong></p>
    <ul>
      <li><strong>Modo Testeo:</strong> Si activas el modo de desarrollo, los pagos no se procesarán de manera real.
        Utilízalo solo para realizar pruebas con tarjetas de prueba.</li>
      <li><strong>Ambiente de Producción:</strong> Asegúrate de tener las claves correctas y que tu sitio esté bajo
        HTTPS antes de activar en producción.</li>
    </ul>
  </section>

  <!-- Sección de Soporte -->
  <section id="support">
    <h2>Soporte</h2>
    <p>Si necesitas ayuda adicional o encuentras algún problema, puedes contactarme en <a
        href="https://ckdyd.net/contacto" target="_blank" rel="noreferrer">mi sitio web</a>. También estoy
      disponibles para ofrecer soporte técnico y actualizaciones del plugin (Solo con suscripción).</p>
  </section>

  <!-- Sección de Información Personal -->
  <section id="about">
    <h2>Acerca de Luciano Katze</h2>
    <p>Desarrollador backend apasionado por crear arquitecturas eficientes y escalables, con interés en proyectos de
      logística y contabilidad. Me encanta explorar nuevas tecnologías y compartir conocimientos.</p>
  </section>

  <!-- Sección de Conexión -->
  <section id="connect">
    <h2>Conéctate Conmigo</h2>
    <p>Conóceme mejor a través de los siguientes enlaces:</p>
    <ul>
      <li><a href="https://ckdyd.net/sh" target="_blank" rel="noreferrer">Perfil Completo</a></li>
    </ul>
    <div class="social-links">
      <a href="https://twitter.com/lucianokatze" target="_blank" rel="noreferrer">
        <img src="https://img.shields.io/badge/Twitter-%231DA1F2.svg?style=for-the-badge&logo=Twitter&logoColor=white"
          alt="Twitter">
      </a>
      <a href="https://linkedin.com/in/lucianokatze" target="_blank" rel="noreferrer">
        <img src="https://img.shields.io/badge/LinkedIn-%230077B5.svg?style=for-the-badge&logo=linkedin&logoColor=white"
          alt="LinkedIn">
      </a>
      <a href="https://fb.com/lucianokatze" target="_blank" rel="noreferrer">
        <img src="https://img.shields.io/badge/Facebook-%231877F2.svg?style=for-the-badge&logo=Facebook&logoColor=white"
          alt="Facebook">
      </a>
      <a href="https://instagram.com/lucianokatze" target="_blank" rel="noreferrer">
        <img
          src="https://img.shields.io/badge/Instagram-%23E4405F.svg?style=for-the-badge&logo=Instagram&logoColor=white"
          alt="Instagram">
      </a>
      <a href="https://ckdyd.net/feed/" target="_blank" rel="noreferrer">
        <img src="https://img.shields.io/badge/RSS-%23FFA500.svg?style=for-the-badge&logo=RSS&logoColor=white"
          alt="RSS">
      </a>
    </div>
  </section>

  <!-- Sección de Tecnologías -->
  <section id="technologies">
    <h2>Tecnologías y Herramientas</h2>
    <p>Aquí tienes algunas de las tecnologías y herramientas que utilizo:</p>
    <div class="tech-icons">
      <a href="https://aws.amazon.com" target="_blank" rel="noreferrer">
        <img
          src="https://raw.githubusercontent.com/devicons/devicon/master/icons/amazonwebservices/amazonwebservices-original-wordmark.svg"
          alt="aws" width="40" height="40" />
      </a>
      <a href="https://azure.microsoft.com/en-in/" target="_blank" rel="noreferrer">
        <img src="https://www.vectorlogo.zone/logos/microsoft_azure/microsoft_azure-icon.svg" alt="azure" width="40"
          height="40" />
      </a>
      <a href="https://www.gnu.org/software/bash/" target="_blank" rel="noreferrer">
        <img src="https://www.vectorlogo.zone/logos/gnu_bash/gnu_bash-icon.svg" alt="bash" width="40" height="40" />
      </a>
      <a href="https://www.blender.org/" target="_blank" rel="noreferrer">
        <img src="https://download.blender.org/branding/community/blender_community_badge_white.svg" alt="blender"
          width="40" height="40" />
      </a>
      <a href="https://getbootstrap.com" target="_blank" rel="noreferrer">
        <img
          src="https://raw.githubusercontent.com/devicons/devicon/master/icons/bootstrap/bootstrap-plain-wordmark.svg"
          alt="bootstrap" width="40" height="40" />
      </a>
      <a href="https://www.cprogramming.com/" target="_blank" rel="noreferrer">
        <img src="https://raw.githubusercontent.com/devicons/devicon/master/icons/c/c-original.svg" alt="c" width="40"
          height="40" />
      </a>
      <a href="https://codeigniter.com" target="_blank" rel="noreferrer">
        <img src="https://cdn.worldvectorlogo.com/logos/codeigniter.svg" alt="codeigniter" width="40" height="40" />
      </a>
      <a href="https://www.w3schools.com/cpp/" target="_blank" rel="noreferrer">
        <img src="https://raw.githubusercontent.com/devicons/devicon/master/icons/cplusplus/cplusplus-original.svg"
          alt="cplusplus" width="40" height="40" />
      </a>
      <a href="https://www.w3schools.com/cs/" target="_blank" rel="noreferrer">
        <img src="https://raw.githubusercontent.com/devicons/devicon/master/icons/csharp/csharp-original.svg"
          alt="csharp" width="40" height="40" />
      </a>
      <a href="https://www.w3schools.com/css/" target="_blank" rel="noreferrer">
        <img src="https://raw.githubusercontent.com/devicons/devicon/master/icons/css3/css3-original-wordmark.svg"
          alt="css3" width="40" height="40" />
      </a>
      <a href="https://www.docker.com/" target="_blank" rel="noreferrer">
        <img src="https://raw.githubusercontent.com/devicons/devicon/master/icons/docker/docker-original-wordmark.svg"
          alt="docker" width="40" height="40" />
      </a>
      <a href="https://dotnet.microsoft.com/" target="_blank" rel="noreferrer">
        <img src="https://raw.githubusercontent.com/devicons/devicon/master/icons/dot-net/dot-net-original-wordmark.svg"
          alt="dotnet" width="40" height="40" />
      </a>
      <a href="https://expressjs.com" target="_blank" rel="noreferrer">
        <img src="https://raw.githubusercontent.com/devicons/devicon/master/icons/express/express-original-wordmark.svg"
          alt="express" width="40" height="40" />
      </a>
      <a href="https://www.figma.com/" target="_blank" rel="noreferrer">
        <img src="https://www.vectorlogo.zone/logos/figma/figma-icon.svg" alt="figma" width="40" height="40" />
      </a>
      <a href="https://firebase.google.com/" target="_blank" rel="noreferrer">
        <img src="https://www.vectorlogo.zone/logos/firebase/firebase-icon.svg" alt="firebase" width="40" height="40" />
      </a>
      <a href="https://flask.palletsprojects.com/" target="_blank" rel="noreferrer">
        <img src="https://www.vectorlogo.zone/logos/pocoo_flask/pocoo_flask-icon.svg" alt="flask" width="40"
          height="40" />
      </a>
      <a href="https://cloud.google.com" target="_blank" rel="noreferrer">
        <img src="https://www.vectorlogo.zone/logos/google_cloud/google_cloud-icon.svg" alt="gcp" width="40"
          height="40" />
      </a>
      <a href="https://git-scm.com/" target="_blank" rel="noreferrer">
        <img src="https://www.vectorlogo.zone/logos/git-scm/git-scm-icon.svg" alt="git" width="40" height="40" />
      </a>
      <a href="https://graphql.org" target="_blank" rel="noreferrer">
        <img src="https://www.vectorlogo.zone/logos/graphql/graphql-icon.svg" alt="graphql" width="40" height="40" />
      </a>
      <a href="https://www.w3.org/html/" target="_blank" rel="noreferrer">
        <img src="https://raw.githubusercontent.com/devicons/devicon/master/icons/html5/html5-original-wordmark.svg"
          alt="html5" width="40" height="40" />
      </a>
      <a href="https://www.adobe.com/in/products/illustrator.html" target="_blank" rel="noreferrer">
        <img src="https://www.vectorlogo.zone/logos/adobe_illustrator/adobe_illustrator-icon.svg" alt="illustrator"
          width="40" height="40" />
      </a>
      <a href="https://www.java.com" target="_blank" rel="noreferrer">
        <img src="https://raw.githubusercontent.com/devicons/devicon/master/icons/java/java-original.svg" alt="java"
          width="40" height="40" />
      </a>
      <a href="https://developer.mozilla.org/en-US/docs/Web/JavaScript" target="_blank" rel="noreferrer">
        <img src="https://raw.githubusercontent.com/devicons/devicon/master/icons/javascript/javascript-original.svg"
          alt="javascript" width="40" height="40" />
      </a>
      <a href="https://laravel.com/" target="_blank" rel="noreferrer">
        <img src="https://raw.githubusercontent.com/devicons/devicon/master/icons/laravel/laravel-original.svg"
          alt="laravel" width="40" height="40" />
      </a>
      <a href="https://www.linux.org/" target="_blank" rel="noreferrer">
        <img src="https://raw.githubusercontent.com/devicons/devicon/master/icons/linux/linux-original.svg" alt="linux"
          width="40" height="40" />
      </a>
      <a href="https://mariadb.org/" target="_blank" rel="noreferrer">
        <img src="https://www.vectorlogo.zone/logos/mariadb/mariadb-icon.svg" alt="mariadb" width="40" height="40" />
      </a>
      <a href="https://www.mongodb.com/" target="_blank" rel="noreferrer">
        <img src="https://raw.githubusercontent.com/devicons/devicon/master/icons/mongodb/mongodb-original-wordmark.svg"
          alt="mongodb" width="40" height="40" />
      </a>
      <a href="https://www.microsoft.com/en-us/sql-server" target="_blank" rel="noreferrer">
        <img src="https://www.svgrepo.com/show/303229/microsoft-sql-server-logo.svg" alt="mssql" width="40"
          height="40" />
      </a>
      <a href="https://www.mysql.com/" target="_blank" rel="noreferrer">
        <img src="https://raw.githubusercontent.com/devicons/devicon/master/icons/mysql/mysql-original-wordmark.svg"
          alt="mysql" width="40" height="40" />
      </a>
      <a href="https://nextjs.org/" target="_blank" rel="noreferrer">
        <img src="https://cdn.worldvectorlogo.com/logos/nextjs-2.svg" alt="nextjs" width="40" height="40" />
      </a>
      <a href="https://www.nginx.com" target="_blank" rel="noreferrer">
        <img src="https://raw.githubusercontent.com/devicons/devicon/master/icons/nginx/nginx-original.svg" alt="nginx"
          width="40" height="40" />
      </a>
      <a href="https://nodejs.org" target="_blank" rel="noreferrer">
        <img src="https://raw.githubusercontent.com/devicons/devicon/master/icons/nodejs/nodejs-original-wordmark.svg"
          alt="nodejs" width="40" height="40" />
      </a>
      <a href="https://www.photoshop.com/en" target="_blank" rel="noreferrer">
        <img src="https://raw.githubusercontent.com/devicons/devicon/master/icons/photoshop/photoshop-line.svg"
          alt="photoshop" width="40" height="40" />
      </a>
      <a href="https://www.php.net" target="_blank" rel="noreferrer">
        <img src="https://raw.githubusercontent.com/devicons/devicon/master/icons/php/php-original.svg" alt="php"
          width="40" height="40" />
      </a>
      <a href="https://www.postgresql.org" target="_blank" rel="noreferrer">
        <img
          src="https://raw.githubusercontent.com/devicons/devicon/master/icons/postgresql/postgresql-original-wordmark.svg"
          alt="postgresql" width="40" height="40" />
      </a>
      <a href="https://postman.com" target="_blank" rel="noreferrer">
        <img src="https://www.vectorlogo.zone/logos/getpostman/getpostman-icon.svg" alt="postman" width="40"
          height="40" />
      </a>
      <a href="https://github.com/puppeteer/puppeteer" target="_blank" rel="noreferrer">
        <img src="https://www.vectorlogo.zone/logos/pptrdev/pptrdev-official.svg" alt="puppeteer" width="40"
          height="40" />
      </a>
      <a href="https://www.python.org" target="_blank" rel="noreferrer">
        <img src="https://raw.githubusercontent.com/devicons/devicon/master/icons/python/python-original.svg"
          alt="python" width="40" height="40" />
      </a>
      <a href="https://reactjs.org/" target="_blank" rel="noreferrer">
        <img src="https://raw.githubusercontent.com/devicons/devicon/master/icons/react/react-original-wordmark.svg"
          alt="react" width="40" height="40" />
      </a>
      <a href="https://www.selenium.dev" target="_blank" rel="noreferrer">
        <img
          src="https://raw.githubusercontent.com/detain/svg-logos/780f25886640cef088af994181646db2f6b1a3f8/svg/selenium-logo.svg"
          alt="selenium" width="40" height="40" />
      </a>
      <a href="https://www.sqlite.org/" target="_blank" rel="noreferrer">
        <img src="https://www.vectorlogo.zone/logos/sqlite/sqlite-icon.svg" alt="sqlite" width="40" height="40" />
      </a>
      <a href="https://symfony.com" target="_blank" rel="noreferrer">
        <img src="https://symfony.com/logos/symfony_black_03.svg" alt="symfony" width="40" height="40" />
      </a>
    </div>
  </section>
  <section>
  <p>Si querés apoyar el plugin y futuros desarrollos, podés hacerlo invitándome un cafecito:</p>
<a href="https://cafecito.app/lucianokatze" target="_blank" style="display: inline-block; background-color: #007bff; color: white; padding: 10px 20px; text-align: center; text-decoration: none; border-radius: 5px; font-size: 16px;">
  ☕ Donar al proyecto
</a>
  </section>
</div>

<style>
  .wrap {
    padding: 20px;
    background: #f9f9f9;
    border: 1px solid #ddd;
    border-radius: 8px;
  }

  h1 {
    font-size: 24px;
    margin-bottom: 20px;
  }

  h2 {
    font-size: 20px;
    margin-top: 20px;
    border-bottom: 2px solid #ddd;
    padding-bottom: 5px;
  }

  p {
    font-size: 16px;
    line-height: 1.5;
  }

  ul,
  ol {
    margin-left: 20px;
  }

  .social-links a {
    margin-right: 10px;
    display: inline-block;
  }

  .social-links img {
    vertical-align: middle;
  }

  .tech-icons a {
    margin-right: 10px;
    display: inline-block;
  }

  .tech-icons img {
    vertical-align: middle;
  }
</style>