<?php
session_start();
require_once 'auth_check.php';
require_once('../../includes/admin_sidebar.php');
require_once 'functions.php';

$conn = db_connect();
$message = '';
$messageType = '';

// Récupérer le cours_id depuis l'URL
$coursId = isset($_GET['cours_id']) ? intval($_GET['cours_id']) : 0;

// Traitement des actions POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'ajouter') {
        $cours_id = intval($_POST['cours_id'] ?? $coursId);
        $ville_id = intval($_POST['ville_id'] ?? 0);
        $formateur_id = intval($_POST['formateur_id'] ?? 0);
        $date_formation = $_POST['date_formation'] ?? '';
        $prix = floatval($_POST['prix'] ?? 0);
        $type_formation = $_POST['type_formation'] ?? '';

        $result = ajouterFormation($conn, $cours_id, $ville_id, $formateur_id, $date_formation, $prix, $type_formation);
        $message = $result['message'];
        $messageType = $result['success'] ? 'success' : 'error';
    }

    if ($action === 'supprimer') {
        $id = intval($_POST['id'] ?? 0);
        if ($id) {
            $result = supprimerFormation($conn, $id);
            $message = $result['message'];
            $messageType = $result['success'] ? 'success' : 'error';
        }
    }
}

// Récupérer les infos du cours
$coursInfo = null;
if ($coursId) {
    $stmt = $conn->prepare("
        SELECT c.*, s.nom_sujet, s.domaine_id, d.nom_domaine
        FROM cours c
        JOIN sujets s ON c.sujet_id = s.id
        JOIN domaines d ON s.domaine_id = d.id
        WHERE c.id = ?
    ");
    $stmt->execute([$coursId]);
    $coursInfo = $stmt->fetch(PDO::FETCH_ASSOC);
}

// Récupérer les formations du cours
$formations = $coursId ? getFormationsByCours($conn, $coursId) : [];

// Données pour les listes déroulantes
$villes = getAllVilles($conn);
$formateurs = getAllFormateurs($conn);
$allCours = getAllCours($conn);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Formations - FormationPro</title>
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

        /* Breadcrumb */
        .breadcrumb {
            display: flex; gap: 8px; align-items: center; margin-bottom: 16px;
            font-size: 0.88rem; color: var(--text-muted); flex-wrap: wrap;
        }
        .breadcrumb a { color: var(--secondary); text-decoration: none; }
        .breadcrumb a:hover { text-decoration: underline; }
        .breadcrumb .sep { color: var(--text-muted); }

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
        .card-body p.empty { text-align: center; color: var(--text-muted); padding: 20px 0; }

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
        .btn-secondary { background: #6c757d; color: var(--white); }
        .btn-secondary:hover { background: #5a6268; transform: translateY(-1px); }
        .btn-sm { padding: 6px 12px; font-size: 0.8rem; }

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

        /* Badge */
        .badge {
            display: inline-block; padding: 4px 10px; border-radius: 20px;
            font-size: 0.78rem; font-weight: 600;
        }
        .badge-presentiel { background: #3498db; color: white; }
        .badge-distanciel { background: #27ae60; color: white; }

        @media (max-width: 992px) {
            .main-content { margin-left: 0; padding: 20px 16px; padding-top: 70px; }
            .page-title { font-size: 26px; }
            .form-row { flex-direction: column; }
        }

        @media (max-width: 768px) {
            .table td, .table th { padding: 6px 8px; font-size: 0.82rem; }
            .page-title { font-size: 22px; }
        }
    </style>
</head>
<body>
    <!-- Écran de chargement -->
    <div class="loading-overlay" id="loadingOverlay">
        <div class="spinner-gradient"></div>
        <div class="loading-text">Chargement en cours...</div>
        <div class="loading-logo">FormationPro</div>
    </div>

    <div class="main-content" id="mainContent" style="opacity: 0;">
        <div style="max-width: 1200px; margin: 0 auto; padding: 32px;">

        <h1 class="page-title">Gestion des Formations</h1>

        <?php if ($coursInfo): ?>
        <div class="breadcrumb">
            <a href="domains.php">Domaines</a>
            <span class="sep">›</span>
            <a href="sujet.php?domaine_id=<?= $coursInfo['domaine_id'] ?>"><?= htmlspecialchars($coursInfo['nom_domaine']) ?></a>
            <span class="sep">›</span>
            <a href="cours.php?sujet_id=<?= $coursInfo['sujet_id'] ?>"><?= htmlspecialchars($coursInfo['nom_sujet']) ?></a>
            <span class="sep">›</span>
            <span><?= htmlspecialchars($coursInfo['nom_cours']) ?></span>
        </div>
        <?php endif; ?>

        <?php if ($message): ?>
            <div class="alert alert-<?= $messageType ?>">
                <?= htmlspecialchars($message) ?>
            </div>
        <?php endif; ?>

        <?php if (!$coursInfo && $coursId): ?>
            <div class="alert alert-danger">Cours introuvable.</div>
        <?php elseif (!$coursId): ?>
            <div class="alert alert-danger">Aucun cours sélectionné.</div>
        <?php else: ?>

        <!-- Ajouter une formation -->
        <div class="card">
            <div class="card-header">
                <i class="fas fa-plus-circle"></i> Ajouter une formation — <?= htmlspecialchars($coursInfo['nom_cours']) ?>
            </div>
            <div class="card-body">
                <form method="POST">
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label" for="cours_id">Cours</label>
                            <select class="form-select" id="cours_id" name="cours_id" required>
                                <?php foreach ($allCours as $c): ?>
                                    <option value="<?= $c['id'] ?>" <?= $c['id'] == $coursId ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($c['nom_domaine']) ?> › <?= htmlspecialchars($c['nom_sujet']) ?> › <?= htmlspecialchars($c['nom_cours']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="ville_id">Ville</label>
                            <select class="form-select" id="ville_id" name="ville_id" required>
                                <option value="">Sélectionner une ville</option>
                                <?php foreach ($villes as $v): ?>
                                    <option value="<?= $v['id'] ?>"><?= htmlspecialchars($v['nom_ville']) ?> (<?= htmlspecialchars($v['nom_pays']) ?>)</option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="formateur_id">Formateur</label>
                            <select class="form-select" id="formateur_id" name="formateur_id" required>
                                <option value="">Sélectionner un formateur</option>
                                <?php foreach ($formateurs as $f): ?>
                                    <option value="<?= $f['id'] ?>"><?= htmlspecialchars($f['nom_formateur']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label" for="date_formation">Date de formation</label>
                            <input class="form-control" type="date" id="date_formation" name="date_formation" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="prix">Prix (€)</label>
                            <input class="form-control" type="number" step="0.01" min="0" id="prix" name="prix" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="type_formation">Type</label>
                            <select class="form-select" id="type_formation" name="type_formation" required>
                                <option value="">Sélectionner un type</option>
                                <option value="presentiel">Présentiel</option>
                                <option value="distanciel">Distanciel</option>
                            </select>
                        </div>
                    </div>
                    <input type="hidden" name="action" value="ajouter">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-plus"></i> Ajouter la formation</button>
                </form>
            </div>
        </div>

        <!-- Liste des formations -->
        <div class="card">
            <div class="card-header">
                <i class="fas fa-list"></i> Liste des formations — <?= htmlspecialchars($coursInfo['nom_cours']) ?>
            </div>
            <div class="card-body">
                <?php if (empty($formations)): ?>
                    <p class="empty">Aucune formation trouvée pour ce cours.</p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Ville</th>
                                    <th>Pays</th>
                                    <th>Formateur</th>
                                    <th>Type</th>
                                    <th>Prix</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($formations as $formation): ?>
                                <tr>
                                    <td><?= date('d/m/Y', strtotime($formation['date_formation'])) ?></td>
                                    <td><?= htmlspecialchars($formation['nom_ville']) ?></td>
                                    <td><?= htmlspecialchars($formation['nom_pays']) ?></td>
                                    <td><?= htmlspecialchars($formation['nom_formateur']) ?></td>
                                    <td>
                                        <span class="badge badge-<?= $formation['type_formation'] ?>">
                                            <?= $formation['type_formation'] === 'presentiel' ? 'Présentiel' : 'Distanciel' ?>
                                        </span>
                                    </td>
                                    <td><?= number_format($formation['prix'], 2) ?> €</td>
                                    <td>
                                        <div style="display: flex; gap: 6px;">
                                            <form method="POST" onsubmit="return confirm('Supprimer cette formation ?');" style="margin:0;">
                                                <input type="hidden" name="id" value="<?= $formation['id'] ?>">
                                                <input type="hidden" name="action" value="supprimer">
                                                <button type="submit" class="btn btn-danger btn-sm"><i class="fas fa-trash"></i></button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <?php endif; ?>

        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(function() {
                document.getElementById('loadingOverlay').classList.add('hidden');
                document.getElementById('mainContent').style.opacity = '1';
            }, 600);
        });
    </script>
</body>
</html>
