// FUNCIONES GLOBALES
function mostrarPaso(index) {
    const pasos = document.querySelectorAll('#formRegistro .paso');
    pasos.forEach((paso, i) => paso.classList.toggle('oculto', i !== index));
    if (index === 2 && typeof actualizarResumen === 'function') actualizarResumen();
}

document.addEventListener('DOMContentLoaded', () => {
    // --- BOTONES ELEGIR PLAN ---
    document.querySelectorAll('.elegir-plan').forEach(boton => {
        boton.addEventListener('click', () => {
            const plan = boton.dataset.plan;
            document.getElementById('modalLogin')?.classList.add('oculto');
            document.getElementById('modalRegistro')?.classList.remove('oculto');
            const radio = document.querySelector(`#modalRegistro input[name="plan"][value="${plan}"]`);
            if (radio) {
                setTimeout(() => {
                    radio.checked = true;
                    radio.dispatchEvent(new Event('change'));
                }, 100);
            }
            mostrarPaso(1);
        });
    });

    // --- FORMULARIO MULTIPASO ---
    const pasos = document.querySelectorAll('#formRegistro .paso');
    const btnsSiguiente = document.querySelectorAll('#formRegistro .siguiente');
    const btnsAnterior = document.querySelectorAll('#formRegistro .anterior');
    let pasoActual = 0;

    btnsSiguiente.forEach(btn => {
        btn.addEventListener('click', () => {
            const visibles = Array.from(pasos[pasoActual].querySelectorAll('input')).filter(input => input.offsetParent !== null);
            const validos = visibles.every(input => input.checkValidity());
            if (validos) {
                pasoActual++;
                mostrarPaso(pasoActual);
            } else {
                visibles.forEach(input => input.reportValidity());
            }
        });
    });

    btnsAnterior.forEach(btn => {
        btn.addEventListener('click', () => {
            pasoActual = Math.max(0, pasoActual - 1);
            mostrarPaso(pasoActual);
        });
    });

    mostrarPaso(pasoActual);

    // --- FAQ ---
    document.querySelectorAll('.faq-titulo').forEach(btn =>
        btn.addEventListener('click', () => btn.parentElement.classList.toggle('activa'))
    );

    // --- MODALES LOGIN / REGISTRO ---
    const modalLogin = document.getElementById('modalLogin');
    const modalRegistro = document.getElementById('modalRegistro');

    document.getElementById('btnLogin')?.addEventListener('click', () => modalLogin?.classList.remove('oculto'));
    modalLogin?.querySelector('.cerrar')?.addEventListener('click', () => modalLogin.classList.add('oculto'));
    modalRegistro?.querySelector('.cerrar')?.addEventListener('click', () => modalRegistro.classList.add('oculto'));

    window.addEventListener('click', e => {
        if (e.target === modalLogin) modalLogin.classList.add('oculto');
        if (e.target === modalRegistro) modalRegistro.classList.add('oculto');
    });

    document.querySelectorAll('.abrir-registro').forEach(btn =>
        btn.addEventListener('click', () => {
            modalLogin?.classList.add('oculto');
            modalRegistro?.classList.remove('oculto');
            mostrarPaso(0);
        })
    );

    // --- FORMATEO NÚMERO TARJETA ---
    document.getElementById('tarjeta')?.addEventListener('input', e => {
        let val = e.target.value.replace(/\D/g, '').slice(0, 16);
        e.target.value = val.replace(/(.{4})/g, '$1-').slice(0, 19).replace(/-$/, '');
    });

    // --- CARRUSEL DE TESTIMONIOS ---
    const slides = document.querySelectorAll(".carrusel-testimonios .slide");
    let slideIndex = 0;

    if (slides.length > 0) {
        setInterval(() => {
            slideIndex = (slideIndex + 1) % slides.length;
            slides.forEach((slide, i) => slide.classList.toggle("activo", i === slideIndex));
        }, 5000);
    }

    // --- PLANES Y RESUMEN ---
    let precioBase = 0;
    let planSeleccionado = '';
    const precioPlan = document.getElementById('precioPlan');
    const resumenPlan = document.getElementById('resumenPlan');
    const resumenPrecio = document.getElementById('resumenPrecio');
    const extraCampos = document.getElementById('extraCampos');

    function actualizarResumen() {
        const tipo = document.querySelector('input[name="tipoPago"]:checked')?.value;
        let final = precioBase;

        if (tipo === 'anual') {
            final = (precioBase * 12 * 0.85).toFixed(2);
            resumenPrecio.textContent = `Precio: ${final}€/año`;
        } else {
            resumenPrecio.textContent = `Precio: ${precioBase}€/mes`;
        }

        resumenPlan.textContent = `Plan: ${planSeleccionado || 'No seleccionado'}`;
    }

    document.querySelectorAll('input[name="plan"]').forEach(radio => {
        radio.addEventListener('change', () => {
            const plan = radio.value;
            extraCampos.innerHTML = '';
            precioPlan.textContent = '';

            switch (plan) {
                case 'basico':
                    precioBase = 5;
                    planSeleccionado = traducciones?.p5 || 'Básico';
                    precioPlan.textContent = `${traducciones?.precio || 'Precio'}: 5€/mes`;
                    break;

                case 'pro':
                    precioBase = 15;
                    planSeleccionado = traducciones?.p11 || 'Pro';
                    extraCampos.innerHTML = `
              <input type="text" name="empresa" placeholder="${traducciones?.empresa_opcional || 'Empresa (opcional)'}">
              <input type="text" name="telefono" placeholder="${traducciones?.telefono_opcional || 'Teléfono (opcional)'}">
            `;
                    precioPlan.textContent = `${traducciones?.precio || 'Precio'}: 15€/mes`;
                    break;

                case 'empresarial':
                    precioBase = 30;
                    planSeleccionado = traducciones?.p15 || 'Empresarial';
                    extraCampos.innerHTML = `
              <input type="text" name="empresa" placeholder="${traducciones?.empresa || 'Nombre empresa'}" required>
              <input type="text" name="cif" placeholder="${traducciones?.cif || 'CIF'}" required>
              <input type="text" name="telefono" placeholder="${traducciones?.telefono || 'Teléfono empresa'}" required>
            `;
                    precioPlan.textContent = `${traducciones?.precio || 'Precio'}: 30€/mes`;
                    break;
            }

            actualizarResumen();
            actualizarTexto?.();
        });
    });

    document.querySelectorAll('input[name="tipoPago"]').forEach(radio =>
        radio.addEventListener('change', actualizarResumen)
    );

    document.querySelectorAll('input[name="frecuencia"]').forEach(radio =>
        radio.addEventListener('change', () => {
            const anual = radio.value === 'anual';
            document.querySelectorAll('.precio').forEach(precio => {
                const val = anual ? precio.dataset.anual : precio.dataset.mensual;
                precio.textContent = anual ? `${val}€/año` : `${val}€/mes`;
            });
        })
    );
});
