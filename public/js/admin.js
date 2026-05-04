(() => {
    const root = document.querySelector('[data-admin-root]');
    if (!root) return;

    const logoutLink = document.querySelector('a[href="/auth/logout"]');
    logoutLink?.addEventListener('click', () => {
        sessionStorage.removeItem('usuarioActivo');
    });

    const syncChannel = typeof BroadcastChannel !== 'undefined' ? new BroadcastChannel('beatdrop-catalog') : null;
    const viewSections = Array.from(document.querySelectorAll('[data-view-section]'));
    const viewLinks = Array.from(document.querySelectorAll('[data-view-link]'));

    const modal = document.getElementById('productModal');
    const orderModal = document.getElementById('orderModal');
    const orderModalTitle = document.getElementById('orderModalTitle');
    const orderModalSubtitle = document.getElementById('orderModalSubtitle');
    const orderDetailContent = document.getElementById('orderDetailContent');
    const form = document.getElementById('productForm');
    const modalTitle = document.getElementById('productModalTitle');
    const submitBtn = document.getElementById('productSubmit');
    const deleteForm = document.getElementById('productDeleteForm');
    const productTableBody = document.querySelector('#view-productos tbody');
    const orderTableBody = document.querySelector('[data-orders-body]');
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

    const fieldNombre = document.getElementById('fieldNombre');
    const fieldImagenActual = document.getElementById('fieldImagenActual');
    const fieldImagenFile = document.getElementById('fieldImagenFile');
    const fieldImagenPreviewWrap = document.getElementById('fieldImagenPreviewWrap');
    const fieldImagenPreview = document.getElementById('fieldImagenPreview');
    const fieldImagenPreviewText = document.getElementById('fieldImagenPreviewText');
    const fieldDescripcion = document.getElementById('fieldDescripcion');
    const fieldPrecio = document.getElementById('fieldPrecio');
    const fieldStock = document.getElementById('fieldStock');
    const fieldGenero = document.getElementById('fieldGenero');
    const fieldFormato = document.getElementById('fieldFormato');

    function showView(view) {
        for (const section of viewSections) {
            section.classList.toggle('is-active', section.getAttribute('data-view-section') === view);
        }
        for (const link of viewLinks) {
            link.classList.toggle('is-active', link.getAttribute('data-view-link') === view);
        }
        try {
            localStorage.setItem('beatdrop_admin_view', view);
        } catch (_) {}
    }

    function viewFromHash() {
        const hash = (window.location.hash || '').replace('#', '').trim();
        return hash || null;
    }

    function escapeHtml(value) {
        return String(value ?? '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function euro(value) {
        return `${Number(value || 0).toFixed(2)} EUR`;
    }

    function updateOrderMetrics(total) {
        document.querySelectorAll('[data-order-count]').forEach((node) => {
            node.textContent = String(Number(total || 0));
        });
    }

    function statusOptions(estado) {
        const estados = [
            ['pendiente', 'Pendiente'],
            ['enviado', 'Enviado'],
            ['entregado', 'Entregado'],
            ['simulado', 'Cancelado']
        ];

        return estados.map(([value, label]) => (
            `<option value="${value}" ${estado === value ? 'selected' : ''}>${label}</option>`
        )).join('');
    }

    function orderRowHtml(order) {
        const estado = order.estado || 'pendiente';
        return `
            <td class="cell-muted" data-label="ID">#${Number(order.id_pedido || 0)}</td>
            <td class="cell-strong" data-label="Usuario">
                <div class="order-user-cell">
                    <strong>${escapeHtml(order.usuario_nombre || '')}</strong>
                    <span>${escapeHtml(order.usuario_email || '')}</span>
                </div>
            </td>
            <td class="cell-muted" data-label="Fecha">${escapeHtml(order.fecha_pedido || '')}</td>
            <td class="cell-strong" data-label="Total"><span class="order-total-cell">${euro(order.total || 0)}</span></td>
            <td data-label="Estado">
                <select class="status-select" data-status value="${escapeHtml(estado)}">
                    ${statusOptions(estado)}
                </select>
            </td>
            <td class="td-actions" data-label="Detalle">
                <button type="button" class="btn btn-ghost btn-sm" data-action="view-order" data-order-id="${Number(order.id_pedido || 0)}">Ver detalle</button>
            </td>
        `;
    }

    function orderImage(path) {
        const src = imageUrl(path);
        return src
            ? `<img src="${escapeHtml(src)}" alt="" class="order-item-thumb">`
            : '<div class="order-item-thumb order-item-thumb-empty">Sin foto</div>';
    }

    function renderOrderDetail(order) {
        if (!orderDetailContent) return;

        const items = Array.isArray(order.items) ? order.items : [];
        const itemsHtml = items.length
            ? items.map((item) => `
                <div class="order-item-row">
                    ${orderImage(item.imagen_url || '')}
                    <div class="order-item-main">
                        <strong>${escapeHtml(item.nombre || 'Producto')}</strong>
                        <span>${escapeHtml([item.genero, item.formato].filter(Boolean).join(' / ') || 'Sin categoria')}</span>
                    </div>
                    <div class="order-item-qty">x${Number(item.cantidad || 0)}</div>
                    <div class="order-item-price">${euro(item.subtotal || 0)}</div>
                </div>
            `).join('')
            : '<p class="text-muted">Este pedido no tiene lineas asociadas.</p>';

        orderDetailContent.innerHTML = `
            <section class="order-summary-grid">
                <div>
                    <span>Cliente</span>
                    <strong>${escapeHtml(order.usuario_nombre || 'Cliente')}</strong>
                    <small>${escapeHtml(order.usuario_email || '')}</small>
                </div>
                <div>
                    <span>Estado</span>
                    <strong>${escapeHtml(order.estado || 'pendiente')}</strong>
                </div>
                <div>
                    <span>Fecha</span>
                    <strong>${escapeHtml(order.fecha_pedido || '')}</strong>
                </div>
                <div>
                    <span>Total</span>
                    <strong>${euro(order.total || 0)}</strong>
                </div>
            </section>

            <section class="order-address">
                <span>Direccion de entrega</span>
                <strong>${escapeHtml(order.direccion_envio || 'No indicada')}</strong>
            </section>

            <section class="order-items">
                <div class="order-section-title">Productos</div>
                ${itemsHtml}
            </section>
        `;
    }

    function openOrderModal() {
        if (!orderModal) return;
        orderModal.classList.add('is-open');
        orderModal.setAttribute('aria-hidden', 'false');
        document.body.classList.add('modal-open');
    }

    function closeOrderModal() {
        if (!orderModal) return;
        orderModal.classList.remove('is-open');
        orderModal.setAttribute('aria-hidden', 'true');
        document.body.classList.remove('modal-open');
    }

    async function loadOrderDetail(orderId) {
        if (!orderId) return;

        if (orderModalTitle) orderModalTitle.textContent = `Pedido #${Number(orderId)}`;
        if (orderModalSubtitle) orderModalSubtitle.textContent = 'Cargando detalle...';
        if (orderDetailContent) orderDetailContent.innerHTML = '<p class="text-muted">Cargando productos y datos de entrega...</p>';
        openOrderModal();

        try {
            const response = await fetch(`/admin/pedidos/detalle/${Number(orderId)}`, {
                headers: { 'Accept': 'application/json' }
            });
            const result = await response.json();

            if (!response.ok || !result.success || !result.pedido) {
                if (orderDetailContent) orderDetailContent.innerHTML = `<p class="text-muted">${escapeHtml(result.message || 'No se pudo cargar el pedido.')}</p>`;
                return;
            }

            const order = result.pedido;
            if (orderModalTitle) orderModalTitle.textContent = `Pedido #${Number(order.id_pedido || orderId)}`;
            if (orderModalSubtitle) orderModalSubtitle.textContent = `${order.usuario_nombre || 'Cliente'} - ${euro(order.total || 0)}`;
            renderOrderDetail(order);
        } catch (error) {
            console.error('Error loading order detail:', error);
            if (orderDetailContent) orderDetailContent.innerHTML = '<p class="text-muted">Error de conexion al cargar el detalle.</p>';
        }
    }

    function renderOrders(orders) {
        if (!orderTableBody) return;

        orderTableBody.innerHTML = '';
        if (!Array.isArray(orders) || orders.length === 0) {
            const row = document.createElement('tr');
            row.innerHTML = '<td colspan="6" class="cell-muted">Todavia no hay pedidos.</td>';
            orderTableBody.appendChild(row);
            return;
        }

        for (const order of orders) {
            const row = document.createElement('tr');
            row.innerHTML = orderRowHtml(order);
            orderTableBody.appendChild(row);
            syncStatusSelect(row.querySelector('[data-status]'));
        }
    }

    async function refreshOrders() {
        if (!orderTableBody) return;

        try {
            const response = await fetch('/admin/pedidos/latest', {
                headers: { 'Accept': 'application/json' }
            });
            const result = await response.json();
            if (!response.ok || !result.success) return;

            updateOrderMetrics(result.total);
            renderOrders(result.pedidos);
        } catch (error) {
            console.error('Error refreshing orders:', error);
        }
    }

    function imageUrl(path) {
        if (!path) return '';
        if (/^https?:\/\//i.test(path)) return path;
        return `/${String(path).replace(/^\/+/, '')}`;
    }

    function stockClass(stock) {
        if (stock <= 0) return 'stock-out';
        if (stock <= 3) return 'stock-low';
        return 'stock-ok';
    }

    function productPayload(product) {
        return escapeHtml(JSON.stringify({
            id_producto: Number(product.id_producto || 0),
            id_categoria: product.id_categoria === null ? null : Number(product.id_categoria || 0),
            nombre: product.nombre || '',
            descripcion: product.descripcion || '',
            precio: Number(product.precio || 0),
            stock: Number(product.stock || 0),
            imagen_url: product.imagen_url || '',
            genero: product.genero || '',
            formato: product.formato || '',
            talla: product.talla || ''
        }));
    }

    function rowHtml(product) {
        const stock = Number(product.stock || 0);
        const image = product.imagen_url
            ? `<img src="${escapeHtml(imageUrl(product.imagen_url))}" alt="${escapeHtml(product.nombre || '')}" class="product-thumb">`
            : '<div class="product-thumb product-thumb-placeholder">Sin foto</div>';

        return `
            <td class="cell-muted">#${Number(product.id_producto || 0)}</td>
            <td class="cell-strong">
                <div class="product-cell">
                    ${image}
                    <span>${escapeHtml(product.nombre || '')}</span>
                </div>
            </td>
            <td class="cell-muted">${escapeHtml(product.nombre_categoria || '')}</td>
            <td class="cell-strong">${euro(product.precio || 0)}</td>
            <td>
                <span class="stock-value ${stockClass(stock)}">${stock}</span>
                <button type="button" class="stock-btn" data-action="stock-change" data-delta="-1" title="Reducir stock">-</button>
                <button type="button" class="stock-btn" data-action="stock-change" data-delta="1" title="Aumentar stock">+</button>
            </td>
            <td class="td-actions">
                <button type="button" class="btn btn-ghost btn-sm" data-action="edit-product" data-product="${productPayload(product)}" title="Editar producto">
                    Editar
                </button>
                <button type="button" class="btn btn-danger btn-sm" data-action="delete-product" data-product-id="${Number(product.id_producto || 0)}" title="Eliminar producto">
                    Eliminar
                </button>
            </td>
        `;
    }

    function upsertRow(product, options = {}) {
        if (!productTableBody || !product || !product.id_producto) return;

        const prepend = Boolean(options.prepend);
        const selector = `[data-product-id="${Number(product.id_producto)}"]`;
        let row = productTableBody.querySelector(selector);

        if (!row) {
            row = document.createElement('tr');
            row.className = 'row';
            row.setAttribute('data-row', '');
            row.setAttribute('data-product-id', String(Number(product.id_producto)));
            if (prepend && productTableBody.firstChild) {
                productTableBody.insertBefore(row, productTableBody.firstChild);
            } else {
                productTableBody.appendChild(row);
            }
        }

        row.innerHTML = rowHtml(product);
    }

    function removeRow(productId) {
        const row = productTableBody?.querySelector(`[data-product-id="${Number(productId)}"]`);
        row?.remove();
    }

    function updateProductMetrics() {
        const rows = Array.from(document.querySelectorAll('#view-productos tr[data-product-id]'));
        const total = rows.length;
        const lowStock = rows.filter((row) => {
            const stock = parseInt(row.querySelector('.stock-value')?.textContent || '0', 10) || 0;
            return stock <= 3;
        }).length;

        document.querySelectorAll('[data-product-count]').forEach((node) => {
            node.textContent = String(total);
        });
        document.querySelectorAll('[data-low-stock-count]').forEach((node) => {
            node.textContent = String(lowStock);
        });
    }

    function notifyCatalogChange(type, product) {
        const payload = {
            type,
            productId: product?.id_producto ?? null,
            at: Date.now()
        };

        try {
            localStorage.setItem('beatdrop_catalog_sync', JSON.stringify(payload));
        } catch (_) {}

        try {
            syncChannel?.postMessage(payload);
        } catch (_) {}
    }

    function clearImagePreview() {
        if (fieldImagenFile) fieldImagenFile.value = '';
        if (fieldImagenPreview) fieldImagenPreview.src = '';
        if (fieldImagenPreviewText) fieldImagenPreviewText.textContent = '';
        fieldImagenPreviewWrap?.classList.add('is-hidden');
    }

    function setImagePreview(src, label = '') {
        if (!fieldImagenPreview || !fieldImagenPreviewWrap) return;
        if (!src) {
            clearImagePreview();
            return;
        }

        fieldImagenPreview.src = src;
        if (fieldImagenPreviewText) fieldImagenPreviewText.textContent = label;
        fieldImagenPreviewWrap.classList.remove('is-hidden');
    }

    function validateImageFile(file) {
        if (!file) return true;
        const name = String(file.name || '').toLowerCase();
        const type = String(file.type || '').toLowerCase();
        const validName = name.endsWith('.jpg') || name.endsWith('.jpeg');
        const validType = type === '' || type === 'image/jpeg' || type === 'image/pjpeg';

        if (validName && validType) return true;

        alert('Solo puedes subir imagenes JPG o JPEG.');
        if (fieldImagenFile) fieldImagenFile.value = '';
        return false;
    }

    function openModal() {
        if (!modal) return;
        modal.classList.add('is-open');
        modal.setAttribute('aria-hidden', 'false');
        document.body.classList.add('modal-open');
        setTimeout(() => fieldNombre?.focus(), 0);
    }

    function closeModal() {
        if (!modal) return;
        modal.classList.remove('is-open');
        modal.setAttribute('aria-hidden', 'true');
        document.body.classList.remove('modal-open');
    }

    function setModeCreate() {
        if (!form) return;
        modalTitle.textContent = 'Anadir producto';
        submitBtn.textContent = 'Guardar';
        form.setAttribute('action', '/admin/productos/crear');
        deleteForm?.classList.add('is-hidden');
        if (deleteForm) deleteForm.setAttribute('action', '#');
        if (fieldImagenActual) fieldImagenActual.value = '';
        if (fieldNombre) fieldNombre.value = '';
        clearImagePreview();
        if (fieldDescripcion) fieldDescripcion.value = '';
        if (fieldPrecio) fieldPrecio.value = '0';
        if (fieldStock) fieldStock.value = '0';
        if (fieldGenero) fieldGenero.value = '';
        if (fieldFormato) fieldFormato.value = '';
    }

    function setModeEdit(product) {
        if (!form) return;
        modalTitle.textContent = 'Editar producto';
        submitBtn.textContent = 'Guardar cambios';
        form.setAttribute('action', `/admin/productos/editar/${product.id_producto}`);
        deleteForm?.classList.remove('is-hidden');
        if (deleteForm) deleteForm.setAttribute('action', `/admin/productos/eliminar/${product.id_producto}`);

        if (fieldImagenActual) fieldImagenActual.value = product.imagen_url || '';
        if (fieldNombre) fieldNombre.value = product.nombre || '';
        clearImagePreview();
        if (product.imagen_url) {
            setImagePreview(imageUrl(product.imagen_url), 'Imagen actual');
        }
        if (fieldDescripcion) fieldDescripcion.value = product.descripcion || '';
        if (fieldPrecio) fieldPrecio.value = String(product.precio ?? 0);
        if (fieldStock) fieldStock.value = String(product.stock ?? 0);
        if (fieldGenero) fieldGenero.value = product.genero || '';
        if (fieldFormato) fieldFormato.value = product.formato || '';
    }

    async function submitProductForm(event) {
        event.preventDefault();
        if (!form) return;

        const formData = new FormData(form);
        const isCreate = !(form.getAttribute('action') || '').includes('/editar/');
        const selectedFile = fieldImagenFile?.files?.[0];

        if (selectedFile && !validateImageFile(selectedFile)) {
            return;
        }

        try {
            const response = await fetch(form.getAttribute('action') || '/admin/productos/crear', {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                body: formData
            });

            const result = await response.json();
            if (!response.ok || !result.success || !result.producto) {
                alert(result.error || result.message || 'No se pudo guardar el producto.');
                return;
            }

            upsertRow(result.producto, { prepend: isCreate });
            updateProductMetrics();
            notifyCatalogChange(isCreate ? 'create' : 'update', result.producto);
            closeModal();
            setModeCreate();
        } catch (error) {
            console.error('Error saving product:', error);
            alert('Error de conexion al guardar el producto.');
        }
    }

    async function deleteProduct(productId, actionUrl) {
        const formData = new FormData();
        formData.append('csrf_token', csrfToken);

        const response = await fetch(actionUrl, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            },
            body: formData
        });

        const result = await response.json();
        if (!response.ok || !result.success) {
            throw new Error(result.error || result.message || 'No se pudo eliminar el producto.');
        }

        removeRow(productId);
        updateProductMetrics();
        notifyCatalogChange('delete', { id_producto: Number(productId) });
        closeModal();
        setModeCreate();
    }

    const initial = viewFromHash()
        || (() => {
            try { return localStorage.getItem('beatdrop_admin_view'); } catch (_) { return null; }
        })()
        || 'dashboard';
    showView(initial);

    window.addEventListener('hashchange', () => {
        const view = viewFromHash();
        if (view) showView(view);
    });

    document.addEventListener('click', (e) => {
        const target = e.target.closest('[data-view-link]');
        if (!target) return;
        const view = target.getAttribute('data-view-link');
        if (!view) return;
        e.preventDefault();
        window.location.hash = view;
        showView(view);
    });

    document.addEventListener('click', (e) => {
        const btn = e.target.closest('[data-action="go-view"]');
        if (!btn) return;
        const view = btn.getAttribute('data-target') || 'dashboard';
        window.location.hash = view;
        showView(view);
    });

    form?.addEventListener('submit', submitProductForm);
    deleteForm?.addEventListener('submit', async (event) => {
        event.preventDefault();
        const actionUrl = deleteForm.getAttribute('action') || '';
        const productId = actionUrl.split('/').pop();
        if (!productId || actionUrl === '#') return;

        if (!confirm('Estas seguro de que deseas eliminar este producto? Esta accion no se puede deshacer.')) {
            return;
        }

        try {
            await deleteProduct(productId, actionUrl);
        } catch (error) {
            console.error('Error deleting product:', error);
            alert(error.message || 'Error de conexion al eliminar el producto.');
        }
    });

    fieldImagenFile?.addEventListener('change', () => {
        const file = fieldImagenFile.files?.[0];
        if (!file) {
            if (fieldImagenActual?.value) {
                setImagePreview(imageUrl(fieldImagenActual.value), 'Imagen actual');
            } else {
                clearImagePreview();
            }
            return;
        }

        if (!validateImageFile(file)) {
            if (fieldImagenActual?.value) {
                setImagePreview(imageUrl(fieldImagenActual.value), 'Imagen actual');
            } else {
                clearImagePreview();
            }
            return;
        }

        const reader = new FileReader();
        reader.onload = () => {
            setImagePreview(String(reader.result || ''), file.name);
        };
        reader.readAsDataURL(file);
    });

    document.addEventListener('click', (e) => {
        const openBtn = e.target.closest('[data-action="open-product-modal"]');
        if (!openBtn) return;
        setModeCreate();
        openModal();
    });

    document.addEventListener('click', (e) => {
        const editBtn = e.target.closest('[data-action="edit-product"]');
        if (!editBtn) return;
        const raw = editBtn.getAttribute('data-product') || '{}';
        let product = {};
        try {
            product = JSON.parse(raw);
        } catch (_) {}
        setModeEdit(product);
        openModal();
    });

    document.addEventListener('click', (e) => {
        const closeBtn = e.target.closest('[data-action="close-product-modal"]');
        if (closeBtn) closeModal();
    });

    document.addEventListener('click', (e) => {
        const orderBtn = e.target.closest('[data-action="view-order"]');
        if (!orderBtn) return;
        loadOrderDetail(orderBtn.getAttribute('data-order-id'));
    });

    document.addEventListener('click', (e) => {
        const closeBtn = e.target.closest('[data-action="close-order-modal"]');
        if (closeBtn) closeOrderModal();
    });

    modal?.addEventListener('click', (e) => {
        if (e.target === modal) closeModal();
    });

    orderModal?.addEventListener('click', (e) => {
        if (e.target === orderModal) closeOrderModal();
    });

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && modal?.classList.contains('is-open')) closeModal();
        if (e.key === 'Escape' && orderModal?.classList.contains('is-open')) closeOrderModal();
    });

    document.addEventListener('click', (e) => {
        const stepBtn = e.target.closest('[data-step]');
        if (!stepBtn || !fieldStock) return;
        const delta = parseInt(stepBtn.getAttribute('data-step'), 10) || 0;
        const current = parseInt(fieldStock.value || '0', 10) || 0;
        fieldStock.value = String(Math.max(0, current + delta));
        fieldStock.focus();
    });

    document.addEventListener('click', async (e) => {
        const stockBtn = e.target.closest('[data-action="stock-change"]');
        if (!stockBtn) return;

        const row = stockBtn.closest('[data-product-id]');
        if (!row) return;

        const productId = row.getAttribute('data-product-id');
        const delta = parseInt(stockBtn.getAttribute('data-delta'), 10) || 0;
        if (delta === 0) return;

        const stockSpan = row.querySelector('.stock-value');
        if (!stockSpan) return;

        const currentStock = parseInt(stockSpan.textContent.trim(), 10) || 0;
        const newStock = Math.max(0, currentStock + delta);

        stockSpan.textContent = String(newStock);
        stockSpan.classList.remove('stock-ok', 'stock-low', 'stock-out');
        stockSpan.classList.add(stockClass(newStock));
        updateProductMetrics();

        try {
            const response = await fetch('/admin/productos/agregarStock/' + productId, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ cantidad: delta })
            });

            const result = await response.json();
            if (!response.ok || result.error || !result.producto) {
                stockSpan.textContent = String(currentStock);
                stockSpan.classList.remove('stock-ok', 'stock-low', 'stock-out');
                stockSpan.classList.add(stockClass(currentStock));
                updateProductMetrics();
                alert(result.error || 'Error al actualizar el stock');
                return;
            }

            upsertRow(result.producto);
            updateProductMetrics();
            notifyCatalogChange('stock', result.producto);
        } catch (error) {
            stockSpan.textContent = String(currentStock);
            stockSpan.classList.remove('stock-ok', 'stock-low', 'stock-out');
            stockSpan.classList.add(stockClass(currentStock));
            updateProductMetrics();
            console.error('Error updating stock:', error);
            alert('Error de conexion al actualizar el stock');
        }
    });

    document.addEventListener('click', (e) => {
        const deleteBtn = e.target.closest('[data-action="delete-product"]');
        if (!deleteBtn) return;

        const productId = deleteBtn.getAttribute('data-product-id');
        if (!productId) return;

        if (!confirm('Estas seguro de que deseas eliminar este producto? Esta accion no se puede deshacer.')) {
            return;
        }

        deleteProduct(productId, '/admin/productos/eliminar/' + productId).catch((error) => {
            console.error('Error deleting product:', error);
            alert(error.message || 'Error de conexion al eliminar el producto.');
        });
    });

    document.querySelectorAll('th.sortable').forEach((th) => {
        th.addEventListener('click', () => {
            const table = th.closest('table');
            const tbody = table?.querySelector('tbody');
            if (!tbody) return;
            const rows = Array.from(tbody.querySelectorAll('tr[data-product-id]'));
            if (!rows.length) return;

            const type = th.dataset.sort;
            const isAsc = th.classList.contains('asc');
            table.querySelectorAll('th.sortable').forEach((head) => head.classList.remove('asc', 'desc'));
            th.classList.add(isAsc ? 'desc' : 'asc');
            const direction = isAsc ? -1 : 1;

            rows.sort((a, b) => {
                let valA;
                let valB;

                switch (type) {
                    case 'id':
                        valA = parseInt(a.querySelector('td:nth-child(1)')?.textContent.replace(/[^\d]/g, '') || '0', 10);
                        valB = parseInt(b.querySelector('td:nth-child(1)')?.textContent.replace(/[^\d]/g, '') || '0', 10);
                        break;
                    case 'nombre':
                        valA = a.querySelector('td:nth-child(2) span:last-child')?.textContent.trim().toLowerCase() || '';
                        valB = b.querySelector('td:nth-child(2) span:last-child')?.textContent.trim().toLowerCase() || '';
                        break;
                    case 'categoria':
                        valA = a.querySelector('td:nth-child(3)')?.textContent.trim().toLowerCase() || '';
                        valB = b.querySelector('td:nth-child(3)')?.textContent.trim().toLowerCase() || '';
                        break;
                    case 'precio':
                        valA = parseFloat(a.querySelector('td:nth-child(4)')?.textContent.replace(/[^\d.,]/g, '').replace(',', '.') || '0');
                        valB = parseFloat(b.querySelector('td:nth-child(4)')?.textContent.replace(/[^\d.,]/g, '').replace(',', '.') || '0');
                        break;
                    case 'stock':
                        valA = parseInt(a.querySelector('.stock-value')?.textContent.replace(/[^\d]/g, '') || '0', 10);
                        valB = parseInt(b.querySelector('.stock-value')?.textContent.replace(/[^\d]/g, '') || '0', 10);
                        break;
                    default:
                        return 0;
                }

                if (valA < valB) return -1 * direction;
                if (valA > valB) return 1 * direction;
                return 0;
            });

            rows.forEach((row) => tbody.appendChild(row));
        });
    });

    function syncStatusSelect(select) {
        select.setAttribute('data-state', select.value);
    }

    const statusSelects = Array.from(document.querySelectorAll('[data-status]'));
    for (const select of statusSelects) syncStatusSelect(select);
    document.addEventListener('change', (e) => {
        const select = e.target.closest('[data-status]');
        if (!select) return;
        syncStatusSelect(select);
    });

    updateProductMetrics();
    refreshOrders();
    if (orderTableBody) {
        setInterval(refreshOrders, 15000);
    }
})();
