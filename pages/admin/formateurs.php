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
    <title>Gestion des Formateurs - FormationPro</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

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

        .form-control, .form-select, .form-textarea {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 2px solid #e9ecef;
            border-radius: 8px;
            font-size: 1rem;
            transition: all 0.3s ease;
            background: white;
        }

        .form-control:focus, .form-select:focus, .form-textarea:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .form-textarea {
            height: 100px;
            resize: vertical;
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

        .btn-danger {
            background: #dc3545;
            color: white;
        }

        .btn-danger:hover {
            background: #c82333;
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

        /* Utilitaires */
        .input-small {
            width: 80px !important;
        }

        .input-medium {
            width: 150px !important;
        }

        .input-large {
            width: 200px !important;
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

            .page-title {
                font-size: 2rem;
            }

            .table td, .table th {
                padding: 0.75rem;
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
            <a href="dashboard.php" class="back-link">← Retour au tableau de bord</a>
            
            <h1 class="page-title">Gestion des Formateurs</h1>

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
                    Ajouter un nouveau formateur
                </div>
                <div class="card-body">
                    <form method="POST">
                        <div class="form-row">
                            <div class="form-group">
                                <label for="nom_formateur" class="form-label">Nom du formateur :</label>
                                <input type="text" class="form-control" id="nom_formateur" name="nom_formateur" 
                                       placeholder="Ex: Jean Dupont" required>
                            </div>
                            <div class="form-group">
                                <label for="email_formateur" class="form-label">Email :</label>
                                <input type="email" class="form-control" id="email_formateur" name="email_formateur" 
                                       placeholder="Ex: jean.dupont@email.com" required>
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="telephone" class="form-label">Téléphone :</label>
                                <input type="tel" class="form-control" id="telephone" name="telephone" 
                                       placeholder="Ex: +33 1 23 45 67 89">
                            </div>
                            <div class="form-group">
                                <label for="experience_annees" class="form-label">Années d'expérience :</label>
                                <input type="number" class="form-control input-small" id="experience_annees" name="experience_annees" 
                                       min="0" max="50" value="0">
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="specialite" class="form-label">Spécialité :</label>
                            <textarea class="form-control form-textarea" id="specialite" name="specialite" 
                                      placeholder="Ex: Management de projets, Développement web, Marketing digital..."></textarea>
                        </div>
                        
                        <input type="hidden" name="action" value="ajouter">
                        <button type="submit" class="btn btn-primary">Ajouter le formateur</button>
                    </form>
                </div>
            </div>

            <!-- Liste des formateurs -->
            <div class="card">
                <div class="card-header">
                    Liste des formateurs existants
                </div>
                <div class="card-body">
                    <?php if (empty($formateurs)): ?>
                        <div class="alert alert-info">
                            Aucun formateur trouvé. Ajoutez le premier formateur ci-dessus.
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table">
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
                                        <form method="POST">
                                            <td><?= $formateur['id'] ?></td>
                                            <td>
                                                <input type="text" class="form-control input-large" name="nom_formateur" 
                                                       value="<?= htmlspecialchars($formateur['nom_formateur']) ?>" required>
                                            </td>
                                            <td>
                                                <input type="email" class="form-control input-large" name="email_formateur" 
                                                       value="<?= htmlspecialchars($formateur['email_formateur']) ?>" required>
                                            </td>
                                            <td>
                                                <input type="tel" class="form-control input-medium" name="telephone" 
                                                       value="<?= htmlspecialchars($formateur['telephone'] ?? '') ?>">
                                            </td>
                                            <td>
                                                <textarea class="form-control" name="specialite" 
                                                          style="height: 40px;"><?= htmlspecialchars($formateur['specialite'] ?? '') ?></textarea>
                                            </td>
                                            <td>
                                                <input type="number" class="form-control input-small" name="experience_annees" 
                                                       value="<?= $formateur['experience_annees'] ?? 0 ?>" 
                                                       min="0" max="50">
                                            </td>
                                            <td>
                                                <div style="display: flex; gap: 0.5rem;">
                                                    <input type="hidden" name="id" value="<?= $formateur['id'] ?>">
                                                    <button type="submit" name="action" value="modifier" class="btn btn-primary">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <button type="submit" name="action" value="supprimer" 
                                                            class="btn btn-danger"
                                                            onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce formateur ? Cette action supprimera aussi toutes les formations associées.');">
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