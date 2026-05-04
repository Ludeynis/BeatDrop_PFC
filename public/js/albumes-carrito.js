// js/albumes-carrito.js

document.addEventListener('click', (e) => {
    // Detectamos si el elemento clickeado es un botón dentro de una tarjeta
    if (e.target && e.target.matches('.cards article button')) {
        agregarProducto(e.target);
    }
});

function agregarProducto(boton) {
    const card = boton.closest('article');

    // 1. CAPTURAR DATOS CON SEGURIDAD
    // Título: Intentamos h3, si no h2, si no el primer encabezado que haya
    const tituloEl = card.querySelector('h3') || card.querySelector('h2');
    const imgEl = card.querySelector('img');
    
    // Precio: Buscamos en todos los párrafos el que tenga el símbolo '$'
    let precio = 0;
    const parrafos = card.querySelectorAll('p');
    
    parrafos.forEach(p => {
        const texto = p.innerText;
        if (texto.includes('$')) {
            // Si hay un rango o oferta (ej: "$30 -> $22"), cogemos el último valor
            const partes = texto.split('$');
            const valorNumerico = partes[partes.length - 1]; // El último trozo
            precio = parseFloat(valorNumerico.replace(/[^0-9.]/g, ''));
        }
    });

    // Si por alguna razón no capturamos datos, paramos para evitar errores
    if (!tituloEl || !precio) {
        console.error("Error capturando datos del producto:", card);
        return;
    }

    const producto = {
        id: card.dataset.productId || tituloEl.innerText.replace(/\s+/g, '-').toLowerCase(),
        id_producto: Number(card.dataset.productId || 0),
        titulo: tituloEl.innerText,
        precio: precio,
        imagen: imgEl ? imgEl.src : '/img/future-album.jpg',
        cantidad: 1
    };

    // 2. GUARDAR EN EL CARRITO
    let carrito = JSON.parse(localStorage.getItem('carrito')) || [];
    const existe = carrito.find(item => item.id === producto.id);

    if (existe) {
        existe.cantidad++;
    } else {
        carrito.push(producto);
    }

    localStorage.setItem('carrito', JSON.stringify(carrito));

    // 3. EFECTO VISUAL VERDE (Feedback sin alertas)
    const textoOriginal = boton.innerText;
    
    boton.innerText = "¡Añadido! ✓";
    boton.style.transition = "all 0.3s ease";
    boton.style.backgroundColor = "#28a745"; // Verde éxito
    boton.style.color = "#ffffff";
    boton.style.border = "1px solid #28a745";
    boton.disabled = true; // Evitar doble clic rápido

    // Restaurar el botón después de 1.5 segundos
    setTimeout(() => {
        boton.innerText = textoOriginal;
        boton.style.backgroundColor = ""; // Volver al original del CSS
        boton.style.color = "";
        boton.style.border = "";
        boton.disabled = false;
    }, 1500);
}
