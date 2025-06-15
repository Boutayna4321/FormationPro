<?php
session_start();
require_once('../../includes/admin_sidebar.php');
require_once 'functions.php';

$conn = db_connect();
$message = '';
$messageType = '';

$sujetId = $_GET['sujet_id'] ?? null;
$sujetInfo = null;

if ($sujetId) {
    $stmt = $conn->prepare("
        SELECT s.*, d.nom_domaine 
        FROM sujets s 
        JOIN domaines d ON s.domaine_id = d.id 
        WHERE s.id = ?
    ");
    $stmt->execute([$sujetId]);
    $sujetInfo = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$sujetInfo) {
        header('Location: sujets.php');
        exit();
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'ajouter') {
        $nomCours = $_POST['nom_cours'] ?? '';
        $description = $_POST['description'] ?? '';
        $sujetIdPost = $_POST['sujet_id'] ?? '';

        $result = ajouterCours($conn, $nomCours, $description, $sujetIdPost);
        $message = $result['message'];
        $messageType = $result['success'] ? 'success' : 'error';
    }

    if ($action === 'modifier') {
        $id = $_POST['id'] ?? '';
        $nomCours = $_POST['nom_cours'] ?? '';
        $description = $_POST['description'] ?? '';
        $sujetIdPost = $_POST['sujet_id'] ?? '';

        if ($id && $nomCours && $sujetIdPost) {
            try {
                if (!checkUniqueName($conn, 'cours', 'nom_cours', $nomCours, $id)) {
                    $message = "Ce nom de cours existe déjà.";
                    $messageType = 'error';
                } else if (!validateCoursCreation($conn, $sujetIdPost)) {
                    $message = "Le sujet sélectionné n'existe pas.";
                    $messageType = 'error';
                } else {
                    $stmt = $conn->prepare("UPDATE cours SET nom_cours = ?, description = ?, sujet_id = ? WHERE id = ?");
                    $stmt->execute([$nomCours, $description, $sujetIdPost, $id]);
                    $message = "Cours modifié avec succès.";
                    $messageType = 'success';
                }
            } catch (PDOException $e) {
                $message = "Erreur lors de la modification : " . $e->getMessage();
                $messageType = 'error';
            }
        }
    }

    if ($action === 'supprimer') {
        $id = $_POST['id'] ?? '';
        if ($id) {
            $result = supprimerCours($conn, $id);
            $message = $result['message'];
            $messageType = $result['success'] ? 'success' : 'error';
        }
    }
}

$sujets = getAllSujets($conn);
$cours = $sujetId ? getCoursBySujet($conn, $sujetId) : getAllCours($conn);
?>

 <style> 
/* Style CSS pour harmoniser le backend avec le frontend */

/* RESET */


body {
  font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
  background-color: #f8f9fa;
  color: #333;
  line-height: 1.6;
}

.container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 0 20px;
}

