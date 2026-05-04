<?php
global $pdo;

$productos = $pdo->query(
    'SELECT p.*, c.nombre_categoria
     FROM productos p
     LEFT JOIN categorias c ON c.id_categoria = p.id_categoria
     ORDER BY p.id_producto DESC'
)->fetchAll();

$pedidos = $pdo->query(
    'SELECT p.*, u.nombre AS usuario_nombre, u.email AS usuario_email
     FROM pedidos p
     LEFT JOIN usuarios u ON u.id_usuario = p.id_usuario
     ORDER BY p.fecha_pedido DESC
     LIMIT 50'
)->fetchAll();

$totalPedidos = (int)$pdo->query('SELECT COUNT(*) FROM pedidos')->fetchColumn();

$usuarios = $pdo->query(
    'SELECT id_usuario, nombre, email, rol
     FROM usuarios
     ORDER BY id_usuario DESC
     LIMIT 100'
)->fetchAll();

$totalProductos = count($productos);
$totalUsuarios = count($usuarios);
$stockBajo = 0;
foreach ($productos as $p) {
    if ((int)($p['stock'] ?? 0) <= 3) {
        $stockBajo++;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?php echo htmlspecialchars(csrf_token()); ?>">
    <title>Admin - BeatDrop</title>
    <link rel="stylesheet" href="/css/admin.css">
    <script src="/js/admin.js" defer></script>
</head>
<body class="admin" data-admin-root>
<div class="admin-shell">
    <aside class="admin-sidebar">
        <div class="admin-brand" role="banner">
            <div class="title">BeatDrop</div>
            <div class="subtitle">Admin</div>
        </div>

        <nav class="admin-nav" aria-label="Navegacion del panel">
            <a href="#dashboard" data-view-link="dashboard" class="is-active">
                <span>Dashboard</span>
            </a>
            <a href="#productos" data-view-link="productos">
                <span>Productos</span>
                <span class="nav-count" data-product-count><?php echo (int)$totalProductos; ?></span>
            </a>
            <a href="#pedidos" data-view-link="pedidos">
                <span>Pedidos</span>
                <span class="nav-count" data-order-count><?php echo (int)$totalPedidos; ?></span>
            </a>
            <a href="#usuarios" data-view-link="usuarios">
                <span>Usuarios</span>
                <span class="nav-count"><?php echo (int)$totalUsuarios; ?></span>
            </a>
        </nav>

        <div class="admin-sidebar-footer">
            <a class="btn btn-danger btn-full" href="/auth/logout">Cerrar sesion</a>
        </div>
    </aside>

    <div class="admin-main">
        <header class="admin-topbar">
            <h1 class="admin-logo"><a href="/admin" aria-label="BeatDrop Admin">BeatDrop</a></h1>
        </header>

        <main class="admin-content">
            <section class="view is-active" id="view-dashboard" data-view-section="dashboard" aria-label="Dashboard">
                <section class="admin-hero">
                    <h1>Dashboard</h1>
                    <p>Gestiona el catalogo musical con cambios rapidos y sin salir del panel.</p>
                </section>

                <section class="admin-grid" aria-label="Resumen">
                    <article class="card">
                        <h2>Productos</h2>
                        <p><strong data-product-count><?php echo (int)$totalProductos; ?></strong> en catalogo. <span class="text-muted"><span data-low-stock-count><?php echo (int)$stockBajo; ?></span> con stock bajo.</span></p>
                        <div class="card-actions">
                            <button class="btn btn-primary" type="button" data-action="go-view" data-target="productos">Gestionar</button>
                        </div>
                    </article>

                    <article class="card">
                        <h2>Pedidos</h2>
                        <p><strong data-order-count><?php echo (int)$totalPedidos; ?></strong> registrados. Cambia estados rapido para mantener operaciones al dia.</p>
                        <div class="card-actions">
                            <button class="btn btn-primary" type="button" data-action="go-view" data-target="pedidos">Ver pedidos</button>
                        </div>
                    </article>

                    <article class="card">
                        <h2>Usuarios</h2>
                        <p><strong><?php echo (int)$totalUsuarios; ?></strong> recientes. Identifica admins y clientes de un vistazo.</p>
                        <div class="card-actions">
                            <button class="btn btn-primary" type="button" data-action="go-view" data-target="usuarios">Ver usuarios</button>
                        </div>
                    </article>
                </section>
            </section>

            <section class="view" id="view-productos" data-view-section="productos" aria-label="Productos">
                <div class="view-header">
                    <div>
                        <h1>Productos</h1>
                        <p class="text-muted">Puedes crear, editar, borrar y ajustar stock sin salir del dashboard.</p>
                    </div>
                    <div class="view-actions">
                        <button class="btn btn-primary" type="button" data-action="open-product-modal" data-mode="create">Anadir producto</button>
                    </div>
                </div>

                <div class="table-wrap">
                    <table class="table">
                        <thead>
                        <tr>
                            <th class="sortable asc" data-sort="id">ID</th>
                            <th class="sortable" data-sort="nombre">Producto</th>
                            <th class="sortable" data-sort="categoria">Categoria</th>
                            <th class="sortable" data-sort="precio">Precio</th>
                            <th class="sortable" data-sort="stock">Stock</th>
                            <th class="th-actions">Acciones</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($productos as $p): ?>
                            <?php
                            $payload = [
                                'id_producto' => (int)$p['id_producto'],
                                'id_categoria' => $p['id_categoria'] !== null ? (int)$p['id_categoria'] : null,
                                'nombre' => (string)($p['nombre'] ?? ''),
                                'descripcion' => $p['descripcion'],
                                'precio' => (float)($p['precio'] ?? 0),
                                'stock' => (int)($p['stock'] ?? 0),
                                'imagen_url' => (string)($p['imagen_url'] ?? ''),
                                'genero' => (string)($p['genero'] ?? ''),
                                'formato' => (string)($p['formato'] ?? ''),
                            ];
                            $payloadJson = htmlspecialchars(json_encode($payload, JSON_UNESCAPED_UNICODE), ENT_QUOTES, 'UTF-8');
                            $stock = (int)($p['stock'] ?? 0);
                            $imagen = ltrim((string)($p['imagen_url'] ?? ''), '/');
                            ?>
                            <tr class="row" data-row data-product-id="<?php echo (int)$p['id_producto']; ?>">
                                <td class="cell-muted">#<?php echo (int)$p['id_producto']; ?></td>
                                <td class="cell-strong">
                                    <div class="product-cell">
                                        <?php if ($imagen !== ''): ?>
                                            <img src="/<?php echo htmlspecialchars($imagen); ?>" alt="<?php echo htmlspecialchars((string)$p['nombre']); ?>" class="product-thumb">
                                        <?php else: ?>
                                            <div class="product-thumb product-thumb-placeholder">Sin foto</div>
                                        <?php endif; ?>
                                        <span><?php echo htmlspecialchars((string)$p['nombre']); ?></span>
                                    </div>
                                </td>
                                <td class="cell-muted"><?php echo htmlspecialchars((string)($p['nombre_categoria'] ?? '')); ?></td>
                                <td class="cell-strong"><?php echo number_format((float)$p['precio'], 2); ?> EUR</td>
                                <td>
                                    <span class="stock-value <?php echo $stock <= 0 ? 'stock-out' : ($stock <= 3 ? 'stock-low' : 'stock-ok'); ?>">
                                        <?php echo $stock; ?>
                                    </span>
                                    <button type="button" class="stock-btn" data-action="stock-change" data-delta="-1" title="Reducir stock">-</button>
                                    <button type="button" class="stock-btn" data-action="stock-change" data-delta="1" title="Aumentar stock">+</button>
                                </td>
                                <td class="td-actions">
                                    <button type="button" class="btn btn-ghost btn-sm" data-action="edit-product" data-product="<?php echo $payloadJson; ?>" title="Editar producto">
                                        Editar
                                    </button>
                                    <button type="button" class="btn btn-danger btn-sm" data-action="delete-product" data-product-id="<?php echo (int)$p['id_producto']; ?>" title="Eliminar producto">
                                        Eliminar
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </section>

            <section class="view" id="view-pedidos" data-view-section="pedidos" aria-label="Pedidos">
                <div class="view-header">
                    <div>
                        <h1>Pedidos</h1>
                        <p class="text-muted">Estados con color para escaneo rapido.</p>
                    </div>
                </div>

                <div class="table-wrap">
                    <table class="table orders-table">
                        <thead>
                        <tr>
                            <th>ID</th>
                            <th>Usuario</th>
                            <th>Fecha</th>
                            <th>Total</th>
                            <th>Estado</th>
                            <th class="th-actions">Detalle</th>
                        </tr>
                        </thead>
                        <tbody data-orders-body>
                        <?php foreach ($pedidos as $p): ?>
                            <?php $estado = (string)($p['estado'] ?? 'pendiente'); ?>
                            <tr>
                                <td class="cell-muted" data-label="ID">#<?php echo (int)$p['id_pedido']; ?></td>
                                <td class="cell-strong" data-label="Usuario">
                                    <div class="order-user-cell">
                                        <strong><?php echo htmlspecialchars((string)($p['usuario_nombre'] ?? '')); ?></strong>
                                        <span><?php echo htmlspecialchars((string)($p['usuario_email'] ?? '')); ?></span>
                                    </div>
                                </td>
                                <td class="cell-muted" data-label="Fecha"><?php echo htmlspecialchars((string)($p['fecha_pedido'] ?? '')); ?></td>
                                <td class="cell-strong" data-label="Total"><span class="order-total-cell"><?php echo number_format((float)($p['total'] ?? 0), 2); ?> EUR</span></td>
                                <td data-label="Estado">
                                    <select class="status-select" data-status value="<?php echo htmlspecialchars($estado); ?>">
                                        <option value="pendiente" <?php echo $estado === 'pendiente' ? 'selected' : ''; ?>>Pendiente</option>
                                        <option value="enviado" <?php echo $estado === 'enviado' ? 'selected' : ''; ?>>Enviado</option>
                                        <option value="entregado" <?php echo $estado === 'entregado' ? 'selected' : ''; ?>>Entregado</option>
                                        <option value="simulado" <?php echo $estado === 'simulado' ? 'selected' : ''; ?>>Cancelado</option>
                                    </select>
                                </td>
                                <td class="td-actions" data-label="Detalle">
                                    <button type="button" class="btn btn-ghost btn-sm" data-action="view-order" data-order-id="<?php echo (int)$p['id_pedido']; ?>">Ver detalle</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </section>

            <section class="view" id="view-usuarios" data-view-section="usuarios" aria-label="Usuarios">
                <div class="view-header">
                    <div>
                        <h1>Usuarios</h1>
                        <p class="text-muted">Tabla limpia con rol bien visible.</p>
                    </div>
                </div>

                <div class="table-wrap">
                    <table class="table">
                        <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nombre</th>
                            <th>Email</th>
                            <th>Rol</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($usuarios as $u): ?>
                            <?php $rol = (string)($u['rol'] ?? 'cliente'); ?>
                            <tr>
                                <td class="cell-muted">#<?php echo (int)$u['id_usuario']; ?></td>
                                <td class="cell-strong"><?php echo htmlspecialchars((string)($u['nombre'] ?? '')); ?></td>
                                <td class="cell-muted"><?php echo htmlspecialchars((string)($u['email'] ?? '')); ?></td>
                                <td>
                                    <span class="pill <?php echo $rol === 'admin' ? 'pill-admin' : 'pill-user'; ?>">
                                        <?php echo htmlspecialchars($rol); ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </section>
        </main>
    </div>
</div>

<div class="modal-backdrop" id="orderModal" aria-hidden="true">
    <div class="modal order-modal" role="dialog" aria-modal="true" aria-labelledby="orderModalTitle">
        <div class="modal-header">
            <div>
                <h2 class="modal-title" id="orderModalTitle">Detalle del pedido</h2>
                <p class="order-modal-subtitle" id="orderModalSubtitle">Cargando pedido...</p>
            </div>
            <button type="button" class="btn btn-ghost btn-icon" data-action="close-order-modal" aria-label="Cerrar">
                X
            </button>
        </div>
        <div class="modal-body">
            <div id="orderDetailContent" class="order-detail-content">
                <p class="text-muted">Selecciona un pedido para ver sus detalles.</p>
            </div>
        </div>
    </div>
</div>

<div class="modal-backdrop" id="productModal" aria-hidden="true">
    <div class="modal" role="dialog" aria-modal="true" aria-labelledby="productModalTitle">
        <div class="modal-header">
            <h2 class="modal-title" id="productModalTitle">Anadir producto</h2>
            <button type="button" class="btn btn-ghost btn-icon" data-action="close-product-modal" aria-label="Cerrar">
                X
            </button>
        </div>
        <div class="modal-body">
            <form id="productForm" method="post" action="/admin/productos/crear" enctype="multipart/form-data">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(csrf_token()); ?>">
                <input type="hidden" name="id_categoria" id="fieldIdCategoria" value="1">
                <input type="hidden" name="imagen_url_actual" id="fieldImagenActual" value="">

                <div class="form-grid">
                    <div class="form-field col-8">
                        <label for="fieldNombre">Titulo</label>
                        <input id="fieldNombre" name="nombre" required placeholder="Ej: Future - I Never Liked You">
                    </div>

                    <div class="form-field col-4"></div>

                    <div class="form-field col-12">
                        <label for="fieldImagenFile">Portada del album (JPG)</label>
                        <input id="fieldImagenFile" name="imagen_archivo" type="file" accept=".jpg,.jpeg,image/jpeg">
                        <div class="hint">Solo se permiten archivos JPG o JPEG.</div>
                        <div class="image-preview is-hidden" id="fieldImagenPreviewWrap">
                            <img id="fieldImagenPreview" src="" alt="Vista previa de la portada">
                            <p id="fieldImagenPreviewText" class="hint"></p>
                        </div>
                    </div>

                    <div class="form-field col-12">
                        <label for="fieldDescripcion">Descripcion</label>
                        <textarea id="fieldDescripcion" name="descripcion" placeholder="Descripcion corta del producto..."></textarea>
                    </div>

                    <div class="form-field col-6">
                        <label for="fieldPrecio">Precio (EUR)</label>
                        <input id="fieldPrecio" name="precio" type="number" step="0.01" min="0" required value="0">
                    </div>

                    <div class="form-field col-6">
                        <label for="fieldStock">Stock</label>
                        <div class="stepper">
                            <button type="button" class="stepper-btn" data-step="-1" aria-label="Disminuir stock">-</button>
                            <input id="fieldStock" name="stock" type="number" step="1" min="0" value="0">
                            <button type="button" class="stepper-btn" data-step="1" aria-label="Aumentar stock">+</button>
                        </div>
                    </div>

                    <div class="form-field col-6">
                        <label for="fieldGenero">Genero</label>
                        <select id="fieldGenero" name="genero">
                            <option value="">-</option>
                            <option value="Hip-Hop">Hip Hop</option>
                            <option value="Pop">Pop</option>
                            <option value="R&B">R&B</option>
                            <option value="Rock">Rock</option>
                            <option value="Jazz">Jazz</option>
                            <option value="Reggae">Reggae</option>
                            <option value="Blues">Blues</option>
                            <option value="Electrónica">Electrónica</option>
                        </select>
                    </div>

                    <div class="form-field col-6">
                        <label for="fieldFormato">Formato</label>
                        <select id="fieldFormato" name="formato">
                            <option value="">-</option>
                            <option value="Vinilo">Vinilo</option>
                            <option value="Cassette">Cassette</option>
                            <option value="Digital">Digital</option>
                        </select>
                    </div>
                </div>
            </form>

            <div class="modal-actions">
                <div class="modal-actions-left">
                    <form id="productDeleteForm" method="post" action="#" class="inline is-hidden" data-delete-form>
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(csrf_token()); ?>">
                        <button type="submit" class="btn btn-danger">Eliminar producto</button>
                    </form>
                </div>
                <div class="modal-actions-right">
                    <button type="button" class="btn btn-ghost" data-action="close-product-modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary" id="productSubmit" form="productForm">Guardar</button>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
