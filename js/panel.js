function mostrarSeccion(id) {
    document.querySelectorAll('.seccion').forEach(sec => {
        sec.style.display = 'none';
    });

    const seccion = document.getElementById(id);
    if (seccion) {
        seccion.style.display = 'block';

        if (id === 'dashboard') actualizarDashboard();
        if (id === 'whitelist') {
            renderizarWhitelist();
            inicializarModalWhitelist();
        }
        if (id === 'blacklist') {
            renderizarBlacklist();
            inicializarModalBlacklist();
        }
        if (id === 'logs') renderizarLogs();
        if (id === 'auditoria') renderizarAuditoria();
    }
}


function actualizarDashboard() {
    const whitelist = JSON.parse(localStorage.getItem("whitelist") || "[]");
    const blacklist = JSON.parse(localStorage.getItem("blacklist") || "[]");
    const logs = JSON.parse(localStorage.getItem("logs") || "[]");
    const acciones = JSON.parse(localStorage.getItem("acciones") || "[]");

    const contadorW = document.getElementById("contador-whitelist");
    const contadorB = document.getElementById("contador-blacklist");
    const contadorL = document.getElementById("contador-logs");

    if (contadorW) contadorW.textContent = whitelist.length;
    if (contadorB) contadorB.textContent = blacklist.length;
    if (contadorL) contadorL.textContent = logs.length;

    const lista = document.getElementById("lista-acciones");
    if (lista) {
        lista.innerHTML = "";
        acciones.slice(-5).reverse().forEach(accion => {
            const li = document.createElement("li");
            li.textContent = `${accion.fecha} - ${accion.descripcion}`;
            lista.appendChild(li);
        });
    }
}

function renderizarWhitelist() {
    fetch("php/whitelist_lista.php")
        .then(res => res.json())
        .then(data => {
            const tabla = document.getElementById("tabla-whitelist");
            if (!tabla) return;
            tabla.innerHTML = "";

            data.forEach(entrada => {
                const fila = `
                <tr>
                    <td>${entrada.tipo}</td>
                    <td>${entrada.valor}</td>
                    <td>${entrada.rango || "-"}</td>
                    <td>${entrada.fecha}</td>
                    <td>${entrada.comentario || ""}</td>
                    <td>
                        <button class="btn-editar" data-id="${entrada.id}">📝</button>
                        <button class="btn-eliminar" data-id="${entrada.id}">🗑️</button>
                    </td>
                </tr>`;
                tabla.innerHTML += fila;
            });

            document.querySelectorAll(".btn-eliminar").forEach(btn => {
                btn.addEventListener("click", () => {
                    const id = btn.getAttribute("data-id");
                    if (confirm("¿Eliminar esta entrada?")) {
                        eliminarWhitelist(id);
                    }
                });
            });

            document.querySelectorAll(".btn-editar").forEach(btn => {
                btn.addEventListener("click", () => {
                    const id = btn.getAttribute("data-id");
                    editarWhitelist(id);
                });
            });
        });
}

function eliminarWhitelist(id) {
    mostrarConfirmacion("¿Estás seguro de que deseas eliminar esta entrada de la whitelist?", () => {
        fetch("php/whitelist_eliminar.php", {
            method: "POST",
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            body: `id=${encodeURIComponent(id)}`
        })
            .then(res => res.json())
            .then(data => {
                alert(data.mensaje || "Eliminado correctamente");
                renderizarWhitelist();
            })
            .catch(err => {
                console.error("Error al eliminar:", err);
                alert("Error al eliminar la entrada");
            });
    });
}


function registrarAccion(desc) {
    const acciones = JSON.parse(localStorage.getItem("acciones") || "[]");
    acciones.push({
        fecha: new Date().toISOString().split("T")[0],
        descripcion: desc
    });
    localStorage.setItem("acciones", JSON.stringify(acciones));
}

document.addEventListener("DOMContentLoaded", () => {
    // Mostrar la sección inicial
    mostrarSeccion('dashboard');

    // Formulario de whitelist (solo si existe en DOM)
    const form = document.getElementById("form-whitelist");
    if (form) {
        form.addEventListener("submit", e => {
            e.preventDefault();
            const input = document.getElementById("entrada-whitelist");
            const entrada = input?.value.trim();
            if (entrada) {
                const lista = JSON.parse(localStorage.getItem("whitelist") || "[]");
                lista.push(entrada);
                localStorage.setItem("whitelist", JSON.stringify(lista));
                input.value = "";
                renderizarWhitelist();
                actualizarDashboard();
                registrarAccion("Añadió una entrada a la whitelist");
            }
        });
    }
});

