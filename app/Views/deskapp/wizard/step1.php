<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Wizard con Pestañas</title>
    <!-- Incluye Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<div class="container mt-5">
    <h2>Wizard con Pestañas</h2>
    <!-- Pestañas del Wizard -->
    <ul class="nav nav-tabs" id="myTab" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="step1-tab" data-bs-toggle="tab" data-bs-target="#step1" type="button" role="tab" aria-controls="step1" aria-selected="true">Paso 1</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="step2-tab" data-bs-toggle="tab" data-bs-target="#step2" type="button" role="tab" aria-controls="step2" aria-selected="false">Paso 2</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="step3-tab" data-bs-toggle="tab" data-bs-target="#step3" type="button" role="tab" aria-controls="step3" aria-selected="false">Paso 3</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="complete-tab" data-bs-toggle="tab" data-bs-target="#complete" type="button" role="tab" aria-controls="complete" aria-selected="false">Finalizar</button>
        </li>
    </ul>

    <!-- Contenido del Wizard -->
    <div class="tab-content" id="myTabContent">
        <!-- Paso 1 -->
        <div class="tab-pane fade show active" id="step1" role="tabpanel" aria-labelledby="step1-tab">
            <form action="/wizard/step1" method="post">
                <!-- Contenido del paso 1 -->
                <div class="mb-3">
                    <label for="name" class="form-label">Nombre</label>
                    <input type="text" class="form-control" id="name" name="name" required>
                </div>
                <button type="button" class="btn btn-primary next-step" data-next="step2-tab">Siguiente</button>
            </form>
        </div>

        <!-- Paso 2 -->
        <div class="tab-pane fade" id="step2" role="tabpanel" aria-labelledby="step2-tab">
            <form action="/wizard/step2" method="post">
                <!-- Contenido del paso 2 -->
                <div class="mb-3">
                    <label for="email" class="form-label">Correo Electrónico</label>
                    <input type="email" class="form-control" id="email" name="email" required>
                </div>
                <button type="button" class="btn btn-secondary prev-step" data-prev="step1-tab">Anterior</button>
                <button type="button" class="btn btn-primary next-step" data-next="step3-tab">Siguiente</button>
            </form>
        </div>

        <!-- Paso 3 -->
        <div class="tab-pane fade" id="step3" role="tabpanel" aria-labelledby="step3-tab">
            <form action="/wizard/step3" method="post">
                <!-- Contenido del paso 3 -->
                <div class="mb-3">
                    <label for="address" class="form-label">Dirección</label>
                    <input type="text" class="form-control" id="address" name="address" required>
                </div>
                <button type="button" class="btn btn-secondary prev-step" data-prev="step2-tab">Anterior</button>
                <button type="button" class="btn btn-primary next-step" data-next="complete-tab">Siguiente</button>
            </form>
        </div>

        <!-- Finalizar -->
        <div class="tab-pane fade" id="complete" role="tabpanel" aria-labelledby="complete-tab">
            <form action="/wizard/complete" method="post">
                <!-- Contenido de finalización -->
                <div class="alert alert-success" role="alert">
                    ¡Todos los pasos completados con éxito!
                </div>
                <button type="button" class="btn btn-secondary prev-step" data-prev="step3-tab">Anterior</button>
                <button type="submit" class="btn btn-success">Finalizar</button>
            </form>
        </div>
    </div>
</div>

<!-- Incluye Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Manejar la navegación entre pasos
    document.querySelectorAll('.next-step').forEach(button => {
        button.addEventListener('click', function() {
            const nextTab = document.getElementById(this.getAttribute('data-next'));
            new bootstrap.Tab(nextTab).show();
        });
    });

    document.querySelectorAll('.prev-step').forEach(button => {
        button.addEventListener('click', function() {
            const prevTab = document.getElementById(this.getAttribute('data-prev'));
            new bootstrap.Tab(prevTab).show();
        });
    });
</script>

</body>
</html>
