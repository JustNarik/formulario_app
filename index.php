<?php
// === Configuración de conexión a la base de datos ===
$host = "tcp:erikservidor.database.windows.net,1433";
$db = "formulario_app";
$user = "erik";
$pass = "LovingYouIsEasy01";

// Intentamos conectar a la base de datos usando PDO
try {
    $conn = new PDO("sqlsrv:server=$host;Database=$db", $user, $pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Error de conexión: " . $e->getMessage());
}

// === Procesamiento del formulario ===
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nombre = trim($_POST["nombre"] ?? '');
    $correo = trim($_POST["correo"] ?? '');

    // Validamos si los campos están completos antes de insertar
    if ($nombre !== '' && $correo !== '') {
        $stmt = $conn->prepare("INSERT INTO usuarios (nombre, correo) VALUES (?, ?)");
        $stmt->execute([$nombre, $correo]);
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
                <th>Correo</th>
            </tr>
        </thead>
        <tbody>
            <?php
            // Consultamos los usuarios registrados
            try {
                $query = $conn->query("SELECT id, nombre, correo FROM usuarios");
                $usuarios = $query->fetchAll(PDO::FETCH_ASSOC);

                if ($usuarios) {
                    foreach ($usuarios as $usuario) {
                        echo "<tr>
                                <td>{$usuario['id']}</td>
                                <td>{$usuario['nombre']}</td>
                                <td>{$usuario['correo']}</td>
                              </tr>";
                    }
                } else {
                    echo "<tr><td colspan='3'>No hay registros.</td></tr>";
                }
            } catch (PDOException $e) {
                echo "<tr><td colspan='3'>Error al consultar los datos.</td></tr>";
            }

            // Cerrar conexión al final
            $conn = null;
            ?>
        </tbody>
    </table>
</body>
</html>
