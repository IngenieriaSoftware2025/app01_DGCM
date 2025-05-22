console.log('Hola desde productos/index.js');

import Swal from "sweetalert2";
import DataTable from "datatables.net-bs5";
import { lenguaje } from "../lenguaje.js";
import { Dropdown } from 'bootstrap';

const FormProductos = document.getElementById("FormProductos");
const btnGuardar = document.getElementById("btnGuardar");
const btnModificar = document.getElementById("btnModificar");
const btnLimpiar = document.getElementById("btnLimpiar");
const selectCategoria = document.getElementById("id_categoria");
const selectPrioridad = document.getElementById("id_prioridad");

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

// Cargar selectores
const cargarCategorias = async () => {
    try {
        const { categoria } = await apiFetch('/app01_DGCM/categorias/obtenerCategorias');
        selectCategoria.innerHTML = '<option value="">Seleccione una categoría</option>';
        categoria.forEach(cat => {
            selectCategoria.innerHTML += `<option value="${cat.id_categoria}">${cat.nombre}</option>`;
        });
    } catch (err) {
        console.error('Error al cargar categorías:', err);
    }
};

const cargarPrioridades = async () => {
    try {
        const { prioridades } = await apiFetch('/app01_DGCM/prioridades/obtenerPrioridades');
        selectPrioridad.innerHTML = '<option value="">Seleccione una prioridad</option>';
        prioridades.forEach(pri => {
            selectPrioridad.innerHTML += `<option value="${pri.id_prioridad}">${pri.nombre}</option>`;
        });
    } catch (err) {
        console.error('Error al cargar prioridades:', err);
    }
};

// Reglas
const camposObligatorios = {
    nombre: 'El nombre del producto es obligatorio',
    cantidad: 'La cantidad es obligatoria',
    id_categoria: 'La categoría es obligatoria',
    id_prioridad: 'La prioridad es obligatoria'
};

const reglasEspecificas = {
    nombre: {
        evaluar: v => v.length >= 3 && v.length <= 100,
        msg: 'El nombre debe tener entre 3 y 100 caracteres'
    },
    cantidad: {
        evaluar: v => v > 0 && Number.isInteger(Number(v)),
        msg: 'La cantidad debe ser un número entero positivo'
    }
};

const validarDatos = (formData) => {
    const errores = [];
    const datos = Object.fromEntries(formData);

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
    FormProductos.reset();
}

// CRUD
const guardarProducto = async (e) => {
    e.preventDefault();
    estadoBoton(btnGuardar, true);

    try {
        const formData = new FormData(FormProductos);
        const errores = validarDatos(formData);

        if (errores.length) {
            await mostrarAlerta('error', 'Error de validación', errores.join('\n'));
            return;
        }

        const data = await apiFetch('/app01_DGCM/productos/guardarProducto', {
            method: 'POST',
            body: formData
        });

        await mostrarAlerta('success', 'Éxito', data.mensaje);
        limpiarFormulario();
        await cargarProductos();

    } catch (err) {
        console.error(err);
        await mostrarAlerta('error', 'Error', err.message);
    } finally {
        estadoBoton(btnGuardar, false);
    }
};

const tablaProductos = new DataTable('#tablaProductos', {
    language: lenguaje,
    dom: 'Bfrtip',
    order: [[2, 'asc'], [3, 'desc']], // Ordenar por categoría y luego por prioridad
    columns: [
        {
            title: '#',
            data: 'id_producto',
            render: (data, type, row, meta) => meta.row + 1
        },
        { title: 'Nombre', data: 'nombre' },
        { title: 'Cantidad', data: 'cantidad' },
        { title: 'Categoría', data: 'categoria_nombre' },
        { title: 'Prioridad', data: 'prioridad_nombre' },
        {
            title: 'Estado',
            data: 'comprado',
            render: (data) => `
                <span class="badge ${data ? 'bg-success' : 'bg-warning'}">
                    ${data ? 'Comprado' : 'Pendiente'}
                </span>
            `
        },
        {
            title: 'Acciones',
            data: null,
            render: (data, type, row) => `
                <div class="d-flex justify-content-center">
                    <button class="btn btn-warning btn-editar me-2" data-id="${row.id_producto}">
                        <i class="bi bi-pencil-fill"></i>
                    </button>
                    <button class="btn btn-danger btn-eliminar me-2" data-id="${row.id_producto}">
                        <i class="bi bi-trash-fill"></i>
                    </button>
                    <button class="btn ${row.comprado ? 'btn-secondary' : 'btn-success'} btn-comprado" 
                            data-id="${row.id_producto}" data-estado="${row.comprado}">
                        <i class="bi ${row.comprado ? 'bi-x-lg' : 'bi-check-lg'}"></i>
                    </button>
                </div>
            `
        }
    ],
    rowCallback: function (row, data) {
        if (data.comprado) {
            row.classList.add('text-decoration-line-through', 'text-muted');
        }
    }
});

