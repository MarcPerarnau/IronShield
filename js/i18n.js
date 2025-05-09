// js/i18n.js
let traducciones = {};

async function cargarIdioma(idioma) {
    const res = await fetch(`lang/index/${idioma}.json`);
    traducciones = await res.json();
    actualizarTexto();
    localStorage.setItem('idioma', idioma);
}

function actualizarTexto() {
    // Traducción de texto interno
    document.querySelectorAll('[data-i18n]').forEach(el => {
        const clave = el.getAttribute('data-i18n');
        if (traducciones[clave]) el.textContent = traducciones[clave];
    });

    // Traducción de atributos placeholder
    document.querySelectorAll('[data-i18n-placeholder]').forEach(el => {
        const clave = el.getAttribute('data-i18n-placeholder');
        if (traducciones[clave]) el.setAttribute('placeholder', traducciones[clave]);
    });
}

document.addEventListener('DOMContentLoaded', () => {
    const idiomaGuardado = localStorage.getItem('idioma') || 'es';
    cargarIdioma(idiomaGuardado);

    document.getElementById('selectorIdioma').addEventListener('change', e => {
        cargarIdioma(e.target.value);
    });
});
