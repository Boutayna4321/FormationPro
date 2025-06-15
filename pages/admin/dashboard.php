<?php
session_start();
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
        :root {
            --primary-color: #2c3e50;
            --secondary-color: #3498db;
            --dark-text: #2c3e50;
            --white: #ffffff;
            --light-gray: #f8f9fa;
            --border-color: #e9ecef;
            --gradient-primary: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --gradient-secondary: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            --sidebar-width: 280px;
            --hover-bg: rgba(52, 152, 219, 0.1);
            --success-color: #27ae60;
            --warning-color: #f39c12;
            --danger-color: #e74c3c;
            --info-color: #3498db;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: var(--light-gray);
            margin-left: var(--sidebar-width);
            transition: margin-left 0.3s ease;
        }

        /* Sidebar Styles - Copié du fichier original 
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            width: var(--sidebar-width);
            height: 100vh;
            background: rgba(255, 255, 255, 0.98);
            backdrop-filter: blur(15px);
            border-right: 1px solid var(--border-color);
            box-shadow: 2px 0 20px rgba(0,0,0,0.1);
            z-index: 1000;
            transition: all 0.3s ease;
            overflow-y: auto;
        }

        .sidebar::-webkit-scrollbar {
            width: 6px;
        }

        .sidebar::-webkit-scrollbar-track {
            background: transparent;
        }

        .sidebar::-webkit-scrollbar-thumb {
            background: rgba(52, 152, 219, 0.3);
            border-radius: 10px;
        }

        .sidebar-header {
            padding: 2rem 1.5rem;
            border-bottom: 1px solid var(--border-color);
            background: linear-gradient(135deg, rgba(102, 126, 234, 0.1) 0%, rgba(118, 75, 162, 0.1) 100%);
        }

        .sidebar-logo {
            font-size: 1.8rem;
            font-weight: bold;
            background: var(--gradient-primary);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            text-decoration: none;
            display: block;
            text-align: center;
        }

        .sidebar-subtitle {
            text-align: center;
            color: var(--dark-text);
            font-size: 0.9rem;
            margin-top: 0.5rem;
            opacity: 0.7;
            font-weight: 500;
        }

        .sidebar-nav {
            padding: 1rem 0;
        }

        .nav-list {
            list-style: none;
        }

        .nav-item {
            margin: 0.2rem 0;
        }

        .nav-link {
            display: flex;
            align-items: center;
            padding: 1rem 1.5rem;
            text-decoration: none;
            color: var(--dark-text);
            font-weight: 500;
            transition: all 0.3s ease;
            position: relative;
            border-radius: 0 25px 25px 0;
            margin-right: 1rem;
        }

        .nav-link:hover {
            background: var(--hover-bg);
            color: var(--secondary-color);
            transform: translateX(5px);
        }

        .nav-link::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            height: 100%;
            width: 3px;
            background: var(--gradient-primary);
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .nav-link:hover::before,
        .nav-link.active::before {
            opacity: 1;
        }

        .nav-link.active {
            background: var(--hover-bg);
            color: var(--secondary-color);
        }

        .nav-icon {
            width: 20px;
            margin-right: 1rem;
            text-align: center;
            font-size: 1.1rem;
        }

        .nav-text {
            flex: 1;
        }

        .sidebar-footer {
            position: absolute;
            bottom: 0;
            width: 100%;
            padding: 1rem;
            border-top: 1px solid var(--border-color);
            background: rgba(255, 255, 255, 0.8);
        }

        .logout-link {
            display: flex;
            align-items: center;
            padding: 1rem 1.5rem;
            text-decoration: none;
            color: #e74c3c;
            font-weight: 500;
            transition: all 0.3s ease;
            border-radius: 10px;
            border: 1px solid transparent;
        }

        .logout-link:hover {
            background: rgba(231, 76, 60, 0.1);
            border-color: rgba(231, 76, 60, 0.2);
            transform: translateY(-2px);
        }

        /* Main Content */
        .main-content {
            padding: 2rem;
            min-height: 100vh;
        }

        .page-header {
            background: var(--white);
            padding: 2rem;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
            background: var(--gradient-primary);
            color: var(--white);
        }

        .page-title {
            font-size: 2.5rem;
            margin-bottom: 0.5rem;
            font-weight: 700;
        }

        .page-subtitle {
            font-size: 1.1rem;
            opacity: 0.9;
        }

        /* Stats Cards */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: var(--white);
            padding: 2rem;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            display: flex;
            align-items: center;
            transition: all 0.3s ease;
            border-left: 4px solid var(--secondary-color);
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0,0,0,0.15);
        }

        .stat-card.pays { border-left-color: #3498db; }
        .stat-card.villes { border-left-color: #9b59b6; }
        .stat-card.formateurs { border-left-color: #e67e22; }
        .stat-card.domaines { border-left-color: #1abc9c; }
        .stat-card.sujets { border-left-color: #f39c12; }
        .stat-card.cours { border-left-color: #e74c3c; }
        .stat-card.formations { border-left-color: #27ae60; }

        .stat-icon {
            font-size: 3rem;
            margin-right: 1.5rem;
            padding: 1rem;
            border-radius: 50%;
            background: rgba(52, 152, 219, 0.1);
            color: var(--secondary-color);
        }

        .stat-card.pays .stat-icon { background: rgba(52, 152, 219, 0.1); color: #3498db; }
        .stat-card.villes .stat-icon { background: rgba(155, 89, 182, 0.1); color: #9b59b6; }
        .stat-card.formateurs .stat-icon { background: rgba(230, 126, 34, 0.1); color: #e67e22; }
        .stat-card.domaines .stat-icon { background: rgba(26, 188, 156, 0.1); color: #1abc9c; }
        .stat-card.sujets .stat-icon { background: rgba(243, 156, 18, 0.1); color: #f39c12; }
        .stat-card.cours .stat-icon { background: rgba(231, 76, 60, 0.1); color: #e74c3c; }
        .stat-card.formations .stat-icon { background: rgba(39, 174, 96, 0.1); color: #27ae60; }

        .stat-info {
            flex: 1;
        }

        .stat-number {
            font-size: 2.5rem;
            font-weight: bold;
            color: var(--dark-text);
            margin-bottom: 0.5rem;
        }

        .stat-label {
            color: #6c757d;
            font-size: 1rem;
            font-weight: 500;
        }

        /* Dashboard Content Grid */
        .dashboard-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 2rem;
            margin-bottom: 2rem;
        }

        .dashboard-card {
            background: var(--white);
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            overflow: hidden;
        }

        .card-header {
            padding: 1.5rem 2rem;
            border-bottom: 1px solid var(--border-color);
            background: linear-gradient(135deg, rgba(102, 126, 234, 0.05) 0%, rgba(118, 75, 162, 0.05) 100%);
        }

        .card-title {
            font-size: 1.3rem;
            color: var(--dark-text);
            font-weight: 600;
            display: flex;
            align-items: center;
        }

        .card-title i {
            margin-right: 0.5rem;
            color: var(--secondary-color);
        }

        .card-content {
            padding: 2rem;
        }

        /* Recent Formations */
        .formation-item {
            display: flex;
            align-items: center;
            padding: 1rem;
            border-radius: 10px;
            margin-bottom: 1rem;
            transition: all 0.3s ease;
            border-left: 3px solid var(--secondary-color);
        }

        .formation-item:hover {
            background: var(--light-gray);
            transform: translateX(5px);
        }

        .formation-info {
            flex: 1;
        }

        .formation-title {
            font-weight: 600;
            color: var(--dark-text);
            margin-bottom: 0.3rem;
        }

        .formation-details {
            font-size: 0.9rem;
            color: #6c757d;
        }

        .formation-date {
            background: var(--gradient-primary);
            color: var(--white);
            padding: 0.3rem 0.8rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 500;
        }

        /* Quick Actions */
        .quick-actions {
            grid-column: 1 / -1;
        }

        .actions-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
        }

        .action-btn {
            display: flex;
            align-items: center;
            padding: 1.5rem;
            background: var(--white);
            border: 2px solid var(--border-color);
            border-radius: 15px;
            text-decoration: none;
            color: var(--dark-text);
            transition: all 0.3s ease;
            font-weight: 500;
        }

        .action-btn:hover {
            border-color: var(--secondary-color);
            background: var(--hover-bg);
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }

        .action-btn i {
            font-size: 1.5rem;
            margin-right: 1rem;
            color: var(--secondary-color);
        }

        /* Charts */
        .chart-container {
            height: 200px;
            display: flex;
            align-items: end;
            justify-content: space-around;
            padding: 1rem;
            background: linear-gradient(135deg, rgba(102, 126, 234, 0.05) 0%, rgba(118, 75, 162, 0.05) 100%);
            border-radius: 10px;
        }

        .chart-bar {
            background: var(--gradient-primary);
            width: 30px;
            border-radius: 5px 5px 0 0;
            transition: all 0.3s ease;
            position: relative;
        }

        .chart-bar:hover {
            transform: scale(1.1);
        }

        .chart-label {
            position: absolute;
            bottom: -25px;
            left: 50%;
            transform: translateX(-50%);
            font-size: 0.8rem;
            color: #6c757d;
            white-space: nowrap;
        }

        /* Mobile Responsive */
        .sidebar-toggle {
            display: none;
            position: fixed;
            top: 1rem;
            left: 1rem;
            background: var(--white);
            border: 1px solid var(--border-color);
            border-radius: 10px;
            padding: 0.8rem;
            font-size: 1.2rem;
            color: var(--dark-text);
            cursor: pointer;
            z-index: 1001;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
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
        @media (max-width: 768px) {
            body {
                margin-left: 0;
            }

            .sidebar {
                left: -100%;
            }

            .sidebar.active {
                left: 0;
            }

            .sidebar-toggle {
                display: block;
            }

            .main-content {
                padding: 1rem;
                padding-top: 5rem;
            }

            .stats-grid {
                grid-template-columns: 1fr;
            }

            .dashboard-grid {
                grid-template-columns: 1fr;
            }

            .page-title {
                font-size: 2rem;
            }
        }

        /* Animation */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .stat-card,
        .dashboard-card {
            animation: fadeInUp 0.5s ease forwards;
        }

        .stat-card:nth-child(1) { animation-delay: 0.1s; }
        .stat-card:nth-child(2) { animation-delay: 0.2s; }
        .stat-card:nth-child(3) { animation-delay: 0.3s; }
        .stat-card:nth-child(4) { animation-delay: 0.4s; }
        .stat-card:nth-child(5) { animation-delay: 0.5s; }
        .stat-card:nth-child(6) { animation-delay: 0.6s; }
        .stat-card:nth-child(7) { animation-delay: 0.7s; }
    </style>
</head>
<body>
    <div class="loading" id="loading">
        <div class="spinner-gradient"></div>
        <div class="loading-text">Chargement</div>
        <div class="loading-logo">FormationPro</div>
    </div>
    </div>
    <!-- Mobile Toggle Button -->
    <button class="sidebar-toggle" id="sidebarToggle">
        <i class="fas fa-bars"></i>
    </button>

    <!-- Sidebar --
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <a href="dashboard.php" class="sidebar-logo">FormationPro</a>
            <div class="sidebar-subtitle">Administration</div>
        </div>

        <nav class="sidebar-nav">
            <ul class="nav-list">
                <li class="nav-item">
                    <a href="../admin/dashboard.php" class="nav-link active">
                        <i class="fas fa-tachometer-alt nav-icon"></i>
                        <span class="nav-text">Dashboard</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="pays.php" class="nav-link">
                        <i class="fas fa-globe nav-icon"></i>
                        <span class="nav-text">Pays</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="villes.php" class="nav-link">
                        <i class="fas fa-city nav-icon"></i>
                        <span class="nav-text">Villes</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="formateurs.php" class="nav-link">
                        <i class="fas fa-chalkboard-teacher nav-icon"></i>
                        <span class="nav-text">Formateurs</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="domains.php" class="nav-link">
                        <i class="fas fa-sitemap nav-icon"></i>
                        <span class="nav-text">Domaines</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="sujet.php" class="nav-link">
                        <i class="fas fa-tags nav-icon"></i>
                        <span class="nav-text">Sujets</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="cours.php" class="nav-link">
                        <i class="fas fa-book nav-icon"></i>
                        <span class="nav-text">Cours</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="formation_admin.php" class="nav-link">
                        <i class="fas fa-graduation-cap nav-icon"></i>
                        <span class="nav-text">Formations</span>
                    </a>
                </li>
            </ul>
        </nav>

        <div class="sidebar-footer">
            <a href="logout.php" class="logout-link">
                <i class="fas fa-sign-out-alt nav-icon"></i>
                <span class="nav-text">Déconnexion</span>
            </a>
        </div>
    </aside>

    <!-- Main Content -->
    <main class="main-content">
        <!-- Page Header -->
        <div class="page-header">
            <h1 class="page-title">
                <i class="fas fa-tachometer-alt" style="margin-right: 0.5rem;"></i>
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
                <div class="stat-icon">
                    <i class="fas fa-globe"></i>
                </div>
                <div class="stat-info">
                    <div class="stat-number"><?php echo $stats['pays']; ?></div>
                    <div class="stat-label">Pays</div>
                </div>
            </div>

            <div class="stat-card villes">
                <div class="stat-icon">
                    <i class="fas fa-city"></i>
                </div>
                <div class="stat-info">
                    <div class="stat-number"><?php echo $stats['villes']; ?></div>
                    <div class="stat-label">Villes</div>
                </div>
            </div>

            <div class="stat-card formateurs">
                <div class="stat-icon">
                    <i class="fas fa-chalkboard-teacher"></i>
                </div>
                <div class="stat-info">
                    <div class="stat-number"><?php echo $stats['formateurs']; ?></div>
                    <div class="stat-label">Formateurs</div>
                </div>
            </div>

            <div class="stat-card domaines">
                <div class="stat-icon">
                    <i class="fas fa-sitemap"></i>
                </div>
                <div class="stat-info">
                    <div class="stat-number"><?php echo $stats['domaines']; ?></div>
                    <div class="stat-label">Domaines</div>
                </div>
            </div>

            <div class="stat-card sujets">
                <div class="stat-icon">
                    <i class="fas fa-tags"></i>
                </div>
                <div class="stat-info">
                    <div class="stat-number"><?php echo $stats['sujets']; ?></div>
                    <div class="stat-label">Sujets</div>
                </div>
            </div>

            <div class="stat-card cours">
                <div class="stat-icon">
                    <i class="fas fa-book"></i>
                </div>
                <div class="stat-info">
                    <div class="stat-number"><?php echo $stats['cours']; ?></div>
                    <div class="stat-label">Cours</div>
                </div>
            </div>

            <div class="stat-card formations">
                <div class="stat-icon">
                    <i class="fas fa-graduation-cap"></i>
                </div>
                <div class="stat-info">
                    <div class="stat-number"><?php echo $stats['formations']; ?></div>
                    <div class="stat-label">Formations</div>
                </div>
            </div>
        </div>

        <!-- Dashboard Content -->
        <div class="dashboard-grid">
            <!-- Recent Formations -->
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
                                        <i class="fas fa-user"></i> <?php echo htmlspecialchars($formation['nom_formateur']); ?> • 
                                        <i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($formation['nom_ville'] . ', ' . $formation['nom_pays']); ?> • 
                                        <i class="fas fa-euro-sign"></i> <?php echo number_format($formation['prix'], 2); ?>€
                                    </div>
                                </div>
                                <div class="formation-date">
                                    <?php echo date('d/m/Y', strtotime($formation['date_formation'])); ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p style="text-align: center; color: #6c757d; padding: 2rem;">
                            <i class="fas fa-info-circle"></i><br>
                            Aucune formation enregistrée
                        </p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Popular Domains -->
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
                            <div class="formation-item">
                                <div class="formation-info">
                                    <div class="formation-title"><?php echo htmlspecialchars($domaine['nom_domaine']); ?></div>
                                    <div class="formation-details">
                                        <?php echo $domaine['count']; ?> formation(s)
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p style="text-align: center; color: #6c757d;">Aucune donnée disponible</p>
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
                        <br>
                            Aucun domain enregistré
                        </p>
                    <?php endif; ?>
                </div>
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