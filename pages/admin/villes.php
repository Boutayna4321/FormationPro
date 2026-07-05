<?php
session_start();
require_once 'auth_check.php';
require_once('../../includes/admin_sidebar.php');
require_once 'functions.php';

// Vérifier si l'admin est connecté

$conn = db_connect();
$message = '';
$messageType = '';

// Traitement des actions POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'ajouter') {
        $nomVille = $_POST['nom_ville'] ?? '';
        $paysId = $_POST['pays_id'] ?? '';
        
        $result = ajouterVille($conn, $nomVille, $paysId);
        $message = $result['message'];
        $messageType = $result['success'] ? 'success' : 'error';
    }
    
    if ($action === 'modifier') {
        $id = $_POST['id'] ?? '';
        $nomVille = $_POST['nom_ville'] ?? '';
        $paysId = $_POST['pays_id'] ?? '';
        
        if ($id && $nomVille && $paysId) {
            try {
                // Vérifier que le pays existe
                if (!validateVilleCreation($conn, $paysId)) {
                    $message = "Le pays sélectionné n'existe pas.";
                    $messageType = 'error';
                } else {
                    $stmt = $conn->prepare("UPDATE villes SET nom_ville = ?, pays_id = ? WHERE id = ?");
                    $stmt->execute([$nomVille, $paysId, $id]);
                    $message = "Ville modifiée avec succès.";
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
            $result = supprimerVille($conn, $id);
            $message = $result['message'];
            $messageType = $result['success'] ? 'success' : 'error';
        }
    }
}

// Récupérer toutes les villes avec les informations du pays
$villes = getAllVilles($conn);

// Récupérer tous les pays pour le formulaire
$pays = getAllPays($conn);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Villes</title>
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
        .alert-dismissible { padding-right: 3rem; }
        .btn-close {
            position: absolute; top: 50%; right: 1rem; transform: translateY(-50%);
            background: none; border: none; font-size: 1.2rem; cursor: pointer; opacity: 0.7;
        }
        .btn-close:hover { opacity: 1; }

        /* Form */
        .form-row { display: flex; flex-wrap: wrap; gap: 12px; margin-bottom: 12px; }
        .form-group { flex: 1; min-width: 200px; }
        .form-label { display: block; margin-bottom: 4px; font-weight: 600; color: var(--text); font-size: 0.85rem; }
        .form-control, .form-select {
            width: 100%; padding: 8px 12px; border: 1.5px solid var(--border);
            border-radius: 8px; font-size: 0.9rem; transition: all 0.2s; background: var(--white);
        }
        .form-control:focus, .form-select:focus {
            outline: none; border-color: var(--accent); box-shadow: 0 0 0 3px rgba(102,126,234,0.1);
        }

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
        .table td .form-control {
            padding: 6px 10px; font-size: 0.85rem; border-radius: 6px;
        }

        @media (max-width: 992px) {
            .main-content { margin-left: 0; padding: 20px 16px; padding-top: 70px; }
            .page-title { font-size: 26px; }
            .form-row { flex-direction: column; }
            .form-group { min-width: auto; }
        }

        @media (max-width: 768px) {
            .table td, .table th { padding: 6px 8px; font-size: 0.82rem; }
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
    <div class="main-content" id="mainContent">
        <div class="container">
            
            <h1 class="page-title">Gestion des Villes</h1>

            <?php if ($message): ?>
                <div class="alert alert-<?= $messageType == 'success' ? 'success' : 'danger' ?> alert-dismissible">
                    <strong><?= $messageType == 'success' ? 'Succès!' : 'Erreur!' ?></strong>
                    <?= htmlspecialchars($message) ?>
                    <button type="button" class="btn-close" onclick="this.parentElement.style.display='none'">&times;</button>
                </div>
            <?php endif; ?>

            <!-- Formulaire d'ajout -->
            <div class="card">
                <div class="card-header">
                    Ajouter une nouvelle ville
                </div>
                <div class="card-body">
                    <form method="POST">
                        <div class="form-row">
                            <div class="form-group">
                                <label for="nom_ville" class="form-label">Nom de la ville :</label>
                                <input type="text" class="form-control" id="nom_ville" name="nom_ville" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="pays_id" class="form-label">Pays :</label>
                                <select class="form-select" id="pays_id" name="pays_id" required>
                                    <option value="">Sélectionner un pays</option>
                                    <?php foreach ($pays as $p): ?>
                                        <option value="<?= $p['id'] ?>">
                                            <?= htmlspecialchars($p['nom_pays']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        
                        <input type="hidden" name="action" value="ajouter">
                        <button type="submit" class="btn btn-primary">Ajouter la ville</button>
                    </form>
                </div>
            </div>

            <!-- Liste des villes -->
            <div class="card">
                <div class="card-header">
                    Liste des villes existantes
                </div>
                <div class="card-body">
                    <?php if (empty($villes)): ?>
                        <div class="alert alert-info">
                            Aucune ville trouvée. Ajoutez la première ville ci-dessus.
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Nom de la ville</th>
                                        <th>Pays</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($villes as $ville): ?>
                                    <tr>
                                        <form method="POST">
                                            <td><?= $ville['id'] ?></td>
                                            <td>
                                                <input type="text" class="form-control" name="nom_ville" 
                                                       value="<?= htmlspecialchars($ville['nom_ville']) ?>" 
                                                       required>
                                            </td>
                                            <td>
                                                <select class="form-select" name="pays_id" required>
                                                    <?php foreach ($pays as $p): ?>
                                                        <option value="<?= $p['id'] ?>" 
                                                                <?= ($p['id'] == $ville['pays_id']) ? 'selected' : '' ?>>
                                                            <?= htmlspecialchars($p['nom_pays']) ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </td>
                                            <td>
                                                <div style="display: flex; gap: 0.5rem;">
                                                    <input type="hidden" name="id" value="<?= $ville['id'] ?>">
                                                    <button type="submit" name="action" value="modifier" class="btn btn-primary">
                                                        Modifier
                                                    </button>
                                                    <button type="submit" name="action" value="supprimer" 
                                                            class="btn btn-danger"
                                                            onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette ville ? Cette action supprimera aussi toutes les formations associées.');">
                                                        Supprimer
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
            const mainContent = document.getElementById('mainContent');
            
            // Masquer l'écran de chargement après 1.5 seconde
            setTimeout(() => {
                loadingOverlay.classList.add('hidden');
                mainContent.style.opacity = '1';
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