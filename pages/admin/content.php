<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Dashboard</title>
  <link rel="stylesheet" href="css/style.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>

<?php
session_start();
require_once('../../includes/admin_sidebar.php');
?>

<main class="main-content">
  <div class="content-header">
    <h1 class="content-title">Dashboard</h1>
    <p class="content-subtitle">Bienvenue dans l’espace d’administration</p>
  </div>

  <div id="pageContent"></div>
</main>

<script src="js/sidebar.js"></script>
<script>
  document.addEventListener('DOMContentLoaded', function () {
    initNavbar(); // 👈 ton code JS du header ici
  });
</script>
</body>
</html>
