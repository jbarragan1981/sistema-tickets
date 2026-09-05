/**
 * Validaciones del formulario de registro de incidencias.
 * Se ejecutan en el navegador antes de enviar los datos al controlador.
 *
 * Qué función cubre cada regla:
 *  - Campos vacíos (los siete campos):          estaVacio, usada dentro de cada validarX
 *  - Longitud de título/reportante/descripción: validarTitulo, validarReportante, validarDescripcion
 *  - Código de área, entero entre 1 y 999:      esEnteroEnRango, validarAreaCodigo
 *  - Prioridad dentro del catálogo permitido:   validarPrioridad
 *  - Categoría distinta de la opción vacía:     validarCategoria
 *  - Formato de correo electrónico:             esCorreoValido, validarCorreo
 */

const PRIORIDADES_VALIDAS = ['Baja', 'Media', 'Alta', 'Crítica'];

document.addEventListener('DOMContentLoaded', () => {
    const formulario = document.querySelector('#form-incidencia');
    if (!formulario) return;

    const reglasPorCampo = {
        titulo: validarTitulo,
        categoria_id: validarCategoria,
        prioridad: validarPrioridad,
        reportante: validarReportante,
        correo: validarCorreo,
        area_codigo: validarAreaCodigo,
        descripcion: validarDescripcion,
    };

    Object.keys(reglasPorCampo).forEach((nombre) => {
        const campo = formulario.querySelector(`[name="${nombre}"]`);
        if (!campo) return;
        campo.addEventListener('blur', () => validarCampo(campo, reglasPorCampo[nombre]));
        campo.addEventListener('input', () => limpiarError(campo));
    });

    const descripcion = formulario.querySelector('#descripcion');
    const contador = document.querySelector('#contador-descripcion');
    if (descripcion && contador) {
        actualizarContador(descripcion, contador);
        descripcion.addEventListener('input', () => actualizarContador(descripcion, contador));
    }

    formulario.addEventListener('submit', (evento) => {
        if (!validarFormulario(formulario, reglasPorCampo)) {
            evento.preventDefault();
        }
    });
});

/** Recorre todos los campos del formulario y detiene el envío si alguno falla. */
function validarFormulario(formulario, reglasPorCampo) {
    let formularioValido = true;
    let primerCampoConError = null;

    Object.keys(reglasPorCampo).forEach((nombre) => {
        const campo = formulario.querySelector(`[name="${nombre}"]`);
        if (!campo) return;

        const campoValido = validarCampo(campo, reglasPorCampo[nombre]);
        if (!campoValido) {
            formularioValido = false;
            if (!primerCampoConError) {
                primerCampoConError = campo;
            }
        }
    });

    if (primerCampoConError) {
        primerCampoConError.focus();
    }

    return formularioValido;
}

/** Aplica la regla de un campo y refleja el resultado en la interfaz. */
function validarCampo(campo, regla) {
    const mensaje = regla(campo.value);
    if (mensaje) {
        mostrarError(campo, mensaje);
        return false;
    }
    limpiarError(campo);
    return true;
}

/** Marca el campo como inválido y escribe el mensaje en su contenedor de error. */
function mostrarError(campo, mensaje) {
    const contenedor = document.getElementById(`error-${campo.name}`);
    campo.classList.add('campo__control--error');
    campo.setAttribute('aria-invalid', 'true');
    if (contenedor) {
        contenedor.textContent = mensaje;
    }
}

/** Quita la marca de error y borra el mensaje del campo. */
function limpiarError(campo) {
    const contenedor = document.getElementById(`error-${campo.name}`);
    campo.classList.remove('campo__control--error');
    campo.removeAttribute('aria-invalid');
    if (contenedor) {
        contenedor.textContent = '';
    }
}

/** Título: obligatorio, entre 5 y 120 caracteres. */
function validarTitulo(valor) {
    if (estaVacio(valor)) return 'El título no puede quedar vacío.';
    if (valor.trim().length < 5) return 'El título necesita al menos 5 caracteres.';
    if (valor.trim().length > 120) return 'El título no puede superar los 120 caracteres.';
    return null;
}

/** Categoría: obligatoria, distinta de la opción vacía. */
function validarCategoria(valor) {
    if (estaVacio(valor) || Number(valor) <= 0) {
        return 'Selecciona una categoría de la lista.';
    }
    return null;
}

/** Prioridad: obligatoria y dentro del catálogo permitido. */
function validarPrioridad(valor) {
    if (estaVacio(valor) || !PRIORIDADES_VALIDAS.includes(valor)) {
        return 'Selecciona una prioridad válida.';
    }
    return null;
}

/** Persona que reporta: obligatoria, entre 3 y 100 caracteres. */
function validarReportante(valor) {
    if (estaVacio(valor)) return 'Indica quién reporta la incidencia.';
    if (valor.trim().length < 3) return 'El nombre de quien reporta necesita al menos 3 caracteres.';
    if (valor.trim().length > 100) return 'El nombre de quien reporta no puede superar los 100 caracteres.';
    return null;
}

/** Correo electrónico: obligatorio y con formato válido. */
function validarCorreo(valor) {
    if (estaVacio(valor)) return 'El correo electrónico es obligatorio.';
    if (!esCorreoValido(valor)) {
        return 'Escribe un correo con formato válido, por ejemplo nombre@dominio.com.';
    }
    return null;
}

/** Código de área: obligatorio, entero entre 1 y 999. */
function validarAreaCodigo(valor) {
    if (estaVacio(valor)) return 'Indica el código de área.';
    if (!esEnteroEnRango(valor, 1, 999)) {
        return 'El código de área debe ser un número entero entre 1 y 999.';
    }
    return null;
}

/** Descripción: obligatoria, mínimo 15 caracteres. */
function validarDescripcion(valor) {
    if (estaVacio(valor)) return 'Describe la incidencia con más detalle.';
    if (valor.trim().length < 15) return 'La descripción necesita al menos 15 caracteres.';
    return null;
}

/** Un campo está vacío si no tiene valor o solo contiene espacios. */
function estaVacio(valor) {
    return valor === null || valor === undefined || valor.trim() === '';
}

/** Verifica que el texto sea un entero, sin decimales, dentro de un rango. */
function esEnteroEnRango(valor, minimo, maximo) {
    if (!/^-?\d+$/.test(valor.trim())) return false;
    const numero = Number(valor);
    return numero >= minimo && numero <= maximo;
}

/** Formato básico de correo electrónico: algo@algo.algo, sin espacios. */
function esCorreoValido(valor) {
    const patron = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return patron.test(valor.trim());
}

/** Actualiza el contador de caracteres escritos debajo de la descripción. */
function actualizarContador(campoDescripcion, contadorElemento) {
    contadorElemento.textContent = `${campoDescripcion.value.length} caracteres`;
}