function inicializarModalWhitelist() {
    const modal = document.getElementById("modal-whitelist");
    const abrir = document.getElementById("btn-abrir-modal-whitelist");
    const cerrar = document.getElementById("cerrar-modal-whitelist");
    const form = document.getElementById("form-whitelist-modal");

    if (!modal || !abrir || !cerrar || !form) {
        console.warn("⚠️ Elementos del modal whitelist no encontrados.");
        return;
    }

    abrir.onclick = () => modal.style.display = "flex";
    cerrar.onclick = () => {
        modal.style.display = "none";
        form.reset();
        form.removeAttribute("data-edicion-id");
    };

    form.onsubmit = (e) => {
        e.preventDefault();
        const datos = new FormData(form);
        const idEdicion = form.getAttribute("data-edicion-id");
        if (idEdicion) datos.append("id", idEdicion);

        const url = idEdicion ? "php/u_whitelist.php" : "php/i_whitelist.php";

        fetch(url, {
            method: "POST",
            body: datos
        })
            .then(res => res.json())
            .then(data => {
                if (data.error) {
                    alert("❌ " + data.error);
                } else {
                    alert("✅ " + (data.mensaje || "Guardado correctamente"));
                    modal.style.display = "none";
                    form.reset();
                    form.removeAttribute("data-edicion-id");
                    renderizarWhitelist();
                }
            })
            .catch(err => {
                console.error("❌ Error al guardar whitelist:", err);
                alert("Error al procesar el formulario");
            });
    };
}


function renderizarBlacklist() {
    fetch("php/blacklist_lista.php")
        .then(res => res.json())
        .then(data => {
            const tabla = document.getElementById("tabla-blacklist");
            if (!tabla) return;
            tabla.innerHTML = "";

            data.forEach(registro => {
                const fila = `
            <tr>
              <td>${registro.tipo}</td>
              <td>${registro.valor}</td>
              <td>${registro.rango || "-"}</td>
              <td>${registro.fecha}</td>
              <td>${registro.comentario || ""}</td>
              <td>
                <button class="btn-editar" data-id="${registro.id}">📝</button>
                <button class="btn-eliminar" data-id="${registro.id}">🗑️</button>
              </td>
            </tr>
          `;
                tabla.innerHTML += fila;
            });

            // Activar botones
            document.querySelectorAll(".btn-eliminar").forEach(btn => {
                btn.addEventListener("click", () => {
                    const id = btn.getAttribute("data-id");
                    if (confirm("¿Eliminar esta entrada?")) {
                        eliminarBlacklist(id);
                    }
                });
            });

            document.querySelectorAll(".btn-editar").forEach(btn => {
                btn.addEventListener("click", () => {
                    const id = btn.getAttribute("data-id");
                    editarBlacklist(id);
                });
            });
        });
}

function eliminarBlacklist(id) {
    fetch("php/blacklist_eliminar.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: `id=${id}`
    })
        .then(res => res.json())
        .then(data => {
            alert(data.mensaje || "Eliminado");
            renderizarBlacklist();
        });
}

function editarBlacklist(id) {
    fetch(`php/blacklist_get.php?id=${id}`)
        .then(res => res.json())
        .then(data => {
            const modal = document.getElementById("modal-blacklist");
            const form = document.getElementById("form-blacklist-modal");

            // Rellenar campos
            form.tipo.value = data.tipo;
            form.valor.value = data.valor;
            form.rango.value = data.rango || "";
            form.comentario.value = data.comentario || "";
            form.setAttribute("data-edicion-id", data.id); // Marcar como edición

            modal.style.display = "flex";
        });
}


function inicializarModalBlacklist() {
    const modal = document.getElementById("modal-blacklist");
    const abrir = document.getElementById("btn-abrir-modal-blacklist");
    const cerrar = document.getElementById("cerrar-modal-blacklist");
    const form = document.getElementById("form-blacklist-modal");

    if (abrir && modal) {
        abrir.onclick = () => modal.style.display = "flex";
    }

    if (cerrar && modal) {
        cerrar.onclick = () => modal.style.display = "none";
    }

    if (form) {
        form.onsubmit = (e) => {
            e.preventDefault();
            const datos = new FormData(form);
            fetch("php/blacklist_insert.php", {
                method: "POST",
                body: datos
            })
                .then(res => res.json())
                .then(data => {
                    alert(data.mensaje || "Añadido correctamente");
                    modal.style.display = "none";
                    form.reset();
                    renderizarBlacklist();
                });
        };
    }
}

