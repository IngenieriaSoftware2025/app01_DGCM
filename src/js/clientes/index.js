console.log('Hola desde clientes/index.js');

import Swal from "sweetalert2";
import DataTable from "datatables.net-bs5";
import { lenguaje } from "../lenguaje.js";
import { Dropdown } from 'bootstrap'; 

const FormClientes = document.getElementById("FormClientes");
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
    nombres: 'El nombre es obligatorio',
    apellidos: 'El apellido es obligatorio',
    telefono: 'El teléfono es obligatorio',
    correo: 'El correo es obligatorio',
    direccion: 'La dirección es obligatoria'
};


const reglasEspecificas = {
    telefono: {
        test: v => /^\d{8}$/.test(v),
        msg: 'El teléfono debe tener 8 dígitos'
    },
    correo: {
        test: v => /^[\w.-]+@[\w-]+\.\w{2,4}$/.test(v),
        msg: 'El correo no es válido'
    }
};

const validarDatos = (form) => {
    const errores = [];
    const datos = Object.fromEntries(form);

    for (const [campo, mensaje] of Object.entries(camposObligatorios)) {
        if (!datos[campo] || datos[campo].trim() === '') {
            errores.push(mensaje);
        }
    }

    for (const [campo, regla] of Object.entries(reglasEspecificas)) {
        if (datos[campo] && !regla.test(datos[campo])) {
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
    FormClientes.reset();
}

const guardarCliente = async (e) => {
    e.preventDefault();
    estadoBoton(btnGuardar, true);

    try {
        const formData = new FormData(FormClientes);
        const errores = validarDatos(formData);

        if (errores.length) {
            await mostrarAlerta('error', 'Error de validación', errores.join('\n'));
            return;
        }

        const data = await apiFetch('/app01_DGCM/clientes/guardarCliente', {
            method: 'POST',
            body: formData
        });

        await mostrarAlerta('success', 'Éxito', data.mensaje);
        limpiarFormulario();
        await cargarClientes();

    } catch (err) {
        console.error(err);
        await mostrarAlerta('error', 'Error', err.message);
    } finally {
        estadoBoton(btnGuardar, false);
    }
};

const tablaClientes = new DataTable('#tablaClientes', {
    language: lenguaje,
    dom: 'Bfrtip',
    columns: [
        {
            title: '#',
            data: 'id_cliente',
            render: (data, type, row, meta) => meta.row + 1
        },
        { title: 'Nombres', data: 'nombres' },
        { title: 'Apellidos', data: 'apellidos' },
        { title: 'Teléfono', data: 'telefono' },
        { title: 'Correo', data: 'correo' },
        { title: 'Dirección', data: 'direccion' },
        {
            title: 'Acciones',
            data: null,
            render: (data, type, row) => `
                <div class="d-flex justify-content-center">
                    <button class="btn btn-warning btn-editar me-2" data-id="${row.id_cliente}">
                        <i class="bi bi-pencil-fill"></i>
                    </button>
                    <button class="btn btn-danger btn-eliminar" data-id="${row.id_cliente}">
                        <i class="bi bi-trash-fill"></i>
                    </button>
                </div>
            `
        }
    ]
});

const cargarClientes = async () => {
    try {
        const { clientes } = await apiFetch('/app01_DGCM/clientes/obtenerClientes');
        tablaClientes.clear().rows.add(clientes).draw();

        if (!clientes.length) {
            await mostrarAlerta('info', 'Información', 'No hay clientes registrados');
        }

    } catch (err) {
        console.error(err);
        await mostrarAlerta('error', 'Error', err.message);
    }
};


const llenarFormulario = async (event) => {
    const id = event.currentTarget.dataset.id;   

    try {
        const { cliente } = await apiFetch(
            `/app01_DGCM/clientes/buscarCliente?id_cliente=${id}`
        );

        ['id_cliente', 'nombres', 'apellidos', 'telefono', 'correo', 'direccion']
            .forEach(campo => {
                const input = document.getElementById(campo);
                if (input) input.value = cliente[campo] ?? '';
            });

        btnGuardar.classList.add('d-none');
        btnModificar.classList.remove('d-none');

        window.scrollTo({ top: 0, behavior: 'smooth' });

    } catch (err) {
        console.error(err);
        await mostrarAlerta('error', 'Error', err.message);
    }
};


const modificarCliente = async (e) => {
    e.preventDefault();
    estadoBoton(btnModificar, true);    

    try {
        const formData = new FormData(FormClientes);
        const errores = validarDatos(formData);

        if (errores.length) {
            await mostrarAlerta('error', 'Error de validación', errores.join('\n'));
            return;
        }

        const data = await apiFetch('/app01_DGCM/clientes/modificarCliente', {
            method: 'POST',
            body: formData
        });

        await mostrarAlerta('success', 'Éxito', data.mensaje);
        limpiarFormulario();
        await cargarClientes();

        btnGuardar.classList.remove('d-none');
        btnModificar.classList.add('d-none');

    } catch (err) {
        console.error(err);
        await mostrarAlerta('error', 'Error', err.message);
    } finally {
        estadoBoton(btnModificar, false);   
    }
};

const eliminarCliente = async (event) => {
    const btn = event.currentTarget;
    const id = btn.dataset.id;
    const row = tablaClientes.row(btn.closest('tr')).data();  
    const nombreCompleto = `${row.nombres} ${row.apellidos}`;

    const { isConfirmed } = await Swal.fire({
        icon: 'warning',
        title: '¿Estás seguro?',
        html: `Esta acción eliminará al cliente:<br><strong>${nombreCompleto}</strong>`,
        showCancelButton: true,
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d'
    });

    if (!isConfirmed) return;

    const formData = new FormData();
    formData.append('id_cliente', id);

    try {
        await apiFetch('/app01_DGCM/clientes/eliminarCliente', {
            method: 'POST',
            body: formData
        });

        await mostrarAlerta('success', 'Éxito', 'Cliente eliminado correctamente');
        await cargarClientes();  

    } catch (err) {
        console.error(err);
        await mostrarAlerta('error', 'Error', err.message);
    }
};

tablaClientes.on('click', '.btn-editar', llenarFormulario);
tablaClientes.on('click', '.btn-eliminar', eliminarCliente);
btnModificar.addEventListener('click', modificarCliente);
FormClientes.addEventListener('submit', guardarCliente);
btnLimpiar.addEventListener('click', () => {
    FormClientes.reset();
    btnGuardar.classList.remove('d-none');
    btnModificar.classList.add('d-none');
});

document.addEventListener('DOMContentLoaded', cargarClientes);