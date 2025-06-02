<?php
// === Conexión a la base de datos formulario_app ===
$host = "tcp:erikservidor.database.windows.net,1433";
$db = "formulario_app";
$user = "erikservidor";
$pass = "LovingYouIsEasy01";

try {
    $conn = new PDO("sqlsrv:server=$host;Database=$db", $user, $pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Error de conexión: " . $e->getMessage());
}

$mensaje = '';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nombre = trim($_POST["nombre"] ?? '');
    $correo = trim($_POST["correo"] ?? '');

    if ($nombre !== '' && filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        $stmt = $conn->prepare("INSERT INTO usuarios (nombre, correo) VALUES (?, ?)");
        $stmt->execute([$nombre, $correo]);
        $mensaje = "Datos guardados correctamente.";
    } else {
        $mensaje = "Por favor, ingresa un nombre y un correo electrónico válido.";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Formulario de Registro</title>
    <style>
    :root {
        --primary-color: #000000;
        --secondary-color: #0033cc;
        --light-color: #f0f0f0;
        --dark-color: #000000;
        --success-color: #28a745;
        --error-color: #dc3545;
    }

    * {
        box-sizing: border-box;
        margin: 0;
        padding: 0;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }

    body {
        background: linear-gradient(to right, var(--primary-color), var(--secondary-color));
        display: flex;
        flex-direction: column;
        align-items: center;
        padding: 40px 20px;
        min-height: 100vh;
    }

    .form-container {
        background-color: white;
        border-radius: 10px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
        padding: 40px;
        width: 100%;
        max-width: 600px;
        margin-bottom: 40px;
    }

    h1, h2 {
        color: var(--primary-color);
        margin-bottom: 30px;
        text-align: center;
    }

    .form-group {
        margin-bottom: 20px;
    }

    label {
        display: block;
        margin-bottom: 8px;
        color: var(--dark-color);
        font-weight: 600;
    }

    input {
        width: 100%;
        padding: 12px;
        border: 2px solid #ced4da;
        border-radius: 6px;
        font-size: 16px;
    }

    input:focus {
        border-color: var(--secondary-color);
        outline: none;
        box-shadow: 0 0 0 3px rgba(0, 51, 204, 0.2);
    }

    .btn-submit {
        background-color: var(--primary-color);
        color: white;
        border: none;
        padding: 14px;
        font-size: 16px;
        font-weight: 600;
        border-radius: 6px;
        cursor: pointer;
        width: 100%;
        transition: background 0.3s ease;
    }

    .btn-submit:hover {
        background-color: var(--secondary-color);
    }

    .response {
        margin-top: 30px;
        padding: 20px;
        border-radius: 6px;
        background-color: #e0f7f5;
        border-left: 4px solid var(--success-color);
    }

    .error {
        border-left-color: var(--error-color) !important;
        background-color: #fdecea !important;
    }

    .table-container {
        width: 100%;
        overflow-x: auto;
        margin-top: 20px;
        display: flex;
        justify-content: center;
    }

    table {
        border-collapse: collapse;
        width: auto;
        min-width: 800px;
        background: white;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        border-radius: 8px;
        overflow: hidden;
    }

    th, td {
        padding: 16px 24px;
        text-align: left;
        border-bottom: 1px solid #eee;
        white-space: nowrap;
    }

    th {
        background-color: var(--primary-color);
        color: white;
    }

    tr:hover {
        background-color: #f1f1f1;
    }
    </style>
</head>
<body>

    <div class="form-container">
        <h1>Registro de Usuario</h1>

        <?php if ($mensaje): ?>
            <div class="response <?= strpos($mensaje, 'correctamente') !== false ? '' : 'error' ?>">
                <p><?= htmlspecialchars($mensaje) ?></p>
            </div>
        <?php endif; ?>

        <form method="post" action="">
            <div class="form-group">
                <label for="nombre">Nombre(s)</label>
                <input type="text" id="nombre" name="nombre" required>
            </div>
            <div class="form-group">
                <label for="correo">Correo Electrónico</label>
                <input type="email" id="correo" name="correo" required>
            </div>
            <button type="submit" class="btn-submit">Enviar Datos</button>
        </form>
    </div>

    <div class="table-container">
        <div style="padding: 30px; background-color: white; border-radius: 10px;">
            <h2>Usuarios Registrados</h2>
            <table>
                <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>Correo</th>
                </tr>
                <?php
                try {
                    $query = $conn->query("SELECT id, nombre, correo FROM usuarios ORDER BY id DESC");
                    $usuarios = $query->fetchAll(PDO::FETCH_ASSOC);

                    if ($usuarios) {
                        foreach ($usuarios as $usuario) {
                            echo "<tr>
                                    <td>{$usuario['id']}</td>
                                    <td>" . htmlspecialchars($usuario['nombre']) . "</td>
                                    <td>" . htmlspecialchars($usuario['correo']) . "</td>
                                  </tr>";
                        }
                    } else {
                        echo "<tr><td colspan='3'>No hay registros.</td></tr>";
                    }
                } catch (PDOException $e) {
                    echo "<tr><td colspan='3'>Error al consultar los datos.</td></tr>";
                }

                // Cierra conexión
                $conn = null;
                ?>
            </table>
        </div>
    </div>

</body>
</html>
