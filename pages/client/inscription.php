<?php
// contact.php - Page de contact
require_once('../../includes/header.php');
require_once('../admin/functions.php');

$conn = db_connect();
$message = '';
$messageType = '';

// Vérifier si un ID de formation est fourni
$formationId = $_GET['formation_id'] ?? null;

if (!$formationId) {
    header('Location: formation.php');
    exit;
}

// Récupérer les détails de la formation
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
    WHERE f.id = ? AND f.date_formation >= CURDATE()
";

$stmt = $conn->prepare($sql);
$stmt->execute([$formationId]);
$formation = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$formation) {
    header('Location: formation.php');
    exit;
}

// Traitement du formulaire d'inscription
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = trim($_POST['nom'] ?? '');
    $prenom = trim($_POST['prenom'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $telephone = trim($_POST['telephone'] ?? '');
    $entreprise = trim($_POST['entreprise'] ?? '');
    $poste = trim($_POST['poste'] ?? '');
    $commentaires = trim($_POST['commentaires'] ?? '');
    
    // Validation des données
    $errors = [];
    
    if (empty($nom)) {
        $errors[] = "Le nom est obligatoire";
    }
    
    if (empty($prenom)) {
        $errors[] = "Le prénom est obligatoire";
    }
    
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Une adresse email valide est requise";
    }
    
    if (empty($telephone)) {
        $errors[] = "Le numéro de téléphone est obligatoire";
    }
    
    // Vérifier si l'email n'est pas déjà inscrit pour cette formation
    if (empty($errors)) {
        $checkSql = "SELECT id FROM inscriptions WHERE formation_id = ? AND email = ?";
        $checkStmt = $conn->prepare($checkSql);
        $checkStmt->execute([$formationId, $email]);
        
        if ($checkStmt->fetch()) {
            $errors[] = "Cette adresse email est déjà inscrite pour cette formation";
        }
    }
    
    // Si pas d'erreurs, enregistrer l'inscription
    if (empty($errors)) {
        try {
            $insertSql = "
                INSERT INTO inscriptions (
                    formation_id, nom, prenom, email, telephone, 
                    entreprise, poste, commentaires, date_inscription, statut
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW(), 'en_attente')
            ";
            
            $insertStmt = $conn->prepare($insertSql);
            $insertStmt->execute([
                $formationId, $nom, $prenom, $email, $telephone,
                $entreprise, $poste, $commentaires
            ]);
            
            $message = "Votre inscription a été enregistrée avec succès ! Vous recevrez une confirmation par email.";
            $messageType = 'success';
            
            // Réinitialiser le formulaire
            $_POST = [];
            
        } catch (PDOException $e) {
            $message = "Erreur lors de l'inscription. Veuillez réessayer.";
            $messageType = 'error';
        }
    } else {
        $message = implode('<br>', $errors);
        $messageType = 'error';
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription - <?php echo htmlspecialchars($formation['nom_cours']); ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <style>
        :root {
            --primary-color: #2c3e50;
            --secondary-color: #3498db;
            --success-color: #27ae60;
            --warning-color: #f39c12;
            --error-color: #e74c3c;
            --dark-text: #2c3e50;
            --light-text: #7f8c8d;
            --white: #ffffff;
            --light-bg: #f8f9fa;
            --border-color: #dee2e6;
            --gradient-primary: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --gradient-secondary: linear-gradient(135deg, #2c3e50, #3498db);
            --shadow-light: 0 2px 10px rgba(0,0,0,0.1);
            --shadow-medium: 0 4px 20px rgba(0,0,0,0.15);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: var(--dark-text);
            background-color: var(--light-bg);
            padding-top: 80px;
        }

        

        /* Main Content */
        .container {
            max-width: 1000px;
            margin: 0 auto;
            padding: 2rem;
        }

        /* Page Header */
        .page-header {
            text-align: center;
            margin-bottom: 3rem;
            padding: 0 1rem;
        }

        .page-title {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--primary-color);
            margin-bottom: 0.5rem;
            background: var(--gradient-primary);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .page-subtitle {
            font-size: 1.1rem;
            color: var(--light-text);
            max-width: 600px;
            margin: 0 auto;
        }

        /* Formation Card */
        .formation-card {
            background: var(--white);
            border-radius: 12px;
            overflow: hidden;
            box-shadow: var(--shadow-light);
            margin-bottom: 2rem;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .formation-card:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-medium);
        }

        .formation-header {
            background: var(--gradient-secondary);
            color: var(--white);
            padding: 2rem;
            text-align: center;
        }

        .formation-title {
            font-size: 1.8rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }

        .formation-breadcrumb {
            font-size: 0.95rem;
            opacity: 0.9;
            margin-bottom: 1rem;
        }

        .formation-price {
            font-size: 2rem;
            font-weight: 700;
            color: #fff;
            background: rgba(255, 255, 255, 0.2);
            padding: 0.5rem 1rem;
            border-radius: 8px;
            display: inline-block;
        }

        .formation-details {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            padding: 2rem;
        }

        .detail-item {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 1rem;
            background: #f8f9fa;
            border-radius: 8px;
            border-left: 4px solid var(--secondary-color);
        }

        .detail-icon {
            width: 24px;
            height: 24px;
            color: var(--secondary-color);
            flex-shrink: 0;
        }

        .type-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.4rem 0.8rem;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .type-presentiel {
            background: #e3f2fd;
            color: #1976d2;
        }

        .type-distanciel {
            background: #e8f5e8;
            color: #2e7d2e;
        }

        /* Form Styles */
        .inscription-form {
            background: var(--white);
            border-radius: 12px;
            padding: 2.5rem;
            box-shadow: var(--shadow-light);
            margin-bottom: 2rem;
        }

        .form-header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .form-title {
            font-size: 1.6rem;
            font-weight: 600;
            color: var(--primary-color);
            margin-bottom: 0.5rem;
        }

        .form-subtitle {
            color: var(--light-text);
            font-size: 0.95rem;
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .form-group {
            display: flex;
            flex-direction: column;
        }

        .form-group.full-width {
            grid-column: 1 / -1;
        }

        .form-label {
            font-weight: 600;
            color: var(--dark-text);
            margin-bottom: 0.5rem;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .form-label.required::after {
            content: " *";
            color: var(--error-color);
        }

        .form-input {
            padding: 0.9rem 1rem;
            border: 2px solid var(--border-color);
            border-radius: 8px;
            font-size: 1rem;
            transition: all 0.3s ease;
            background: var(--white);
        }

        .form-input:focus {
            outline: none;
            border-color: var(--secondary-color);
            box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.1);
            transform: translateY(-1px);
        }

        .form-textarea {
            min-height: 120px;
            resize: vertical;
            font-family: inherit;
        }

        /* Messages */
        .alert {
            padding: 1rem 1.5rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
            border-left: 4px solid;
            font-weight: 500;
        }

        .alert-success {
            background: #d4edda;
            color: #155724;
            border-left-color: var(--success-color);
        }

        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border-left-color: var(--error-color);
        }

        /* Buttons */
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            padding: 0.9rem 2rem;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            text-decoration: none;
            cursor: pointer;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .btn-primary {
            background: var(--gradient-secondary);
            color: var(--white);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(52, 152, 219, 0.3);
        }

        .btn-success {
            background:#3498db;
            color: var(--white);
        }

        .btn-success:hover {
            background:#2c3e50;
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(39, 174, 96, 0.3);
        }

        .btn-secondary {
            background: #6c757d;
            color: var(--white);
        }

        .btn-secondary:hover {
            background: #5a6268;
            transform: translateY(-2px);
        }

        .form-actions {
            display: flex;
            gap: 1rem;
            justify-content: center;
            flex-wrap: wrap;
        }

        .required-note {
            text-align: center;
            color: var(--light-text);
            font-style: italic;
            margin-bottom: 2rem;
            padding: 1rem;
            background: #f8f9fa;
            border-radius: 8px;
            border-left: 4px solid var(--warning-color);
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .nav-menu {
                position: fixed;
                top: 70px;
                left: -100%;
                width: 100%;
                height: calc(100vh - 70px);
                background: rgba(255, 255, 255, 0.98);
                backdrop-filter: blur(10px);
                flex-direction: column;
                justify-content: flex-start;
                align-items: center;
                padding-top: 2rem;
                transition: left 0.3s ease;
            }

            .nav-menu.active {
                left: 0;
            }

            .nav-menu li {
                margin: 1rem 0;
            }

            .mobile-menu-toggle {
                display: block;
            }

            .container {
                padding: 1rem;
            }

            .page-title {
                font-size: 2rem;
            }

            .formation-details {
                grid-template-columns: 1fr;
                padding: 1.5rem;
            }

            .form-grid {
                grid-template-columns: 1fr;
            }

            .inscription-form {
                padding: 1.5rem;
            }

            .form-actions {
                flex-direction: column;
            }

            .btn {
                width: 100%;
            }
        }

        /* Animations */
        .slide-up {
            opacity: 0;
            transform: translateY(30px);
            animation: slideUp 0.6s ease forwards;
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

        @keyframes slideUp {
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .stagger-1 { animation-delay: 0.1s; }
        .stagger-2 { animation-delay: 0.2s; }
        .stagger-3 { animation-delay: 0.3s; }
    </style>
</head>
<body>
    <!-- Navigation -->
 <div class="loading" id="loading">
        <div class="spinner-gradient"></div>
        <div class="loading-text">Chargement</div>
        <div class="loading-logo">FormationPro</div>
    </div>
    </div>

    <div class="container">
        <!-- Page Header -->
        <div class="page-header slide-up">
            <h1 class="page-title">Inscription Formation</h1>
            <p class="page-subtitle">Finalisez votre inscription en remplissant le formulaire ci-dessous</p>
        </div>

        <!-- Formation Info -->
        <div class="formation-card slide-up stagger-1">
            <div class="formation-header">
                <h2 class="formation-title"><?php echo htmlspecialchars($formation['nom_cours']); ?></h2>
                <div class="formation-breadcrumb">
                    <?php echo htmlspecialchars($formation['nom_domaine'] . ' › ' . $formation['nom_sujet']); ?>
                </div>
                <div class="formation-price">
                    <?php echo number_format($formation['prix'], 2, ',', ' '); ?> DH
                </div>
            </div>
            
            <div class="formation-details">
                <div class="detail-item">
                    <i class="fas fa-calendar-alt detail-icon"></i>
                    <span><?php echo date('d/m/Y', strtotime($formation['date_formation'])); ?></span>
                </div>
                <div class="detail-item">
                    <i class="fas fa-map-marker-alt detail-icon"></i>
                    <span><?php echo htmlspecialchars($formation['nom_ville'] . ', ' . $formation['nom_pays']); ?></span>
                </div>
                <div class="detail-item">
                    <i class="fas fa-chalkboard-teacher detail-icon"></i>
                    <span><?php echo htmlspecialchars($formation['nom_formateur']); ?></span>
                </div>
                <div class="detail-item">
                    <i class="fas fa-laptop detail-icon"></i>
                    <span class="type-badge type-<?php echo $formation['type_formation']; ?>">
                        <i class="fas fa-<?php echo $formation['type_formation'] === 'presentiel' ? 'users' : 'video'; ?>"></i>
                        <?php echo ucfirst($formation['type_formation']); ?>
                    </span>
                </div>
            </div>
        </div>

        <!-- Inscription Form -->
        <div class="inscription-form slide-up stagger-2">
            <div class="form-header">
                <h2 class="form-title">Vos Informations</h2>
                <p class="form-subtitle">Toutes vos données sont traitées de manière confidentielle</p>
            </div>
            
            <?php if (!empty($message)): ?>
                <div class="alert alert-<?php echo $messageType; ?>">
                    <i class="fas fa-<?php echo $messageType === 'success' ? 'check-circle' : 'exclamation-triangle'; ?>"></i>
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>
            
            <?php if ($messageType !== 'success'): ?>
                <div class="required-note">
                    <i class="fas fa-info-circle"></i>
                    Les champs marqués d'un astérisque (*) sont obligatoires
                </div>
                
                <form method="POST" action="" id="inscriptionForm">
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="nom" class="form-label required">Nom</label>
                            <input type="text" id="nom" name="nom" class="form-input" required 
                                   value="<?php echo htmlspecialchars($_POST['nom'] ?? ''); ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="prenom" class="form-label required">Prénom</label>
                            <input type="text" id="prenom" name="prenom" class="form-input" required 
                                   value="<?php echo htmlspecialchars($_POST['prenom'] ?? ''); ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="email" class="form-label required">Email</label>
                            <input type="email" id="email" name="email" class="form-input" required 
                                   value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="telephone" class="form-label required">Téléphone</label>
                            <input type="tel" id="telephone" name="telephone" class="form-input" required 
                                   value="<?php echo htmlspecialchars($_POST['telephone'] ?? ''); ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="entreprise" class="form-label">Entreprise</label>
                            <input type="text" id="entreprise" name="entreprise" class="form-input" 
                                   value="<?php echo htmlspecialchars($_POST['entreprise'] ?? ''); ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="poste" class="form-label">Poste/Fonction</label>
                            <input type="text" id="poste" name="poste" class="form-input" 
                                   value="<?php echo htmlspecialchars($_POST['poste'] ?? ''); ?>">
                        </div>
                        
                        <div class="form-group full-width">
                            <label for="commentaires" class="form-label">Commentaires</label>
                            <textarea id="commentaires" name="commentaires" class="form-input form-textarea" 
                                      placeholder="Vos attentes, besoins spécifiques ou questions..."><?php echo htmlspecialchars($_POST['commentaires'] ?? ''); ?></textarea>
                        </div>
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-check"></i>
                            Confirmer l'inscription
                        </button>
                        <a href="formations.php" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i>
                            Retour aux formations
                        </a>
                    </div>
                </form>
            <?php else: ?>
                <div class="form-actions">
                    <a href="formations.php" class="btn btn-primary">
                        <i class="fas fa-search"></i>
                        Autres formations
                    </a>
                    <a href="calendrier.php" class="btn btn-secondary">
                        <i class="fas fa-calendar"></i>
                        Calendrier
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        // Navigation functionality
        function initNavbar() {
            const mobileMenuToggle = document.getElementById('mobileMenuToggle');
            const navMenu = document.getElementById('navMenu');
            
            if (mobileMenuToggle && navMenu) {
                mobileMenuToggle.addEventListener('click', function() {
                    navMenu.classList.toggle('active');
                    const icon = this.querySelector('i');
                    icon.classList.toggle('fa-bars');
                    icon.classList.toggle('fa-times');
                });
            }

            document.querySelectorAll('.nav-link').forEach(link => {
                link.addEventListener('click', function() {
                    if (navMenu) {
                        navMenu.classList.remove('active');
                    }
                    if (mobileMenuToggle) {
                        const icon = mobileMenuToggle.querySelector('i');
                        icon.classList.add('fa-bars');
                        icon.classList.remove('fa-times');
                    }
                });
            });

            window.addEventListener('scroll', function() {
                const navbar = document.getElementById('navbar');
                if (navbar) {
                    if (window.scrollY > 100) {
                        navbar.style.background = 'rgba(255, 255, 255, 0.98)';
                        navbar.style.boxShadow = '0 2px 30px rgba(0,0,0,0.15)';
                    } else {
                        navbar.style.background = 'rgba(255, 255, 255, 0.95)';
                        navbar.style.boxShadow = '0 2px 10px rgba(0,0,0,0.1)';
                    }
                }
            });
        }

        // Form validation and interaction
        document.getElementById('inscriptionForm')?.addEventListener('submit', function(e) {
            const requiredFields = ['nom', 'prenom', 'email', 'telephone'];
            let hasErrors = false;
            
            requiredFields.forEach(field => {
                const input = document.getElementById(field);
                if (!input.value.trim()) {
                    input.style.borderColor = 'var(--error-color)';
                    hasErrors = true;
                } else {
                    input.style.borderColor = 'var(--border-color)';
                }
            });
            
            const email = document.getElementById('email');
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(email.value)) {
                email.style.borderColor = 'var(--error-color)';
                hasErrors = true;
            }
            
            if (hasErrors) {
                e.preventDefault();
                alert('Veuillez remplir correctement tous les champs obligatoires.');
                return false;
            }
            
            return confirm('Confirmer votre inscription à cette formation ?');
        });

        // Phone number formatting
        document.getElementById('telephone')?.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length >= 10) {
                value = value.substring(0, 10);
                value = value.replace(/(\d{2})(\d{2})(\d{2})(\d{2})(\d{2})/, '$1 $2 $3 $4 $5');
            }
            e.target.value = value;
        });

        // Input animations
        document.querySelectorAll('.form-input').forEach(input => {
            input.addEventListener('focus', function() {
                this.style.transform = 'translateY(-1px)';
            });
            
            input.addEventListener('blur', function() {
                this.style.transform = 'translateY(0)';
            });
        });

        // Initialize when DOM is loaded
        document.addEventListener('DOMContentLoaded', initNavbar);
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