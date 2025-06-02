<?php
// === Configuración de conexión a la base de datos SII ===
$host = "tcp:erikservidor.database.windows.net,1433";
$db = "formulario_app";
$user = "erikservidor";
$pass = "LovingYouIsEasy01";

// Intentamos conectar a la base de datos usando PDO
try {
    $conn = new PDO("sqlsrv:server=$host;Database=$db", $user, $pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Error de conexión: " . $e->getMessage());
}

// === Procesamiento del formulario ===
$error = '';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Obtener valores sin espacios
    $nombreCrudo = trim($_POST["nombre"] ?? '');
    $correoCrudo = trim($_POST["correo"] ?? '');

    // Quitar etiquetas HTML
    $nombre = strip_tags($nombreCrudo);

    // Eliminar caracteres no alfanuméricos ni letras acentuadas ni espacios
    $nombre = preg_replace("/[^a-zA-Z0-9áéíóúÁÉÍÓÚñÑ\s]/u", "", $nombre);

    // Sanear el correo
    $correo = filter_var($correoCrudo, FILTER_SANITIZE_EMAIL);

    // Bloquear palabras peligrosas (inyección de scripts)
    if (preg_match('/<script|alert|onerror|onload|<|>/', $nombre)) {
        $error = "Contenido no permitido en el campo nombre.";
    } elseif ($nombre !== '' && $correo !== '') {
        // Insertar si todo es válido
        $stmt = $conn->prepare("INSERT INTO usuarios (nombre, correo) VALUES (?, ?)");
        $stmt->execute([$nombre, $correo]);
    } else {
        $error = "Por favor completa todos los campos correctamente.";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Formulario con SQL Server (PDO)</title>
</head>
<body>
    <h2>Formulario de Registro</h2>

    <?php if ($error): ?>
        <p style="color:red;"><?php echo htmlspecialchars($error); ?></p>
    <?php endif; ?>

    <form method="POST" action="">
        <label for="nombre">Nombre:</label><br>
        <input type="text" id="nombre" name="nombre" required><br><br>

        <label for="correo">Correo:</label><br>
        <input type="email" id="correo" name="correo" required><br><br>

        <input type="submit" value="Guardar">
    </form>

    <h2>Usuarios Registrados</h2>
    <table border="1" cellpadding="5">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nombre</th>
                <th>Correo Electronico</th>
            </tr>
        </thead>
        <tbody>
            <?php
            // Consultar usuarios registrados
            try {
                $query = $conn->query("SELECT id, nombre, correo FROM usuarios");
                $usuarios = $query->fetchAll(PDO::FETCH_ASSOC);

                if ($usuarios) {
                    foreach ($usuarios as $usuario) {
                        echo "<tr>
                                <td>" . htmlspecialchars($usuario['id']) . "</td>
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

            // Cerrar conexión
            $conn = null;
            ?>
        </tbody>
    </table>
</body>
</html>
