<?php
session_start();
require_once 'auth_check.php';
require_once('../../includes/admin_sidebar.php');
require_once 'functions.php';

// Vérifier si l'admin est connecté


// Récupérer les statistiques
$conn = db_connect();

try {
    // Statistiques générales
    $stats = [];
    $stats['pays'] = $conn->query("SELECT COUNT(*) FROM pays")->fetchColumn();
    $stats['villes'] = $conn->query("SELECT COUNT(*) FROM villes")->fetchColumn();
    $stats['formateurs'] = $conn->query("SELECT COUNT(*) FROM formateurs")->fetchColumn();
    $stats['domaines'] = $conn->query("SELECT COUNT(*) FROM domaines")->fetchColumn();
    $stats['sujets'] = $conn->query("SELECT COUNT(*) FROM sujets")->fetchColumn();
    $stats['cours'] = $conn->query("SELECT COUNT(*) FROM cours")->fetchColumn();
    $stats['formations'] = $conn->query("SELECT COUNT(*) FROM formations")->fetchColumn();
    
    // Formations récentes
    $formations_recentes = $conn->query("
        SELECT f.*, c.nom_cours, v.nom_ville, p.nom_pays, fo.nom_formateur
        FROM formations f 
        JOIN cours c ON f.cours_id = c.id 
        JOIN villes v ON f.ville_id = v.id 
        JOIN pays p ON v.pays_id = p.id 
        JOIN formateurs fo ON f.formateur_id = fo.id 
        ORDER BY f.date_formation DESC 
        LIMIT 5
    ")->fetchAll(PDO::FETCH_ASSOC);
    
    // Formations par type
    $formations_par_type = $conn->query("
        SELECT type_formation, COUNT(*) as count 
        FROM formations 
        GROUP BY type_formation
    ")->fetchAll(PDO::FETCH_ASSOC);
    
    // Domaines les plus populaires
    $domaines_populaires = $conn->query("
        SELECT d.nom_domaine, COUNT(f.id) as count 
        FROM domaines d 
        LEFT JOIN sujets s ON d.id = s.domaine_id 
        LEFT JOIN cours c ON s.id = c.sujet_id 
        LEFT JOIN formations f ON c.id = f.cours_id 
        GROUP BY d.id, d.nom_domaine 
        ORDER BY count DESC 
        LIMIT 5
    ")->fetchAll(PDO::FETCH_ASSOC);
    
    // Revenus mensuels (derniers 6 mois)
    $revenus_mensuels = $conn->query("
        SELECT 
            DATE_FORMAT(date_formation, '%Y-%m') as mois,
            SUM(prix) as revenus,
            COUNT(*) as formations
        FROM formations 
        WHERE date_formation >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
        GROUP BY DATE_FORMAT(date_formation, '%Y-%m')
        ORDER BY mois DESC
    ")->fetchAll(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    $error = "Erreur lors de la récupération des données : " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - FormationPro</title>
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
            --shadow-lg: 0 10px 40px rgba(0,0,0,0.08), 0 2px 8px rgba(0,0,0,0.06);
            --radius: 18px;
            --header-h: 150px;
        }

        body {
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
            background: var(--bg);
            color: var(--text);
            overflow-x: hidden;
        }

        /* Loading Screen */
        .loading {
            position: fixed; inset: 0; background: var(--white); z-index: 9999;
            display: flex; flex-direction: column; align-items: center; justify-content: center;
            transition: opacity 0.5s ease; gap: 1.5rem;
        }
        .loading.hidden { opacity: 0; pointer-events: none; }
        .spinner {
            width: 56px; height: 56px; border-radius: 50%;
            background: conic-gradient(from 0deg, #2c3e50, #3498db, #667eea, #764ba2, #2c3e50);
            animation: spin 1.2s linear infinite; position: relative;
            box-shadow: 0 4px 20px rgba(52, 152, 219, 0.2);
        }
        .spinner::before {
            content: ''; position: absolute; top: 5px; left: 5px; right: 5px; bottom: 5px;
            background: var(--white); border-radius: 50%;
        }
        @keyframes spin { to { transform: rotate(360deg); } }
        .loading-text { font-size: 1.1rem; color: var(--text); font-weight: 500; }
        .loading-logo { font-size: 1.4rem; font-weight: 700; background: linear-gradient(135deg, var(--primary), var(--secondary)); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; }

        /* Page Header */
        .page-header {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            padding: 28px 32px; border-radius: var(--radius);
            margin-bottom: 24px; color: var(--white);
            height: var(--header-h); display: flex; flex-direction: column;
            justify-content: center;
        }
        .page-title {
            font-size: 42px; font-weight: 700; margin-bottom: 4px;
            display: flex; align-items: center; gap: 10px;
        }
        .page-title i { font-size: 32px; }
        .page-subtitle { font-size: 20px; opacity: 0.85; font-weight: 400; }

        /* Stats Grid */
        .stats-grid {
            display: grid; grid-template-columns: repeat(3, 1fr);
            gap: 24px; margin-bottom: 24px;
        }
        .stat-card {
            height: 90px; background: var(--white);
            border-radius: 14px; padding: 16px;
            display: flex; align-items: center; gap: 12px;
            box-shadow: var(--shadow); transition: all 0.3s ease;
            border-left: 3px solid var(--secondary); overflow: hidden;
        }
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-lg);
        }

        .stat-card.pays { border-left-color: #3498db; }
        .stat-card.villes { border-left-color: #9b59b6; }
        .stat-card.formateurs { border-left-color: #e67e22; }
        .stat-card.domaines { border-left-color: #1abc9c; }
        .stat-card.sujets { border-left-color: #f39c12; }
        .stat-card.cours { border-left-color: #e74c3c; }
        .stat-card.formations { border-left-color: #27ae60; }

        .stat-icon {
            width: 42px; height: 42px; border-radius: 50%; flex-shrink: 0;
            display: flex; align-items: center; justify-content: center;
            font-size: 18px; background: rgba(52, 152, 219, 0.1); color: var(--secondary);
        }
        .stat-card.pays .stat-icon { background: rgba(52, 152, 219, 0.1); color: #3498db; }
        .stat-card.villes .stat-icon { background: rgba(155, 89, 182, 0.1); color: #9b59b6; }
        .stat-card.formateurs .stat-icon { background: rgba(230, 126, 34, 0.1); color: #e67e22; }
        .stat-card.domaines .stat-icon { background: rgba(26, 188, 156, 0.1); color: #1abc9c; }
        .stat-card.sujets .stat-icon { background: rgba(243, 156, 18, 0.1); color: #f39c12; }
        .stat-card.cours .stat-icon { background: rgba(231, 76, 60, 0.1); color: #e74c3c; }
        .stat-card.formations .stat-icon { background: rgba(39, 174, 96, 0.1); color: #27ae60; }

        .stat-info { flex: 1; min-width: 0; }
        .stat-number { font-size: 24px; font-weight: 700; color: var(--text); line-height: 1.1; }
        .stat-label { font-size: 14px; color: var(--text-muted); font-weight: 500; }

        /* Dashboard Grid */
        .dashboard-grid {
            display: grid; grid-template-columns: 2fr 1fr; gap: 24px; margin-bottom: 24px;
        }
        .dashboard-card {
            background: var(--white); border-radius: var(--radius);
            box-shadow: var(--shadow); overflow: hidden;
        }
        .card-header {
            padding: 16px 24px; border-bottom: 1px solid var(--border);
        }
        .card-title {
            font-size: 1.1rem; font-weight: 600; color: var(--text);
            display: flex; align-items: center; gap: 8px;
        }
        .card-title i { color: var(--secondary); }
        .card-content { padding: 16px 24px; }

        .formation-item {
            display: flex; align-items: center; padding: 12px 16px;
            border-radius: 12px; margin-bottom: 8px;
            transition: all 0.2s; border-left: 3px solid var(--secondary);
            background: rgba(52, 152, 219, 0.03);
        }
        .formation-item:hover { background: rgba(52, 152, 219, 0.06); }
        .formation-info { flex: 1; min-width: 0; }
        .formation-title { font-weight: 600; color: var(--text); margin-bottom: 2px; font-size: 0.95rem; }
        .formation-details { font-size: 0.82rem; color: var(--text-muted); }
        .formation-details i { margin-right: 2px; }
        .formation-date {
            background: linear-gradient(135deg, var(--accent), var(--purple));
            color: var(--white); padding: 4px 12px; border-radius: 20px;
            font-size: 0.78rem; font-weight: 500; white-space: nowrap; margin-left: 8px;
        }

        /* Quick Actions */
        .quick-actions { grid-column: 1 / -1; }
        .actions-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 12px; }
        .action-btn {
            display: flex; align-items: center; gap: 10px;
            padding: 14px 18px; background: rgba(52, 152, 219, 0.04);
            border: 1px solid var(--border); border-radius: 12px;
            text-decoration: none; color: var(--text); font-weight: 500; font-size: 0.9rem;
            transition: all 0.2s;
        }
        .action-btn:hover {
            border-color: var(--secondary); background: rgba(52, 152, 219, 0.06);
            transform: translateY(-2px); box-shadow: var(--shadow);
        }
        .action-btn i { font-size: 1.3rem; color: var(--secondary); flex-shrink: 0; }

        /* Animations */
        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(16px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .stat-card, .dashboard-card { animation: fadeUp 0.4s ease forwards; }
        .stat-card:nth-child(1) { animation-delay: 0.05s; }
        .stat-card:nth-child(2) { animation-delay: 0.1s; }
        .stat-card:nth-child(3) { animation-delay: 0.15s; }
        .stat-card:nth-child(4) { animation-delay: 0.2s; }
        .stat-card:nth-child(5) { animation-delay: 0.25s; }
        .stat-card:nth-child(6) { animation-delay: 0.3s; }
        .stat-card:nth-child(7) { animation-delay: 0.35s; }

        @media (max-width: 992px) {
            .stats-grid { grid-template-columns: repeat(2, 1fr); }
            .dashboard-grid { grid-template-columns: 1fr; }
            .page-title { font-size: 32px; }
            .page-title i { font-size: 26px; }
            .page-subtitle { font-size: 17px; }
        }

        @media (max-width: 768px) {
            .main-content { padding: 20px 16px; padding-top: 70px; }
            .page-header { height: auto; padding: 20px 24px; }
            .page-title { font-size: 28px; }
            .page-subtitle { font-size: 15px; }
            .stats-grid { grid-template-columns: 1fr; gap: 16px; }
            .stat-card { height: 80px; padding: 12px; }
            .stat-number { font-size: 20px; }
            .stat-label { font-size: 13px; }
            .stat-icon { width: 36px; height: 36px; font-size: 16px; }
            .dashboard-grid { gap: 16px; }
            .card-content { padding: 12px 16px; }
            .actions-grid { grid-template-columns: 1fr 1fr; }
        }

        @media (max-width: 480px) {
            .actions-grid { grid-template-columns: 1fr; }
            .page-title { font-size: 24px; }
            .formation-item { flex-direction: column; align-items: flex-start; gap: 8px; }
        }
    </style>
</head>
<body>
    <div class="loading" id="loading">
        <div class="spinner"></div>
        <div class="loading-text">Chargement</div>
        <div class="loading-logo">FormationPro</div>
    </div>

    <!-- Main Content -->
    <main class="main-content">
        <div class="page-header">
            <h1 class="page-title">
                <i class="fas fa-tachometer-alt"></i>
                Tableau de bord
            </h1>
            <p class="page-subtitle">Vue d'ensemble de votre système de gestion des formations</p>
        </div>

        <?php if (isset($error)): ?>
            <div class="alert alert-danger">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php else: ?>

        <!-- Statistics Cards -->
        <div class="stats-grid">
            <div class="stat-card pays">
                <div class="stat-icon"><i class="fas fa-globe"></i></div>
                <div class="stat-info">
                    <div class="stat-number"><?php echo $stats['pays']; ?></div>
                    <div class="stat-label">Pays</div>
                </div>
            </div>
            <div class="stat-card villes">
                <div class="stat-icon"><i class="fas fa-city"></i></div>
                <div class="stat-info">
                    <div class="stat-number"><?php echo $stats['villes']; ?></div>
                    <div class="stat-label">Villes</div>
                </div>
            </div>
            <div class="stat-card formateurs">
                <div class="stat-icon"><i class="fas fa-chalkboard-teacher"></i></div>
                <div class="stat-info">
                    <div class="stat-number"><?php echo $stats['formateurs']; ?></div>
                    <div class="stat-label">Formateurs</div>
                </div>
            </div>
            <div class="stat-card domaines">
                <div class="stat-icon"><i class="fas fa-sitemap"></i></div>
                <div class="stat-info">
                    <div class="stat-number"><?php echo $stats['domaines']; ?></div>
                    <div class="stat-label">Domaines</div>
                </div>
            </div>
            <div class="stat-card sujets">
                <div class="stat-icon"><i class="fas fa-tags"></i></div>
                <div class="stat-info">
                    <div class="stat-number"><?php echo $stats['sujets']; ?></div>
                    <div class="stat-label">Sujets</div>
                </div>
            </div>
            <div class="stat-card cours">
                <div class="stat-icon"><i class="fas fa-book"></i></div>
                <div class="stat-info">
                    <div class="stat-number"><?php echo $stats['cours']; ?></div>
                    <div class="stat-label">Cours</div>
                </div>
            </div>
            <div class="stat-card formations">
                <div class="stat-icon"><i class="fas fa-graduation-cap"></i></div>
                <div class="stat-info">
                    <div class="stat-number"><?php echo $stats['formations']; ?></div>
                    <div class="stat-label">Formations</div>
                </div>
            </div>
        </div>

        <!-- Dashboard Content -->
        <div class="dashboard-grid">
            <div class="dashboard-card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-clock"></i>
                        Formations récentes
                    </h3>
                </div>
                <div class="card-content">
                    <?php if (!empty($formations_recentes)): ?>
                        <?php foreach ($formations_recentes as $formation): ?>
                            <div class="formation-item">
                                <div class="formation-info">
                                    <div class="formation-title"><?php echo htmlspecialchars($formation['nom_cours']); ?></div>
                                    <div class="formation-details">
                                        <i class="fas fa-user"></i> <?php echo htmlspecialchars($formation['nom_formateur']); ?> &bull;
                                        <i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($formation['nom_ville'] . ', ' . $formation['nom_pays']); ?> &bull;
                                        <i class="fas fa-euro-sign"></i> <?php echo number_format($formation['prix'], 2); ?>DH
                                    </div>
                                </div>
                                <div class="formation-date">
                                    <?php echo date('d/m/Y', strtotime($formation['date_formation'])); ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p style="text-align: center; color: var(--text-muted); padding: 2rem;">
                            <i class="fas fa-info-circle"></i><br>
                            Aucune formation enregistrée
                        </p>
                    <?php endif; ?>
                </div>
            </div>

            <div class="dashboard-card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-chart-pie"></i>
                        Domaines populaires
                    </h3>
                </div>
                <div class="card-content">
                    <?php if (!empty($domaines_populaires)): ?>
                        <?php foreach ($domaines_populaires as $domaine): ?>
                            <div class="formation-item" style="border-left-color: #1abc9c;">
                                <div class="formation-info">
                                    <div class="formation-title"><?php echo htmlspecialchars($domaine['nom_domaine']); ?></div>
                                    <div class="formation-details"><?php echo $domaine['count']; ?> formation(s)</div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p style="text-align: center; color: var(--text-muted); padding: 2rem;">
                            <i class="fas fa-info-circle"></i><br>
                            Aucune donnée disponible
                        </p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="dashboard-card quick-actions">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-bolt"></i>
                    Actions rapides
                </h3>
            </div>
            <div class="card-content">
                <div class="actions-grid">
                    <a href="formation_admin.php" class="action-btn">
                        <i class="fas fa-plus"></i>
                        Nouvelle formation
                    </a>
                    <a href="formateurs.php" class="action-btn">
                        <i class="fas fa-user-plus"></i>
                        Ajouter formateur
                    </a>
                    <a href="cours.php" class="action-btn">
                        <i class="fas fa-book-open"></i>
                        Gérer les cours
                    </a>
                    <a href="domains.php" class="action-btn">
                        <i class="fas fa-sitemap"></i>
                        Gérer les domaines
                    </a>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </main>

    <script>
        function initLoading() {
            const loading = document.getElementById('loading');
            setTimeout(() => loading.classList.add('hidden'), 800);
        }
        window.addEventListener('load', initLoading);
    </script>
</body>
</html>