const cargarProductos = async () => {
    try {
        const { productos } = await apiFetch('/app01_DGCM/productos/obtenerProductos');
        tablaProductos.clear().rows.add(productos).draw();

        if (!productos.length) {
            await mostrarAlerta('info', 'Información', 'No hay productos registrados');
        }

    } catch (err) {
        console.error(err);
        await mostrarAlerta('error', 'Error', err.message);
    }
};

const llenarFormulario = async (event) => {
    const id = event.currentTarget.dataset.id;

    try {
        const { producto } = await apiFetch(
            `/app01_DGCM/productos/buscarProducto?id_producto=${id}`
        );

        ['id_producto', 'nombre', 'cantidad', 'id_categoria', 'id_prioridad']
            .forEach(campo => {
                const input = document.getElementById(campo);
                if (input) input.value = producto[campo] ?? '';
            });

        btnGuardar.classList.add('d-none');
        btnModificar.classList.remove('d-none');

    } catch (err) {
        console.error(err);
        await mostrarAlerta('error', 'Error', err.message);
    }
};

const modificarProducto = async (e) => {
    e.preventDefault();
    estadoBoton(btnModificar, true);

    try {
        const formData = new FormData(FormProductos);
        const errores = validarDatos(formData);

        if (errores.length) {
            await mostrarAlerta('error', 'Error de validación', errores.join('\n'));
            return;
        }

        const data = await apiFetch('/app01_DGCM/productos/modificarProducto', {
            method: 'POST',
            body: formData
        });

        await mostrarAlerta('success', 'Éxito', data.mensaje);
        limpiarFormulario();
        await cargarProductos();

        btnGuardar.classList.remove('d-none');
        btnModificar.classList.add('d-none');

    } catch (err) {
        console.error(err);
        await mostrarAlerta('error', 'Error', err.message);
    } finally {
        estadoBoton(btnModificar, false);
    }
};

const eliminarProducto = async (event) => {
    const btn = event.currentTarget;
    const id = btn.dataset.id;
    const row = tablaProductos.row(btn.closest('tr')).data();
    const nombre = row.nombre;

    const { isConfirmed } = await Swal.fire({
        icon: 'warning',
        title: '¿Estás seguro?',
        html: `Esta acción eliminará el producto:<br><strong>${nombre}</strong>`,
        showCancelButton: true,
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d'
    });

    if (!isConfirmed) return;

    const formData = new FormData();
    formData.append('id_producto', id);

    try {
        await apiFetch('/app01_DGCM/productos/eliminarProducto', {
            method: 'POST',
            body: formData
        });

        await mostrarAlerta('success', 'Éxito', 'Producto eliminado correctamente');
        await cargarProductos();

    } catch (err) {
        console.error(err);
        await mostrarAlerta('error', 'Error', err.message);
    }
};

const marcarComprado = async (event) => {
    const btn = event.currentTarget;
    const id = btn.dataset.id;
    const estadoActual = btn.dataset.estado === "1";
    const nuevoEstado = !estadoActual;

    const formData = new FormData();
    formData.append('id_producto', id);
    formData.append('comprado', nuevoEstado ? 1 : 0);

    try {
        await apiFetch('/app01_DGCM/productos/marcarComprado', {
            method: 'POST',
            body: formData
        });

        await cargarProductos();

    } catch (err) {
        console.error(err);
        await mostrarAlerta('error', 'Error', err.message);
    }
};

// Listeners
tablaProductos.on('click', '.btn-editar', llenarFormulario);
tablaProductos.on('click', '.btn-eliminar', eliminarProducto);
tablaProductos.on('click', '.btn-comprado', marcarComprado);
btnModificar.addEventListener('click', modificarProducto);
FormProductos.addEventListener('submit', guardarProducto);
btnLimpiar.addEventListener('click', () => {
    FormProductos.reset();
    btnGuardar.classList.remove('d-none');
    btnModificar.classList.add('d-none');
});

// Inicialización
document.addEventListener('DOMContentLoaded', async () => {
    await Promise.all([
        cargarCategorias(),
        cargarPrioridades(),
        cargarProductos()
    ]);
});