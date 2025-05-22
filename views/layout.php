<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tienda</title>
    <link rel="shortcut icon" href="<?= asset('images/cit.png') ?>" type="image/x-icon">
    <link rel="stylesheet" href="<?= asset('build/styles.css') ?>">
    <script src="<?= asset('build/js/app.js') ?>"></script>
</head>

<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="/app01_DGCM">
                <img src="<?= asset('images/cit.png') ?>" width="35" alt="cit"> Aplicaciones
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarToggler">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarToggler">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                        <a class="nav-link" href="/app01_DGCM"><i class="bi bi-house-fill me-2"></i>Inicio</a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
                            <i class="bi bi-gear me-2"></i>Clientes
                        </a>
                        <ul class="dropdown-menu dropdown-menu-dark">
                            <li>
                                <a class="dropdown-item" href="/app01_DGCM/guardaBosques">
                                    <i class="bi bi-plus-circle me-2"></i>Crear clientes
                                </a>
                            </li>
                        </ul>
                    </li>

                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
                            <i class="bi bi-tags-fill me-2"></i>Categorías
                        </a>
                        <ul class="dropdown-menu dropdown-menu-dark">
                            <li>
                                <a class="dropdown-item" href="/app01_DGCM/patitos">
                                    <i class="bi bi-tag-plus-fill me-2"></i>Crear categorías
                                </a>
                            </li>
                        </ul>
                    </li>
                </ul>
                <div class="d-grid">
                    <a href="/app01_DGCM" class="btn btn-danger"><i class="bi bi-arrow-bar-left"></i> MENÚ</a>
                </div>
            </div>
        </div>
    </nav>

    <div class="progress fixed-bottom" style="height: 6px;">
        <div class="progress-bar progress-bar-animated bg-danger" id="bar" role="progressbar"></div>
    </div>

    <main class="container-fluid pt-5 mb-4" style="min-height: 85vh;">
        <?= $contenido; ?>
    </main>

    <footer class="container-fluid">
        <div class="row justify-content-center text-center">
            <div class="col-12">
                <p style="font-size: xx-small; font-weight: bold;">
                    Comando de Informática y Tecnología, <?= date('Y') ?> &copy;
                </p>
            </div>
        </div>
    </footer>
</body>

</html>
