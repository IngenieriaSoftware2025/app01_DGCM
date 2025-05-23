console.log('Hola desde prioridades/index.js');

import Swal from "sweetalert2";
import DataTable from "datatables.net-bs5";
import { lenguaje } from "../lenguaje.js";
import { Dropdown } from 'bootstrap'; 

const FormPrioridades = document.getElementById("FormPrioridades");
const btnGuardar = document.getElementById("btnGuardar");
const btnModificar = document.getElementById("btnModificar");
const btnLimpiar = document.getElementById("btnLimpiar");

// Helpers 
const estadoBoton = (btn, disabled) => {
    if (btn) {
        btn.disabled = disabled;
    }
}

const apiFetch = async (url, { method = 'GET', body = null } = {}) => {
    const resp = await fetch(url, {
        method,
        body,
        headers: { 'Accept': 'application/json' }
    });

    const raw = await resp.text();
    if (!raw.trim()) throw new Error('Respuesta vacía del servidor');

    let data;
    try { data = JSON.parse(raw); }
    catch { throw new Error('La respuesta no es JSON válido'); }

    if (data.tipo !== 'success') {
        const msg = data.mensaje || 'Error desconocido';
        throw new Error(msg);
    }
    return data;
};

// Reglas
const camposObligatorios = {
    nombre: 'El nombre de la prioridad es obligatorio'
};

const reglasEspecificas = {
    nombre: {
        evaluar: v => v.length >= 3 && v.length <= 50,
        msg: 'El nombre debe tener entre 3 y 50 caracteres'
    }
};

const validarDatos = (formData) => {
    const errores = [];
    const datos = Object.fromEntries(formData);
    console.log('Datos a validar:', datos);

    for (const [campo, mensaje] of Object.entries(camposObligatorios)) {
        if (!datos[campo] || datos[campo].trim() === '') {
            errores.push(mensaje);
        }
    }

    for (const [campo, regla] of Object.entries(reglasEspecificas)) {
        if (datos[campo] && !regla.evaluar(datos[campo])) {
            errores.push(regla.msg);
        }
    }

    return errores;
};

const mostrarAlerta = async (tipo, titulo, mensaje) => {
    return await Swal.fire({
        icon: tipo,
        title: titulo,
        text: mensaje,
        confirmButtonText: 'Aceptar'
    });
}

const limpiarFormulario = () => {
    FormPrioridades.reset();
}

const guardarPrioridad = async (e) => {
    e.preventDefault();
    estadoBoton(btnGuardar, true);

    try {
        const formData = new FormData(FormPrioridades);
        const errores = validarDatos(formData);

        if (errores.length) {
            await mostrarAlerta('error', 'Error de validación', errores.join('\n'));
            return;
        }

        const data = await apiFetch('/app01_DGCM/prioridades/guardarPrioridad', {
            method: 'POST',
            body: formData
        });

        await mostrarAlerta('success', 'Éxito', data.mensaje);
        limpiarFormulario();
        await cargarPrioridades();

    } catch (err) {
        console.error(err);
        await mostrarAlerta('error', 'Error', err.message);
    } finally {
        estadoBoton(btnGuardar, false);
    }
};

const tablaPrioridades = new DataTable('#tablaPrioridades', {
    language: lenguaje,
    dom: 'Bfrtip',
    columns: [
        {
            title: '#',
            data: 'id_prioridad',
            render: (data, type, row, meta) => meta.row + 1
        },
        { title: 'Nombre', data: 'nombre' },
        {
            title: 'Acciones',
            data: null,
            render: (data, type, row) => `
                <div class="d-flex justify-content-center">
                    <button class="btn btn-warning btn-editar me-2" data-id="${row.id_prioridad}">
                        <i class="bi bi-pencil-fill"></i>
                    </button>
                    <button class="btn btn-danger btn-eliminar" data-id="${row.id_prioridad}">
                        <i class="bi bi-trash-fill"></i>
                    </button>
                </div>
            `
        }
    ]
});

const cargarPrioridades = async () => {
    try {
        const { prioridades } = await apiFetch('/app01_DGCM/prioridades/obtenerPrioridades');
        tablaPrioridades.clear().rows.add(prioridades).draw();

        if (!prioridades.length) {
            await mostrarAlerta('info', 'Información', 'No hay prioridades registradas');
        }

    } catch (err) {
        console.error(err);
        await mostrarAlerta('error', 'Error', err.message);
    }
};

const llenarFormulario = async (event) => {
    const id = event.currentTarget.dataset.id;   

    try {
        const { prioridad } = await apiFetch(
            `/app01_DGCM/prioridades/buscarPrioridad?id_prioridad=${id}`
        );

        ['id_prioridad', 'nombre']
            .forEach(campo => {
                const input = document.getElementById(campo);
                if (input) input.value = prioridad[campo] ?? '';
            });

        btnGuardar.classList.add('d-none');
        btnModificar.classList.remove('d-none');

        window.scrollTo({ top: 0, behavior: 'smooth' });

    } catch (err) {
        console.error(err);
        await mostrarAlerta('error', 'Error', err.message);
    }
};

const modificarPrioridad = async (e) => {
    e.preventDefault();
    estadoBoton(btnModificar, true);    

    try {
        const formData = new FormData(FormPrioridades);
        const errores = validarDatos(formData);

        if (errores.length) {
            await mostrarAlerta('error', 'Error de validación', errores.join('\n'));
            return;
        }

        const data = await apiFetch('/app01_DGCM/prioridades/modificarPrioridad', {
            method: 'POST',
            body: formData
        });

        await mostrarAlerta('success', 'Éxito', data.mensaje);
        limpiarFormulario();
        await cargarPrioridades();

        btnGuardar.classList.remove('d-none');
        btnModificar.classList.add('d-none');

    } catch (err) {
        console.error(err);
        await mostrarAlerta('error', 'Error', err.message);
    } finally {
        estadoBoton(btnModificar, false);   
    }
};

const eliminarPrioridad = async (event) => {
    const btn = event.currentTarget;
    const id = btn.dataset.id;
    const row = tablaPrioridades.row(btn.closest('tr')).data();  
    const nombre = `${row.nombre}`;

    const { isConfirmed } = await Swal.fire({
        icon: 'warning',
        title: '¿Estás seguro?',
        html: `Esta acción eliminará la prioridad:<br><strong>${nombre}</strong>`,
        showCancelButton: true,
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d'
    });

    if (!isConfirmed) return;

    const formData = new FormData();
    formData.append('id_prioridad', id);

    try {
        await apiFetch('/app01_DGCM/prioridades/eliminarPrioridad', {
            method: 'POST',
            body: formData
        });

        await mostrarAlerta('success', 'Éxito', 'Prioridad eliminada correctamente');
        await cargarPrioridades();  

    } catch (err) {
        console.error(err);
        await mostrarAlerta('error', 'Error', err.message);
    }
};

tablaPrioridades.on('click', '.btn-editar', llenarFormulario);
tablaPrioridades.on('click', '.btn-eliminar', eliminarPrioridad);
btnModificar.addEventListener('click', modificarPrioridad);
FormPrioridades.addEventListener('submit', guardarPrioridad);
btnLimpiar.addEventListener('click', () => {
    FormPrioridades.reset();
    btnGuardar.classList.remove('d-none');
    btnModificar.classList.add('d-none');
});

document.addEventListener('DOMContentLoaded', cargarPrioridades);