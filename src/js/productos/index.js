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
const resumenProductos = document.getElementById("resumenProductos");

// Helpers
const estadoBoton = (btn, disabled) => {
    if (btn) btn.disabled = disabled;
};

const apiFetch = async (url, { method = 'GET', body = null } = {}) => {
    const resp = await fetch(url, { method, body, headers: { 'Accept': 'application/json' } });
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

// Reglas de validación
const camposObligatorios = {
    nombre: 'El nombre del producto es obligatorio',
    cantidad: 'La cantidad es obligatoria',
    id_categoria: 'Debe seleccionar una categoría',
    id_prioridad: 'Debe seleccionar una prioridad'
};
const reglasEspecificas = {
    cantidad: {
        evaluar: v => !isNaN(v) && Number(v) >= 1,
        msg: 'La cantidad debe ser un número mayor o igual a 1'
    }
};

const validarDatos = formData => {
    const errores = [];
    const datos = Object.fromEntries(formData);
    // Obligatorios
    for (const [campo, msg] of Object.entries(camposObligatorios)) {
        if (!datos[campo] || datos[campo].toString().trim() === '') {
            errores.push(msg);
        }
    }
    // Específicas
    for (const [campo, regla] of Object.entries(reglasEspecificas)) {
        if (datos[campo] && !regla.evaluar(datos[campo])) {
            errores.push(regla.msg);
        }
    }
    return errores;
};

const mostrarAlerta = async (icon, title, text) => {
    return await Swal.fire({ icon, title, text, confirmButtonText: 'Aceptar' });
};

const limpiarFormulario = () => {
    FormProductos.reset();
};

// DataTables
const tablaPendientes = new DataTable('#tablaProductos', {
    language: lenguaje,
    dom: 'Bfrtip',
    columns: [
        { title: '#', data: 'id_producto', render: (_, __, ___, meta) => meta.row + 1 },
        { title: 'Producto', data: 'nombre' },
        { title: 'Cantidad', data: 'cantidad' },
        { title: 'Categoría', data: 'categoria_nombre' },
        { title: 'Prioridad', data: 'prioridad_nombre' },
        {
            title: 'Acciones',
            data: null,
            render: row => `
                <div class="d-flex justify-content-center">
                <button class="btn btn-success btn-comprar me-2" data-id="${row.id_producto}" title="Marcar comprado">
                    <i class="bi bi-check-circle-fill"></i>
                </button>
                <button class="btn btn-warning btn-editar me-2" data-id="${row.id_producto}" title="Editar">
                    <i class="bi bi-pencil-fill"></i>
                </button>
                <button class="btn btn-danger btn-eliminar" data-id="${row.id_producto}" title="Eliminar">
                    <i class="bi bi-trash-fill"></i>
                </button>
                </div>`
        }
    ]
});

const tablaComprados = new DataTable('#tablaProductosComprados', {
    language: lenguaje,
    dom: 'Bfrtip',
    columns: [
        { title: '#', data: 'id_producto', render: (_, __, ___, meta) => meta.row + 1 },
        { title: 'Producto', data: 'nombre' },
        { title: 'Cantidad', data: 'cantidad' },
        { title: 'Categoría', data: 'categoria_nombre' },
        { title: 'Prioridad', data: 'prioridad_nombre' },
        {
            title: 'Acciones',
            data: null,
            render: row => `
                <div class="d-flex justify-content-center">
                    <button class="btn btn-secondary btn-pendiente me-2" data-id="${row.id_producto}" title="Marcar como pendiente">
                        <i class="bi bi-arrow-counterclockwise"></i>
                    </button>
                    <button class="btn btn-danger btn-eliminar" data-id="${row.id_producto}" title="Eliminar">
                        <i class="bi bi-trash-fill"></i>
                    </button>
                </div>`
        }
    ]
});

// Carga de selects
const cargarCategorias = async () => {
    const { categoria } = await apiFetch('/app01_DGCM/categorias/obtenerCategorias');
    selectCategoria.innerHTML = `
        <option value="">Seleccione una categoría</option>
        ${categoria.map(c => `<option value="${c.id_categoria}">${c.nombre}</option>`).join('')}
    `;
};
const cargarPrioridades = async () => {
    const { prioridades } = await apiFetch('/app01_DGCM/prioridades/obtenerPrioridades');
    selectPrioridad.innerHTML = `
        <option value="">Seleccione una prioridad</option>
        ${prioridades.map(p => `<option value="${p.id_prioridad}">${p.nombre}</option>`).join('')}
    `;
};

let productosPendientes = [], productosComprados = [];

const cargarProductos = async () => {
    const { productos } = await apiFetch('/app01_DGCM/productos/obtenerProductos');
    productosPendientes = productos.filter(p => p.comprado == 0);
    productosComprados = productos.filter(p => p.comprado == 1);

    tablaPendientes.clear().rows.add(productosPendientes).draw();
    tablaComprados.clear().rows.add(productosComprados).draw();

    resumenProductos.textContent = `
        Pendientes: ${productosPendientes.length} —
        Comprados: ${productosComprados.length}
    `;
};

// Llenar formulario para editar
const llenarFormulario = async event => {
    const id = event.currentTarget.dataset.id;
    const { producto } = await apiFetch(
        `/app01_DGCM/productos/buscarProducto?id_producto=${id}`
    );
    ['id_producto', 'nombre', 'cantidad', 'id_categoria', 'id_prioridad']
        .forEach(f => document.getElementById(f).value = producto[f] ?? '');

    btnGuardar.classList.add('d-none');
    btnModificar.classList.remove('d-none');

    window.scrollTo({ top: 0, behavior: 'smooth' });
};

// Guardar producto
const guardarProducto = async e => {
    e.preventDefault();
    estadoBoton(btnGuardar, true);

    try {
        const formData = new FormData(FormProductos);
        const errores = validarDatos(formData);

        if (errores.length) {
            await mostrarAlerta('error', 'Error de validación', errores.join('\n'));
            return;
        }

        const { mensaje } = await apiFetch('/app01_DGCM/productos/guardarProducto', {
            method: 'POST',
            body: formData
        });

        await mostrarAlerta('success', 'Éxito', mensaje);
        limpiarFormulario();
        await cargarProductos();
    } catch (err) {
        console.error(err);
        await mostrarAlerta('error', 'Error', err.message);
    } finally {
        estadoBoton(btnGuardar, false);
    }
};

// Modificar producto
const modificarProducto = async e => {
    e.preventDefault();
    estadoBoton(btnModificar, true);

    try {
        const formData = new FormData(FormProductos);
        const errores = validarDatos(formData);

        if (errores.length) {
            await mostrarAlerta('error', 'Error de validación', errores.join('\n'));
            return;
        }

        const { mensaje } = await apiFetch('/app01_DGCM/productos/modificarProducto', {
            method: 'POST',
            body: formData
        });

        await mostrarAlerta('success', 'Éxito', mensaje);
        limpiarFormulario();
        btnGuardar.classList.remove('d-none');
        btnModificar.classList.add('d-none');
        await cargarProductos();
    } catch (err) {
        console.error(err);
        await mostrarAlerta('error', 'Error', err.message);
    } finally {
        estadoBoton(btnModificar, false);
    }
};

// Eliminar o marcar comprado
const eliminarProducto = async event => {
    const btn = event.currentTarget;
    const id = btn.dataset.id;
    const fila = btn.closest('table').id === 'tablaProductos'
        ? tablaPendientes.row(btn.closest('tr')).data()
        : tablaComprados.row(btn.closest('tr')).data();

    const texto = fila.comprado == 0
        ? `marcar como comprado: "${fila.nombre}"?`
        : `eliminar definitivamente: "${fila.nombre}"?`;

    const { isConfirmed } = await Swal.fire({
        icon: 'warning',
        title: '¿Estás seguro?',
        html: `¿Deseas ${texto}`,
        showCancelButton: true,
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
    });
    if (!isConfirmed) return;

    const formData = new FormData();
    formData.append('id_producto', id);

    await apiFetch('/app01_DGCM/productos/eliminarProducto', {
        method: 'POST',
        body: formData
    });

    await mostrarAlerta('success', 'Éxito',
        fila.comprado == 0
            ? 'Producto marcado como comprado'
            : 'Producto eliminado correctamente'
    );
    await cargarProductos();
};


const comprarProducto = async e => {
    const id = e.currentTarget.dataset.id;
    const { isConfirmed } = await Swal.fire({
        icon: 'question',
        title: 'Marcar como comprado',
        text: '¿Confirmas que ya compraste este producto?',
        showCancelButton: true,
        confirmButtonText: 'Sí',
        cancelButtonText: 'No'
    });
    if (!isConfirmed) return;

    const fd = new FormData();
    fd.append('id_producto', id);
    fd.append('comprado', 1);

    await apiFetch('/app01_DGCM/productos/modificarProducto', {
        method: 'POST',
        body: fd
    });
    await mostrarAlerta('success', 'Éxito', 'Producto marcado como comprado');
    await cargarProductos();
};

const revertirApendiente = async e => {
    const id = e.currentTarget.dataset.id;
    const { isConfirmed } = await Swal.fire({
        icon: 'question',
        title: 'Marcar como pendiente',
        text: '¿Quieres devolver este producto a pendientes?',
        showCancelButton: true,
        confirmButtonText: 'Sí',
        cancelButtonText: 'No'
    });
    if (!isConfirmed) return;

    const fd = new FormData();
    fd.append('id_producto', id);
    fd.append('comprado', 0);

    await apiFetch('/app01_DGCM/productos/modificarProducto', {
        method: 'POST',
        body: fd
    });
    await mostrarAlerta('success', 'Éxito', 'Producto marcado como pendiente');
    await cargarProductos();
};



// Eventos
document.addEventListener('DOMContentLoaded', async () => {
    await Promise.all([
        cargarCategorias(),
        cargarPrioridades(),
        cargarProductos()
    ]);
    FormProductos.addEventListener('submit', guardarProducto);
    btnModificar.addEventListener('click', modificarProducto);
    btnLimpiar.addEventListener('click', () => {
        limpiarFormulario();
        btnGuardar.classList.remove('d-none');
        btnModificar.classList.add('d-none');
    });
    tablaPendientes.on('click', '.btn-editar', llenarFormulario);
    tablaPendientes.on('click', '.btn-eliminar', eliminarProducto);
    tablaComprados.on('click', '.btn-eliminar', eliminarProducto);

    tablaPendientes.on('click', '.btn-comprar', comprarProducto);
    tablaComprados.on('click', '.btn-pendiente', revertirApendiente);


});
