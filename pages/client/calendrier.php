<?php
// calendrier.php - Calendrier des formations

session_start();
require_once('../../includes/header.php');
// formations.php - Page client pour consulter les formations
require_once('../admin/functions.php');

// Vérifier l'authentification (optionnel selon vos besoins)
// requireAdminLogin();

$conn = db_connect();

// Récupération des paramètres de date
$mois = isset($_GET['mois']) ? intval($_GET['mois']) : date('n');
$annee = isset($_GET['annee']) ? intval($_GET['annee']) : date('Y');

// Validation des paramètres
if ($mois < 1 || $mois > 12) $mois = date('n');
if ($annee < 2020 || $annee > 2030) $annee = date('Y');

// Calculs pour le calendrier
$premier_jour = mktime(0, 0, 0, $mois, 1, $annee);
$nombre_jours = date('t', $premier_jour);
$jour_semaine_debut = date('w', $premier_jour);
$jour_semaine_debut = ($jour_semaine_debut == 0) ? 7 : $jour_semaine_debut; // Dimanche = 7

$mois_precedent = ($mois == 1) ? 12 : $mois - 1;
$annee_precedente = ($mois == 1) ? $annee - 1 : $annee;
$mois_suivant = ($mois == 12) ? 1 : $mois + 1;
$annee_suivante = ($mois == 12) ? $annee + 1 : $annee;

// Récupération des formations du mois
$date_debut = sprintf('%04d-%02d-01', $annee, $mois);
$date_fin = sprintf('%04d-%02d-%02d', $annee, $mois, $nombre_jours);

