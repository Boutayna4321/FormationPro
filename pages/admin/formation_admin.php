<?php
// formations.php - Version sans Bootstrap
session_start();
require_once('../../includes/admin_sidebar.php');
require_once 'functions.php';

// Vérifier l'authentification admin
$conn = db_connect();
$message = "";
$messageType = "";

// ==================== TRAITEMENT DES ACTIONS ====================

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'ajouter':
            // Récupération et nettoyage des données
            $coursId = intval($_POST['cours_id'] ?? 0);
            $villeId = intval($_POST['ville_id'] ?? 0);
            $formateurId = intval($_POST['formateur_id'] ?? 0);
            $dateFormation = $_POST['date_formation'] ?? '';
            $prix = floatval($_POST['prix'] ?? 0);
            $typeFormation = $_POST['type_formation'] ?? '';
            
            // Utilisation de la fonction de validation complète
            $result = ajouterFormation($conn, $coursId, $villeId, $formateurId, $dateFormation, $prix, $typeFormation);
            $message = $result['message'];
            $messageType = $result['success'] ? 'success' : 'error';
            break;
            
        case 'modifier':
            $id = intval($_POST['id'] ?? 0);
            $coursId = intval($_POST['cours_id'] ?? 0);
            $villeId = intval($_POST['ville_id'] ?? 0);
            $formateurId = intval($_POST['formateur_id'] ?? 0);
            $dateFormation = $_POST['date_formation'] ?? '';
            $prix = floatval($_POST['prix'] ?? 0);
            $typeFormation = $_POST['type_formation'] ?? '';
            
            // Validations avant modification
            if (empty($dateFormation) || $prix <= 0) {
                $message = "La date et le prix sont obligatoires et valides.";
                $messageType = 'error';
            } elseif (!in_array($typeFormation, ['presentiel', 'distanciel'])) {
                $message = "Le type de formation doit être 'presentiel' ou 'distanciel'.";
                $messageType = 'error';
            } elseif (!validateFormationCreation($conn, $coursId, $villeId, $formateurId)) {
                $message = "Erreur : Vérifiez que le cours, la ville et le formateur existent.";
                $messageType = 'error';
            } elseif (strtotime($dateFormation) < strtotime('today')) {
                $message = "La date de formation ne peut pas être dans le passé.";
                $messageType = 'error';
            } else {
                try {
                    $stmt = $conn->prepare("
                        UPDATE formations 
                        SET cours_id = ?, ville_id = ?, formateur_id = ?, date_formation = ?, prix = ?, type_formation = ? 
                        WHERE id = ?
                    ");
                    $stmt->execute([$coursId, $villeId, $formateurId, $dateFormation, $prix, $typeFormation, $id]);
                    $message = "Formation modifiée avec succès.";
                    $messageType = 'success';
                } catch (Exception $e) {
                    $message = "Erreur lors de la modification : " . $e->getMessage();
                    $messageType = 'error';
                }
            }
            break;
            
        case 'supprimer':
            $id = intval($_POST['id'] ?? 0);
            $result = supprimerFormation($conn, $id);
            $message = $result['message'];
            $messageType = $result['success'] ? 'success' : 'error';
            break;
    }
}

// Récupération des données pour les formulaires et l'affichage
$cours = getAllCours($conn);
$villes = getAllVilles($conn);
$formateurs = getAllFormateurs($conn);

