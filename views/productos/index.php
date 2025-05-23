<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="card shadow-lg border-primary">
                <div class="card-body p-4">
                    <div class="text-center mb-4">
                        <i class="bi bi-cart-plus-fill fs-1 text-primary"></i>
                        <h3 class="fw-bold mb-2 p-2 text-bg-primary">Registro de Productos</h3>
                    </div>
                    <form id="FormProductos">
                        <input type="hidden" name="id_producto" id="id_producto">

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="nombre" class="form-label">
                                    Nombre del Producto
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-box-seam"></i></span>
                                    <input type="text" class="form-control" id="nombre" name="nombre"
                                        placeholder="Ej: Papel higiénico" required>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label for="cantidad" class="form-label">
                                    Cantidad
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-123"></i></span>
                                    <input type="number" class="form-control" id="cantidad" name="cantidad"
                                        placeholder="Ej: 3" min="1" required>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label for="id_categoria" class="form-label">
                                    Categoría
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-tags"></i></span>
                                    <select class="form-select" id="id_categoria" name="id_categoria" required>
                                        <option value="">Seleccione una categoría</option>
                                        <!-- jajajajajaja -->
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label for="id_prioridad" class="form-label">
                                    Prioridad
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-flag"></i></span>
                                    <select class="form-select" id="id_prioridad" name="id_prioridad" required>
                                        <option value="">Seleccione una prioridad</option>
                                        <!-- jajajajjajaja -->
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label for="id_cliente" class="form-label">Cliente</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-people-fill"></i></span>
                                    <select class="form-select" id="id_cliente" name="id_cliente" required>
                                        <option value="">Seleccione un cliente</option>
                                        <!-- aquí se insertarán las opciones -->
                                    </select>
                                </div>
                            </div>

                            <div class="row justify-content-center mt-4 g-2">
                                <div class="col-auto">
                                    <button class="btn btn-success px-4" type="submit" id="btnGuardar">
                                        <i class="bi bi-floppy-fill me-1"></i>Guardar
                                    </button>
                                </div>
                                <div class="col-auto">
                                    <button class="btn btn-warning px-4 d-none" type="button" id="btnModificar">
                                        <i class="bi bi-pencil-fill me-1"></i>Modificar
                                    </button>
                                </div>
                                <div class="col-auto">
                                    <button class="btn btn-secondary px-4" type="reset" id="btnLimpiar">
                                        <i class="bi bi-eraser-fill me-1"></i>Limpiar
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="row justify-content-center mt-4">
        <div class="col-lg-10">
            <div class="card shadow-lg border-primary">
                <div class="card-body p-4">
                    <h4 class="text-center fw-bold mb-3 text-primary">
                        <i class="bi bi-cart-fill me-2"></i>Lista de Productos
                    </h4>

                    <ul class="nav nav-tabs mb-3" id="productTabs">
                        <li class="nav-item">
                            <a class="nav-link active" data-bs-toggle="tab" href="#pendientes">
                                <i class="bi bi-clock-fill me-1"></i>Pendientes
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="tab" href="#comprados">
                                <i class="bi bi-check-circle-fill me-1"></i>Comprados
                            </a>
                        </li>
                    </ul>

                    <div class="tab-content">
                        <div class="tab-pane fade show active" id="pendientes">

                            <div class="row mb-3">
                                <div class="col">
                                    <div class="card">
                                        <div class="card-body">
                                            <h5 class="card-title">Estado de Productos</h5>
                                            <p id="resumenProductos" class="mb-0">Cargando...</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="table-responsive">
                                <table class="table table-striped table-hover table-bordered w-100 table-sm"
                                    id="tablaProductos">
                                    <!-- jajajajaja -->
                                </table>
                            </div>
                        </div>
                        <div class="tab-pane fade" id="comprados">
                            <div class="table-responsive">
                                <table class="table table-striped table-hover table-bordered w-100 table-sm"
                                    id="tablaProductosComprados">
                                    <!-- jajajajajaja -->
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<link rel="stylesheet" href="https://cdn.datatables.net/rowgroup/1.3.1/css/rowGroup.bootstrap5.min.css">
<script src="https://cdn.datatables.net/rowgroup/1.3.1/js/dataTables.rowGroup.min.js"></script>

<script src="<?= asset('build/js/productos/index.js') ?>"></script>