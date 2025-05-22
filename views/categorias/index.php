<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="card shadow-lg border-primary">
                <div class="card-body p-4">
                    <div class="text-center mb-4">
                        <i class="bi bi-tags-fill fs-1 text-primary"></i>
                        <h3 class="fw-bold mb-2 p-2 text-bg-primary">Registro de Categorias</h3>
                    </div>
                    <form id="FormCategorias" autocomplete="off">
                        <input type="hidden" name="id_categoria" id="id_categoria">

                        <div class="row g-3">

                            <div class="col-md-6">
                                <label for="nombre" class="form-label">
                                    Categoria
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-tag-fill"></i></span>
                                    <input type="text" class="form-control" id="nombre" name="nombre" placeholder="Ej: Linea blanca" required>
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
                        <i class="bi bi-tags-fill me-2"></i>Categorias Registrados
                    </h4>
                    <div class="table-responsive">
                        <table class="table table-striped table-hover table-bordered w-100 table-sm" id="tablaCategorias">
                            <!-- Ups se genera solo -->
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="<?= asset('build/js/categorias/index.js') ?>"></script>