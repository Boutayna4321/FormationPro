<?php
session_start();
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
        /* Styles généraux */
        body {
            font-family: Arial, sans-serif;
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
            margin-left: 280px; /* Pour la sidebar */
        }
        
        /* Style pour les messages */
        .message {
            padding: 15px;
            margin: 20px 0;
            border-radius: 10px;
            font-weight: 500;
            display: flex;
            align-items: center;
        }
        
        .message.success {
            background-color: #d4edda;
            color: #155724;
            border-left: 5px solid #28a745;
        }
        
        .message.error {
            background-color: #f8d7da;
            color: #721c24;
            border-left: 5px solid #dc3545;
        }
        
        /* Style pour les cartes */
        .card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            margin-bottom: 30px;
            overflow: hidden;
        }
        
        .card-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 15px 20px;
            font-size: 1.2rem;
            font-weight: 600;
        }
        
        .card-body {
            padding: 20px;
        }
        
        /* Style pour les formulaires */
        .form-group {
            margin-bottom: 20px;
        }
        
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #495057;
        }
        
        input, select {
            width: 100%;
            padding: 10px 15px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 16px;
            transition: all 0.3s;
        }
        
        input:focus, select:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.2);
            outline: none;
        }
        
        /* Style pour les boutons */
        .btn {
            display: inline-block;
            padding: 10px 20px;
            border-radius: 8px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s;
            border: none;
            text-align: center;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
        }
        
        .btn-danger {
            background: #dc3545;
            color: white;
        }
        
        .btn-danger:hover {
            background: #c82333;
        }
        
        .btn-info {
            background: #17a2b8;
            color: white;
        }
        
        /* Style pour les tables */
        .table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        
        .table th, .table td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #e9ecef;
        }
        
        .table th {
            background-color: rgba(102, 126, 234, 0.1);
            font-weight: 600;
            color: #2c3e50;
        }
        
        .table tr:hover {
            background-color: #f8f9fa;
        }
        
        /* Style pour les actions */
        .actions {
            display: flex;
            gap: 10px;
        }
        
        /* Style pour le titre */
        .page-title {
            margin-bottom: 30px;
            color: #2c3e50;
            font-size: 2rem;
            background: linear-gradient(135deg, #2c3e50, #3498db);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        /* Style pour le loading */
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
            flex-direction: column;
            gap: 20px;
        }
        
        .spinner {
            width: 60px;
            height: 60px;
            border: 5px solid #f3f3f3;
            border-top: 5px solid #3498db;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        .loading-text {
            font-size: 1.2rem;
            color: #2c3e50;
        }
        
        /* Style responsive */
        @media (max-width: 768px) {
            .container {
                margin-left: 0;
                padding: 15px;
            }
            
            .card-header {
                font-size: 1rem;
            }
            
            .actions {
                flex-direction: column;
                gap: 5px;
            }
        }
    </style>
</head>
<body>
    <!-- Écran de chargement -->
    <div class="loading-overlay" id="loadingOverlay">
        <div class="spinner"></div>
        <div class="loading-text">Chargement en cours...</div>
    </div>

    <div class="container" id="mainContent">
        <a href="dashboard.php" class="btn btn-secondary" style="margin-bottom: 20px;">← Retour au tableau de bord</a>
        
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

    <script>
        // Gestion de l'écran de chargement
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(function() {
                document.getElementById('loadingOverlay').style.display = 'none';
                document.getElementById('mainContent').style.opacity = '1';
            }, 1000);
        });
    </script>
</body>
</html>