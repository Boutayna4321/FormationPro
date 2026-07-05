<?php
session_start();
require_once 'auth_check.php';
require_once('../../includes/admin_sidebar.php');
require_once 'functions.php';

// Vérifier si l'admin est connecté

$conn = db_connect();
$message = '';
$messageType = '';

// Récupérer l'ID du domaine si spécifié dans l'URL
$domaineId = $_GET['domaine_id'] ?? null;
$domaineInfo = null;

// Si un domaine spécifique est demandé, récupérer ses informations
if ($domaineId) {
    $stmt = $conn->prepare("SELECT * FROM domaines WHERE id = ?");
    $stmt->execute([$domaineId]);
    $domaineInfo = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$domaineInfo) {
        header('Location: domaines.php');
        exit();
    }
}

// Traitement des actions POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'ajouter') {
        $nomSujet = $_POST['nom_sujet'] ?? '';
        $domaineIdPost = $_POST['domaine_id'] ?? '';
        
        $result = ajouterSujet($conn, $nomSujet, $domaineIdPost);
        $message = $result['message'];
        $messageType = $result['success'] ? 'success' : 'error';
    }
    
    if ($action === 'modifier') {
        $id = $_POST['id'] ?? '';
        $nomSujet = $_POST['nom_sujet'] ?? '';
        $domaineIdPost = $_POST['domaine_id'] ?? '';
        
        if ($id && $nomSujet && $domaineIdPost) {
            try {
                // Vérifier l'unicité du nom (excluant l'ID actuel)
                if (!checkUniqueName($conn, 'sujets', 'nom_sujet', $nomSujet, $id)) {
                    $message = "Ce nom de sujet existe déjà.";
                    $messageType = 'error';
                } else if (!validateSujetCreation($conn, $domaineIdPost)) {
                    $message = "Le domaine sélectionné n'existe pas.";
                    $messageType = 'error';
                } else {
                    $stmt = $conn->prepare("UPDATE sujets SET nom_sujet = ?, domaine_id = ? WHERE id = ?");
                    $stmt->execute([$nomSujet, $domaineIdPost, $id]);
                    $message = "Sujet modifié avec succès.";
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
            $result = supprimerSujet($conn, $id);
            $message = $result['message'];
            $messageType = $result['success'] ? 'success' : 'error';
        }
    }
}

// Récupérer tous les domaines pour la liste déroulante
$domaines = getAllDomaines($conn);

// Récupérer les sujets selon le contexte
if ($domaineId) {
    $sujets = getSujetsByDomaine($conn, $domaineId);
} else {
    $sujets = getAllSujets($conn);
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Sujets - FormationPro</title>
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
        .alert-info { background: #cce7ff; color: #0c5460; border-left: 4px solid #17a2b8; }
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
        .btn-secondary { background: #6c757d; color: var(--white); }
        .btn-secondary:hover { background: #5a6268; transform: translateY(-1px); }
        .btn-info { background: #17a2b8; color: var(--white); }
        .btn-info:hover { background: #138496; transform: translateY(-1px); }
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

        /* Breadcrumb */
        .breadcrumb {
            background: var(--border); padding: 10px 14px; border-radius: 8px;
            margin-bottom: 16px; font-size: 0.85rem;
        }
        .breadcrumb a { color: var(--accent); text-decoration: none; }
        .breadcrumb a:hover { text-decoration: underline; }

        /* Quick Nav */
        .quick-nav {
            background: #fff3cd; border: 1px solid #ffeaa7;
            border-radius: 8px; padding: 14px; margin-bottom: 16px;
        }
        .quick-nav h4 { color: #856404; margin-bottom: 10px; font-size: 0.95rem; }
        .quick-nav-items { display: flex; flex-wrap: wrap; gap: 8px; }
        .quick-nav-item {
            background: var(--accent); color: var(--white); padding: 6px 12px;
            border-radius: 6px; text-decoration: none; font-size: 0.85rem; transition: all 0.2s;
        }
        .quick-nav-item:hover { background: var(--purple); transform: translateY(-1px); }

        @media (max-width: 992px) {
            .main-content { margin-left: 0; padding: 20px 16px; padding-top: 70px; }
            .page-title { font-size: 26px; }
            .form-row { flex-direction: column; }
            .form-group { min-width: auto; }
            .quick-nav-items { flex-direction: column; }
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
            <?php if ($domaineInfo): ?>
                <div class="breadcrumb">
                    <a href="dashboard.php">Tableau de bord</a> > 
                    <a href="domaines.php">Domaines</a> > 
                    <strong><?= htmlspecialchars($domaineInfo['nom_domaine']) ?></strong>
                </div>
            <?php else: ?>
                
            <?php endif; ?>
            
            <h1 class="page-title">Gestion des Sujets</h1>

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
                    Ajouter un nouveau sujet
                </div>
                <div class="card-body">
                    <form method="POST">
                        <div class="form-row">
                            <?php if (!$domaineInfo): ?>
                            <div class="form-group">
                                <label for="domaine_id" class="form-label">Domaine :</label>
                                <select class="form-select" id="domaine_id" name="domaine_id" required>
                                    <option value="">Sélectionnez un domaine</option>
                                    <?php foreach ($domaines as $domaine): ?>
                                        <option value="<?= $domaine['id'] ?>"><?= htmlspecialchars($domaine['nom_domaine']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <?php else: ?>
                                <input type="hidden" name="domaine_id" value="<?= $domaineInfo['id'] ?>">
                                <div class="form-group">
                                    <label class="form-label">Domaine sélectionné :</label>
                                    <input type="text" class="form-control" value="<?= htmlspecialchars($domaineInfo['nom_domaine']) ?>" readonly style="background-color: #e9ecef;">
                                </div>
                            <?php endif; ?>
                            
                            <div class="form-group">
                                <label for="nom_sujet" class="form-label">Nom du sujet :</label>
                                <input type="text" class="form-control" id="nom_sujet" name="nom_sujet" 
                                       placeholder="Ex: Management de Projet, Programmation Web, Marketing Digital..." required>
                            </div>
                        </div>
                        
                        <input type="hidden" name="action" value="ajouter">
                        <button type="submit" class="btn btn-primary">Ajouter le sujet</button>
                    </form>
                </div>
            </div>

            <!-- Navigation rapide -->
            <?php if (!$domaineInfo && !empty($domaines)): ?>
            <div class="quick-nav">
                <h4>🔍 Navigation rapide par domaine :</h4>
                <div class="quick-nav-items">
                    <?php foreach ($domaines as $domaine): ?>
                        <a href="sujets.php?domaine_id=<?= $domaine['id'] ?>" class="quick-nav-item">
                            <?= htmlspecialchars($domaine['nom_domaine']) ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- Liste des sujets -->
            <div class="card">
                <div class="card-header">
                    Liste des sujets<?= $domaineInfo ? ' du domaine ' . htmlspecialchars($domaineInfo['nom_domaine']) : '' ?>
                </div>
                <div class="card-body">
                    <?php if (empty($sujets)): ?>
                        <div class="alert alert-info">
                            Aucun sujet trouvé<?= $domaineInfo ? ' pour ce domaine' : '' ?>. Ajoutez le premier sujet ci-dessus.
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <?php if (!$domaineInfo): ?>
                                        <th>Domaine</th>
                                        <?php endif; ?>
                                        <th>Nom du sujet</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($sujets as $sujet): ?>
                                    <tr>
                                        <form method="POST">
                                            <td><?= $sujet['id'] ?></td>
                                            <?php if (!$domaineInfo): ?>
                                            <td>
                                                <select class="form-select" name="domaine_id" required>
                                                    <?php foreach ($domaines as $domaine): ?>
                                                        <option value="<?= $domaine['id'] ?>" 
                                                                <?= $domaine['id'] == $sujet['domaine_id'] ? 'selected' : '' ?>>
                                                            <?= htmlspecialchars($domaine['nom_domaine']) ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </td>
                                            <?php else: ?>
                                                <input type="hidden" name="domaine_id" value="<?= $sujet['domaine_id'] ?>">
                                            <?php endif; ?>
                                            <td>
                                                <input type="text" class="form-control" name="nom_sujet" 
                                                       value="<?= htmlspecialchars($sujet['nom_sujet']) ?>" required>
                                            </td>
                                            <td>
                                                <div style="display: flex; gap: 0.5rem;">
                                                    <input type="hidden" name="id" value="<?= $sujet['id'] ?>">
                                                    <button type="submit" name="action" value="modifier" class="btn btn-primary">
                                                        Modifier
                                                    </button>
                                                    <a href="cours.php?sujet_id=<?= $sujet['id'] ?>" 
                                                       class="btn btn-info">
                                                        Voir cours
                                                    </a>
                                                    <button type="submit" name="action" value="supprimer" 
                                                            class="btn btn-danger"
                                                            onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce sujet ? Cette action supprimera aussi tous les cours et formations associés.');">
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

            <?php if ($domaineInfo): ?>
            <div style="margin-top: 20px;">
                <a href="domaines.php" class="back-link">← Retour à la liste des domaines</a>
                <span style="margin: 0 10px;">|</span>
                <a href="sujets.php" class="back-link">Voir tous les sujets</a>
            </div>
            <?php endif; ?>
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