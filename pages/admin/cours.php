<?php
session_start();
require_once 'auth_check.php';
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

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Cours</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        *, *::before, *::after { margin: 0; padding: 0; box-sizing: border-box; }

        :root {
            --primary: #2c3e50;
            --secondary: #3498db;
            --accent: #667eea;
            --purple: #764ba2;
            --text: #2c3e50;
            --text-muted: #6c757d;
            --bg: #f0f2f5;
            --white: #ffffff;
            --border: #e9ecef;
            --shadow: 0 1px 3px rgba(0,0,0,0.08), 0 1px 2px rgba(0,0,0,0.06);
            --radius: 14px;
        }

        body {
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
            background: var(--bg);
            color: var(--text);
            overflow-x: hidden;
        }

        /* Loading */
        .loading-overlay {
            position: fixed; inset: 0; background: rgba(255,255,255,0.95);
            display: flex; flex-direction: column; align-items: center; justify-content: center;
            z-index: 9999; gap: 1.5rem; transition: opacity 0.5s;
        }
        .loading-overlay.hidden { opacity: 0; pointer-events: none; }
        .spinner-gradient {
            width: 56px; height: 56px; border-radius: 50%;
            background: conic-gradient(from 0deg, #2c3e50, #3498db, #667eea, #764ba2, #2c3e50);
            animation: spin 1.2s linear infinite; position: relative;
            box-shadow: 0 4px 20px rgba(52,152,219,0.2);
        }
        .spinner-gradient::before {
            content: ''; position: absolute; top: 5px; left: 5px; right: 5px; bottom: 5px;
            background: var(--white); border-radius: 50%;
        }
        @keyframes spin { to { transform: rotate(360deg); } }
        .loading-text { font-size: 1.1rem; color: var(--text); font-weight: 500; }
        .loading-logo { font-size: 1.4rem; font-weight: 700; background: linear-gradient(135deg,var(--primary),var(--secondary)); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; }

        /* Page Header */
        .page-title {
            font-size: 32px; font-weight: 700; margin-bottom: 20px;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;
        }

        /* Card */
        .card {
            background: var(--white); border-radius: var(--radius);
            box-shadow: var(--shadow); margin-bottom: 20px; overflow: hidden;
        }
        .card-header {
            padding: 14px 20px; font-size: 1rem; font-weight: 600; color: var(--white);
            background: linear-gradient(135deg, var(--accent), var(--purple));
        }
        .card-body { padding: 20px; }

        /* Alert */
        .alert {
            padding: 12px 16px; border-radius: 10px; margin-bottom: 16px;
            font-weight: 500; font-size: 0.9rem; position: relative;
        }
        .alert-success { background: #d4edda; color: #155724; border-left: 4px solid #28a745; }
        .alert-danger { background: #f8d7da; color: #721c24; border-left: 4px solid #dc3545; }

        /* Form */
        .form-row { display: flex; flex-wrap: wrap; gap: 12px; margin-bottom: 12px; }
        .form-group { flex: 1; min-width: 200px; }
        .form-label { display: block; margin-bottom: 4px; font-weight: 600; color: var(--text); font-size: 0.85rem; }
        .form-control, .form-select, .form-textarea {
            width: 100%; padding: 8px 12px; border: 1.5px solid var(--border);
            border-radius: 8px; font-size: 0.9rem; transition: all 0.2s; background: var(--white);
        }
        .form-control:focus, .form-select:focus, .form-textarea:focus {
            outline: none; border-color: var(--accent); box-shadow: 0 0 0 3px rgba(102,126,234,0.1);
        }
        .form-textarea { min-height: 80px; resize: vertical; }

        /* Buttons */
        .btn {
            padding: 8px 16px; border: none; border-radius: 8px;
            font-size: 0.85rem; font-weight: 500; cursor: pointer;
            display: inline-flex; align-items: center; gap: 6px;
            text-decoration: none; transition: all 0.2s;
        }
        .btn-primary { background: linear-gradient(135deg,var(--accent),var(--purple)); color: var(--white); }
        .btn-primary:hover { transform: translateY(-1px); box-shadow: 0 4px 12px rgba(102,126,234,0.3); }
        .btn-danger { background: #dc3545; color: var(--white); }
        .btn-danger:hover { background: #c82333; transform: translateY(-1px); }
        .btn-success { background: #28a745; color: var(--white); }
        .btn-success:hover { background: #218838; transform: translateY(-1px); }
        .btn-sm { padding: 6px 10px; font-size: 0.8rem; }
        .btn-group { display: flex; gap: 4px; }

        /* Table */
        .table-responsive { overflow-x: auto; }
        .table { width: 100%; border-collapse: collapse; font-size: 0.88rem; }
        .table th {
            padding: 10px 12px; text-align: left; font-weight: 600; color: var(--text);
            background: linear-gradient(135deg, rgba(102,126,234,0.06), rgba(118,75,162,0.06));
            border-bottom: 2px solid var(--border); white-space: nowrap;
        }
        .table td {
            padding: 8px 12px; border-bottom: 1px solid var(--border); vertical-align: middle;
        }
        .table tr:hover { background: rgba(102,126,234,0.03); }
        .table input, .table select, .table textarea {
            width: 100%; padding: 6px 10px; border: 1.5px solid var(--border);
            border-radius: 6px; font-size: 0.85rem;
        }
        .table textarea { min-height: 36px; }

        @media (max-width: 992px) {
            .main-content { margin-left: 0; padding: 20px 16px; padding-top: 70px; }
            .page-title { font-size: 26px; }
            .form-row { flex-direction: column; }
            .form-group { min-width: auto; }
            .btn-group { flex-direction: column; }
        }

        @media (max-width: 768px) {
            .table td, .table th { padding: 6px 8px; font-size: 0.82rem; }
            .table-responsive { font-size: 0.82rem; }
        }
    </style>
</head>
<body>
    <!-- Écran de chargement -->
    <div class="loading-overlay" id="loadingOverlay">
        <div class="spinner-gradient"></div>
        <div class="loading-text">Chargement</div>
        <div class="loading-logo">FormationPro</div>
    </div>

    <!-- Contenu principal -->
    <div class="main-content">
        <div class="container">
            <h1 class="page-title">Gestion des Cours</h1>
            
            <!-- Messages d'état -->
            <?php if ($message): ?>
                <div class="alert alert-<?= $messageType == 'success' ? 'success' : 'danger' ?>">
                    <?= htmlspecialchars($message) ?>
                </div>
            <?php endif; ?>

            <!-- Formulaire d'ajout -->
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-plus-circle me-1"></i>
                    Ajouter un Cours
                </div>
                <div class="card-body">
                    <form method="POST">
                        <?php if (!$sujetInfo): ?>
                            <div class="form-group">
                                <label for="sujet_id" class="form-label">Sujet</label>
                                <select class="form-select" name="sujet_id" required>
                                    <option value="">Choisissez un sujet</option>
                                    <?php foreach ($sujets as $s): ?>
                                        <option value="<?= $s['id'] ?>"><?= htmlspecialchars($s['nom_domaine']) ?> > <?= htmlspecialchars($s['nom_sujet']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        <?php else: ?>
                            <input type="hidden" name="sujet_id" value="<?= $sujetInfo['id'] ?>">
                        <?php endif; ?>

                        <div class="form-group">
                            <label for="nom_cours" class="form-label">Nom du cours</label>
                            <input type="text" class="form-control" name="nom_cours" required>
                        </div>

                        <div class="form-group">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-textarea" name="description"></textarea>
                        </div>

                        <input type="hidden" name="action" value="ajouter">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-plus me-1"></i>Ajouter
                        </button>
                    </form>
                </div>
            </div>

            <!-- Liste des cours -->
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-list me-1"></i>
                    Liste des Cours
                </div>
                <div class="card-body">
                    <?php if (empty($cours)): ?>
                        <p>Aucun cours trouvé.</p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table">
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
                                                        <select class="form-select" name="sujet_id" required>
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
                                                <td><input type="text" class="form-control" name="nom_cours" value="<?= htmlspecialchars($coursItem['nom_cours']) ?>" required></td>
                                                <td><textarea class="form-textarea" name="description"><?= htmlspecialchars($coursItem['description']) ?></textarea></td>
                                                <td>
                                                    <div class="btn-group">
                                                        <input type="hidden" name="id" value="<?= $coursItem['id'] ?>">
                                                        <button type="submit" name="action" value="modifier" class="btn btn-primary btn-sm" title="Modifier">
                                                            <i class="fas fa-edit"></i>
                                                        </button>
                                                        <a href="formations.php?cours_id=<?= $coursItem['id'] ?>" class="btn btn-success btn-sm" title="Voir formations">
                                                            <i class="fas fa-eye"></i>
                                                        </a>
                                                        <button type="submit" name="action" value="supprimer" class="btn btn-danger btn-sm" onclick="return confirm('Supprimer ce cours ?')" title="Supprimer">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </div>
                                                </td>
                                            </form>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Gestion de l'écran de chargement
        document.addEventListener('DOMContentLoaded', function() {
            const loadingOverlay = document.getElementById('loadingOverlay');
            
            // Masquer l'écran de chargement après 1.5 seconde
            setTimeout(() => {
                loadingOverlay.classList.add('hidden');
            }, 1500);
        });

        // Animation des cartes au survol
        document.querySelectorAll('.card').forEach(card => {
            card.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-2px)';
            });
            
            card.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0)';
            });
        });
    </script>
</body>
</html>