<?php 
    session_start();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <link href="css/sign-in.css" rel="stylesheet">
</head>
<body class="text-center">
    <main class="form-signin">
        <form action="views/login.php" method="POST">
            <h1 class="h3 mb-3 fw-normal">Inicio de sesión</h1>
            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger" role="alert">
                    <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                </div>
            <?php endif; ?>
            <div class="form-floating">
                <input type="email" class="form-control" id="email" name="email" placeholder="Correo electrónico" required>
                <label for="email">Correo electrónico</label>
            </div>
            <div class="form-floating">
                <input type="password" class="form-control" id="password" name="password" placeholder="Contraseña" required>
                <label for="password">Contraseña</label>
            </div>
            <button class="w-100 btn btn-lg btn-primary" type="submit">Iniciar sesión</button>
        </form>
    </main>
</body>
</html>