.header {
  background: linear-gradient(135deg, #2c3e50, #3498db);
  color: white;
  padding: 2rem 0;
  text-align: center;
  margin-bottom: 2rem;
}

.header h1 {
  font-size: 2.5rem;
  margin-bottom: 0.5rem;
}

.header p {
  font-size: 1.2rem;
  opacity: 0.9;
}

.filters {
  background: white;
  padding: 2rem;
  border-radius: 10px;
  box-shadow: 0 5px 15px rgba(0,0,0,0.1);
  margin-bottom: 2rem;
}

.filters h2 {
  text-align: center;
  margin-bottom: 1.5rem;
  color: #2c3e50;
}

.filter-group {
  margin-bottom: 1.5rem;
}

.filter-group label {
  display: block;
  font-weight: 500;
  margin-bottom: 0.5rem;
  color: #2c3e50;
}

.filter-group input,
.filter-group textarea,
.filter-group select {
  width: 100%;
  padding: 0.75rem;
  border: 2px solid #e0e0e0;
  border-radius: 5px;
  font-size: 1rem;
}

.filter-group input:focus,
.filter-group select:focus,
.filter-group textarea:focus {
  outline: none;
  border-color: #3498db;
}

button,
.btn {
  padding: 0.6rem 1.2rem;
  border: none;
  border-radius: 6px;
  font-size: 0.9rem;
  font-weight: 500;
  cursor: pointer;
  text-align: center;
  transition: all 0.2s ease-in-out;
  display: inline-flex;
  align-items: center;
  gap: 5px;
}

.btn-primary {
  background: #3498db;
  color: white;
}

.btn-primary:hover {
  background: #2980b9;
}

.btn-danger {
  background: #e74c3c;
  color: white;
}

.btn-danger:hover {
  background: #c0392b;
}

.btn-success {
  background: #2ecc71;
  color: white;
}

.btn-success:hover {
  background: #27ae60;
}

.message {
  padding: 15px;
  border-radius: 5px;
  margin-bottom: 1rem;
  text-align: center;
}

.success {
  background-color: #d4edda;
  color: #155724;
  border: 1px solid #c3e6cb;
}

.error {
  background-color: #f8d7da;
  color: #721c24;
  border: 1px solid #f5c6cb;
}

/* Table améliorée */
table {
  width: 100%;
  border-collapse: separate;
  border-spacing: 0;
  background: white;
  border-radius: 12px;
  overflow: hidden;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
  margin-bottom: 2rem;
}

th, td {
  padding: 14px 16px;
  font-size: 0.95rem;
  border-bottom: 1px solid #e0e0e0;
  vertical-align: middle;
}

th {
  background-color: #ecf0f1;
  color: #2c3e50;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

tr:nth-child(even) {
  background-color: #f8f9fc;
}

tr:hover {
  background-color: #eef5fb;
}

.actions {
  display: flex;
  justify-content: flex-start;
  gap: 8px;
}

.actions .btn {
  padding: 6px 10px;
  font-size: 0.85rem;
  border-radius: 4px;
}

/* Loading Spinner */
.loading {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: white;
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 9999;
  transition: opacity 0.5s ease;
  flex-direction: column;
  gap: 1.5rem;
}

.loading.hidden {
  opacity: 0;
  pointer-events: none;
}

.spinner-gradient {
  width: 60px;
  height: 60px;
  border-radius: 50%;
  background: conic-gradient(from 0deg, #2c3e50 0deg, #3498db 90deg, #2c3e50 180deg, #3498db 270deg, #2c3e50 360deg);
  animation: spin 1.2s linear infinite;
  position: relative;
  box-shadow: 0 4px 15px rgba(52, 152, 219, 0.3);
}

.spinner-gradient::before {
  content: '';
  position: absolute;
  top: 4px;
  left: 4px;
  right: 4px;
  bottom: 4px;
  background: white;
  border-radius: 50%;
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

.loading-text {
  font-size: 1.2rem;
  color: #2c3e50;
  font-weight: 500;
  text-align: center;
}

.loading-text::after {
  content: '';
  animation: dots 1.5s steps(4, end) infinite;
}

@keyframes dots {
  0% { content: ''; }
  25% { content: '.'; }
  50% { content: '..'; }
  75% { content: '...'; }
  100% { content: ''; }
}

.loading-logo {
  font-size: 1.5rem;
  font-weight: bold;
  background: linear-gradient(135deg, #2c3e50, #3498db);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
  margin-top: 0.5rem;
}

</style>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Cours</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="style-front.css">
</head>
<body>
    
<div class="loading" id="loading">
    <div class="spinner-gradient"></div>
    <div class="loading-text">Chargement</div>
    <div class="loading-logo">FormationPro</div>
</div>


<div id="pageContent">
<div class="container">
    <?php if ($message): ?>
        <div class="message <?= $messageType ?>"> <?= htmlspecialchars($message) ?> </div>
    <?php endif; ?>

    <div class="filters">
        <h2><i class="fas fa-plus-circle"></i> Ajouter un Cours</h2>
        <form method="POST">
            <?php if (!$sujetInfo): ?>
                <div class="filter-group">
                    <label for="sujet_id">Sujet</label>
                    <select name="sujet_id" required>
                        <option value="">Choisissez un sujet</option>
                        <?php foreach ($sujets as $s): ?>
                            <option value="<?= $s['id'] ?>"> <?= htmlspecialchars($s['nom_domaine']) ?> > <?= htmlspecialchars($s['nom_sujet']) ?> </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            <?php else: ?>
                <input type="hidden" name="sujet_id" value="<?= $sujetInfo['id'] ?>">
            <?php endif; ?>

            <div class="filter-group">
                <label for="nom_cours">Nom du cours</label>
                <input type="text" name="nom_cours" required>
            </div>

            <div class="filter-group">
                <label for="description">Description</label>
                <textarea name="description"></textarea>
            </div>

            <input type="hidden" name="action" value="ajouter">
            <button type="submit" class="btn btn-primary">Ajouter</button>
        </form>
    </div>

    <h2>Liste des Cours</h2>
    <?php if (empty($cours)): ?>
        <p>Aucun cours trouvé.</p>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <?php if (!$sujetInfo): ?><th>Sujet</th><?php endif; ?>
                    <th>Nom</th>
                    <th>Description</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($cours as $coursItem): ?>
                    <tr>
                        <form method="POST">
                            <td><?= $coursItem['id'] ?></td>
                            <?php if (!$sujetInfo): ?>
                                <td>
                                    <select name="sujet_id" required>
                                        <?php foreach ($sujets as $s): ?>
                                            <option value="<?= $s['id'] ?>" <?= $s['id'] == $coursItem['sujet_id'] ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($s['nom_domaine']) ?> > <?= htmlspecialchars($s['nom_sujet']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </td>
                            <?php else: ?>
                                <input type="hidden" name="sujet_id" value="<?= $coursItem['sujet_id'] ?>">
                            <?php endif; ?>
                            <td><input type="text" name="nom_cours" value="<?= htmlspecialchars($coursItem['nom_cours']) ?>" required></td>
                            <td><textarea name="description"><?= htmlspecialchars($coursItem['description']) ?></textarea></td>
                            <td>
                                <input type="hidden" name="id" value="<?= $coursItem['id'] ?>">
                                <button type="submit" name="action" value="modifier" class="btn btn-primary"><i class="fas fa-edit"></i></button>
                                <a href="formations.php?cours_id=<?= $coursItem['id'] ?>" class="btn btn-success"><i class="fas fa-eye"></i></a>
                                <button type="submit" name="action" value="supprimer" class="btn btn-danger" onclick="return confirm('Supprimer ce cours ?')">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </form>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>
</div>
<script>
    window.addEventListener('load', function () {
        document.getElementById('loading').classList.add('hidden');
    });
</script>
</body>
</html>
