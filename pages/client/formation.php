<?php
// formations.php - Page client pour consulter les formations
require_once('../../includes/header.php');
require_once('../admin/functions.php');



$conn = db_connect();

// Récupération des paramètres de filtre
$domaineFilter = $_GET['domaine'] ?? '';
$sujetFilter = $_GET['sujet'] ?? '';
$coursFilter = $_GET['cours'] ?? '';
$villeFilter = $_GET['ville'] ?? '';
$searchTerm = $_GET['search'] ?? '';

// Construction de la requête avec filtres
$sql = "
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
    WHERE f.date_formation >= CURDATE()
";

$params = [];

// Ajout des filtres
if (!empty($domaineFilter)) {
    $sql .= " AND d.id = ?";
    $params[] = $domaineFilter;
}

if (!empty($sujetFilter)) {
    $sql .= " AND s.id = ?";
    $params[] = $sujetFilter;
}

if (!empty($coursFilter)) {
    $sql .= " AND c.id = ?";
    $params[] = $coursFilter;
}

if (!empty($villeFilter)) {
    $sql .= " AND v.id = ?";
    $params[] = $villeFilter;
}

if (!empty($searchTerm)) {
    $sql .= " AND (c.nom_cours LIKE ? OR s.nom_sujet LIKE ? OR d.nom_domaine LIKE ? OR v.nom_ville LIKE ?)";
    $searchParam = "%$searchTerm%";
    $params = array_merge($params, [$searchParam, $searchParam, $searchParam, $searchParam]);
}

$sql .= " ORDER BY f.date_formation ASC";

