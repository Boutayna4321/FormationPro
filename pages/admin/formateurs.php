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
        $nomFormateur = $_POST['nom_formateur'] ?? '';
        $emailFormateur = $_POST['email_formateur'] ?? '';
        $telephone = $_POST['telephone'] ?? '';
        $specialite = $_POST['specialite'] ?? '';
        $experienceAnnees = $_POST['experience_annees'] ?? 0;
        
        $result = ajouterFormateur($conn, $nomFormateur, $emailFormateur, $telephone, $specialite, $experienceAnnees);
        $message = $result['message'];
        $messageType = $result['success'] ? 'success' : 'error';
    }
    
    if ($action === 'modifier') {
        $id = $_POST['id'] ?? '';
        $nomFormateur = $_POST['nom_formateur'] ?? '';
        $emailFormateur = $_POST['email_formateur'] ?? '';
        $telephone = $_POST['telephone'] ?? '';
        $specialite = $_POST['specialite'] ?? '';
        $experienceAnnees = $_POST['experience_annees'] ?? 0;
        
        if ($id && $nomFormateur && $emailFormateur) {
            try {
                // Vérifier l'unicité de l'email (excluant l'ID actuel)
                if (!checkUniqueName($conn, 'formateurs', 'email_formateur', $emailFormateur, $id)) {
                    $message = "Cette adresse email existe déjà.";
                    $messageType = 'error';
                } else {
                    $stmt = $conn->prepare("UPDATE formateurs SET nom_formateur = ?, email_formateur = ?, telephone = ?, specialite = ?, experience_annees = ? WHERE id = ?");
                    $stmt->execute([$nomFormateur, $emailFormateur, $telephone, $specialite, $experienceAnnees, $id]);
                    $message = "Formateur modifié avec succès.";
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
            $result = supprimerFormateur($conn, $id);
            $message = $result['message'];
            $messageType = $result['success'] ? 'success' : 'error';
        }
    }
}

// Récupérer tous les formateurs
$formateurs = getAllFormateurs($conn);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Formateurs</title>
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
        .form-row {
            display: flex;
            gap: 15px;
        }
        .form-row .form-group {
            flex: 1;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        input, select, textarea {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
            box-sizing: border-box;
        }
        textarea {
            height: 80px;
            resize: vertical;
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
        .btn-info {
            background: #17a2b8;
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
            flex-wrap: wrap;
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
        .formateur-examples {
            background: #e9ecef;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .formateur-examples h4 {
            margin-top: 0;
            color: #495057;
        }
        .formateur-examples ul {
            margin-bottom: 0;
        }
        .table-responsive {
            overflow-x: auto;
        }
        .input-small {
            width: 80px !important;
        }
        .input-medium {
            width: 150px !important;
        }
        .input-large {
            width: 200px !important;
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
    <a href="dashboard.php" class="back-link">← Retour au tableau de bord</a>
    
    <h1>Gestion des Formateurs</h1>

    <?php if ($message): ?>
        <div class="message <?= $messageType ?>">
            <?= htmlspecialchars($message) ?>
        </div>
    <?php endif; ?>

    <!-- Exemples de formateurs -->
    <div class="formateur-examples">
        <h4>Informations sur les formateurs :</h4>
        <ul>
            <li><strong>Nom</strong> - Nom complet du formateur (obligatoire)</li>
            <li><strong>Email</strong> - Adresse email unique pour la communication (obligatoire)</li>
            <li><strong>Téléphone</strong> - Numéro de contact (optionnel)</li>
            <li><strong>Spécialité</strong> - Domaine d'expertise principal (optionnel)</li>
            <li><strong>Expérience</strong> - Nombre d'années d'expérience en formation</li>
        </ul>
    </div>

    <!-- Formulaire d'ajout -->
    <div class="form-container">
        <h2>Ajouter un nouveau formateur</h2>
        <form method="POST">
            <div class="form-row">
                <div class="form-group">
                    <label for="nom_formateur">Nom du formateur :</label>
                    <input type="text" id="nom_formateur" name="nom_formateur" 
                           placeholder="Ex: Jean Dupont" required>
                </div>
                <div class="form-group">
                    <label for="email_formateur">Email :</label>
                    <input type="email" id="email_formateur" name="email_formateur" 
                           placeholder="Ex: jean.dupont@email.com" required>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="telephone">Téléphone :</label>
                    <input type="tel" id="telephone" name="telephone" 
                           placeholder="Ex: +33 1 23 45 67 89">
                </div>
                <div class="form-group">
                    <label for="experience_annees">Années d'expérience :</label>
                    <input type="number" id="experience_annees" name="experience_annees" 
                           min="0" max="50" value="0">
                </div>
            </div>
            
            <div class="form-group">
                <label for="specialite">Spécialité :</label>
                <textarea id="specialite" name="specialite" 
                          placeholder="Ex: Management de projets, Développement web, Marketing digital..."></textarea>
            </div>
            
            <input type="hidden" name="action" value="ajouter">
            <button type="submit">Ajouter le formateur</button>
        </form>
    </div>

    <!-- Liste des formateurs -->
    <h2>Liste des formateurs existants</h2>
    
    <?php if (empty($formateurs)): ?>
        <p>Aucun formateur trouvé. Ajoutez le premier formateur ci-dessus.</p>
    <?php else: ?>
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nom</th>
                        <th>Email</th>
                        <th>Téléphone</th>
                        <th>Spécialité</th>
                        <th>Exp.</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($formateurs as $formateur): ?>
                    <tr>
                        <form method="POST" style="display: contents;">
                            <td><?= $formateur['id'] ?></td>
                            <td>
                                <input type="text" name="nom_formateur" 
                                       value="<?= htmlspecialchars($formateur['nom_formateur']) ?>" 
                                       required class="input-large">
                            </td>
                            <td>
                                <input type="email" name="email_formateur" 
                                       value="<?= htmlspecialchars($formateur['email_formateur']) ?>" 
                                       required class="input-large">
                            </td>
                            <td>
                                <input type="tel" name="telephone" 
                                       value="<?= htmlspecialchars($formateur['telephone'] ?? '') ?>" 
                                       class="input-medium">
                            </td>
                            <td>
                                <textarea name="specialite" 
                                          class="input-large" 
                                          style="height: 40px;"><?= htmlspecialchars($formateur['specialite'] ?? '') ?></textarea>
                            </td>
                            <td>
                                <input type="number" name="experience_annees" 
                                       value="<?= $formateur['experience_annees'] ?? 0 ?>" 
                                       min="0" max="50" class="input-small">
                            </td>
                            <td>
                                <div class="actions">
                                    <input type="hidden" name="id" value="<?= $formateur['id'] ?>">
                                    <button type="submit" name="action" value="modifier">
                                        Modifier
                                    </button>
                                    <button type="submit" name="action" value="supprimer" 
                                            class="btn-danger"
                                            onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce formateur ? Cette action supprimera aussi toutes les formations associées.');">
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

    <div style="margin-top: 30px;">
        <h3>Informations importantes :</h3>
        <ul>
            <li><strong>Formateur</strong> : Personne qui anime les formations proposées par l'entreprise</li>
            <li>L'adresse email du formateur doit être unique dans le système</li>
            <li>Le nom et l'email sont obligatoires, les autres champs sont optionnels</li>
            <li>Vous ne pouvez pas supprimer un formateur qui a des formations programmées</li>
            <li>La suppression d'un formateur supprimera automatiquement toutes les formations associées</li>
            <li>Les formateurs peuvent animer des formations en présentiel ou à distance</li>
            <li>Renseignez la spécialité pour faciliter l'attribution des formations</li>
        </ul>
    </div>

    <div style="margin-top: 20px; padding: 15px; background: #fff3cd; border: 1px solid #ffeaa7; border-radius: 5px;">
        <h4 style="color: #856404; margin-top: 0;">💡 Conseil :</h4>
        <p style="color: #856404; margin-bottom: 0;">
            Maintenez les informations des formateurs à jour pour faciliter la communication et l'organisation des formations. 
            Une spécialité bien définie aide à attribuer les bonnes formations aux bons formateurs.
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