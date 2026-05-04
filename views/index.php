<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BeatDrop | Inicio</title>
    <link rel="stylesheet" href="/css/global.css">
    <link rel="stylesheet" href="/css/albumes.css">
    <link rel="stylesheet" href="/css/chatbot.css">
</head>
<body>
<header>
    <h1>BeatDrop</h1>
    <nav>
        <a href="/" class="activo">Inicio</a>
        <a href="/albumes">Álbumes</a>
        <a href="/carrito">Carrito</a>
        <span id="usuario-header"></span>
    </nav>
    <button id="loginBtn">Iniciar sesión / Crear cuenta</button>
</header>
<main>
    <section class="catalogo">
        <h2 class="section-title">Artistas destacados</h2>
        <section class="cards">
            <?php foreach ($destacados as $prod): ?>
                <article data-product-id="<?php echo (int)$prod['id_producto']; ?>">
                    <img src="/<?php echo ltrim(htmlspecialchars($prod['imagen_url']), '/'); ?>" alt="<?php echo htmlspecialchars($prod['nombre']); ?>" class="album-img" />
                    <h3><?php echo htmlspecialchars($prod['nombre']); ?></h3>
                    <p><?php echo htmlspecialchars($prod['genero']); ?> / <?php echo htmlspecialchars($prod['formato']); ?></p>
                    <p>Precio: $<?php echo number_format($prod['precio'], 2); ?></p>
                    <?php if ($prod['stock'] > 0): ?>
                        <button>Añadir al carrito</button>
                    <?php else: ?>
                        <button disabled style="background-color: #555; cursor: not-allowed; border-color: #555;">Agotado</button>
                    <?php endif; ?>
                </article>
            <?php endforeach; ?>
        </section>
    </section>
    <section class="novedades">
        <h2 class="section-title">Novedades</h2>
        <section class="cards">
            <?php foreach ($novedades as $prod): ?>
                <article data-product-id="<?php echo (int)$prod['id_producto']; ?>">
                    <img src="/<?php echo ltrim(htmlspecialchars($prod['imagen_url']), '/'); ?>" alt="<?php echo htmlspecialchars($prod['nombre']); ?>" class="album-img" />
                    <h3><?php echo htmlspecialchars($prod['nombre']); ?></h3>
                    <p><?php echo htmlspecialchars($prod['genero']); ?> / <?php echo htmlspecialchars($prod['formato']); ?></p>
                    <p>Precio: $<?php echo number_format($prod['precio'], 2); ?></p>
                    <?php if ($prod['stock'] > 0): ?>
                        <button>Añadir al carrito</button>
                    <?php else: ?>
                        <button disabled style="background-color: #555; cursor: not-allowed; border-color: #555;">Agotado</button>
                    <?php endif; ?>
                </article>
            <?php endforeach; ?>
        </section>
    </section>
    <section class="catalogo">
        <h2 class="section-title">Ofertas</h2>
        <section class="cards">
            <?php foreach ($ofertas as $prod): $precio_antiguo = $prod['precio'] * 1.20; ?>
                <article data-product-id="<?php echo (int)$prod['id_producto']; ?>">
                    <img src="/<?php echo ltrim(htmlspecialchars($prod['imagen_url']), '/'); ?>" alt="<?php echo htmlspecialchars($prod['nombre']); ?>" class="album-img" />
                    <h3><?php echo htmlspecialchars($prod['nombre']); ?></h3>
                    <p><?php echo htmlspecialchars($prod['genero']); ?> / <?php echo htmlspecialchars($prod['formato']); ?></p>
                    <p class="precio-oferta">Antes: $<?php echo number_format($precio_antiguo, 2); ?> → Ahora: $<?php echo number_format($prod['precio'], 2); ?></p>
                    <?php if ($prod['stock'] > 0): ?>
                        <button>Añadir al carrito</button>
                    <?php else: ?>
                        <button disabled style="background-color: #555; cursor: not-allowed; border-color: #555;">Agotado</button>
                    <?php endif; ?>
                </article>
            <?php endforeach; ?>
        </section>
    </section>
</main>
<div id="loginModal" class="modal">
    <div class="modal-content">
        <span class="close">&times;</span>
        <h2 id="modalTitle">Iniciar sesión</h2>
        <form id="loginForm">
            <input type="text" id="registerName" placeholder="Nombre" style="display: none; margin-bottom: 15px;">
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
                <p>Calle Música 123, Madrid</p>
                <p>+34 912 345 678</p>
                <p>info@beatdrop.es</p>
            </div>
        </div>
    </div>
    <div class="footer-bottom">© 2026 BeatDrop. Todos los derechos reservados.</div>
</footer>
<script src="/js/login-modal.js"></script>
<script src="/js/catalog-sync.js"></script>
<script src="/js/albumes-carrito.js"></script>
<script src="/js/carrito.js"></script>
<script src="/js/menu.js"></script>
<button id="chat-abrir" type="button">🤖 Lara</button>
<div id="chat-ventana">
    <div id="chat-cabecera">
        <div class="chat-brand">
            <span id="chat-avatar-header">🤖</span>
            <div id="chat-cabecera-texto">
                <span id="chat-cabecera-nombre">Lara</span>
                <span id="chat-cabecera-estado">Asistente de BeatDrop</span>
            </div>
        </div>
        <button id="chat-cerrar" type="button">&times;</button>
    </div>
    <div id="chat-mensajes"></div>
</div>
<script src="/js/chatbot.js"></script>
</body>
</html>
