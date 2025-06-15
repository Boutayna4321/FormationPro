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
    <title>Gestion des Sujets</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f8f9fa;
            margin: 0;
            padding: 0;
            color: #2c3e50;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
            margin-left: 280px; /* Pour la sidebar */
        }
        .message {
             padding: 15px;
            margin: 20px 0;
            border-radius: 10px;
            font-weight: 500;
            display: flex;
            align-items: center;
        }
        .success {
            background-color: #d4edda;
            color: #155724;
            border-left: 5px solid #28a745;
        }
        .error {
            background-color: #f8d7da;
            color: #721c24;
            border-left: 5px solid #dc3545;
        }
        .form-container {
              max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
            margin-left: 280px; 
        }
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
        button {
            display: inline-block;
            padding: 10px 20px;
            border-radius: 8px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s;
            border: none;
            text-align: center;
        }
        button:hover {
           background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
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
        .btn-info:hover {
            background: #138496;

        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
              padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #e9ecef;
        }
        th {
             background-color: rgba(102, 126, 234, 0.1);
            font-weight: 600;
            color: #2c3e50;
        }
        tr:hover {
            background-color: #f8f9fa;
        }
        .actions {
            display: flex;
            gap: 10px;
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
        .domain-info {
            background: #d1ecf1;
            border: 1px solid #bee5eb;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .domain-info h3 {
            margin-top: 0;
            color: #0c5460;
        }
        .subject-examples {
            background: #e9ecef;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .subject-examples h4 {
            margin-top: 0;
            color: #495057;
        }
        .subject-examples ul {
            margin-bottom: 0;
        }
        .domain-badge {
            background: #007bff;
            color: white;
            padding: 4px 8px;
            border-radius: 3px;
            font-size: 12px;
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
        };
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
    <div class="loading" id="loading">
        <div class="spinner-gradient"></div>
        <div class="loading-text">Chargement</div>
        <div class="loading-logo">FormationPro</div>
    </div>
    </div>
    <div class="container" id="mainContent">
    <?php if ($domaineInfo): ?>
        <div class="breadcrumb">
            <a href="dashboard.php">Tableau de bord</a> > 
            <a href="domaines.php">Domaines</a> > 
            <strong><?= htmlspecialchars($domaineInfo['nom_domaine']) ?></strong>
        </div>
        
        <div class="domain-info">
            <h3>📚 Gestion des sujets du domaine : <?= htmlspecialchars($domaineInfo['nom_domaine']) ?></h3>
            <p>Vous gérez actuellement les sujets spécifiques au domaine "<strong><?= htmlspecialchars($domaineInfo['nom_domaine']) ?></strong>". 
            Chaque sujet représente une sous-catégorie de ce domaine.</p>
        </div>
    <?php else: ?>
        <a href="dashboard.php" class="back-link">← Retour au tableau de bord</a>
    <?php endif; ?>
    
    <h1>Gestion des Sujets<?= $domaineInfo ? ' - ' . htmlspecialchars($domaineInfo['nom_domaine']) : '' ?></h1>

    <?php if ($message): ?>
        <div class="message <?= $messageType ?>">
            <?= htmlspecialchars($message) ?>
        </div>
    <?php endif; ?>

    <!-- Exemples de sujets -->
    <div class="subject-examples">
        <h4>Exemples de sujets par domaine :</h4>
        <ul>
            <li><strong>Management</strong> : Management de Projet, Management de Services, Leadership, Ressources Humaines</li>
            <li><strong>Computer Science</strong> : Programmation Web, Bases de Données, Intelligence Artificielle, Réseaux</li>
            <li><strong>Marketing</strong> : Marketing Digital, Communication, Publicité, Études de Marché</li>
            <li><strong>Finance</strong> : Analyse Financière, Comptabilité, Gestion Budgétaire, Investissements</li>
            <li><strong>Design</strong> : Design Graphique, UX/UI, Illustration, Photographie</li>
        </ul>
    </div>

    <!-- Formulaire d'ajout -->
    <div class="form-container">
        <h2>Ajouter un nouveau sujet</h2>
        <form method="POST">
            <?php if (!$domaineInfo): ?>
            <div class="form-group">
                <label for="domaine_id">Domaine :</label>
                <select id="domaine_id" name="domaine_id" required>
                    <option value="">Sélectionnez un domaine</option>
                    <?php foreach ($domaines as $domaine): ?>
                        <option value="<?= $domaine['id'] ?>"><?= htmlspecialchars($domaine['nom_domaine']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <?php else: ?>
                <input type="hidden" name="domaine_id" value="<?= $domaineInfo['id'] ?>">
                <div class="form-group">
                    <label>Domaine sélectionné :</label>
                    <input type="text" value="<?= htmlspecialchars($domaineInfo['nom_domaine']) ?>" readonly style="background-color: #e9ecef;">
                </div>
            <?php endif; ?>
            
            <div class="form-group">
                <label for="nom_sujet">Nom du sujet :</label>
                <input type="text" id="nom_sujet" name="nom_sujet" 
                       placeholder="Ex: Management de Projet, Programmation Web, Marketing Digital..." required>
            </div>
            
            <input type="hidden" name="action" value="ajouter">
            <button type="submit">Ajouter le sujet</button>
        </form>
    </div>

    <!-- Navigation rapide -->
    <?php if (!$domaineInfo && !empty($domaines)): ?>
    <div style="background: #fff3cd; border: 1px solid #ffeaa7; border-radius: 5px; padding: 15px; margin-bottom: 20px;">
        <h4 style="color: #856404; margin-top: 0;">🔍 Navigation rapide par domaine :</h4>
        <div style="display: flex; flex-wrap: wrap; gap: 10px;">
            <?php foreach ($domaines as $domaine): ?>
                <a href="sujets.php?domaine_id=<?= $domaine['id'] ?>" 
                   style="background: #007bff; color: white; padding: 8px 12px; border-radius: 4px; text-decoration: none; font-size: 14px;">
                    <?= htmlspecialchars($domaine['nom_domaine']) ?>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- Liste des sujets -->
    <h2>Liste des sujets<?= $domaineInfo ? ' du domaine ' . htmlspecialchars($domaineInfo['nom_domaine']) : '' ?></h2>
    
    <?php if (empty($sujets)): ?>
        <p>Aucun sujet trouvé<?= $domaineInfo ? ' pour ce domaine' : '' ?>. Ajoutez le premier sujet ci-dessus.</p>
    <?php else: ?>
        <table>
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
                    <form method="POST" style="display: contents;">
                        <td><?= $sujet['id'] ?></td>
                        <?php if (!$domaineInfo): ?>
                        <td>
                            <select name="domaine_id" required style="width: 180px;">
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
                            <input type="text" name="nom_sujet" 
                                   value="<?= htmlspecialchars($sujet['nom_sujet']) ?>" 
                                   required style="width: 300px;">
                        </td>
                        <td>
                            <div class="actions">
                                <input type="hidden" name="id" value="<?= $sujet['id'] ?>">
                                <button type="submit" name="action" value="modifier">
                                    Modifier
                                </button>
                                <a href="cours.php?sujet_id=<?= $sujet['id'] ?>" 
                                   class="btn-info" style="text-decoration: none; padding: 8px 16px; border-radius: 4px; color: white;">
                                    Voir cours
                                </a>
                                <button type="submit" name="action" value="supprimer" 
                                        class="btn-danger"
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
    <?php endif; ?>

    <?php if ($domaineInfo): ?>
    <div style="margin-top: 20px;">
        <a href="domaines.php" class="back-link">← Retour à la liste des domaines</a>
        <span style="margin: 0 10px;">|</span>
        <a href="sujets.php" class="back-link">Voir tous les sujets</a>
    </div>
    <?php endif; ?>

    <div style="margin-top: 30px;">
        <h3>Informations importantes :</h3>
        <ul>
            <li><strong>Sujet</strong> : Sous-catégorie d'un domaine (ex: "Management de Projet" dans le domaine "Management")</li>
            <li>Le nom du sujet doit être unique dans le système</li>
            <li>Chaque sujet appartient obligatoirement à un domaine</li>
            <li>Vous ne pouvez pas supprimer un sujet qui a des cours associés</li>
            <li>La suppression d'un sujet supprimera automatiquement tous les cours et formations associés</li>
            <li>Les sujets permettent d'organiser vos cours de manière logique</li>
        </ul>
    </div>

    <div style="margin-top: 20px; padding: 15px; background: #fff3cd; border: 1px solid #ffeaa7; border-radius: 5px;">
        <h4 style="color: #856404; margin-top: 0;">💡 Conseil :</h4>
        <p style="color: #856404; margin-bottom: 0;">
            Un sujet regroupe des cours similaires dans un domaine. Par exemple, dans le domaine "Computer Science", 
            vous pourriez avoir les sujets "Programmation Web", "Bases de Données", "Intelligence Artificielle", etc.
            Gardez les noms de sujets spécifiques mais pas trop techniques.
        </p>
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