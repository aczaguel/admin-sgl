<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Wizard - Paso 2</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container">
        <h2>Paso 2: Información de contacto</h2>
        <form action="/wizard/step2" method="POST">
            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" name="email" class="form-control" id="email" required>
                <?php if (isset($validation)): ?>
                    <div class="text-danger"><?= $validation->getError('email') ?></div>
                <?php endif; ?>
            </div>
            <button type="submit" class="btn btn-primary">Siguiente</button>
        </form>
    </div>
</body>
</html>