$stmt = $conn->prepare("
    SELECT f.*, 
           c.nom_cours, 
           s.nom_sujet, 
           d.nom_domaine,
           v.nom_ville, 
           p.nom_pays,
           fo.nom_formateur,
           DAY(f.date_formation) as jour
    FROM formations f 
    JOIN cours c ON f.cours_id = c.id 
    JOIN sujets s ON c.sujet_id = s.id 
    JOIN domaines d ON s.domaine_id = d.id 
    JOIN villes v ON f.ville_id = v.id 
    JOIN pays p ON v.pays_id = p.id 
    JOIN formateurs fo ON f.formateur_id = fo.id 
    WHERE f.date_formation BETWEEN ? AND ?
    ORDER BY f.date_formation, f.id
");
$stmt->execute([$date_debut, $date_fin]);
$formations = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Organisation des formations par jour
$formations_par_jour = [];
foreach ($formations as $formation) {
    $jour = $formation['jour'];
    if (!isset($formations_par_jour[$jour])) {
        $formations_par_jour[$jour] = [];
    }
    $formations_par_jour[$jour][] = $formation;
}

$mois_noms = [
    1 => 'Janvier', 2 => 'Février', 3 => 'Mars', 4 => 'Avril',
    5 => 'Mai', 6 => 'Juin', 7 => 'Juillet', 8 => 'Août',
    9 => 'Septembre', 10 => 'Octobre', 11 => 'Novembre', 12 => 'Décembre'
];

$jours_semaine = ['Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam', 'Dim'];
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Calendrier des Formations</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f7fa;
            color: #333;
            line-height: 1.6;
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
        

        .navigation {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .nav-btn {
            background: #3498db;
            color: white;
            border: none;
            padding: 10px 15px;
            margin: 1rem;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            transition: background-color 0.3s;
        }

        .nav-btn:hover {
            background: #2980b9;
            color: white;
            text-decoration: none;
        }

        .month-year {
            font-size: 1.8rem;
            font-weight: bold;
            color: white;
        }

        .calendrier {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            
        }

        .calendrier-header {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            background: #34495e;
            color: white;
        }

        .jour-header {
            padding: 15px 5px;
            text-align: center;
            font-weight: bold;
            font-size: 0.9rem;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem;
        }
        
       
        .calendrier-body {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: 1px;
            background: #ecf0f1;
        }

        .jour {
            background: white;
            min-height: 120px;
            padding: 8px;
            position: relative;
            border: 1px solid #ecf0f1;
            transition: background-color 0.2s;
        }

        .jour:hover {
            background: #f8f9fa;
        }

        .jour.autre-mois {
            background: #f8f9fa;
            color: #bdc3c7;
        }

        .jour.aujourd-hui {
            background: #e8f4fd;
            border: 2px solid #3498db;
        }

        .numero-jour {
            font-weight: bold;
            font-size: 1.1rem;
            margin-bottom: 5px;
            color: #2c3e50;
        }

        .autre-mois .numero-jour {
            color: #bdc3c7;
        }

        .formation {
            background: #3498db;
            color: white;
            padding: 2px 4px;
            margin-bottom: 2px;
            border-radius: 3px;
            font-size: 0.75rem;
            cursor: pointer;
            transition: background-color 0.2s;
        }

        .formation:hover {
            background: #2980b9;
        }

        .formation.presentiel {
            background: #e74c3c;
        }

        .formation.presentiel:hover {
            background: #c0392b;
        }

        .formation.distanciel {
            background: #27ae60;
        }

        .formation.distanciel:hover {
            background: #229954;
        }

        .formation-plus {
            background: #95a5a6;
            color: white;
            padding: 2px 4px;
            border-radius: 3px;
            font-size: 0.7rem;
            text-align: center;
        }

        .legende {
            display: flex;
            justify-content: center;
            gap: 20px;
            margin-top: 20px;
            padding: 15px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .legende-item {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 0.9rem;
        }

        .legende-couleur {
            width: 15px;
            height: 15px;
            border-radius: 3px;
        }

        .actions {
            text-align: center;
            margin-top: 20px;
        }

        .btn {
            background: #2c3e50;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            margin: 0 5px;
            transition: background-color 0.3s;
        }

        .btn:hover {
            background: #34495e;
            color: white;
            text-decoration: none;
        }

        /* Modal pour les détails de formation */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
        }

        .modal-content {
            background-color: white;
            margin: 5% auto;
            padding: 20px;
            border-radius: 10px;
            width: 90%;
            max-width: 600px;
            position: relative;
        }

        .close {
            position: absolute;
            right: 15px;
            top: 15px;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
            color: #aaa;
        }

        .close:hover {
            color: #000;
        }

        .formation-details {
            margin-top: 15px;
        }

        .formation-details h3 {
            color: #2c3e50;
            margin-bottom: 15px;
        }

        .detail-item {
            margin-bottom: 10px;
            padding: 8px;
            background: #f8f9fa;
            border-radius: 5px;
        }

        .detail-label {
            font-weight: bold;
            color: #34495e;
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
            .container {
                padding: 10px;
            }
            
            .jour {
                min-height: 80px;
                padding: 4px;
            }
            
            .formation {
                font-size: 0.7rem;
                padding: 1px 2px;
            }
            
            .navigation {
                flex-direction: column;
                gap: 10px;
                text-align: center;
            }
            
            .legende {
                flex-direction: column;
                gap: 10px;
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
   
        <div class="header">
            <h1><i class="fas fa-calendar-alt"></i> Calendrier des Formations</h1>
            
            <div class="navigation">
                <a href="?mois=<?php echo $mois_precedent; ?>&annee=<?php echo $annee_precedente; ?>" class="nav-btn">
                    ← Mois précédent
                </a>
                
                <div class="month-year">
                    <?php echo $mois_noms[$mois] . ' ' . $annee; ?>
                </div>
                
                <a href="?mois=<?php echo $mois_suivant; ?>&annee=<?php echo $annee_suivante; ?>" class="nav-btn">
                    Mois suivant →
                </a>
            </div>
        </div>
 <d class="container">
        <div class="calendrier">
            <div class="calendrier-header">
                <?php foreach ($jours_semaine as $jour): ?>
                    <div class="jour-header"><?php echo $jour; ?></div>
                <?php endforeach; ?>
            </div>
            
            <div class="calendrier-body">
                <?php
                // Cases vides du début
                for ($i = 1; $i < $jour_semaine_debut; $i++) {
                    echo '<div class="jour autre-mois"></div>';
                }
                
                // Jours du mois
                for ($jour = 1; $jour <= $nombre_jours; $jour++) {
                    $date_actuelle = sprintf('%04d-%02d-%02d', $annee, $mois, $jour);
                    $est_aujourd_hui = ($date_actuelle == date('Y-m-d')) ? 'aujourd-hui' : '';
                    
                    echo '<div class="jour ' . $est_aujourd_hui . '">';
                    echo '<div class="numero-jour">' . $jour . '</div>';
                    
                    // Affichage des formations du jour
                    if (isset($formations_par_jour[$jour])) {
                        $formations_jour = $formations_par_jour[$jour];
                        $max_affichage = 3; // Maximum de formations à afficher
                        
                        for ($i = 0; $i < min(count($formations_jour), $max_affichage); $i++) {
                            $formation = $formations_jour[$i];
                            $class_type = $formation['type_formation'];
                            
                            echo '<div class="formation ' . $class_type . '" onclick="afficherDetails(' . $formation['id'] . ')">';
                            echo htmlspecialchars(substr($formation['nom_cours'], 0, 20));
                            if (strlen($formation['nom_cours']) > 20) echo '...';
                            echo '</div>';
                        }
                        
                        // Si il y a plus de formations
                        if (count($formations_jour) > $max_affichage) {
                            $reste = count($formations_jour) - $max_affichage;
                            echo '<div class="formation-plus">+' . $reste . ' autres</div>';
                        }
                    }
                    
                    echo '</div>';
                }
                
                // Cases vides de la fin pour compléter la dernière semaine
                $cases_fin = 42 - ($jour_semaine_debut - 1) - $nombre_jours;
                for ($i = 0; $i < $cases_fin; $i++) {
                    echo '<div class="jour autre-mois"></div>';
                }
                ?>
            </div>
        </div>

        <div class="legende">
            <div class="legende-item">
                <div class="legende-couleur" style="background: #e74c3c;"></div>
                <span>Formation Présentielle</span>
            </div>
            <div class="legende-item">
                <div class="legende-couleur" style="background: #27ae60;"></div>
                <span>Formation Distancielle</span>
            </div>
            <div class="legende-item">
                <div class="legende-couleur" style="background: #3498db;"></div>
                <span>Aujourd'hui</span>
            </div>
        </div>

        <div class="actions">
            
            <button onclick="window.print()" class="btn">🖨️ Imprimer</button>
        </div>
    </div>

    <!-- Modal pour les détails -->
    <div id="detailModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="fermerModal()">&times;</span>
            <div id="modalContent">
                <!-- Contenu chargé dynamiquement -->
            </div>
        </div>
    </div>

    <script>
        // Données des formations pour JavaScript
        const formations = <?php echo json_encode($formations); ?>;
        
        function afficherDetails(formationId) {
            const formation = formations.find(f => f.id == formationId);
            if (!formation) return;
            
            const modalContent = document.getElementById('modalContent');
            modalContent.innerHTML = `
                <div class="formation-details">
                    <h3>📚 ${formation.nom_cours}</h3>
                    <div class="detail-item">
                        <span class="detail-label">🏷️ Domaine :</span> 
                        ${formation.nom_domaine} > ${formation.nom_sujet}
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">📅 Date :</span> 
                        ${new Date(formation.date_formation).toLocaleDateString('fr-FR', {
                            weekday: 'long',
                            year: 'numeric',
                            month: 'long',
                            day: 'numeric'
                        })}
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">📍 Lieu :</span> 
                        ${formation.nom_ville}, ${formation.nom_pays}
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">👨‍🏫 Formateur :</span> 
                        ${formation.nom_formateur}
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">💰 Prix :</span> 
                        ${parseFloat(formation.prix).toFixed(2)} €
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">📡 Type :</span> 
                        <span style="color: ${formation.type_formation === 'presentiel' ? '#e74c3c' : '#27ae60'};">
                            ${formation.type_formation === 'presentiel' ? '🏢 Présentiel' : '💻 Distanciel'}
                        </span>
                    </div>
                </div>
            `;
            
            document.getElementById('detailModal').style.display = 'block';
        }
        
        function fermerModal() {
            document.getElementById('detailModal').style.display = 'none';
        }
        
        // Fermer le modal en cliquant à l'extérieur
        window.onclick = function(event) {
            const modal = document.getElementById('detailModal');
            if (event.target === modal) {
                modal.style.display = 'none';
            }
        }
        
        // Navigation au clavier
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                fermerModal();
            }
        });
        
        // Ajout d'une fonction pour naviguer rapidement
        function naviguerVers(mois, annee) {
            window.location.href = `?mois=${mois}&annee=${annee}`;
        }
        
        // Navigation rapide avec les flèches du clavier
        document.addEventListener('keydown', function(e) {
            if (e.key === 'ArrowLeft') {
                document.querySelector('.navigation a:first-child').click();
            } else if (e.key === 'ArrowRight') {
                document.querySelector('.navigation a:last-child').click();
            }
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
</body>
</html>