<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Wizard - Paso 3</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container">
        <h2>Paso 3: Información adicional</h2>
        <form action="/wizard/step3" method="POST">
            <div class="mb-3">
                <label for="telefono" class="form-label">Teléfono</label>
                <input type="text" name="telefono" class="form-control" id="telefono" required>
                <?php if (isset($validation)): ?>
                    <div class="text-danger"><?= $validation->getError('telefono') ?></div>
                <?php endif; ?>
            </div>
            <button type="submit" class="btn btn-primary">Finalizar</button>
        </form>
    </div>
</body>
</html>
