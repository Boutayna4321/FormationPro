<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FormationPro - Excellence en Formation</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <!-- Loading Screen -->
    <div class="loading" id="loading">
        <div class="spinner"></div>
    </div>

    <!-- Navigation -->
    <nav class="navbar">
        <div class="nav-container">
            <a href="#" class="logo">FormationPro</a>
            <ul class="nav-menu">
                <li><a href="index.php" class="nav-link">Accueil</a></li>
                <li><a href="pages/client/formation.php" class="nav-link">Formations</a></li>
                <li><a href="pages/client/calendrier.php" class="nav-link">Calendrier</a></li>
                <li><a href="pages/client/contact.php" class="nav-link">Contact</a></li>
            </ul>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero" id="accueil">
        <div class="hero-content">
            <div class="hero-text">
                <h1>Excellence en Formation Professionnelle</h1>
                <p>Développez vos compétences avec nos formations de qualité supérieure. Nous proposons des programmes complets en Management, Informatique et Technologies pour booster votre carrière.</p>
                <a href="pages/client/formation.php" class="cta-button">Découvrir nos formations</a>
            </div>
            <div class="hero-visual">
                <div class="floating-cards">
                    <div class="floating-card">
                        <i class="fas fa-graduation-cap stat-icon"></i>
                        <h3>Management</h3>
                        <p>Scrum, Prince2, ITIL</p>
                    </div>
                    <div class="floating-card">
                        <i class="fas fa-code stat-icon"></i>
                        <h3>Informatique</h3>
                        <p>JEE, Web, Big Data</p>
                    </div>
                    <div class="floating-card">
                        <i class="fas fa-network-wired stat-icon"></i>
                        <h3>Réseaux</h3>
                        <p>CISCO, Sécurité</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Stats Section -->
    <section class="stats">
        <div class="stats-container">
            <div class="stat-card">
                <i class="fas fa-chart-line stat-icon"></i>
                <div class="stat-number" data-target="95">0</div>
                <div class="stat-label">% de Satisfaction</div>
            </div>
            <div class="stat-card">
                <i class="fas fa-trophy stat-icon"></i>
                <div class="stat-number" data-target="87">0</div>
                <div class="stat-label">% de Succès</div>
            </div>
            <div class="stat-card">
                <i class="fas fa-globe stat-icon"></i>
                <div class="stat-number" data-target="92">0</div>
                <div class="stat-label">% de Couverture</div>
            </div>
            <div class="stat-card">
                <i class="fas fa-users stat-icon"></i>
                <div class="stat-number" data-target="5000">0</div>
                <div class="stat-label">Étudiants Formés</div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="features">
        <div class="features-container">
            <h2 class="section-title">Pourquoi Choisir FormationPro ?</h2>
            <div class="features-grid">
                <div class="feature-card">
                    <i class="fas fa-chalkboard-teacher feature-icon"></i>
                    <h3 class="feature-title">Formateurs Experts</h3>
                    <p class="feature-description">Nos formateurs sont des professionnels expérimentés avec une expertise reconnue dans leur domaine.</p>
                </div>
                <div class="feature-card">
                    <i class="fas fa-laptop feature-icon"></i>
                    <h3 class="feature-title">Formation Hybride</h3>
                    <p class="feature-description">Choisissez entre formation en présentiel ou à distance selon vos préférences et contraintes.</p>
                </div>
                <div class="feature-card">
                    <i class="fas fa-certificate feature-icon"></i>
                    <h3 class="feature-title">Certification Reconnue</h3>
                    <p class="feature-description">Obtenez des certifications valorisées sur le marché du travail pour booster votre carrière.</p>
                </div>
                <div class="feature-card">
                    <i class="fas fa-calendar-alt feature-icon"></i>
                    <h3 class="feature-title">Planning Flexible</h3>
                    <p class="feature-description">Consultez notre calendrier et choisissez les dates qui vous conviennent le mieux.</p>
                </div>
                <div class="feature-card">
                    <i class="fas fa-search feature-icon"></i>
                    <h3 class="feature-title">Recherche Avancée</h3>
                    <p class="feature-description">Trouvez facilement la formation qui correspond à vos besoins grâce à notre système de filtres.</p>
                </div>
                <div class="feature-card">
                    <i class="fas fa-handshake feature-icon"></i>
                    <h3 class="feature-title">Suivi Personnalisé</h3>
                    <p class="feature-description">Bénéficiez d'un accompagnement personnalisé tout au long de votre parcours de formation.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Action Section -->
    <section class="action-section">
        <div class="action-content">
            <h2 class="action-title">Prêt à Commencer Votre Formation ?</h2>
            <p class="action-description">Rejoignez des milliers de professionnels qui ont déjà fait confiance à FormationPro pour développer leurs compétences.</p>
            <div class="action-buttons">
                <a href="pages/client/formation.php" class="btn btn-primary">Voir les Formations</a>
                <a href="pages/client/contact.php" class="btn btn-secondary">Nous Contacter</a>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="footer-container">
            <div class="footer-section">
                <h3>FormationPro</h3>
                <p>Votre partenaire de confiance pour l'excellence en formation professionnelle.</p>
                <p>© 2024 FormationPro. Tous droits réservés.</p>
            </div>
            <div class="footer-section">
                <h3>Formations</h3>
                <a href="#">Management</a>
                <a href="#">Informatique</a>
                <a href="#">Réseaux</a>
                <a href="#">Big Data</a>
            </div>
            <div class="footer-section">
                <h3>Liens Utiles</h3>
                <a href="#">À propos</a>
                <a href="#">Nos formateurs</a>
                <a href="#">Témoignages</a>
                <a href="#">FAQ</a>
            </div>
            <div class="footer-section">
                <h3>Contact</h3>
                <p>Email: info@formationpro.com</p>
                <p>Tél: +212 5XX XX XX XX</p>
                <p>Adresse: Fès, Maroc</p>
            </div>
        </div>
        <div class="footer-bottom">
            <p>Développé avec ❤️ pour l'excellence en formation</p>
        </div>
    </footer>

    <script src="assets/js/script.js"></script>
</body>
</html>