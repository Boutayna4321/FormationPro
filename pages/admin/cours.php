<?php
session_start();
require_once 'auth_check.php';
require_once('../../includes/admin_sidebar.php');
require_once 'functions.php';

$conn = db_connect();
$message = '';
$messageType = '';

$sujetId = $_GET['sujet_id'] ?? null;
$sujetInfo = null;

if ($sujetId) {
    $stmt = $conn->prepare("
        SELECT s.*, d.nom_domaine 
        FROM sujets s 
        JOIN domaines d ON s.domaine_id = d.id 
        WHERE s.id = ?
    ");
    $stmt->execute([$sujetId]);
    $sujetInfo = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$sujetInfo) {
        header('Location: sujets.php');
        exit();
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'ajouter') {
        $nomCours = $_POST['nom_cours'] ?? '';
        $description = $_POST['description'] ?? '';
        $sujetIdPost = $_POST['sujet_id'] ?? '';

        $result = ajouterCours($conn, $nomCours, $description, $sujetIdPost);
        $message = $result['message'];
        $messageType = $result['success'] ? 'success' : 'error';
    }

    if ($action === 'modifier') {
        $id = $_POST['id'] ?? '';
        $nomCours = $_POST['nom_cours'] ?? '';
        $description = $_POST['description'] ?? '';
        $sujetIdPost = $_POST['sujet_id'] ?? '';

        if ($id && $nomCours && $sujetIdPost) {
            try {
                if (!checkUniqueName($conn, 'cours', 'nom_cours', $nomCours, $id)) {
                    $message = "Ce nom de cours existe déjà.";
                    $messageType = 'error';
                } else if (!validateCoursCreation($conn, $sujetIdPost)) {
                    $message = "Le sujet sélectionné n'existe pas.";
                    $messageType = 'error';
                } else {
                    $stmt = $conn->prepare("UPDATE cours SET nom_cours = ?, description = ?, sujet_id = ? WHERE id = ?");
                    $stmt->execute([$nomCours, $description, $sujetIdPost, $id]);
                    $message = "Cours modifié avec succès.";
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
            $result = supprimerCours($conn, $id);
            $message = $result['message'];
            $messageType = $result['success'] ? 'success' : 'error';
        }
    }
}

$sujets = getAllSujets($conn);
$cours = $sujetId ? getCoursBySujet($conn, $sujetId) : getAllCours($conn);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Cours</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
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

        .form-textarea {
            min-height: 100px;
            resize: vertical;
        }

        .form-control:focus, .form-select:focus, .form-textarea:focus {
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

        .btn-danger {
            background: #dc3545;
            color: white;
        }

        .btn-danger:hover {
            background: #c82333;
            transform: translateY(-2px);
        }

        .btn-success {
            background: #28a745;
            color: white;
        }

        .btn-success:hover {
            background: #218838;
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

        .table input, .table select, .table textarea {
            width: 100%;
            padding: 0.5rem;
            border: 1px solid #ddd;
            border-radius: 4px;
        }

        .table textarea {
            min-height: 60px;
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
    <div class="main-content">
        <div class="container">
            <h1 class="page-title">Gestion des Cours</h1>
            
            <!-- Messages d'état -->
            <?php if ($message): ?>
                <div class="alert alert-<?= $messageType == 'success' ? 'success' : 'danger' ?>">
                    <?= htmlspecialchars($message) ?>
                </div>
            <?php endif; ?>

            <!-- Formulaire d'ajout -->
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-plus-circle me-1"></i>
                    Ajouter un Cours
                </div>
                <div class="card-body">
                    <form method="POST">
                        <?php if (!$sujetInfo): ?>
                            <div class="form-group">
                                <label for="sujet_id" class="form-label">Sujet</label>
                                <select class="form-select" name="sujet_id" required>
                                    <option value="">Choisissez un sujet</option>
                                    <?php foreach ($sujets as $s): ?>
                                        <option value="<?= $s['id'] ?>"><?= htmlspecialchars($s['nom_domaine']) ?> > <?= htmlspecialchars($s['nom_sujet']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        <?php else: ?>
                            <input type="hidden" name="sujet_id" value="<?= $sujetInfo['id'] ?>">
                        <?php endif; ?>

                        <div class="form-group">
                            <label for="nom_cours" class="form-label">Nom du cours</label>
                            <input type="text" class="form-control" name="nom_cours" required>
                        </div>

                        <div class="form-group">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-textarea" name="description"></textarea>
                        </div>

                        <input type="hidden" name="action" value="ajouter">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-plus me-1"></i>Ajouter
                        </button>
                    </form>
                </div>
            </div>

            <!-- Liste des cours -->
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-list me-1"></i>
                    Liste des Cours
                </div>
                <div class="card-body">
                    <?php if (empty($cours)): ?>
                        <p>Aucun cours trouvé.</p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <?php if (!$sujetInfo): ?><th>Sujet</th><?php endif; ?>
                                        <th>Nom</th>
                                        <th>Description</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($cours as $coursItem): ?>
                                        <tr>
                                            <form method="POST">
                                                <td><?= $coursItem['id'] ?></td>
                                                <?php if (!$sujetInfo): ?>
                                                    <td>
                                                        <select class="form-select" name="sujet_id" required>
                                                            <?php foreach ($sujets as $s): ?>
                                                                <option value="<?= $s['id'] ?>" <?= $s['id'] == $coursItem['sujet_id'] ? 'selected' : '' ?>>
                                                                    <?= htmlspecialchars($s['nom_domaine']) ?> > <?= htmlspecialchars($s['nom_sujet']) ?>
                                                                </option>
                                                            <?php endforeach; ?>
                                                        </select>
                                                    </td>
                                                <?php else: ?>
                                                    <input type="hidden" name="sujet_id" value="<?= $coursItem['sujet_id'] ?>">
                                                <?php endif; ?>
                                                <td><input type="text" class="form-control" name="nom_cours" value="<?= htmlspecialchars($coursItem['nom_cours']) ?>" required></td>
                                                <td><textarea class="form-textarea" name="description"><?= htmlspecialchars($coursItem['description']) ?></textarea></td>
                                                <td>
                                                    <div class="btn-group">
                                                        <input type="hidden" name="id" value="<?= $coursItem['id'] ?>">
                                                        <button type="submit" name="action" value="modifier" class="btn btn-primary btn-sm" title="Modifier">
                                                            <i class="fas fa-edit"></i>
                                                        </button>
                                                        <a href="formations.php?cours_id=<?= $coursItem['id'] ?>" class="btn btn-success btn-sm" title="Voir formations">
                                                            <i class="fas fa-eye"></i>
                                                        </a>
                                                        <button type="submit" name="action" value="supprimer" class="btn btn-danger btn-sm" onclick="return confirm('Supprimer ce cours ?')" title="Supprimer">
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
            
            // Masquer l'écran de chargement après 1.5 seconde
            setTimeout(() => {
                loadingOverlay.classList.add('hidden');
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