// Récupération des formations avec toutes les informations
$stmt = $conn->query("
    SELECT f.*, 
           c.nom_cours, 
           s.nom_sujet, 
           d.nom_domaine,
           v.nom_ville, 
           p.nom_pays,
           fo.nom_formateur
    FROM formations f 
    JOIN cours c ON f.cours_id = c.id 
    JOIN sujets s ON c.sujet_id = s.id 
    JOIN domaines d ON s.domaine_id = d.id 
    JOIN villes v ON f.ville_id = v.id 
    JOIN pays p ON v.pays_id = p.id 
    JOIN formateurs fo ON f.formateur_id = fo.id 
    ORDER BY f.date_formation DESC
");
$formations = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Formations - FormationPro</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
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

        /* Styles pour l'écran de chargement */
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

        /* Style pour le contenu principal */
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

        .form-group.small {
            flex: 0 0 150px;
        }

        .form-group.tiny {
            flex: 0 0 100px;
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

        .btn-warning {
            background: #ffc107;
            color: #212529;
        }

        .btn-warning:hover {
            background: #e0a800;
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

        .btn-sm {
            padding: 0.5rem 0.75rem;
            font-size: 0.875rem;
        }

        .btn-group {
            display: flex;
            gap: 0.25rem;
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

        /* Styles des badges */
        .badge {
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.875rem;
            font-weight: 500;
            display: inline-block;
        }

        .badge.bg-primary {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
        }

        .badge.bg-success {
            background: linear-gradient(135deg, #28a745, #20c997);
            color: white;
        }

        /* Styles des modales */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 1000;
            justify-content: center;
            align-items: center;
        }

        .modal.show {
            display: flex;
        }

        .modal-dialog {
            background: white;
            border-radius: 15px;
            max-width: 500px;
            width: 90%;
            max-height: 90vh;
            overflow: auto;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
        }

        .modal-header {
            padding: 1.5rem;
            border-bottom: 1px solid #e9ecef;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .modal-title {
            font-size: 1.25rem;
            font-weight: 600;
            margin: 0;
        }

        .modal-body {
            padding: 1.5rem;
        }

        .modal-footer {
            padding: 1rem 1.5rem;
            border-top: 1px solid #e9ecef;
            display: flex;
            gap: 1rem;
            justify-content: flex-end;
        }

        /* Texte avec couleurs */
        .text-muted {
            color: #6c757d;
        }

        .text-warning {
            color: #ffc107;
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

            .form-group.small,
            .form-group.tiny {
                flex: 1;
            }

            .table-responsive {
                font-size: 0.875rem;
            }

            .btn-group {
                flex-direction: column;
            }

            .page-title {
                font-size: 2rem;
            }
        }

        /* Utilitaires */
        .mb-0 { margin-bottom: 0; }
        .mb-4 { margin-bottom: 2rem; }
        .mt-4 { margin-top: 2rem; }
        .me-1 { margin-right: 0.25rem; }
        .me-2 { margin-right: 0.5rem; }
        .d-inline { display: inline; }
        .d-flex { display: flex; }
        .justify-content-between { justify-content: space-between; }
        .align-items-center { align-items: center; }
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
            <h1 class="page-title">Gestion des Formations</h1>
            
            <!-- Messages d'état -->
            <?php if (!empty($message)): ?>
                <div class="alert alert-<?php echo $messageType == 'success' ? 'success' : 'danger'; ?> alert-dismissible">
                    <strong><?php echo $messageType == 'success' ? 'Succès!' : 'Erreur!'; ?></strong>
                    <?php echo htmlspecialchars($message); ?>
                    <button type="button" class="btn-close" onclick="this.parentElement.style.display='none'">&times;</button>
                </div>
            <?php endif; ?>
            
            <!-- Formulaire d'ajout -->
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-plus-circle me-2"></i>
                    Créer une nouvelle formation
                </div>
                <div class="card-body">
                    <form method="POST" id="addForm">
                        <input type="hidden" name="action" value="ajouter">
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="cours_id" class="form-label">
                                    <i class="fas fa-book me-1"></i>Cours
                                </label>
                                <select class="form-select" id="cours_id" name="cours_id" required>
                                    <option value="">Sélectionnez un cours</option>
                                    <?php foreach ($cours as $c): ?>
                                        <option value="<?php echo $c['id']; ?>">
                                            <?php echo htmlspecialchars($c['nom_domaine'] . ' > ' . $c['nom_sujet'] . ' > ' . $c['nom_cours']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label for="ville_id" class="form-label">
                                    <i class="fas fa-map-marker-alt me-1"></i>Ville
                                </label>
                                <select class="form-select" id="ville_id" name="ville_id" required>
                                    <option value="">Sélectionnez une ville</option>
                                    <?php foreach ($villes as $v): ?>
                                        <option value="<?php echo $v['id']; ?>">
                                            <?php echo htmlspecialchars($v['nom_ville'] . ' (' . $v['nom_pays'] . ')'); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="formateur_id" class="form-label">
                                    <i class="fas fa-user-tie me-1"></i>Formateur
                                </label>
                                <select class="form-select" id="formateur_id" name="formateur_id" required>
                                    <option value="">Sélectionnez un formateur</option>
                                    <?php foreach ($formateurs as $f): ?>
                                        <option value="<?php echo $f['id']; ?>">
                                            <?php echo htmlspecialchars($f['nom_formateur']); ?>
                                            <?php if ($f['specialite']): ?>
                                                (<?php echo htmlspecialchars($f['specialite']); ?>)
                                            <?php endif; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="form-group small">
                                <label for="date_formation" class="form-label">
                                    <i class="fas fa-calendar me-1"></i>Date
                                </label>
                                <input type="date" class="form-control" id="date_formation" name="date_formation" 
                                       min="<?php echo date('Y-m-d'); ?>" required>
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group small">
                                <label for="prix" class="form-label">
                                    <i class="fas fa-euro-sign me-1"></i>Prix (DH)
                                </label>
                                <input type="number" class="form-control" id="prix" name="prix" 
                                       min="0" step="0.01" required>
                            </div>
                            
                            <div class="form-group small">
                                <label for="type_formation" class="form-label">Type</label>
                                <select class="form-select" id="type_formation" name="type_formation" required>
                                    <option value="">Type</option>
                                    <option value="presentiel">Présentiel</option>
                                    <option value="distanciel">Distanciel</option>
                                </select>
                            </div>
                            
                            <div class="form-group" style="align-self: flex-end;">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-plus me-2"></i>Créer la formation
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Liste des formations -->
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-list me-2"></i>
                    Formations existantes
                </div>
                <div class="card-body">
                    <?php if (!empty($formations)): ?>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th><i class="fas fa-calendar me-1"></i>Date</th>
                                        <th><i class="fas fa-book me-1"></i>Cours</th>
                                        <th><i class="fas fa-sitemap me-1"></i>Domaine > Sujet</th>
                                        <th><i class="fas fa-map-marker-alt me-1"></i>Lieu</th>
                                        <th><i class="fas fa-user-tie me-1"></i>Formateur</th>
                                        <th><i class="fas fa-euro-sign me-1"></i>Prix</th>
                                        <th><i class="fas fa-desktop me-1"></i>Type</th>
                                        <th><i class="fas fa-cogs me-1"></i>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($formations as $formation): ?>
                                        <tr>
                                            <td><?php echo date('d/m/Y', strtotime($formation['date_formation'])); ?></td>
                                            <td>
                                                <strong><?php echo htmlspecialchars($formation['nom_cours']); ?></strong>
                                            </td>
                                            <td>
                                                <small class="text-muted">
                                                    <?php echo htmlspecialchars($formation['nom_domaine'] . ' > ' . $formation['nom_sujet']); ?>
                                                </small>
                                            </td>
                                            <td><?php echo htmlspecialchars($formation['nom_ville'] . ', ' . $formation['nom_pays']); ?></td>
                                            <td><?php echo htmlspecialchars($formation['nom_formateur']); ?></td>
                                            <td>
                                                <strong><?php echo number_format($formation['prix'], 2); ?> €</strong>
                                            </td>
                                            <td>
                                                <span class="badge <?php echo $formation['type_formation'] == 'presentiel' ? 'bg-primary' : 'bg-success'; ?>">
                                                    <?php echo ucfirst($formation['type_formation']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <div class="btn-group">
                                                    <button class="btn btn-warning btn-sm" 
                                                            onclick="modifierFormation(<?php echo $formation['id']; ?>)"
                                                            title="Modifier">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <button class="btn btn-danger btn-sm" 
                                                            onclick="confirmerSuppression(<?php echo $formation['id']; ?>, '<?php echo addslashes($formation['nom_cours']); ?>')"
                                                            title="Supprimer">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            Aucune formation programmée pour le moment.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="mt-4">
                <a href="admin_dashboard.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Retour au tableau de bord
                </a>
            </div>
        </div>
    </div>

    <!-- Modal de suppression -->
    <div class="modal" id="deleteModal">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-exclamation-triangle text-warning me-2"></i>
                        Confirmer la suppression
                    </h5>
                    <button type="button" class="btn-close" onclick="closeModal()">&times;</button>
                </div>
                <div class="modal-body">
                    <p>Êtes-vous sûr de vouloir supprimer la formation "<strong><span id="formationName"></span></strong>" ?</p>
                    <p class="text-muted">Cette action est irréversible.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeModal()">
                        <i class="fas fa-times me-1"></i>Annuler
                    </button>
                    <form method="POST" class="d-inline" id="deleteForm">
                        <input type="hidden" name="action" value="supprimer">
                        <input type="hidden" name="id" id="deleteId">
                        <button type="submit" class="btn btn-danger">
                            <i class="fas fa-trash me-1"></i>Supprimer
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    
    
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

        // Fonction de confirmation de suppression
        function confirmerSuppression(id, nom) {
            document.getElementById('deleteId').value = id;
            document.getElementById('formationName').textContent = nom;
            new bootstrap.Modal(document.getElementById('deleteModal')).show();
        }
        
        // Fonction de modification
        function modifierFormation(id) {
            window.location.href = 'modifier_formation.php?id=' + id;
        }

        // Animation des cartes au survol
        document.querySelectorAll('.card').forEach(card => {
            card.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-2px)';
            });
            
            card.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0)';
            });
        });

        // Validation du formulaire
        document.querySelector('form').addEventListener('submit', function(e) {
            const prix = document.getElementById('prix').value;
            const dateFormation = document.getElementById('date_formation').value;
            
            if (parseFloat(prix) <= 0) {
                e.preventDefault();
                alert('Le prix doit être supérieur à 0');
                return;
            }
            
            if (new Date(dateFormation) < new Date()) {
                e.preventDefault();
                alert('La date de formation ne peut pas être dans le passé');
                return;
            }
        });
    </script>
</body>
</html>