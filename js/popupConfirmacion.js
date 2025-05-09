// popupConfirmacion.js

let confirmarCallback = null;

document.addEventListener("DOMContentLoaded", () => {
    const popup = document.getElementById("popup-confirmacion");
    const btnCancelar = document.getElementById("popup-cancelar");
    const btnConfirmar = document.getElementById("popup-confirmar");
    const mensaje = document.getElementById("popup-mensaje");

    if (!popup || !btnCancelar || !btnConfirmar || !mensaje) {
        console.warn("⚠️ No se encontró uno de los elementos del popup de confirmación");
        return;
    }

    btnCancelar.addEventListener("click", () => {
        popup.style.display = "none";
        confirmarCallback = null;
    });

    btnConfirmar.addEventListener("click", () => {
        popup.style.display = "none";
        if (typeof confirmarCallback === "function") {
            confirmarCallback();
        }
        confirmarCallback = null;
    });
});

/**
 * Muestra el popup de confirmación con un mensaje y una acción a ejecutar si el usuario confirma.
 * @param {string} textoMensaje - Texto del mensaje de confirmación.
 * @param {Function} onConfirmar - Función que se ejecuta si el usuario confirma.
 */
function mostrarPopupConfirmacion(textoMensaje, onConfirmar) {
    const popup = document.getElementById("popup-confirmacion");
    const mensaje = document.getElementById("popup-mensaje");

    if (!popup || !mensaje) return;

    mensaje.textContent = textoMensaje;
    confirmarCallback = onConfirmar;
    popup.style.display = "flex";
}
