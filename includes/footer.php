<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Footer Component</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #2c3e50;
            --white: #ffffff;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
        }

        /* Footer Styles */
        .footer {
            background: var(--primary-color);
            color: var(--white);
            padding: 3rem 0 1rem;
            margin-top: auto;
        }

        .footer-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 2rem;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 3rem;
        }

        .footer-section h3 {
            margin-bottom: 1.5rem;
            color: var(--white);
            font-size: 1.2rem;
            font-weight: 600;
        }

        .footer-section p,
        .footer-section a {
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            margin-bottom: 0.5rem;
            display: block;
            transition: color 0.3s ease;
        }

        .footer-section a:hover {
            color: var(--white);
            transform: translateX(5px);
        }

        .footer-section .social-links {
            display: flex;
            gap: 1rem;
            margin-top: 1rem;
        }

        .footer-section .social-links a {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            transition: all 0.3s ease;
        }

        .footer-section .social-links a:hover {
            background: rgba(255, 255, 255, 0.2);
            transform: translateY(-3px);
        }

        .footer-section .contact-item {
            display: flex;
            align-items: center;
            margin-bottom: 1rem;
        }

        .footer-section .contact-item i {
            margin-right: 0.5rem;
            width: 20px;
            color: rgba(255, 255, 255, 0.6);
        }

        .footer-bottom {
            text-align: center;
            padding-top: 2rem;
            margin-top: 2rem;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            color: rgba(255, 255, 255, 0.6);
        }

        .footer-bottom p {
            margin: 0.5rem 0;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .footer-container {
                grid-template-columns: 1fr;
                gap: 2rem;
                text-align: center;
            }

            .footer-section .social-links {
                justify-content: center;
            }

            .footer-section .contact-item {
                justify-content: center;
            }
        }

        /* Demo content for testing */
        .demo-content {
            min-height: 100vh;
            padding: 2rem;
            background: linear-gradient(to bottom, #f8f9fa, #e9ecef);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .demo-section {
            text-align: center;
            padding: 3rem;
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>
    <!-- Demo Content -->
   

    <!-- Footer Component -->
    <footer class="footer">
        <div class="footer-container">
            <div class="footer-section">
                <h3>FormationPro</h3>
                <p>Votre partenaire de confiance pour l'excellence en formation professionnelle.</p>
                <div class="social-links">
                    <a href="#" aria-label="Facebook">
                        <i class="fab fa-facebook-f"></i>
                    </a>
                    <a href="#" aria-label="Twitter">
                        <i class="fab fa-twitter"></i>
                    </a>
                    <a href="#" aria-label="LinkedIn">
                        <i class="fab fa-linkedin-in"></i>
                    </a>
                    <a href="#" aria-label="Instagram">
                        <i class="fab fa-instagram"></i>
                    </a>
                </div>
            </div>
            
            <div class="footer-section">
                <h3>Formations</h3>
                <a href="#">Management</a>
                <a href="#">Informatique</a>
                <a href="#">Réseaux</a>
                <a href="#">Big Data</a>
                <a href="#">Cybersécurité</a>
            </div>
            
            <div class="footer-section">
                <h3>Liens Utiles</h3>
                <a href="#">À propos</a>
                <a href="#">Nos formateurs</a>
                <a href="#">Témoignages</a>
                <a href="#">FAQ</a>
                <a href="#">Politique de confidentialité</a>
            </div>
            
            <div class="footer-section">
                <h3>Contact</h3>
                <div class="contact-item">
                    <i class="fas fa-envelope"></i>
                    <span>info@formationpro.com</span>
                </div>
                <div class="contact-item">
                    <i class="fas fa-phone"></i>
                    <span>+212 5XX XX XX XX</span>
                </div>
                <div class="contact-item">
                    <i class="fas fa-map-marker-alt"></i>
                    <span>Fès, Maroc</span>
                </div>
                <div class="contact-item">
                    <i class="fas fa-clock"></i>
                    <span>Lun-Ven: 9h-18h</span>
                </div>
            </div>
        </div>
        
        <div class="footer-bottom">
            <p>© 2024 FormationPro. Tous droits réservés.</p>
            <p>Développé avec ❤️ pour l'excellence en formation</p>
        </div>
    </footer>

    <script>
        // Footer functionality
        function initFooter() {
            // Add smooth scroll for footer links
            document.querySelectorAll('.footer a[href^="#"]').forEach(anchor => {
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

            // Add current year to copyright
            const currentYear = new Date().getFullYear();
            const copyrightElement = document.querySelector('.footer-bottom p');
            if (copyrightElement) {
                copyrightElement.textContent = copyrightElement.textContent.replace('2024', currentYear);
            }

            // Add hover effects to social links
            document.querySelectorAll('.social-links a').forEach(link => {
                link.addEventListener('mouseenter', function() {
                    this.style.transform = 'translateY(-3px) scale(1.1)';
                });
                
                link.addEventListener('mouseleave', function() {
                    this.style.transform = 'translateY(0) scale(1)';
                });
            });
        }

        // Initialize footer when DOM is loaded
        document.addEventListener('DOMContentLoaded', initFooter);
    </script>
</body>
</html>