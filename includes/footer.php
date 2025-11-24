<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nouveau Footer</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #2c3e50;
            --secondary-color: #3498db;
            --white: #ffffff;
            --light-gray: #f8f9fa;
            --dark-gray: #343a40;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            background: var(--light-gray);
        }

        /* Nouveau Footer Styles */
        .new-footer {
            background: var(--dark-gray);
            color: var(--white);
            padding: 4rem 0 0;
            margin-top: 5rem;
        }

        .footer-wave {
            height: 50px;
            width: 100%;
            background: url('data:image/svg+xml;utf8,<svg viewBox="0 0 1200 120" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="none"><path d="M0,0V46.29c47.79,22.2,103.59,32.17,158,28,70.36-5.37,136.33-33.31,206.8-37.5C438.64,32.43,512.34,53.67,583,72.05c69.27,18,138.3,24.88,209.4,13.08,36.15-6,69.85-17.84,104.45-29.34C989.49,25,1113-14.29,1200,52.47V0Z" fill="%23f8f9fa" opacity=".25"/><path d="M0,0V15.81C13,36.92,27.64,56.86,47.69,72.05,99.41,111.27,165,111,224.58,91.58c31.15-10.15,60.09-26.07,89.67-39.8,40.92-19,84.73-46,130.83-49.67,36.26-2.85,70.9,9.42,98.6,31.56,31.77,25.39,62.32,62,103.63,73,40.44,10.79,81.35-6.69,119.13-24.28s75.16-39,116.92-43.05c59.73-5.85,113.28,22.88,168.9,38.84,30.2,8.66,59,6.17,87.09-7.5,22.43-10.89,48-26.93,60.65-49.24V0Z" fill="%23f8f9fa" opacity=".5"/><path d="M0,0V5.63C149.93,59,314.09,71.32,475.83,42.57c43-7.64,84.23-20.12,127.61-26.46,59-8.63,112.48,12.24,165.56,35.4C827.93,77.22,886,95.24,951.2,90c86.53-7,172.46-45.71,248.8-84.81V0Z" fill="%23f8f9fa"/></svg>');
            background-size: cover;
            margin-top: -50px;
        }

        .footer-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 2rem;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 3rem;
        }

        .footer-section {
            margin-bottom: 2rem;
        }

        .footer-section h3 {
            position: relative;
            margin-bottom: 1.5rem;
            padding-bottom: 0.5rem;
            color: var(--white);
            font-size: 1.3rem;
            font-weight: 600;
        }

        .footer-section h3::after {
            content: '';
            position: absolute;
            left: 0;
            bottom: 0;
            width: 50px;
            height: 2px;
            background: var(--secondary-color);
        }

        .footer-section p,
        .footer-section a {
            color: rgba(255, 255, 255, 0.7);
            text-decoration: none;
            margin-bottom: 0.8rem;
            display: block;
            transition: all 0.3s ease;
            line-height: 1.7;
        }

        .footer-section a:hover {
            color: var(--secondary-color);
            padding-left: 5px;
        }

        .footer-section .social-links {
            display: flex;
            gap: 1rem;
            margin-top: 1.5rem;
        }

        .footer-section .social-links a {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            color: var(--white);
            transition: all 0.3s ease;
        }

        .footer-section .social-links a:hover {
            background: var(--secondary-color);
            transform: translateY(-3px);
        }

        .footer-section .contact-item {
            display: flex;
            align-items: flex-start;
            margin-bottom: 1.2rem;
        }

        .footer-section .contact-item i {
            margin-right: 0.8rem;
            margin-top: 3px;
            color: var(--secondary-color);
            font-size: 1.1rem;
        }

        .footer-newsletter {
            position: relative;
        }

        .footer-newsletter input {
            width: 100%;
            padding: 12px 15px;
            border: none;
            border-radius: 5px;
            margin-bottom: 1rem;
            background: rgba(255, 255, 255, 0.1);
            color: var(--white);
        }

        .footer-newsletter input::placeholder {
            color: rgba(255, 255, 255, 0.6);
        }

        .footer-newsletter button {
            width: 100%;
            padding: 12px;
            border: none;
            border-radius: 5px;
            background: var(--secondary-color);
            color: var(--white);
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .footer-newsletter button:hover {
            background: #2980b9;
            transform: translateY(-2px);
        }

        .footer-bottom {
            text-align: center;
            padding: 2rem 0;
            margin-top: 3rem;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            color: rgba(255, 255, 255, 0.6);
            font-size: 0.9rem;
        }

        .footer-bottom p {
            margin: 0.5rem 0;
        }

        .footer-links {
            display: flex;
            justify-content: center;
            gap: 1.5rem;
            margin-top: 1rem;
        }

        .footer-links a {
            color: rgba(255, 255, 255, 0.6);
            text-decoration: none;
            transition: color 0.3s ease;
        }

        .footer-links a:hover {
            color: var(--secondary-color);
        }

        /* Back to top button */
        .back-to-top {
            position: fixed;
            bottom: 30px;
            right: 30px;
            width: 50px;
            height: 50px;
            background: var(--secondary-color);
            color: var(--white);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            cursor: pointer;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s ease;
            z-index: 999;
        }

        .back-to-top.active {
            opacity: 1;
            visibility: visible;
        }

        .back-to-top:hover {
            background: #2980b9;
            transform: translateY(-3px);
        }

        /* Responsive */
        @media (max-width: 768px) {
            .footer-container {
                grid-template-columns: 1fr;
                gap: 2rem;
                text-align: center;
            }

            .footer-section h3::after {
                left: 50%;
                transform: translateX(-50%);
            }

            .footer-section .social-links {
                justify-content: center;
            }

            .footer-section .contact-item {
                justify-content: center;
            }

            .footer-links {
                flex-direction: column;
                gap: 0.5rem;
            }
        }
    </style>
</head>
<body>
   

    <!-- Nouveau Footer -->
    <div class="footer-wave"></div>
    <footer class="new-footer">
        <div class="footer-container">
            <div class="footer-section">
                <h3>FormationPro</h3>
                <p>Nous nous engageons à fournir des formations professionnelles de qualité pour aider nos clients à atteindre leurs objectifs professionnels.</p>
                <div class="social-links">
                    <a href="#" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a>
                    <a href="#" aria-label="Twitter"><i class="fab fa-twitter"></i></a>
                    <a href="#" aria-label="LinkedIn"><i class="fab fa-linkedin-in"></i></a>
                    <a href="#" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
                    <a href="#" aria-label="YouTube"><i class="fab fa-youtube"></i></a>
                </div>
            </div>
            
            <div class="footer-section">
                <h3>Nos Formations</h3>
                <a href="#">Management et Leadership</a>
                <a href="#">Développement Informatique</a>
                <a href="#">Réseaux et Sécurité</a>
                <a href="#">Big Data et Analytics</a>
                <a href="#">Cloud Computing</a>
                <a href="#">Cybersécurité</a>
            </div>
            
            <div class="footer-section">
                <h3>Liens Rapides</h3>
                <a href="#">Accueil</a>
                <a href="#">À propos de nous</a>
                <a href="#">Nos formateurs</a>
                <a href="#">Témoignages</a>
                <a href="#">Blog</a>
                <a href="#">Contactez-nous</a>
            </div>
            
            <div class="footer-section">
                <h3>Contactez-nous</h3>
                <div class="contact-item">
                    <i class="fas fa-map-marker-alt"></i>
                    <span>123 Rue de la Formation, Fès 30000, Maroc</span>
                </div>
                <div class="contact-item">
                    <i class="fas fa-phone-alt"></i>
                    <span>+212 5XX XXX XXX</span>
                </div>
                <div class="contact-item">
                    <i class="fas fa-envelope"></i>
                    <span>contact@formationpro.ma</span>
                </div>
                <div class="contact-item">
                    <i class="fas fa-clock"></i>
                    <span>Lun-Ven: 8h30-18h30<br>Sam: 9h-13h</span>
                </div>
            </div>
            
            <div class="footer-section footer-newsletter">
                <h3>Newsletter</h3>
                <p>Abonnez-vous à notre newsletter pour recevoir les dernières actualités et offres.</p>
                <input type="email" placeholder="Votre email">
                <button type="submit">S'abonner</button>
            </div>
        </div>
        
        <div class="footer-bottom">
            <p>&copy; <span id="current-year">2024</span> FormationPro. Tous droits réservés.</p>
            <div class="footer-links">
                <a href="#">Politique de confidentialité</a>
                <a href="#">Conditions d'utilisation</a>
                <a href="#">Mentions légales</a>
                <a href="#">Plan du site</a>
            </div>
        </div>
    </footer>

    <!-- Back to top button -->
    <div class="back-to-top" id="back-to-top">
        <i class="fas fa-arrow-up"></i>
    </div>

    <script>
        // Footer functionality
        document.addEventListener('DOMContentLoaded', function() {
            // Update copyright year
            const yearElement = document.getElementById('current-year');
            if (yearElement) {
                yearElement.textContent = new Date().getFullYear();
            }

            // Back to top button
            const backToTopButton = document.getElementById('back-to-top');
            
            window.addEventListener('scroll', function() {
                if (window.pageYOffset > 300) {
                    backToTopButton.classList.add('active');
                } else {
                    backToTopButton.classList.remove('active');
                }
            });

            backToTopButton.addEventListener('click', function(e) {
                e.preventDefault();
                window.scrollTo({
                    top: 0,
                    behavior: 'smooth'
                });
            });

            // Smooth scroll for footer links
            document.querySelectorAll('.footer a[href^="#"]').forEach(anchor => {
                anchor.addEventListener('click', function(e) {
                    e.preventDefault();
                    const targetId = this.getAttribute('href');
                    if (targetId === '#') return;
                    
                    const target = document.querySelector(targetId);
                    if (target) {
                        target.scrollIntoView({
                            behavior: 'smooth',
                            block: 'start'
                        });
                    }
                });
            });

            // Newsletter form handling
            const newsletterForm = document.querySelector('.footer-newsletter');
            if (newsletterForm) {
                newsletterForm.addEventListener('submit', function(e) {
                    e.preventDefault();
                    const emailInput = this.querySelector('input[type="email"]');
                    if (emailInput && emailInput.value) {
                        alert('Merci pour votre abonnement! Vous recevrez bientôt nos actualités.');
                        emailInput.value = '';
                    }
                });
            }
        });
    </script>
</body>
</html>