function renderizarLogs() {
    fetch("php/logs_lista.php")
        .then(res => res.json())
        .then(data => {
            const tabla = document.getElementById("tabla-logs");
            if (!tabla) return;
            tabla.innerHTML = "";

            data.forEach(log => {
                const clase = `severidad-${log.severidad.toLowerCase()}`;
                const fila = `
            <tr>
              <td>${log.fecha}</td>
              <td>${log.hora}</td>
              <td class="${clase}">${log.severidad}</td>
              <td>${log.mensaje}</td>
              <td>${log.origen}</td>
            </tr>
          `;
                tabla.innerHTML += fila;
            });
        });
}

let datosAuditoria = []; // Datos originales cacheados

// Función para cargar los datos desde PHP
function renderizarAuditoria() {
    fetch("php/auditoria_lista.php")
        .then(res => res.json())
        .then(data => {
            datosAuditoria = data; // Guardar para filtrar
            aplicarFiltrosAuditoria();
        })
        .catch(err => {
            console.error("Error al cargar auditoría:", err);
        });
}

// Función que filtra y muestra los datos
function aplicarFiltrosAuditoria() {
    const tabla = document.getElementById("tabla-auditoria");
    if (!tabla) return;

    const usuario = document.getElementById("filtro-usuario")?.value.toLowerCase() || "";
    const fecha = document.getElementById("filtro-fecha")?.value || "";

    const filtrados = datosAuditoria.filter(item => {
        const coincideUsuario = item.usuario.toLowerCase().includes(usuario);
        const coincideFecha = fecha ? item.fecha === fecha : true;
        return coincideUsuario && coincideFecha;
    });

    tabla.innerHTML = "";

    filtrados.forEach(item => {
        const clase = item.accion.toLowerCase().includes("eliminó") ? "accion-destacada" : "";
        tabla.innerHTML += `
      <tr>
        <td>${item.fecha}</td>
        <td>${item.hora}</td>
        <td>${item.usuario}</td>
        <td class="${clase}">${item.accion}</td>
        <td>${item.ip}</td>
      </tr>
    `;
    });
}

// Inicializar eventos de filtrado al cargar el DOM
document.addEventListener("DOMContentLoaded", () => {
    const inputUsuario = document.getElementById("filtro-usuario");
    const inputFecha = document.getElementById("filtro-fecha");

    if (inputUsuario) inputUsuario.addEventListener("input", aplicarFiltrosAuditoria);
    if (inputFecha) inputFecha.addEventListener("change", aplicarFiltrosAuditoria);

    renderizarAuditoria(); // Cargar y mostrar datos al iniciar
});

document.addEventListener("DOMContentLoaded", () => {
    const raw = document.cookie.split("; ").find(row => row.startsWith("ironshield_sesion="));
    if (raw) {
        try {
            const cookie = JSON.parse(decodeURIComponent(raw.split("=")[1]));
            document.getElementById("dni-info").textContent = cookie.dni || "-";
            document.getElementById("cif-info").textContent = cookie.cif || "(ninguno)";
        } catch (e) {
            console.warn("No se pudo leer la cookie de sesión");
        }
    }
});

// Solo si existe el span
const contadorUsuarios = document.getElementById("contador-usuarios");
if (contadorUsuarios) {
    const usuarios = JSON.parse(localStorage.getItem("usuarios") || "[]");
    contadorUsuarios.textContent = usuarios.length;
}

function mostrarConfirmacion(mensaje, callback) {
    const popup = document.getElementById("popup-confirmacion");
    const btnCancelar = document.getElementById("popup-cancelar");
    const btnConfirmar = document.getElementById("popup-confirmar");
    const msg = document.getElementById("popup-mensaje");

    if (!popup || !btnCancelar || !btnConfirmar) return;

    msg.textContent = mensaje;
    popup.style.display = "flex";

    const cerrar = () => {
        popup.style.display = "none";
        btnCancelar.removeEventListener("click", cerrar);
        btnConfirmar.removeEventListener("click", confirmar);
    };

    const confirmar = () => {
        cerrar();
        callback(); // Ejecuta lo que se haya pasado
    };

    btnCancelar.addEventListener("click", cerrar);
    btnConfirmar.addEventListener("click", confirmar);
}
