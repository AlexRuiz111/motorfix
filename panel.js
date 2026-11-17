// Espera a que el documento HTML cargue por completo antes de asignar eventos
document.addEventListener('DOMContentLoaded', () => {
    
    // Referencias a las vistas principales
    const vistaResumen = document.getElementById('vista-resumen');
    const seccionGaraje = document.getElementById('seccion-garaje');
    const seccionCitas = document.getElementById('seccion-citas');
    const seccionFacturas = document.getElementById('seccion-facturas');

    // Referencias a los botones de navegación
    const btnAccederGaraje = document.getElementById('btn-acceder-garaje');
    const btnAccederCitas = document.getElementById('btn-acceder-citas');
    const btnAccederFacturas = document.getElementById('btn-acceder-facturas');
    const btnVolverGaraje = document.getElementById('btn-volver-garaje');
    const btnVolverCitas = document.getElementById('btn-volver-citas');
    const btnVolverFacturas = document.getElementById('btn-volver-facturas');
    const btnLogout = document.getElementById('btnLogout');

    // Referencias a modales y formularios
    const btnNuevoVehiculo = document.getElementById('btn-nuevo-vehiculo');
    const modalVehiculo = document.getElementById('modal-vehiculo');
    const btnCancelarVehiculo = document.getElementById('btn-cancelar-vehiculo');
    const formVehiculo = document.getElementById('form-vehiculo');

    const btnNuevaCita = document.getElementById('btn-nueva-cita');
    const modalCita = document.getElementById('modal-cita');
    const btnCancelarCita = document.getElementById('btn-cancelar-cita');
    const formCita = document.getElementById('form-cita');

    // Función auxiliar que oculta todas las secciones antes de mostrar la elegida
    function ocultarTodasLasVistas() {
        vistaResumen.classList.add('oculto');
        seccionGaraje.classList.add('oculto');
        seccionCitas.classList.add('oculto');
        seccionFacturas.classList.add('oculto');
    }

    // Eventos de navegación entre vistas
    btnAccederGaraje.addEventListener('click', () => {
        ocultarTodasLasVistas();
        seccionGaraje.classList.remove('oculto');
        cargarVehiculos();
    });

    btnAccederCitas.addEventListener('click', () => {
        ocultarTodasLasVistas();
        seccionCitas.classList.remove('oculto');
        cargarCitas();
        cargarSelectorVehiculos();
    });

    btnAccederFacturas.addEventListener('click', () => {
        ocultarTodasLasVistas();
        seccionFacturas.classList.remove('oculto');
        cargarFacturas();
    });

    // Eventos de retorno al resumen principal
    [btnVolverGaraje, btnVolverCitas, btnVolverFacturas].forEach(boton => {
        boton.addEventListener('click', () => {
            ocultarTodasLasVistas();
            vistaResumen.classList.remove('oculto');
        });
    });

    // Gestión de visibilidad de los formularios modales
    btnNuevoVehiculo.addEventListener('click', () => modalVehiculo.classList.remove('oculto'));
    btnCancelarVehiculo.addEventListener('click', () => {
        modalVehiculo.classList.add('oculto');
        formVehiculo.reset();
    });

    btnNuevaCita.addEventListener('click', () => modalCita.classList.remove('oculto'));
    btnCancelarCita.addEventListener('click', () => {
        modalCita.classList.add('oculto');
        formCita.reset();
    });

    // Cierre de sesión contra el backend
    btnLogout.addEventListener('click', async () => {
        try {
            await fetch('auth_sw.php?action=logout');
            window.location.href = 'index.html';
        } catch (error) {
            console.error('Error al cerrar sesión:', error);
            window.location.href = 'index.html';
        }
    });

    // Consulta y renderiza los vehículos del cliente en una tabla
    async function cargarVehiculos() {
        const contenedor = document.getElementById('lista-vehiculos');
        contenedor.innerHTML = '<div class="texto-dashboard">Cargando vehículos...</div>';

        try {
            const respuesta = await fetch('api_cliente.php?action=get_vehiculos');
            const resultado = await respuesta.json();

            if (resultado.status === 'success') {
                if (resultado.data.length === 0) {
                    contenedor.innerHTML = '<div class="texto-modulo">No hay vehículos registrados en el garaje.</div>';
                    return;
                }

                let html = '<table class="tabla-datos"><thead><tr>' +
                           '<th class="celda-cabecera">Matrícula</th>' +
                           '<th class="celda-cabecera">Marca</th>' +
                           '<th class="celda-cabecera">Modelo</th>' +
                           '<th class="celda-cabecera">Año</th>' +
                           '</tr></thead><tbody>';

                resultado.data.forEach(coche => {
                    html += `<tr class="fila-tabla">
                                <td class="celda-dato texto-resaltado">${coche.matricula}</td>
                                <td class="celda-dato">${coche.marca}</td>
                                <td class="celda-dato">${coche.modelo}</td>
                                <td class="celda-dato">${coche.anyo}</td>
                             </tr>`;
                });

                html += '</tbody></table>';
                contenedor.innerHTML = html;
            } else {
                contenedor.innerHTML = `<div class="texto-modulo">${resultado.message}</div>`;
            }
        } catch (error) {
            contenedor.innerHTML = '<div class="texto-modulo">Error de conexión con el servidor.</div>';
        }
    }

    // Envía los datos del formulario para registrar un vehículo nuevo
    formVehiculo.addEventListener('submit', async (e) => {
        e.preventDefault();

        const datos = {
            action: 'add_vehiculo',
            matricula: document.getElementById('v-matricula').value,
            marca: document.getElementById('v-marca').value,
            modelo: document.getElementById('v-modelo').value,
            anyo: document.getElementById('v-anyo').value
        };

        try {
            const respuesta = await fetch('api_cliente.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(datos)
            });
            const resultado = await respuesta.json();

            alert(resultado.message);

            if (resultado.status === 'success') {
                modalVehiculo.classList.add('oculto');
                formVehiculo.reset();
                cargarVehiculos();
            }
        } catch (error) {
            alert('Error al guardar el vehículo.');
        }
    });

    // Rellena el desplegable de vehículos dentro del formulario de citas
    async function cargarSelectorVehiculos() {
        const selector = document.getElementById('c-matricula');
        selector.innerHTML = '<option value="">Selecciona un vehículo...</option>';

        try {
            const respuesta = await fetch('api_cliente.php?action=get_vehiculos');
            const resultado = await respuesta.json();

            if (resultado.status === 'success') {
                resultado.data.forEach(coche => {
                    const opcion = document.createElement('option');
                    opcion.value = coche.matricula;
                    opcion.textContent = `${coche.matricula} - ${coche.marca} ${coche.modelo}`;
                    selector.appendChild(opcion);
                });
            }
        } catch (error) {
            console.error('Error al cargar vehículos en el selector:', error);
        }
    }

    // Consulta y renderiza las citas asociadas a los vehículos del cliente
    async function cargarCitas() {
        const contenedor = document.getElementById('lista-citas');
        contenedor.innerHTML = '<div class="texto-dashboard">Cargando agenda...</div>';

        try {
            const respuesta = await fetch('api_cliente.php?action=get_citas');
            const resultado = await respuesta.json();

            if (resultado.status === 'success') {
                if (resultado.data.length === 0) {
                    contenedor.innerHTML = '<div class="texto-modulo">No existen citas programadas.</div>';
                    return;
                }

                let html = '<table class="tabla-datos"><thead><tr>' +
                           '<th class="celda-cabecera">Vehículo</th>' +
                           '<th class="celda-cabecera">Fecha y Hora</th>' +
                           '<th class="celda-cabecera">Motivo</th>' +
                           '<th class="celda-cabecera">Estado</th>' +
                           '</tr></thead><tbody>';

                resultado.data.forEach(cita => {
                    html += `<tr class="fila-tabla">
                                <td class="celda-dato texto-resaltado">${cita.vehiculo_matricula}</td>
                                <td class="celda-dato">${cita.fecha_hora}</td>
                                <td class="celda-dato">${cita.motivo}</td>
                                <td class="celda-dato"><span class="badge">${cita.estado}</span></td>
                             </tr>`;
                });

                html += '</tbody></table>';
                contenedor.innerHTML = html;
            }
        } catch (error) {
            contenedor.innerHTML = '<div class="texto-modulo">Error al cargar las citas.</div>';
        }
    }

    // Envía la solicitud de una nueva cita al taller
    formCita.addEventListener('submit', async (e) => {
        e.preventDefault();

        const datos = {
            action: 'add_cita',
            vehiculo_matricula: document.getElementById('c-matricula').value,
            fecha_hora: document.getElementById('c-fecha').value,
            motivo: document.getElementById('c-motivo').value
        };

        try {
            const respuesta = await fetch('api_cliente.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(datos)
            });
            const resultado = await respuesta.json();

            alert(resultado.message);

            if (resultado.status === 'success') {
                modalCita.classList.add('oculto');
                formCita.reset();
                cargarCitas();
            }
        } catch (error) {
            alert('Error al solicitar la cita.');
        }
    });

    // Consulta y renderiza el historial de facturas del cliente
    async function cargarFacturas() {
        const contenedor = document.getElementById('lista-facturas');
        contenedor.innerHTML = '<div class="texto-dashboard">Cargando facturas...</div>';

        try {
            const respuesta = await fetch('api_cliente.php?action=get_facturas');
            const resultado = await respuesta.json();

            if (resultado.status === 'success') {
                if (resultado.data.length === 0) {
                    contenedor.innerHTML = '<div class="texto-modulo">No hay facturas emitidas todavía.</div>';
                    return;
                }

                let html = '<table class="tabla-datos"><thead><tr>' +
                           '<th class="celda-cabecera">Nº Factura</th>' +
                           '<th class="celda-cabecera">Matrícula</th>' +
                           '<th class="celda-cabecera">Fecha</th>' +
                           '<th class="celda-cabecera">Total (IVA incl.)</th>' +
                           '<th class="celda-cabecera">Estado</th>' +
                           '</tr></thead><tbody>';

                resultado.data.forEach(factura => {
                    html += `<tr class="fila-tabla">
                                <td class="celda-dato texto-resaltado">#${factura.id}</td>
                                <td class="celda-dato">${factura.vehiculo_matricula}</td>
                                <td class="celda-dato">${factura.fecha_emision}</td>
                                <td class="celda-dato texto-resaltado">${factura.total} €</td>
                                <td class="celda-dato"><span class="badge">${factura.estado_pago}</span></td>
                             </tr>`;
                });

                html += '</tbody></table>';
                contenedor.innerHTML = html;
            }
        } catch (error) {
            contenedor.innerHTML = '<div class="texto-modulo">Error al consultar la facturación.</div>';
        }
    }
});