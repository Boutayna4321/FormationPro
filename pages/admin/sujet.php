<?php
session_start();
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
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Arial', sans-serif;
            background: #f8f9fa;
            color: #333;
            line-height: 1.6;
        }

        /* Écran de chargement */
        .loading-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.95);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 9999;
            transition: opacity 0.5s ease;
            flex-direction: column;
            gap: 1.5rem;
        }

        .loading-overlay.hidden {
            opacity: 0;
            pointer-events: none;
        }

        .spinner-gradient {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: conic-gradient(
                from 0deg,
                #2c3e50 0deg,
                #3498db 90deg,
                #2c3e50 180deg,
                #3498db 270deg,
                #2c3e50 360deg
            );
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

        /* Contenu principal */
        .main-content {
            margin-left: 280px;
            padding: 2rem;
            min-height: 100vh;
            background: #f8f9fa;
            transition: all 0.3s ease;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
        }

        .page-title {
            font-size: 2.5rem;
            margin-bottom: 2rem;
            background: linear-gradient(135deg, #2c3e50, #3498db);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            font-weight: bold;
        }

        /* Styles des cartes */
        .card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            margin-bottom: 2rem;
            overflow: hidden;
        }

        .card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
        }

        .card-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 1.5rem;
            font-size: 1.25rem;
            font-weight: 600;
        }

        .card-body {
            padding: 2rem;
        }

        /* Styles des alertes */
        .alert {
            padding: 1rem 1.5rem;
            border-radius: 10px;
            margin-bottom: 1.5rem;
            border: none;
            font-weight: 500;
            position: relative;
        }

        .alert-success {
            background: linear-gradient(135deg, #d4edda, #c3e6cb);
            color: #155724;
            border-left: 4px solid #28a745;
        }

        .alert-danger {
            background: linear-gradient(135deg, #f8d7da, #f5c6cb);
            color: #721c24;
            border-left: 4px solid #dc3545;
        }

        .alert-info {
            background: linear-gradient(135deg, #cce7ff, #b3d9ff);
            color: #0c5460;
            border-left: 4px solid #17a2b8;
        }

        .alert-dismissible {
            padding-right: 3rem;
        }

        .btn-close {
            position: absolute;
            top: 50%;
            right: 1rem;
            transform: translateY(-50%);
            background: none;
            border: none;
            font-size: 1.2rem;
            cursor: pointer;
            opacity: 0.7;
        }

        .btn-close:hover {
            opacity: 1;
        }

        /* Styles des formulaires */
        .form-row {
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
            margin-bottom: 1rem;
        }

        .form-group {
            flex: 1;
            min-width: 200px;
        }

        .form-label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: #2c3e50;
        }

        .form-control, .form-select {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 2px solid #e9ecef;
            border-radius: 8px;
            font-size: 1rem;
            transition: all 0.3s ease;
            background: white;
        }

        .form-control:focus, .form-select:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        /* Styles des boutons */
        .btn {
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 10px;
            font-size: 1rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            text-decoration: none;
        }

        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
        }

        .btn-secondary {
            background: #6c757d;
            color: white;
        }

        .btn-secondary:hover {
            background: #5a6268;
            transform: translateY(-2px);
        }

        .btn-info {
            background: #17a2b8;
            color: white;
        }

        .btn-info:hover {
            background: #138496;
            transform: translateY(-2px);
        }

        .btn-danger {
            background: #dc3545;
            color: white;
        }

        .btn-danger:hover {
            background: #c82333;
            transform: translateY(-2px);
        }

        /* Styles des tableaux */
        .table-responsive {
            overflow-x: auto;
            margin-top: 1rem;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .table th {
            background: linear-gradient(135deg, rgba(102, 126, 234, 0.1) 0%, rgba(118, 75, 162, 0.1) 100%);
            padding: 1rem;
            text-align: left;
            font-weight: 600;
            color: #2c3e50;
            border-bottom: 2px solid #e9ecef;
        }

        .table td {
            padding: 1rem;
            border-bottom: 1px solid #e9ecef;
            vertical-align: middle;
        }

        .table tr:hover {
            background: rgba(102, 126, 234, 0.05);
        }

        /* Liens */
        .back-link {
            display: inline-block;
            margin-bottom: 20px;
            color: #007bff;
            text-decoration: none;
            font-weight: 500;
        }

        .back-link:hover {
            text-decoration: underline;
        }

        /* Breadcrumb */
        .breadcrumb {
            background: #e9ecef;
            padding: 10px 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            font-size: 14px;
        }

        .breadcrumb a {
            color: #007bff;
            text-decoration: none;
        }

        .breadcrumb a:hover {
            text-decoration: underline;
        }

        /* Navigation rapide */
        .quick-nav {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            border-radius: 5px;
            padding: 15px;
            margin-bottom: 20px;
        }

        .quick-nav h4 {
            color: #856404;
            margin-top: 0;
        }

        .quick-nav-items {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }

        .quick-nav-item {
            background: #007bff;
            color: white;
            padding: 8px 12px;
            border-radius: 4px;
            text-decoration: none;
            font-size: 14px;
            transition: all 0.3s;
        }

        .quick-nav-item:hover {
            background: #0056b3;
            transform: translateY(-2px);
        }

        /* Responsive */
        @media (max-width: 768px) {
            .main-content {
                margin-left: 0;
                padding: 1rem;
                padding-top: 4rem;
            }

            .form-row {
                flex-direction: column;
            }

            .form-group {
                min-width: auto;
            }

            .quick-nav-items {
                flex-direction: column;
            }

            .page-title {
                font-size: 2rem;
            }
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
                <a href="dashboard.php" class="back-link">← Retour au tableau de bord</a>
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