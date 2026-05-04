document.addEventListener('DOMContentLoaded', () => {
    pintarCarrito();

    const envioForm = document.getElementById('envio-form');
    if (envioForm) {
        envioForm.addEventListener('submit', (e) => {
            e.preventDefault();

            const datosPedido = obtenerDatosPedido();
            confirmarPedido(datosPedido);
        });
    }

    document.querySelectorAll('[data-close-pedido]').forEach((elemento) => {
        elemento.addEventListener('click', cerrarConfirmacionPedido);
    });

    document.addEventListener('keydown', (e) => {
        const modal = document.getElementById('pedido-confirmado-modal');
        if (e.key === 'Escape' && modal?.classList.contains('is-visible')) {
            cerrarConfirmacionPedido();
        }
    });
});

function obtenerDatosPedido() {
    const nombre = document.getElementById('nombre-envio')?.value.trim() || '';
    const email = document.getElementById('email-envio')?.value.trim() || '';
    const direccion = document.getElementById('direccion-envio')?.value.trim() || 'Direccion no indicada';
    const ciudad = document.getElementById('ciudad-envio')?.value.trim();
    const cp = document.getElementById('cp-envio')?.value.trim();
    const direccionCompleta = [direccion, ciudad, cp].filter(Boolean).join(', ');

    return {
        nombre,
        email,
        direccion: direccionCompleta,
        fechaEstimada: calcularRangoEntrega()
    };
}

async function confirmarPedido(datosPedido) {
    const carrito = JSON.parse(localStorage.getItem('carrito')) || [];
    const submitBtn = document.querySelector('#envio-form .btn-pagar');

    if (carrito.length === 0) {
        alert('Tu carrito esta vacio.');
        return;
    }

    if (submitBtn) {
        submitBtn.disabled = true;
        submitBtn.textContent = 'Confirmando pedido...';
    }

    try {
        const response = await fetch('/carrito/confirmar', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                ...datosPedido,
                items: carrito
            })
        });

        const result = await response.json();
        if (!response.ok || !result.success) {
            alert(result.message || 'No se pudo confirmar el pedido.');
            return;
        }

        localStorage.removeItem('carrito');
        pintarCarrito();
        mostrarConfirmacionPedido({
            ...datosPedido,
            fechaEstimada: result.fecha_estimada || datosPedido.fechaEstimada
        });
    } catch (error) {
        console.error('Error confirmando pedido:', error);
        alert('Error de conexion al confirmar el pedido.');
    } finally {
        if (submitBtn) {
            submitBtn.disabled = false;
            submitBtn.textContent = 'Confirmar Pedido y Pagar';
        }
    }
}

function calcularRangoEntrega() {
    const inicio = sumarDiasHabiles(new Date(), 3);
    const fin = sumarDiasHabiles(new Date(), 5);
    const formato = new Intl.DateTimeFormat('es-ES', {
        weekday: 'short',
        day: 'numeric',
        month: 'long'
    });

    return `${formato.format(inicio)} - ${formato.format(fin)}`;
}

function sumarDiasHabiles(fecha, dias) {
    const resultado = new Date(fecha);
    let sumados = 0;

    while (sumados < dias) {
        resultado.setDate(resultado.getDate() + 1);
        const diaSemana = resultado.getDay();
        if (diaSemana !== 0 && diaSemana !== 6) {
            sumados++;
        }
    }

    return resultado;
}

function mostrarConfirmacionPedido(datosPedido) {
    const modal = document.getElementById('pedido-confirmado-modal');
    const fecha = document.getElementById('pedido-fecha-estimada');
    const direccion = document.getElementById('pedido-direccion-confirmada');

    if (!modal) {
        alert(`Pedido confirmado. Entrega estimada: ${datosPedido.fechaEstimada}`);
        window.location.href = '/';
        return;
    }

    if (fecha) fecha.textContent = datosPedido.fechaEstimada;
    if (direccion) direccion.textContent = datosPedido.direccion;

    modal.classList.add('is-visible');
    modal.setAttribute('aria-hidden', 'false');
    document.body.classList.add('pedido-modal-open');
}

function cerrarConfirmacionPedido() {
    const modal = document.getElementById('pedido-confirmado-modal');
    if (modal) {
        modal.classList.remove('is-visible');
        modal.setAttribute('aria-hidden', 'true');
    }
    document.body.classList.remove('pedido-modal-open');
    window.location.href = '/';
}

