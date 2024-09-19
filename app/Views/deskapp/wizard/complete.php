<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Wizard - Completado</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container">
        <h2>¡Proceso completado!</h2>
        <p>Gracias por completar el formulario.</p>

        <h3>Datos proporcionados:</h3>
        <p><strong>Nombre:</strong> <?= esc($step1['nombre']) ?></p>
        <p><strong>Email:</strong> <?= esc($step2['email']) ?></p>
        <p><strong>Teléfono:</strong> <?= esc($step3['telefono']) ?></p>
    </div>
</body>
</html>
