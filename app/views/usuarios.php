<?php
    // chequeo el inicio de sesion
    session_start();
    // traigo la conexion
    require_once('../config/config.php');
    // Función para actualizar un usuario
    if (isset($_POST['nombre'])) {
        $id = $_POST['id'];
        $nombre = $_POST['nombre'];
        $email = $_POST['email'];
        $rol = $_POST['rol'];
        $password = $_POST['password'] ? password_hash($_POST['password'], PASSWORD_DEFAULT) : null;
        // Construir la consulta SQL según si hay contraseña o no
        $sql = "UPDATE cuis.usuarios SET nombre = :nombre, email = :email, rol = :rol";
        if ($password) {
            $sql .= ", password_hash = :password";
        }
        $sql .= " WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        // Parametrizamos los valores
        $params = [
            'nombre' => $nombre,
            'email' => $email,
            'rol' => $rol,
            'id' => $id
        ];
        // Solo pasamos el parámetro de la contraseña si existe
        if ($password) {
            $params['password'] = $password;
        }
        $stmt->execute($params);
        // Redirigir a la misma página para actualizar el listado de usuarios
        header('Location: usuarios.php');
        exit;
    }
    // Función para dar de baja o alta a un usuario
    if (isset($_POST['accion']) && isset($_POST['id'])) {
        $id = $_POST['id'];
        if ($_POST['accion'] == 'dar_de_baja') {
            $sql = "UPDATE cuis.usuarios SET estado = false WHERE id = :id";
        } elseif ($_POST['accion'] == 'dar_de_alta') {
            $sql = "UPDATE cuis.usuarios SET estado = true WHERE id = :id";
        }
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['id' => $id]);
        // Redirigir a la misma página para actualizar el listado de usuarios
        header('Location: usuarios.php');
        exit;
    }
    // Función para agregar un nuevo usuario
    if (isset($_POST['nuevo_nombre'])) {
    $nuevo_nombre = $_POST['nuevo_nombre'];
    $nuevo_email = $_POST['nuevo_email'];
    $nuevo_rol = $_POST['nuevo_rol'];
    $nuevo_password = password_hash($_POST['nuevo_password'], PASSWORD_DEFAULT);
    $sql = "INSERT INTO cuis.usuarios (nombre, email, rol, password_hash, estado, fecha_creacion) 
            VALUES (:nombre, :email, :rol, :password, true, NOW())";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        'nombre' => $nuevo_nombre,
        'email' => $nuevo_email,
        'rol' => $nuevo_rol,
        'password' => $nuevo_password
    ]);
    // Redirigir a la misma página para actualizar el listado de usuarios
    header('Location: usuarios.php');
    exit;
    }
    // Obtener la lista de usuarios
    $stmt = $pdo->query("SELECT * FROM cuis.usuarios");
    $usuarios = $stmt->fetchAll();
