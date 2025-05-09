document.addEventListener('DOMContentLoaded', () => {
    const burbuja = document.getElementById('burbuja-tema');
    const root = document.documentElement;

    function actualizarIcono(tema) {
        burbuja.textContent = tema === 'oscuro' ? '☀️' : '🌙';
    }

    burbuja.addEventListener('click', () => {
        const temaActual = root.getAttribute('data-tema');
        const nuevoTema = temaActual === 'oscuro' ? 'claro' : 'oscuro';
        root.setAttribute('data-tema', nuevoTema);
        localStorage.setItem('tema', nuevoTema);
        actualizarIcono(nuevoTema);
    });

    const temaGuardado = localStorage.getItem('tema') || 'claro';
    root.setAttribute('data-tema', temaGuardado);
    actualizarIcono(temaGuardado);
});
