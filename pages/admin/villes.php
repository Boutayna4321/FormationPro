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
        body {
            font-family: Arial, sans-serif;
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        .message {
            padding: 10px;
            margin: 10px 0;
            border-radius: 4px;
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
        .form-container {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .form-group {
            margin-bottom: 15px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        input, select {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
        }
        button {
            background: #007bff;
            color: white;
            padding: 8px 16px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        button:hover {
            background: #0056b3;
        }
        .btn-danger {
            background: #dc3545;
        }
        .btn-danger:hover {
            background: #c82333;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background-color: #f8f9fa;
            font-weight: bold;
        }
        tr:hover {
            background-color: #f5f5f5;
        }
        .actions {
            display: flex;
            gap: 5px;
        }
        .back-link {
            display: inline-block;
            margin-bottom: 20px;
            color: #007bff;
            text-decoration: none;
        }
        .back-link:hover {
            text-decoration: underline;
        }
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

        /* Spinner Gradient avec couleurs FormationPro */
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

        /* Animation de rotation */
        @keyframes spin {
            0% { 
                transform: rotate(0deg);
            }
            100% { 
                transform: rotate(360deg);
            }
        }

        /* Texte de chargement */
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

        /* Logo FormationPro sous le spinner */
        .loading-logo {
            font-size: 1.5rem;
            font-weight: bold;
            background: linear-gradient(135deg, #2c3e50, #3498db);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-top: 0.5rem;
        }

        /* Contenu après chargement */
        .demo-content {
            display: none;
            padding: 2rem;
            text-align: center;
            margin-top: 100px;
        }

        .demo-content.show {
            display: block;
        }

        .demo-title {
            font-size: 2.5rem;
            background: linear-gradient(135deg, #2c3e50, #3498db);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 1rem;
        }

        .demo-text {
            font-size: 1.2rem;
            color: #7f8c8d;
            margin-bottom: 2rem;
        }

         .spinner-large {
            width: 80px;
            height: 80px;
        }

        .spinner-large::before {
            top: 6px;
            left: 6px;
            right: 6px;
            bottom: 6px;
        }
    </style>
</head>
<body>
<div class="loading" id="loading">
        <div class="spinner-gradient"></div>
        <div class="loading-text">Chargement</div>
        <div class="loading-logo">FormationPro</div>
    </div>
    </div>

    <a href="dashboard.php" class="back-link">← Retour au tableau de bord</a>
    
    <h1>Gestion des Villes</h1>

    <?php if ($message): ?>
        <div class="message <?= $messageType ?>">
            <?= htmlspecialchars($message) ?>
        </div>
    <?php endif; ?>

    <!-- Formulaire d'ajout -->
    <div class="form-container">
        <h2>Ajouter une nouvelle ville</h2>
        <form method="POST">
            <div class="form-group">
                <label for="nom_ville">Nom de la ville :</label>
                <input type="text" id="nom_ville" name="nom_ville" required>
            </div>
            
            <div class="form-group">
                <label for="pays_id">Pays :</label>
                <select id="pays_id" name="pays_id" required>
                    <option value="">Sélectionner un pays</option>
                    <?php foreach ($pays as $p): ?>
                        <option value="<?= $p['id'] ?>">
                            <?= htmlspecialchars($p['nom_pays']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <input type="hidden" name="action" value="ajouter">
            <button type="submit">Ajouter la ville</button>
        </form>
    </div>

    <!-- Liste des villes -->
    <h2>Liste des villes existantes</h2>
    
    <?php if (empty($villes)): ?>
        <p>Aucune ville trouvée. Ajoutez la première ville ci-dessus.</p>
    <?php else: ?>
        <table>
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
                    <form method="POST" style="display: contents;">
                        <td><?= $ville['id'] ?></td>
                        <td>
                            <input type="text" name="nom_ville" 
                                   value="<?= htmlspecialchars($ville['nom_ville']) ?>" 
                                   required style="width: 200px;">
                        </td>
                        <td>
                            <select name="pays_id" required style="width: 200px;">
                                <?php foreach ($pays as $p): ?>
                                    <option value="<?= $p['id'] ?>" 
                                            <?= ($p['id'] == $ville['pays_id']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($p['nom_pays']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                        <td>
                            <div class="actions">
                                <input type="hidden" name="id" value="<?= $ville['id'] ?>">
                                <button type="submit" name="action" value="modifier">
                                    Modifier
                                </button>
                                <button type="submit" name="action" value="supprimer" 
                                        class="btn-danger"
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
    <?php endif; ?>

    <div style="margin-top: 30px;">
        <h3>Informations importantes :</h3>
        <ul>
            <li>Chaque ville doit être associée à un pays existant</li>
            <li>Vous ne pouvez pas supprimer une ville qui a des formations associées</li>
            <li>Le nom de la ville doit être unique dans le système</li>
            <li>Assurez-vous d'avoir créé au moins un pays avant d'ajouter des villes</li>
        </ul>
    </div>

    <script>
        // Fonction de gestion du loading
        function initLoading() {
            const loading = document.getElementById('loading');
            const content = document.getElementById('content');
            
            setTimeout(() => {
                loading.classList.add('hidden');
                content.classList.add('show');
            }, 3000);
        }

        // Fonction pour relancer le loading
        function restartLoading() {
            const loading = document.getElementById('loading');
            const content = document.getElementById('content');
            
            content.classList.remove('show');
            loading.classList.remove('hidden');
            
            setTimeout(() => {
                loading.classList.add('hidden');
                content.classList.add('show');
            }, 3000);
        }
         // Démarrer au chargement de la page
        window.addEventListener('load', initLoading);
    </script>
</body>
</html>