?>
<!doctype html>
<html lang="es">
    <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UEICEE : MAPA : CUIS : ABM de Usuarios</title>
    <link href="../css/bootstrap.min.css" rel="stylesheet">
    <link href="../css/sticky.css" rel="stylesheet">
    <link rel="apple-touch-icon" href="../images/apple-icon-180x180.png" sizes="180x180">
    <link rel="icon" href="../images/favicon-32x32.png" sizes="32x32" type="image/png">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.0/dist/js/bootstrap.bundle.min.js"></script>
    </head>
    <body class="d-flex flex-column min-vh-100">
        <!-- traigo el navbar -->
        <?php include('../includes/navbar.php'); ?>
        <main class="flex-grow-1 container py-5">
            <!-- Botón "Dar de alta usuario" con separación -->
            <div class="mb-4 mt-5">
                <a href="#darDeAltaModal" class="btn btn-success btn-lg w-100" data-bs-toggle="modal" data-bs-target="#darDeAltaModal">
                    Dar de Alta un nuevo Usuario
                </a>
            </div>
            <!-- Tabla de usuarios -->
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Email</th>
                        <th>Rol</th>
                        <th>Fecha de Creación</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($usuarios as $usuario): ?>
                        <tr>
                            <td><?php echo $usuario['id']; ?></td>
                            <td><?php echo $usuario['nombre']; ?></td>
                            <td><?php echo $usuario['email']; ?></td>
                            <td><?php echo $usuario['rol']; ?></td>
                            <td><?php echo $usuario['fecha_creacion']; ?></td>
                            <td>
                                <?php if ($usuario['estado']): ?>
                                    <button class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#modificarModal<?php echo $usuario['id']; ?>">Modificar</button>
                                    <button class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#darDeBajaModal<?php echo $usuario['id']; ?>">Dar de baja</button>
                                <?php else: ?>
                                    <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#darDeAltaModal<?php echo $usuario['id']; ?>">Dar de alta</button>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <!-- Modal de modificar -->
                        <div class="modal fade" id="modificarModal<?php echo $usuario['id']; ?>" tabindex="-1" aria-labelledby="modificarModalLabel" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="modificarModalLabel">Modificar Usuario</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        <form method="POST" action="usuarios.php">
                                            <input type="hidden" name="id" value="<?php echo $usuario['id']; ?>">
                                            <div class="mb-3">
                                                <label for="nombre" class="form-label">Nombre</label>
                                                <input type="text" class="form-control" name="nombre" value="<?php echo $usuario['nombre']; ?>" required>
                                            </div>
                                            <div class="mb-3">
                                                <label for="email" class="form-label">Email</label>
                                                <input type="email" class="form-control" name="email" value="<?php echo $usuario['email']; ?>" required>
                                            </div>
                                            <div class="mb-3">
                                                <label for="rol" class="form-label">Rol</label>
                                                <select class="form-select" name="rol" required>
                                                    <option value="admin" <?php echo $usuario['rol'] == 'admin' ? 'selected' : ''; ?>>Admin</option>
                                                    <option value="editor" <?php echo $usuario['rol'] == 'editor' ? 'selected' : ''; ?>>Editor</option>
                                                    <option value="visualizador" <?php echo $usuario['rol'] == 'visualizador' ? 'selected' : ''; ?>>Visualizador</option>
                                                </select>
                                            </div>
                                            <div class="mb-3">
                                                <label for="password" class="form-label">Nueva Contraseña (opcional)</label>
                                                <input type="password" class="form-control" name="password">
                                            </div>
                                            <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- Modal de dar de baja -->
                        <div class="modal fade" id="darDeBajaModal<?php echo $usuario['id']; ?>" tabindex="-1" aria-labelledby="darDeBajaModalLabel" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="darDeBajaModalLabel">¿Estás seguro de dar de baja a este usuario?</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        <form method="POST" action="usuarios.php">
                                            <input type="hidden" name="id" value="<?php echo $usuario['id']; ?>">
                                            <button type="submit" name="accion" value="dar_de_baja" class="btn btn-danger">Sí, dar de baja</button>
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- Modal de dar de alta a un usuario con baja lógica-->
                        <div class="modal fade" id="darDeAltaModal<?php echo $usuario['id']; ?>" tabindex="-1" aria-labelledby="darDeAltaModalLabel" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="darDeAltaModalLabel">¿Estás seguro de dar de alta a este usuario?</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        <form method="POST" action="usuarios.php">
                                            <input type="hidden" name="id" value="<?php echo $usuario['id']; ?>">
                                            <button type="submit" name="accion" value="dar_de_alta" class="btn btn-success">Sí, dar de alta</button>
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </tbody>
            </table>
    <!-- Modal para dar de alta un nuevo usuario -->
    <div class="modal fade" id="darDeAltaModal" tabindex="-1" aria-labelledby="darDeAltaModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="darDeAltaModalLabel">Agregar Nuevo Usuario</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form method="POST" action="usuarios.php">
                        <div class="mb-3">
                            <label for="nuevo_nombre" class="form-label">Nombre</label>
                            <input type="text" class="form-control" name="nuevo_nombre" required>
                        </div>
                        <div class="mb-3">
                            <label for="nuevo_email" class="form-label">Email</label>
                            <input type="email" class="form-control" name="nuevo_email" required>
                        </div>
                        <div class="mb-3">
                            <label for="nuevo_rol" class="form-label">Rol</label>
                            <select class="form-select" name="nuevo_rol" required>
                                <option value="admin">Admin</option>
                                <option value="editor">Editor</option>
                                <option value="visualizador">Visualizador</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="nuevo_password" class="form-label">Contraseña</label>
                            <input type="password" class="form-control" name="nuevo_password" required>
                        </div>
                        <button type="submit" class="btn btn-success">Dar de alta</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
  </main>
  <?php include('../includes/footer.php'); ?>
</body>
</html>