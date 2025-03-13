<?php
    session_start();
    require_once('../config/config.php'); // Ruta correcta al archivo config.php
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $email = $_POST['email'];
        $password = $_POST['password'];
        // Buscar el usuario en la base de datos
        $sql = "SELECT * FROM cuis.usuarios WHERE email = :email";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        $user = $stmt->fetch();
        // Verificar si el usuario existe y la contraseña es correcta
        if ($user && crypt($password, $user['password']) == $user['password']) {
            // Iniciar sesión y redirigir
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['nombre'];
            $_SESSION['user_role'] = $user['rol'];
            header('Location: home.php'); // Redirigir a la página de inicio
            exit();
        } else {
            // Si las credenciales no son válidas, mostrar un error
            $_SESSION['error'] = 'Correo o contraseña incorrectos.';
            header('Location: ../index.php'); // Volver al formulario de login
            exit();
        }
    }
?>