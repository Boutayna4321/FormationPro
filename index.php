<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FormationPro - Excellence en Formation</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        /* Ajout du style pour le loading overlay */
        .loading-overlay {
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
            0% { 
                transform: rotate(0deg);
            }
            100% { 
                transform: rotate(360deg);
            }
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
    </style>
</head>
<body>
    <!-- Loading Screen -->
    <div class="loading-overlay" id="loadingOverlay">
        <div class="spinner-gradient"></div>
        <div class="loading-text">Chargement</div>
        <div class="loading-logo">FormationPro</div>
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

    <script>
        // FormationPro - Scripts JavaScript

        // Loading Screen - Correction pour utiliser loadingOverlay au lieu de loading
        window.addEventListener('load', function() {
            const loading = document.getElementById('loadingOverlay');
            setTimeout(() => {
                loading.classList.add('hidden');
            }, 1000);
        });

        // Smooth Scrolling
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });

        // Navbar scroll effect
        window.addEventListener('scroll', function() {
            const navbar = document.querySelector('.navbar');
            if (window.scrollY > 100) {
                navbar.style.background = 'rgba(255, 255, 255, 0.98)';
                navbar.style.boxShadow = '0 2px 30px rgba(0,0,0,0.15)';
            } else {
                navbar.style.background = 'rgba(255, 255, 255, 0.95)';
                navbar.style.boxShadow = '0 2px 20px rgba(0,0,0,0.1)';
            }
        });

        // Counter Animation
        function animateCounters() {
            const counters = document.querySelectorAll('.stat-number');
            
            counters.forEach(counter => {
                const target = parseInt(counter.getAttribute('data-target'));
                const duration = 2000;
                const step = target / (duration / 16);
                let current = 0;
                
                const timer = setInterval(() => {
                    current += step;
                    if (current >= target) {
                        counter.textContent = target + (target < 100 ? '%' : '');
                        clearInterval(timer);
                    } else {
                        counter.textContent = Math.floor(current) + (target < 100 ? '%' : '');
                    }
                }, 16);
            });
        }

        // Intersection Observer for counter animation
        const statsSection = document.querySelector('.stats');
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    animateCounters();
                    observer.unobserve(entry.target);
                }
            });
        }, { threshold: 0.5 });

        observer.observe(statsSection);

        // Parallax effect for hero section
        window.addEventListener('scroll', () => {
            const scrolled = window.pageYOffset;
            const hero = document.querySelector('.hero');
            const rate = scrolled * -0.5;
            hero.style.transform = `translateY(${rate}px)`;
        });

        // Add hover effects to cards
        document.querySelectorAll('.feature-card, .stat-card').forEach(card => {
            card.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-10px) scale(1.02)';
            });
            
            card.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0) scale(1)';
            });
        });

        // Dynamic background particles (optional enhancement)
        function createParticle() {
            const particle = document.createElement('div');
            particle.style.position = 'absolute';
            particle.style.width = '2px';
            particle.style.height = '2px';
            particle.style.background = 'rgba(255,255,255,0.5)';
            particle.style.borderRadius = '50%';
            particle.style.left = Math.random() * 100 + '%';
            particle.style.animationDuration = (Math.random() * 3 + 2) + 's';
            particle.style.animationName = 'float';
            particle.style.animationIterationCount = 'infinite';
            particle.style.animationTimingFunction = 'ease-in-out';
            
            document.querySelector('.hero').appendChild(particle);
            
            setTimeout(() => {
                particle.remove();
            }, 5000);
        }

        // Create particles periodically
        setInterval(createParticle, 300);
    </script>
    <?php require_once('includes/footer.php'); ?>
</body>
</html>