$stmt = $conn->prepare($sql);
$stmt->execute($params);
$formations = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Récupération des données pour les filtres
$domaines = getAllDomaines($conn);
$sujets = getAllSujets($conn);
$cours = getAllCours($conn);
$villes = getAllVilles($conn);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nos Formations - FormationPro</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: #f8f9fa;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }
        
        /* Header */
        .header {
            background: linear-gradient(135deg, #2c3e50, #3498db);
            color: white;
            padding: 2rem 0;
            text-align: center;
            margin-bottom: 2em;
        }

        .header h1 {
            font-size: 2.5rem;
            margin-bottom: 0.5rem;
        }

        .header p {
            font-size: 1.2rem;
            opacity: 0.9;
        }

        /* Navigation */
        .nav {
            background: white;
            padding: 1rem 0;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
        }

        .nav ul {
            list-style: none;
            display: flex;
            justify-content: center;
            gap: 2rem;
        }

        .nav a {
            text-decoration: none;
            color: #2c3e50;
            font-weight: 500;
            padding: 0.5rem 1rem;
            border-radius: 5px;
            transition: all 0.3s ease;
        }

        .nav a:hover, .nav a.active {
            background: #3498db;
            color: white;
        }


        /* Filtres */
        .filters {
            background: white;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
        }

        .filters h2 {
            color: #2c3e50;
            margin-bottom: 1.5rem;
            text-align: center;
        }

        .filter-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        .filter-group {
            display: flex;
            flex-direction: column;
        }

        .filter-group label {
            font-weight: 500;
            margin-bottom: 0.5rem;
            color: #2c3e50;
        }

        .filter-group select,
        .filter-group input {
            padding: 0.75rem;
            border: 2px solid #e0e0e0;
            border-radius: 5px;
            font-size: 1rem;
            transition: border-color 0.3s ease;
        }

        .filter-group select:focus,
        .filter-group input:focus {
            outline: none;
            border-color: #3498db;
        }

        .search-group {
            grid-column: 1 / -1;
        }

        .filter-buttons {
            display: flex;
            gap: 1rem;
            justify-content: center;
        }

        .btn {
            padding: 0.75rem 2rem;
            border: none;
            border-radius: 5px;
            font-size: 1rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
            text-align: center;
        }

        .btn-primary {
            background: #3498db;
            color: white;
        }

        .btn-primary:hover {
            background: #2980b9;
            transform: translateY(-2px);
        }

        .btn-secondary {
            background: #95a5a6;
            color: white;
        }

        .btn-secondary:hover {
            background: #7f8c8d;
        }

        .btn-success {
            background: #3498db;
            color: white;
        }

        .btn-success:hover {
            background: #2c3e50;
        }

        /* Formations Grid */
        .formations-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 2rem;
            margin-bottom: 2rem;
        }

        .formation-card {
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .formation-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.15);
        }

        .card-header {
            background: linear-gradient(135deg, #2c3e50, #3498db);
            color: white;
            padding: 1.5rem;
        }

        .card-header h3 {
            font-size: 1.3rem;
            margin-bottom: 0.5rem;
        }

        .card-header .breadcrumb {
            font-size: 0.9rem;
            opacity: 0.9;
        }

        .card-body {
            padding: 1.5rem;
        }

        .formation-details {
            display: grid;
            gap: 0.75rem;
            margin-bottom: 1.5rem;
        }

        .detail-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .detail-item i {
            width: 20px;
            color: #3498db;
        }

        .price {
            font-size: 1.5rem;
            font-weight: bold;
            color:#2c3e50;
            text-align: center;
            margin-bottom: 1rem;
        }

        .type-badge {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 500;
            text-transform: uppercase;
        }

        .type-presentiel {
            background: #3498db;
            color: white;
        }

        .type-distanciel {
            background: #e67e33;
            color: white;
        }

        /* Messages */
        .no-results {
            text-align: center;
            padding: 3rem;
            background: white;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }

        .no-results h3 {
            color: #7f8c8d;
            margin-bottom: 1rem;
        }

        .no-results p {
            color: #95a5a6;
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
      


        /* Responsive */
        @media (max-width: 768px) {
            .filter-grid {
                grid-template-columns: 1fr;
            }

            .formations-grid {
                grid-template-columns: 1fr;
            }

            .header h1 {
                font-size: 2rem;
            }

            .nav ul {
                flex-direction: column;
                gap: 0.5rem;
            }

            .filter-buttons {
                flex-direction: column;
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
    <!-- Header -->
    <div class="header">
        <div class="container">
            <h1>Nos Formations</h1>
            <p>Découvrez notre catalogue de formations professionnelles</p>
        </div>
    </div>

    <!-- Navigation -->
   

    <div class="container">
        <!-- Filtres -->
        <div class="filters">
            
            <h2> <i class="fas fa-search"></i> Rechercher une Formation</h2>
            <form method="GET" action="formation.php">
                <div class="filter-grid">
                    <div class="filter-group">
                        <label for="domaine">Domaine</label>
                        <select name="domaine" id="domaine" onchange="updateSujets()">
                            <option value="">Tous les domaines</option>
                            <?php foreach ($domaines as $domaine): ?>
                                <option value="<?php echo $domaine['id']; ?>" 
                                        <?php echo $domaineFilter == $domaine['id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($domaine['nom_domaine']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="filter-group">
                        <label for="sujet">Sujet</label>
                        <select name="sujet" id="sujet" onchange="updateCours()">
                            <option value="">Tous les sujets</option>
                            <?php foreach ($sujets as $sujet): ?>
                                <option value="<?php echo $sujet['id']; ?>" 
                                        data-domaine="<?php echo $sujet['domaine_id']; ?>"
                                        <?php echo $sujetFilter == $sujet['id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($sujet['nom_sujet']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="filter-group">
                        <label for="cours">Cours</label>
                        <select name="cours" id="cours">
                            <option value="">Tous les cours</option>
                            <?php foreach ($cours as $c): ?>
                                <option value="<?php echo $c['id']; ?>" 
                                        data-sujet="<?php echo $c['sujet_id']; ?>"
                                        <?php echo $coursFilter == $c['id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($c['nom_cours']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="filter-group">
                        <label for="ville">Ville</label>
                        <select name="ville" id="ville">
                            <option value="">Toutes les villes</option>
                            <?php foreach ($villes as $ville): ?>
                                <option value="<?php echo $ville['id']; ?>" 
                                        <?php echo $villeFilter == $ville['id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($ville['nom_ville'] . ' (' . $ville['nom_pays'] . ')'); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="filter-group search-group">
                        <label for="search">Recherche libre</label>
                        <input type="text" name="search" id="search" placeholder="Rechercher par nom de cours, sujet, domaine ou ville..." 
                               value="<?php echo htmlspecialchars($searchTerm); ?>">
                    </div>
                </div>

                <div class="filter-buttons">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> Rechercher</button>
                    <a href="formation.php" class="btn btn-secondary"><i class="fas fa-sync-alt"></i> Réinitialiser</a>
                </div>
            </form>
        </div>

        <!-- Résultats -->
        <?php if (!empty($formations)): ?>
            <div class="formations-grid">
                <?php foreach ($formations as $formation): ?>
                    <div class="formation-card">
                        <div class="card-header">
                            <h3><?php echo htmlspecialchars($formation['nom_cours']); ?></h3>
                            <div class="breadcrumb">
                                <?php echo htmlspecialchars($formation['nom_domaine'] . ' › ' . $formation['nom_sujet']); ?>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="formation-details">
                                <div class="detail-item">
                                    <i class="fas fa-calendar-alt"></i>
                                    <span><?php echo date('d/m/Y', strtotime($formation['date_formation'])); ?></span>
                                </div>
                                <div class="detail-item">
                                    <i class="fas fa-map-marker-alt"></i>
                                    <span><?php echo htmlspecialchars($formation['nom_ville'] . ', ' . $formation['nom_pays']); ?></span>
                                </div>
                                <div class="detail-item">
                                    <i class="fas fa-user"></i>
                                    <span><?php echo htmlspecialchars($formation['nom_formateur']); ?></span>
                                </div>
                                <div class="detail-item">
                                    
                                    <span class="type-badge type-<?php echo $formation['type_formation']; ?>">
                                        <?php echo ucfirst($formation['type_formation']); ?>
                                    </span>
                                </div>
                            </div>
                            
                            <div class="price">
                                <?php echo number_format($formation['prix'], 2); ?> DH
                            </div>
                            
                            <button onclick="inscrireFormation(<?php echo $formation['id']; ?>)" class="btn btn-success" style="width: 100%;">
                                 S'inscrire à cette formation
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="no-results">
                <h3> <i class="fas fa-search-minus"></i> Aucune formation trouvée</h3>
                <p>Essayez de modifier vos critères de recherche ou consultez notre calendrier complet.</p>
                <br>
                <a href="formations.php" class="btn btn-primary">Voir toutes les formations</a>
                <a href="calendrier.php" class="btn btn-secondary">Consulter le calendrier</a>
            </div>
        <?php endif; ?>
    </div>

    <script>
        // Fonction pour mettre à jour les sujets selon le domaine sélectionné
        function updateSujets() {
            const domaineSelect = document.getElementById('domaine');
            const sujetSelect = document.getElementById('sujet');
            const coursSelect = document.getElementById('cours');
            
            const selectedDomaine = domaineSelect.value;
            
            // Réinitialiser les sujets et cours
            sujetSelect.value = '';
            coursSelect.value = '';
            
            // Afficher/masquer les options de sujets
            Array.from(sujetSelect.options).forEach(option => {
                if (option.value === '') {
                    option.style.display = 'block';
                    return;
                }
                
                const sujetDomaine = option.getAttribute('data-domaine');
                option.style.display = (selectedDomaine === '' || sujetDomaine === selectedDomaine) ? 'block' : 'none';
            });
            
            updateCours();
        }

        // Fonction pour mettre à jour les cours selon le sujet sélectionné
        function updateCours() {
            const sujetSelect = document.getElementById('sujet');
            const coursSelect = document.getElementById('cours');
            
            const selectedSujet = sujetSelect.value;
            
            // Réinitialiser les cours
            coursSelect.value = '';
            
            // Afficher/masquer les options de cours
            Array.from(coursSelect.options).forEach(option => {
                if (option.value === '') {
                    option.style.display = 'block';
                    return;
                }
                
                const coursSujet = option.getAttribute('data-sujet');
                option.style.display = (selectedSujet === '' || coursSujet === selectedSujet) ? 'block' : 'none';
            });
        }

        // Fonction d'inscription à une formation
        function inscrireFormation(formationId) {
            if (confirm('Souhaitez-vous vous inscrire à cette formation ?')) {
                // Rediriger vers la page d'inscription
                window.location.href = 'inscription.php?formation_id=' + formationId;
            }
        }

        // Initialiser les filtres au chargement de la page
        document.addEventListener('DOMContentLoaded', function() {
            updateSujets();
        });

        // Animation au scroll
        window.addEventListener('scroll', function() {
            const cards = document.querySelectorAll('.formation-card');
            cards.forEach(card => {
                const cardTop = card.getBoundingClientRect().top;
                const cardVisible = 150;
                
                if (cardTop < window.innerHeight - cardVisible) {
                    card.style.opacity = '1';
                    card.style.transform = 'translateY(0)';
                }
            });
        });

        
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

    <?php
// formations.php - Page client pour consulter les formations
require_once('../../includes/footer.php');
?>
</body>
</html>