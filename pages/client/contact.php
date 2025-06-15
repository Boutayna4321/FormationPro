<?php
// contact.php - Page de contact
require_once('../../includes/header.php');
require_once('../admin/functions.php');

$conn = db_connect();
$message = '';
$messageType = '';

// Traitement du formulaire de contact
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = trim($_POST['nom'] ?? '');
    $prenom = trim($_POST['prenom'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $telephone = trim($_POST['telephone'] ?? '');
    $entreprise = trim($_POST['entreprise'] ?? '');
    $sujet = trim($_POST['sujet'] ?? '');
    $message_content = trim($_POST['message'] ?? '');
    $type_demande = $_POST['type_demande'] ?? '';
    
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
    
    if (empty($sujet)) {
        $errors[] = "Le sujet est obligatoire";
    }
    
    if (empty($message_content)) {
        $errors[] = "Le message est obligatoire";
    }
    
    if (empty($type_demande)) {
        $errors[] = "Veuillez sélectionner le type de demande";
    }
    
    // Si pas d'erreurs, enregistrer le message
    if (empty($errors)) {
        try {
            $insertSql = "
                INSERT INTO contacts (
                    nom, prenom, email, telephone, entreprise, 
                    sujet, message, type_demande, date_contact, statut
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW(), 'nouveau')
            ";
            
            $insertStmt = $conn->prepare($insertSql);
            $insertStmt->execute([
                $nom, $prenom, $email, $telephone, $entreprise,
                $sujet, $message_content, $type_demande
            ]);
            
            $message = "Votre message a été envoyé avec succès ! Nous vous répondrons dans les plus brefs délais.";
            $messageType = 'success';
            
            // Réinitialiser le formulaire
            $_POST = [];
            
        } catch (PDOException $e) {
            $message = "Erreur lors de l'envoi du message. Veuillez réessayer.";
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
    <title>Contact - Formation Center</title>
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
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem;
        }

        /* Page Header */
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

        /* Contact Info Cards */
        .contact-info {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
            margin-bottom: 3rem;
        }

        .info-card {
            background: var(--white);
            border-radius: 12px;
            padding: 2rem;
            text-align: center;
            box-shadow: var(--shadow-light);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            border-top: 4px solid var(--secondary-color);
        }

        .info-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-medium);
        }

        .info-icon {
            width: 60px;
            height: 60px;
            background: var(--gradient-secondary);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
            color: var(--white);
            font-size: 1.5rem;
        }

        .info-title {
            font-size: 1.2rem;
            font-weight: 600;
            color: var(--primary-color);
            margin-bottom: 0.5rem;
        }

        .info-content {
            color: var(--light-text);
            line-height: 1.8;
        }

        .info-content a {
            color: var(--secondary-color);
            text-decoration: none;
            transition: color 0.3s ease;
        }

        .info-content a:hover {
            color: var(--primary-color);
        }

        /* Contact Form */
        .contact-section {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 3rem;
            align-items: start;
        }

        .contact-form {
            background: var(--white);
            border-radius: 12px;
            padding: 2.5rem;
            box-shadow: var(--shadow-light);
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
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
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

        .form-input, .form-select {
            padding: 0.9rem 1rem;
            border: 2px solid var(--border-color);
            border-radius: 8px;
            font-size: 1rem;
            transition: all 0.3s ease;
            background: var(--white);
            font-family: inherit;
        }

        .form-input:focus, .form-select:focus {
            outline: none;
            border-color: var(--secondary-color);
            box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.1);
            transform: translateY(-1px);
        }

        .form-textarea {
            min-height: 120px;
            resize: vertical;
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
            background: linear-gradient(135deg, #27ae60, #2ecc71);
            color: var(--white);
        }

        .btn-success:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(39, 174, 96, 0.3);
        }

        .btn-reset {
            background: #6c757d;
            color: var(--white);
        }

        .btn-reset:hover {
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

        /* Map Section */
        .map-section {
            background: var(--white);
            border-radius: 12px;
            padding: 2rem;
            box-shadow: var(--shadow-light);
        }

        .map-header {
            text-align: center;
            margin-bottom: 1.5rem;
        }

        .map-title {
            font-size: 1.4rem;
            font-weight: 600;
            color: var(--primary-color);
            margin-bottom: 0.5rem;
        }

        .map-placeholder {
            width: 100%;
            height: 300px;
            background: linear-gradient(45deg, #f8f9fa, #e9ecef);
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--light-text);
            font-size: 1.1rem;
            margin-bottom: 1rem;
        }

        .map-address {
            text-align: center;
            color: var(--dark-text);
            line-height: 1.8;
        }

        /* FAQ Section */
        .faq-section {
            margin-top: 3rem;
            background: var(--white);
            border-radius: 12px;
            padding: 2.5rem;
            box-shadow: var(--shadow-light);
        }

        .faq-header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .faq-title {
            font-size: 1.8rem;
            font-weight: 600;
            color: var(--primary-color);
            margin-bottom: 0.5rem;
        }

        .faq-item {
            margin-bottom: 1.5rem;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            overflow: hidden;
        }

        .faq-question {
            background: #f8f9fa;
            padding: 1rem 1.5rem;
            cursor: pointer;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-weight: 600;
            color: var(--dark-text);
            transition: background 0.3s ease;
        }

        .faq-question:hover {
            background: #e9ecef;
        }

        .faq-answer {
            padding: 1.5rem;
            background: var(--white);
            color: var(--light-text);
            line-height: 1.8;
            display: none;
        }

        .faq-answer.active {
            display: block;
        }

        .faq-icon {
            transition: transform 0.3s ease;
        }

        .faq-item.active .faq-icon {
            transform: rotate(180deg);
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

            .contact-info {
                grid-template-columns: 1fr;
            }

            .contact-section {
                grid-template-columns: 1fr;
                gap: 2rem;
            }

            .form-grid {
                grid-template-columns: 1fr;
            }

            .contact-form {
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
        .stagger-4 { animation-delay: 0.4s; }
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

     <!-- Header -->
    <div class="pre-header">
        <div class="header">
            <h1>Contactez-nous</h1>
            <p>Nous sommes là pour répondre à toutes vos questions et vous accompagner dans votre parcours de formation</p>
        </div>
    </div>

    <div class="container">
      

      

        <!-- Contact Info Cards -->
        <div class="contact-info slide-up stagger-1">
            <div class="info-card">
                <div class="info-icon">
                    <i class="fas fa-map-marker-alt"></i>
                </div>
                <h3 class="info-title">Notre Adresse</h3>
                <div class="info-content">
                    123 Avenue de la Formation<br>
                    75001 Paris, France<br>
                    <strong>Métro:</strong> Châtelet-Les Halles
                </div>
            </div>

            <div class="info-card">
                <div class="info-icon">
                    <i class="fas fa-phone"></i>
                </div>
                <h3 class="info-title">Téléphone</h3>
                <div class="info-content">
                    <a href="tel:+33123456789">+33 1 23 45 67 89</a><br>
                    <strong>Lun-Ven:</strong> 9h00 - 18h00<br>
                    <strong>Sam:</strong> 9h00 - 12h00
                </div>
            </div>

            <div class="info-card">
                <div class="info-icon">
                    <i class="fas fa-envelope"></i>
                </div>
                <h3 class="info-title">Email</h3>
                <div class="info-content">
                    <a href="mailto:contact@formation-center.co;">contact@formation-center.com</a><br>
                    <a href="mailto:info@formation-center.co;">info@formation-center.com</a><br>
                    <strong>Réponse sous 24h</strong>
                </div>
            </div>
        </div>

        <!-- Contact Section -->
        <div class="contact-section slide-up stagger-2">
            <!-- Contact Form -->
            <div class="contact-form">
                <div class="form-header">
                    <h2 class="form-title">Envoyez-nous un message</h2>
                    <p class="form-subtitle">Remplissez le formulaire ci-dessous et nous vous répondrons rapidement</p>
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
                    
                    <form method="POST" action="" id="contactForm">
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
                                <label for="type_demande" class="form-label required">Type de demande</label>
                                <select id="type_demande" name="type_demande" class="form-select" required>
                                    <option value="">Sélectionnez...</option>
                                    <option value="information" <?php echo (($_POST['type_demande'] ?? '') === 'information') ? 'selected' : ''; ?>>Information générale</option>
                                    <option value="formation" <?php echo (($_POST['type_demande'] ?? '') === 'formation') ? 'selected' : ''; ?>>Question sur une formation</option>
                                    <option value="devis" <?php echo (($_POST['type_demande'] ?? '') === 'devis') ? 'selected' : ''; ?>>Demande de devis</option>
                                    <option value="partenariat" <?php echo (($_POST['type_demande'] ?? '') === 'partenariat') ? 'selected' : ''; ?>>Partenariat</option>
                                    <option value="reclamation" <?php echo (($_POST['type_demande'] ?? '') === 'reclamation') ? 'selected' : ''; ?>>Réclamation</option>
                                    <option value="autre" <?php echo (($_POST['type_demande'] ?? '') === 'autre') ? 'selected' : ''; ?>>Autre</option>
                                </select>
                            </div>
                            
                            <div class="form-group full-width">
                                <label for="sujet" class="form-label required">Sujet</label>
                                <input type="text" id="sujet" name="sujet" class="form-input" required 
                                       placeholder="Résumez votre demande en quelques mots..."
                                       value="<?php echo htmlspecialchars($_POST['sujet'] ?? ''); ?>">
                            </div>
                            
                            <div class="form-group full-width">
                                <label for="message" class="form-label required">Message</label>
                                <textarea id="message" name="message" class="form-input form-textarea" required 
                                          placeholder="Décrivez votre demande en détail..."><?php echo htmlspecialchars($_POST['message'] ?? ''); ?></textarea>
                            </div>
                        </div>
                        
                        <div class="form-actions">
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-paper-plane"></i>
                                Envoyer le message
                            </button>
                            <button type="reset" class="btn btn-reset">
                                <i class="fas fa-undo"></i>
                                Réinitialiser
                            </button>
                        </div>
                    </form>
                <?php else: ?>
                    <div class="form-actions">
                        <a href="formations.php" class="btn btn-primary">
                            <i class="fas fa-search"></i>
                            Découvrir nos formations
                        </a>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Map Section -->
            <div class="map-section">
                <div class="map-header">
                    <h3 class="map-title">Nous trouver</h3>
                </div>
                <div class="map-placeholder">
                    <i class="fas fa-map-marked-alt" style="font-size: 3rem; margin-right: 1rem;"></i>
                    <div>
                        <strong>Carte interactive</strong><br>
                        <small>Intégration Google Maps/OpenStreetMap</small>
                    </div>
                </div>
                <div class="map-address">
                    <strong>Formation Center</strong><br>
                    123 Avenue de la Formation<br>
                    75001 Paris, France<br>
                    <br>
                    <i class="fas fa-metro"></i> <strong>Accès métro:</strong> Châtelet-Les Halles (lignes 1, 4, 7, 11, 14)<br>
                    <i class="fas fa-car"></i> <strong>Parking:</strong> Parking Forum des Halles
                </div>
            </div>
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
      <?php
// formations.php - Page client pour consulter les formations
require_once('../../includes/footer.php');
?>
</body>
</html>