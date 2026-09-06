document.addEventListener('DOMContentLoaded', () => {
    const tablero = document.querySelector('#tablero');
    if (!tablero) {
        return;
    }

    document.body.classList.add('js-activo');

    const aviso = document.querySelector('#tablero-aviso');
    let tarjetaArrastrada = null;
    let enviando = false;

    const mostrarAviso = (texto, tipo) => {
        if (!aviso) {
            return;
        }
        aviso.textContent = texto;
        aviso.className = 'tablero__aviso tablero__aviso--' + tipo;
        aviso.hidden = false;
    };

    const listaDe = (estado) => tablero.querySelector('.columna__lista[data-estado="' + estado + '"]');

    const refrescarColumna = (lista) => {
        const columna = lista.closest('.columna');
        const tarjetas = lista.querySelectorAll('.tarjeta');
        columna.querySelector('.columna__contador').textContent = String(tarjetas.length);

        const vacio = lista.querySelector('.columna__vacio');
        if (tarjetas.length === 0 && !vacio) {
            const mensaje = document.createElement('p');
            mensaje.className = 'columna__vacio';
            mensaje.textContent = 'Todavía no hay incidencias aquí.';
            lista.appendChild(mensaje);
        } else if (tarjetas.length > 0 && vacio) {
            vacio.remove();
        }
    };

    const colocarTarjeta = (tarjeta, lista, estado, referencia) => {
        if (referencia && referencia.parentElement === lista) {
            lista.insertBefore(tarjeta, referencia);
        } else {
            lista.appendChild(tarjeta);
        }
        tarjeta.dataset.estado = estado;
        const select = tarjeta.querySelector('.tarjeta__mover-select');
        if (select) {
            select.value = estado;
        }
    };

    const bloquear = (activo) => {
        enviando = activo;
        tablero.classList.toggle('tablero--enviando', activo);
    };

    const enviarCambio = (id, estado) => fetch('index.php?ruta=mover', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-Requested-With': 'XMLHttpRequest',
        },
        body: new URLSearchParams({ id, estado }).toString(),
    }).then((respuesta) => respuesta.json());

    tablero.addEventListener('dragstart', (evento) => {
        const tarjeta = evento.target.closest('.tarjeta');
        if (!tarjeta || enviando) {
            evento.preventDefault();
            return;
        }
        tarjetaArrastrada = tarjeta;
        tarjeta.classList.add('tarjeta--arrastrando');
        evento.dataTransfer.effectAllowed = 'move';
        evento.dataTransfer.setData('text/plain', tarjeta.dataset.id);
    });

    tablero.addEventListener('dragend', () => {
        if (tarjetaArrastrada) {
            tarjetaArrastrada.classList.remove('tarjeta--arrastrando');
        }
        tarjetaArrastrada = null;
        tablero.querySelectorAll('.columna__lista--soltar')
            .forEach((lista) => lista.classList.remove('columna__lista--soltar'));
    });

    tablero.addEventListener('dragover', (evento) => {
        const lista = evento.target.closest('.columna__lista');
        if (!lista || !tarjetaArrastrada) {
            return;
        }
        evento.preventDefault();
        evento.dataTransfer.dropEffect = 'move';
        lista.classList.add('columna__lista--soltar');
    });

    tablero.addEventListener('dragleave', (evento) => {
        const lista = evento.target.closest('.columna__lista');
        if (lista && !lista.contains(evento.relatedTarget)) {
            lista.classList.remove('columna__lista--soltar');
        }
    });

    tablero.addEventListener('drop', (evento) => {
        const destino = evento.target.closest('.columna__lista');
        if (!destino || !tarjetaArrastrada || enviando) {
            return;
        }
        evento.preventDefault();
        destino.classList.remove('columna__lista--soltar');

        const tarjeta = tarjetaArrastrada;
        const estadoOrigen = tarjeta.dataset.estado;
        const estadoDestino = destino.dataset.estado;
        if (estadoOrigen === estadoDestino) {
            return;
        }

        const origen = listaDe(estadoOrigen);
        const referencia = tarjeta.nextElementSibling;

        bloquear(true);
        colocarTarjeta(tarjeta, destino, estadoDestino);
        [origen, destino].forEach(refrescarColumna);

        enviarCambio(tarjeta.dataset.id, estadoDestino)
            .then((datos) => {
                if (!datos.ok) {
                    throw new Error(datos.mensaje || 'No se pudo actualizar el estado.');
                }
                mostrarAviso(datos.mensaje, 'exito');
            })
            .catch((error) => {
                colocarTarjeta(tarjeta, origen, estadoOrigen, referencia);
                [origen, destino].forEach(refrescarColumna);
                mostrarAviso(error.message, 'error');
            })
            .finally(() => bloquear(false));
    });
});