function normalizarTextoCarrito(texto) {
    const value = String(texto || '');
    return value
        .replace(/ÃƒÂ¢Ã¢â€šÂ¬Ã¢â‚¬Å“|Ã¢â‚¬â€œ/g, '-')
        .replace(/ÃƒÆ’Ã‚Â³|ÃƒÂ³/g, 'o')
        .replace(/ÃƒÂ¡/g, 'a')
        .replace(/ÃƒÂ©/g, 'e')
        .replace(/ÃƒÂ­/g, 'i')
        .replace(/ÃƒÂº/g, 'u')
        .replace(/ÃƒÂ±/g, 'n')
        .replace(/\s+/g, ' ')
        .trim();
}

function pintarCarrito() {
    const contenedor = document.getElementById('carrito-items');
    const totalElemento = document.getElementById('carrito-total');
    const btnFinalizar = document.getElementById('btn-finalizar');
    const btnVaciar = document.getElementById('btn-vaciar');
    
    let carrito = JSON.parse(localStorage.getItem('carrito')) || [];
    let carritoActualizado = false;

    carrito = carrito.map((item) => {
        const tituloNormalizado = normalizarTextoCarrito(item.titulo);
        if (tituloNormalizado !== item.titulo) {
            carritoActualizado = true;
            return { ...item, titulo: tituloNormalizado };
        }
        return item;
    });

    if (carritoActualizado) {
        localStorage.setItem('carrito', JSON.stringify(carrito));
    }

    if (!contenedor) return;
    contenedor.innerHTML = '';
    
    if (carrito.length === 0) {
        contenedor.innerHTML = '<p style="text-align:center; padding:30px; color:#777;">Tu carrito esta vacio.</p>';
        if (totalElemento) totalElemento.innerText = 'Total: $0.00';
        if (btnFinalizar) btnFinalizar.style.display = 'none';
        if (btnVaciar) btnVaciar.style.display = 'none';
        return;
    }

    if (btnFinalizar) btnFinalizar.style.display = 'inline-block';
    if (btnVaciar) btnVaciar.style.display = 'inline-block';

    let totalCaja = 0;

    carrito.forEach((item, index) => {
        const precio = parseFloat(item.precio) || 0;
        const cantidad = parseInt(item.cantidad) || 1;
        totalCaja += precio * cantidad;

        const div = document.createElement('div');
        div.classList.add('carrito-item');
        
        div.innerHTML = `
            <img src="${item.imagen}" alt="${item.titulo}" class="img-producto">
            <div class="carrito-info">
                <h4>${item.titulo}</h4>
                <p>Precio: $${precio.toFixed(2)} | Cantidad: <strong>${cantidad}</strong></p>
            </div>
            <div class="carrito-controls">
                <button onclick="eliminarDelCarrito(${index})" class="btn-eliminar-item">
                    ${cantidad > 1 ? '-1 Unidad' : 'Eliminar'}
                </button>
            </div>
        `;
        contenedor.appendChild(div);
    });

    if (totalElemento) totalElemento.innerText = `Total: $${totalCaja.toFixed(2)}`;
}

window.eliminarDelCarrito = function(index) {
    let carrito = JSON.parse(localStorage.getItem('carrito')) || [];
    
    if (carrito[index].cantidad > 1) {
        carrito[index].cantidad--;
    } else {
        carrito.splice(index, 1);
    }
    
    localStorage.setItem('carrito', JSON.stringify(carrito));
    pintarCarrito();
};

window.vaciarCarrito = function() {
    if (confirm("Estas seguro de que quieres vaciar todo el carrito?")) {
        localStorage.removeItem('carrito');
        pintarCarrito();
    }
};

window.checkUserAndCheckout = function() {
    const usuarioActivo = sessionStorage.getItem('usuarioActivo');
    const formEntrega = document.getElementById('formulario-entrega');
    const btnFinalizar = document.getElementById('btn-finalizar');

    if (!usuarioActivo) {
        if (formEntrega) formEntrega.style.display = 'none';
        alert("Acceso denegado: la cuenta no existe o no has iniciado sesion.");
        const modal = document.getElementById('loginModal');
        if (modal) modal.style.display = 'flex';
    } else {
        if (formEntrega) {
            formEntrega.style.display = 'block';
            if (btnFinalizar) btnFinalizar.style.display = 'none';
            formEntrega.scrollIntoView({ behavior: 'smooth' });
        }
    }
};
