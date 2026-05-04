<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Carrito - BeatDrop</title>
    <link rel="stylesheet" href="/css/global.css">
    <link rel="stylesheet" href="/css/carrito.css">
</head>
<body>
<header>
    <h1>BeatDrop</h1>
    <nav>
        <a href="/">Inicio</a>
        <a href="/albumes">Álbumes</a>
        <a href="/carrito" class="activo">Carrito</a>
        <span id="usuario-header"></span>
    </nav>
    <button id="loginBtn">Iniciar sesión / Crear cuenta</button>
</header>
<main>
    <section class="carrito-container">
        <h2 class="section-title">Tu carrito</h2>
        <div id="carrito-items"></div>
        <div id="carrito-resumen">
          <button id="btn-vaciar" onclick="vaciarCarrito()">Vaciar Carrito 🗑️</button>
          <div id="carrito-total">Total: $0.00</div>
          <button id="btn-finalizar" onclick="checkUserAndCheckout()">Finalizar Compra</button>
        </div>
        <div id="formulario-entrega" style="display: none;">
            <h3>Datos de Envío y Contacto</h3>
            <form id="envio-form">
                <input type="email" id="email-envio" placeholder="Correo electrónico de confirmación" required>
                <input type="text" id="nombre-envio" placeholder="Nombre completo" required>
                <input type="text" id="direccion-envio" placeholder="Dirección de entrega (Calle, No., Piso)" required>
                <div class="form-row">
                    <input type="text" id="ciudad-envio" placeholder="Ciudad" required>
                    <input type="text" id="cp-envio" placeholder="Código Postal" required>
                </div>
                <button type="submit" class="btn-pagar">Confirmar Pedido y Pagar</button>
            </form>
        </div>
    </section>
</main>
<div id="pedido-confirmado-modal" class="pedido-modal" aria-hidden="true">
  <div class="pedido-modal__backdrop" data-close-pedido></div>
  <section class="pedido-modal__panel" role="dialog" aria-modal="true" aria-labelledby="pedido-confirmado-title">
    <button type="button" class="pedido-modal__close" aria-label="Cerrar confirmacion" data-close-pedido>&times;</button>
    <div class="pedido-modal__success" aria-hidden="true">&check;</div>
    <p class="pedido-modal__eyebrow">Pedido confirmado</p>
    <h2 id="pedido-confirmado-title">Tu compra ya esta en marcha</h2>
    <p class="pedido-modal__copy">Hemos recibido tu pedido correctamente. Te enviaremos la confirmacion al correo indicado.</p>
    <div class="pedido-modal__summary">
      <div>
        <span>Entrega estimada</span>
        <strong id="pedido-fecha-estimada">Calculando...</strong>
      </div>
      <div>
        <span>Direccion de entrega</span>
        <strong id="pedido-direccion-confirmada">No disponible</strong>
      </div>
    </div>
    <button type="button" class="pedido-modal__action" data-close-pedido>Volver al inicio</button>
  </section>
</div>
<div id="loginModal" class="modal">
  <div class="modal-content">
    <span class="close">&times;</span>
    <h2 id="modalTitle">Iniciar sesión</h2>
    <form id="loginForm">
      <input type="text" id="registerName" placeholder="Tu nombre" style="display: none; margin-bottom: 10px;">
      <input type="email" id="loginEmail" placeholder="Correo electrónico" required>
      <input type="password" id="loginPassword" placeholder="Contraseña" required>
      <button type="submit" id="loginSubmit" class="btn-submit">Entrar</button>
    </form>
    <p id="switchText">¿No tienes cuenta? <span id="switchMode">Regístrate</span></p>
  </div>
</div>
<footer class="footer">
  <div class="footer-container">
    <div class="footer-info">
      <h3 class="logo"><span class="dot"></span> BeatDrop</h3>
      <p class="descripcion">Tu tienda online de confianza para vinilos, CDs y cassettes. Música de calidad para verdaderos melómanos.</p>
    </div>
    <div class="footer-contacto">
      <h4>Contacto</h4>
      <div class="contact-box">
        <p>📍 Calle Música 123, Madrid</p>
        <p>📞 +34 912 345 678</p>
        <p>✉️ info@beatdrop.es</p>
      </div>
    </div>
  </div>
  <div class="footer-bottom">© 2026 BeatDrop. Todos los derechos reservados.</div>
</footer>
<script src="/js/login-modal.js"></script>
<script src="/js/carrito.js"></script>
<script src=/js/menu.js></script>
</body>
</html>
