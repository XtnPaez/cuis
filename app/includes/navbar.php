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
        <li class="nav-item">
          <a class="nav-link" href="../views/buscar.php">Buscar CUI</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="../views/editar.php">Editar CUI</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="../views/alta.php">Alta de CUI</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="../views/actualizaciones.php">Gestión de Tablas</a>
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