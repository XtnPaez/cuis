<?php
  if (session_status() === PHP_SESSION_NONE) {
    session_start();
  }
?>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top">
  <div class="container-fluid">
    <a class="navbar-brand" href="#">CUIS</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNavDropdown" aria-controls="navbarNavDropdown" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNavDropdown">
      <!-- Links alineados a la izquierda -->
      <ul class="navbar-nav me-auto mb-2 mb-lg-0">
        <li class="nav-item">
          <a class="nav-link" href="../views/home.php">Inicio</a>
        </li>
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle" href="#" id="navbarDarkDropdownMenuLink" role="button" data-bs-toggle="dropdown" aria-expanded="false">Buscar CUI</a>
          <ul class="dropdown-menu dropdown-menu-dark" aria-labelledby="navbarDarkDropdownMenuLink">
            <li><a class="dropdown-item" href="../views/buscarcuixcodigo.php">Por Código</a></li>
            <li><a class="dropdown-item" href="../views/buscarcuixcallealtura.php">Por Calle y Altura</a></li>
          </ul>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="../views/editar.php">Editar CUI</a>
        </li>
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle" href="#" id="navbarDarkDropdownMenuLink" role="button" data-bs-toggle="dropdown" aria-expanded="false">Alta de CUI</a>
          <ul class="dropdown-menu dropdown-menu-dark" aria-labelledby="navbarDarkDropdownMenuLink">
            <li><a class="dropdown-item" href="../views/altaxdireccion.php">Por dirección</a></li>
            <li><a class="dropdown-item" href="../views/altaxmapa.php">Por Mapa</a></li>
          </ul>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="../views/cuicueanexo.php">CUI -> CUEANEXO</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="../views/actualizaciones.php">Gestión de Tablas de Dominio</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="../views/descargas.php">Descargas</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="../views/usuarios.php">ABM de Usuarios</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="../views/mapa.php">Mapa</a>
        </li>
      </ul>

      <!-- Bienvenida + Logout alineado a la derecha -->
      <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
        <li class="nav-item">
          <a class="nav-link" href="../views/logout.php">Logout <?= htmlspecialchars($_SESSION['user_name'] ?? '') ?></a>
        </li>
      </ul>
    </div>
  </div>
</nav>