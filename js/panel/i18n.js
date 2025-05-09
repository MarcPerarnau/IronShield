let traducciones = {};

async function cargarIdioma(idioma) {
  const res = await fetch(`lang/${idioma}.json`);
  traducciones = await res.json();
  actualizarTexto();
  localStorage.setItem('idioma', idioma);
}

function actualizarTexto() {
  document.querySelectorAll('[data-i18n]').forEach(el => {
    const clave = el.getAttribute('data-i18n');
    if (traducciones[clave]) el.textContent = traducciones[clave];
  });
}

document.addEventListener('DOMContentLoaded', () => {
  const idioma = localStorage.getItem('idioma') || 'es';
  cargarIdioma(idioma);

  const selector = document.getElementById('selectorIdioma');
  if (selector) {
    selector.addEventListener('change', e => cargarIdioma(e.target.value));
  }
});
