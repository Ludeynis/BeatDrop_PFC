<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Álbumes - BeatDrop</title>
  <link rel="stylesheet" href="/css/global.css">
  <link rel="stylesheet" href="/css/albumes.css">
  <link rel="stylesheet" href="/css/chatbot.css">
</head>

<body>
  <header>
    <h1>BeatDrop</h1>
    <nav>
      <a href="/">Inicio</a>
      <a href="/albumes" class="activo">Álbumes</a>
      <a href="/carrito">Carrito</a>
      <span id="usuario-header"></span>
    </nav>
    <button id="loginBtn">Iniciar sesión / Crear cuenta</button>
  </header>
  <section class="catalogo-container">
    <section class="catalogo">
      <h2 class="catalogo-title"><span aria-hidden="true"></span>Catalogo completo</h2>
      <aside class="filtros" aria-label="Filtros del catalogo">
        <div class="filtro-grupo">
          <h4>Género</h4>
          <div class="botones-filtro">
            <button class="active">Todos</button>
            <button>Rock</button>
            <button>Jazz</button>
            <button>Pop</button>
            <button>Electrónica</button>
            <button>Hip-Hop</button>
            <button>R&B</button>
            <button>Blues</button>
            <button>Reggae</button>
          </div>
        </div>
        <div class="filtro">
          <h4>Formato</h4>
          <div class="botones-filtro">
            <button class="active">Todos</button>
            <button>Vinilo</button>
            <button>CD</button>
            <button>Cassette</button>
          </div>
        </div>
      </aside>
      <p class="productos-mostrados">Mostrando <?php echo count($productos_db); ?> productos</p>
      <section class="cards">
        <?php if (count($productos_db) > 0): ?>
          <?php foreach ($productos_db as $producto): ?>
            <article data-product-id="<?php echo (int)$producto['id_producto']; ?>" data-genero="<?php echo htmlspecialchars($producto['genero'] ?? ''); ?>" data-formato="<?php echo htmlspecialchars($producto['formato'] ?? ''); ?>">
              <img src="<?php echo !empty($producto['imagen_url']) ? '/' . ltrim(htmlspecialchars($producto['imagen_url']), '/') : '/img/future-album.jpg'; ?>" alt="<?php echo htmlspecialchars($producto['nombre']); ?>" class="album-img" />
              <h3><?php echo htmlspecialchars($producto['nombre']); ?></h3>
              <p><?php echo htmlspecialchars($producto['genero'] ?? 'Varios'); ?> / <?php echo htmlspecialchars($producto['formato'] ?? 'Físico'); ?></p>
              <p>Precio: $<?php echo number_format($producto['precio'], 2); ?></p>
              <?php if ($producto['stock'] > 0): ?>
                <button>Añadir al carrito</button>
              <?php else: ?>
                <button disabled style="background-color: #555; cursor: not-allowed; border-color: #555;">Agotado</button>
              <?php endif; ?>
            </article>
          <?php endforeach; ?>
        <?php else: ?>
          <p>No hay productos disponibles en este momento. ¡El catálogo está vacío!</p>
        <?php endif; ?>
      </section>
    </section>
  </section>
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
  <script src="/js/login-modal.js"></script>
  <script src="/js/albumes-carrito.js"></script>
  <script src="/js/filtros.js"></script>
  <button id="chat-abrir" type="button" aria-controls="chat-ventana" aria-expanded="false">
    🤖 Lara
  </button>
  <div id="chat-ventana" aria-hidden="true">
    <div id="chat-cabecera">
      <div class="chat-brand">
        <span id="chat-avatar-header">🤖</span>
        <div id="chat-cabecera-texto">
          <span id="chat-cabecera-nombre">Lara</span>
          <span id="chat-cabecera-estado">Asistente de descubrimiento</span>
        </div>
      </div>
      <button id="chat-cerrar" type="button" aria-label="Cerrar chat">&times;</button>
    </div>
    <div id="chat-mensajes"></div>
  </div>
  <script src="/js/chatbot.js"></script>
  <script src="js/menu.js"></script>
</body>

</html>
