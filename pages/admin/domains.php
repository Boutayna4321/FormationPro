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
        $nomDomaine = $_POST['nom_domaine'] ?? '';
        
        $result = ajouterDomaine($conn, $nomDomaine);
        $message = $result['message'];
        $messageType = $result['success'] ? 'success' : 'error';
    }
    
    if ($action === 'modifier') {
        $id = $_POST['id'] ?? '';
        $nomDomaine = $_POST['nom_domaine'] ?? '';
        
        if ($id && $nomDomaine) {
            try {
                // Vérifier l'unicité du nom (excluant l'ID actuel)
                if (!checkUniqueName($conn, 'domaines', 'nom_domaine', $nomDomaine, $id)) {
                    $message = "Ce nom de domaine existe déjà.";
                    $messageType = 'error';
                } else {
                    $stmt = $conn->prepare("UPDATE domaines SET nom_domaine = ? WHERE id = ?");
                    $stmt->execute([$nomDomaine, $id]);
                    $message = "Domaine modifié avec succès.";
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
            $result = supprimerDomaine($conn, $id);
            $message = $result['message'];
            $messageType = $result['success'] ? 'success' : 'error';
        }
    }
}

// Récupérer tous les domaines
$domaines = getAllDomaines($conn);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Domaines - FormationPro</title>
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

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 32px;
        }

        /* Loading */
        .loading-overlay {
            position: fixed; inset: 0; background: rgba(255,255,255,0.95);
            display: flex; flex-direction: column; align-items: center; justify-content: center;
            z-index: 9999; gap: 20px;
        }
        .spinner {
            width: 56px; height: 56px; border: 5px solid #f0f2f5;
            border-top: 5px solid var(--secondary); border-radius: 50%;
            animation: spin 1s linear infinite;
        }
        @keyframes spin { to { transform: rotate(360deg); } }
        .loading-text { font-size: 1.1rem; color: var(--text); font-weight: 500; }

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

        /* Message */
        .message {
            padding: 12px 16px; border-radius: 10px; margin-bottom: 16px;
            font-weight: 500; font-size: 0.9rem; display: flex; align-items: center;
        }
        .message.success { background: #d4edda; color: #155724; border-left: 4px solid #28a745; }
        .message.error { background: #f8d7da; color: #721c24; border-left: 4px solid #dc3545; }

        /* Form */
        .form-group { margin-bottom: 12px; }
        label { display: block; margin-bottom: 4px; font-weight: 600; color: var(--text); font-size: 0.85rem; }
        input, select {
            width: 100%; padding: 8px 12px; border: 1.5px solid var(--border);
            border-radius: 8px; font-size: 0.9rem; transition: all 0.2s; background: var(--white);
        }
        input:focus, select:focus {
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
        .btn-info { background: #17a2b8; color: var(--white); }
        .btn-info:hover { background: #138496; transform: translateY(-1px); }

        /* Table */
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

        /* Actions */
        .actions { display: flex; gap: 6px; }

        @media (max-width: 992px) {
            .main-content { margin-left: 0; padding: 20px 16px; padding-top: 70px; }
            .page-title { font-size: 26px; }
            .actions { flex-direction: column; gap: 4px; }
        }

        @media (max-width: 768px) {
            .table td, .table th { padding: 6px 8px; font-size: 0.82rem; }
            .container { padding: 16px; }
            .card-header { font-size: 0.9rem; }
        }
    </style>
</head>
<body>
    <!-- Écran de chargement -->
    <div class="loading-overlay" id="loadingOverlay">
        <div class="spinner"></div>
        <div class="loading-text">Chargement en cours...</div>
    </div>

    <div class="main-content" id="mainContent">
        <div class="container">
        
        <h1 class="page-title">Gestion des Domaines</h1>
        
        <?php if ($message): ?>
            <div class="message <?= $messageType ?>">
                <?= htmlspecialchars($message) ?>
            </div>
        <?php endif; ?>
        
        <!-- Carte pour ajouter un domaine -->
        <div class="card">
            <div class="card-header">
                Ajouter un nouveau domaine
            </div>
            <div class="card-body">
                <form method="POST">
                    <div class="form-group">
                        <label for="nom_domaine">Nom du domaine :</label>
                        <input type="text" id="nom_domaine" name="nom_domaine" 
                               placeholder="Ex: Management, Computer Science, Marketing..." required>
                    </div>
                    
                    <input type="hidden" name="action" value="ajouter">
                    <button type="submit" class="btn btn-primary">Ajouter le domaine</button>
                </form>
            </div>
        </div>
        
        <!-- Carte pour la liste des domaines -->
        <div class="card">
            <div class="card-header">
                Liste des domaines existants
            </div>
            <div class="card-body">
                <?php if (empty($domaines)): ?>
                    <p style="text-align: center; color: #6c757d;">Aucun domaine trouvé. Ajoutez le premier domaine ci-dessus.</p>
                <?php else: ?>
                    <div style="overflow-x: auto;">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Nom du domaine</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($domaines as $domaine): ?>
                                <tr>
                                    <form method="POST" style="display: contents;">
                                        <td><?= $domaine['id'] ?></td>
                                        <td>
                                            <input type="text" name="nom_domaine" 
                                                   value="<?= htmlspecialchars($domaine['nom_domaine']) ?>" 
                                                   required style="width: 100%; padding: 8px;">
                                        </td>
                                        <td>
                                            <div class="actions">
                                                <input type="hidden" name="id" value="<?= $domaine['id'] ?>">
                                                <button type="submit" name="action" value="modifier" class="btn btn-primary" style="padding: 8px 12px;">
                                                    Modifier
                                                </button>
                                                <a href="sujets.php?domaine_id=<?= $domaine['id'] ?>" 
                                                   class="btn btn-info" style="text-decoration: none; padding: 8px 12px;">
                                                    Voir sujets
                                                </a>
                                                <button type="submit" name="action" value="supprimer" 
                                                        class="btn btn-danger" style="padding: 8px 12px;"
                                                        onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce domaine ? Cette action supprimera aussi tous les sujets, cours et formations associés.');">
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
        
        <!-- Note informative -->
        <div style="margin-top: 20px; padding: 15px; background: #fff3cd; border-radius: 8px;">
            <h4 style="margin-top: 0; color: #856404;">💡 Conseil :</h4>
            <p style="margin-bottom: 0; color: #856404;">
                Un domaine représente une grande famille de formations. Gardez les noms courts et explicites. 
                Vous pourrez ensuite créer des sujets plus spécifiques dans chaque domaine.
            </p>
        </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(function() {
                document.getElementById('loadingOverlay').style.display = 'none';
                document.getElementById('mainContent').style.opacity = '1';
            }, 1000);
        });
    </script>
</body>
</html>