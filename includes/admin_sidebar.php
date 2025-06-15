<?php
// Déterminer la page actuelle pour activer le bon lien
$current_page = basename($_SERVER['PHP_SELF']);
?>

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
        --active-bg: rgba(52, 152, 219, 0.15);
        --logout-color: #e74c3c;
        --logout-hover: #c0392b;
    }

    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    body {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        background: var(--light-gray);
    }

    /* Sidebar Styles */
    .admin-sidebar {
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

    .admin-sidebar::-webkit-scrollbar {
        width: 6px;
    }

    .admin-sidebar::-webkit-scrollbar-track {
        background: transparent;
    }

    .admin-sidebar::-webkit-scrollbar-thumb {
        background: rgba(52, 152, 219, 0.3);
        border-radius: 10px;
    }

    .admin-sidebar::-webkit-scrollbar-thumb:hover {
        background: rgba(52, 152, 219, 0.5);
    }

    /* Header Section */
    .sidebar-header {
        padding: 2rem 1.5rem;
        border-bottom: 1px solid var(--border-color);
        background: linear-gradient(135deg, rgba(102, 126, 234, 0.1) 0%, rgba(118, 75, 162, 0.1) 100%);
        text-align: center;
    }

    .sidebar-logo {
        font-size: 1.8rem;
        font-weight: bold;
        background: linear-gradient(135deg, #2c3e50, #3498db);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
        text-decoration: none;
        display: block;
        margin-bottom: 0.5rem;
    }

    .sidebar-subtitle {
        color: var(--dark-text);
        font-size: 0.9rem;
        opacity: 0.7;
        font-weight: 500;
        text-transform: uppercase;
        letter-spacing: 1px;
    }

    /* User Info Section */
    .user-info {
        padding: 1.5rem 1.5rem;
        border-bottom: 1px solid var(--border-color);
        text-align: center;
        background: rgba(52, 152, 219, 0.05);
    }

    .user-avatar {
        width: 50px;
        height: 50px;
        border-radius: 50%;
        background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 0.5rem;
        font-size: 1.5rem;
        font-weight: bold;
        color: white;
        box-shadow: 0 4px 15px rgba(52, 152, 219, 0.3);
    }

    .user-name {
        font-size: 0.95rem;
        color: var(--dark-text);
        margin-bottom: 0.25rem;
        font-weight: 600;
    }

    .user-role {
        font-size: 0.8rem;
        color: var(--secondary-color);
        font-weight: 500;
    }

    /* Navigation Styles */
    .sidebar-nav {
        padding: 1rem 0;
    }

    .nav-section {
        margin: 1.5rem 0 0.5rem 0;
        padding: 0 1.5rem;
        font-size: 0.8rem;
        color: rgba(44, 62, 80, 0.6);
        text-transform: uppercase;
        letter-spacing: 1px;
        font-weight: 600;
    }

    .nav-item {
        margin: 0.2rem 0;
        animation: slideIn 0.5s ease forwards;
        opacity: 0;
    }

    .nav-item:nth-child(1) { animation-delay: 0.1s; }
    .nav-item:nth-child(2) { animation-delay: 0.2s; }
    .nav-item:nth-child(3) { animation-delay: 0.3s; }
    .nav-item:nth-child(4) { animation-delay: 0.4s; }
    .nav-item:nth-child(5) { animation-delay: 0.5s; }
    .nav-item:nth-child(6) { animation-delay: 0.6s; }
    .nav-item:nth-child(7) { animation-delay: 0.7s; }
    .nav-item:nth-child(8) { animation-delay: 0.8s; }

    @keyframes slideIn {
        from {
            opacity: 0;
            transform: translateX(-20px);
        }
        to {
            opacity: 1;
            transform: translateX(0);
        }
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

    .nav-link:hover {
        background: var(--hover-bg);
        color: var(--secondary-color);
        transform: translateX(5px);
    }

    .nav-link:hover::before {
        opacity: 1;
    }

    .nav-link.active {
        background: var(--active-bg);
        color: var(--secondary-color);
        transform: translateX(5px);
        box-shadow: 0 2px 10px rgba(52, 152, 219, 0.15);
    }

    .nav-link.active::before {
        opacity: 1;
    }

    .nav-icon {
        width: 20px;
        margin-right: 1rem;
        text-align: center;
        font-size: 1.1rem;
    }

    .nav-text {
        flex: 1;
        font-size: 0.95rem;
    }

    /* Badge pour notifications */
    .nav-badge {
        background: var(--logout-color);
        color: white;
        font-size: 0.7rem;
        padding: 0.2rem 0.5rem;
        border-radius: 10px;
        margin-left: auto;
    }

    /* Footer Section */
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
        color: var(--logout-color);
        font-weight: 500;
        transition: all 0.3s ease;
        border-radius: 10px;
        border: 1px solid transparent;
    }

    .logout-link:hover {
        background: rgba(231, 76, 60, 0.1);
        border-color: rgba(231, 76, 60, 0.2);
        color: var(--logout-hover);
        transform: translateY(-2px);
    }

    /* Content adjustment */
    .main-content {
        margin-left: var(--sidebar-width);
        padding: 2rem;
        min-height: 100vh;
        background: var(--light-gray);
        transition: all 0.3s ease;
    }

    /* Mobile Toggle */
    .mobile-toggle {
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
        transition: all 0.3s ease;
    }

    .mobile-toggle:hover {
        background: var(--secondary-color);
        color: var(--white);
        transform: scale(1.05);
    }

    /* Overlay for mobile */
    .sidebar-overlay {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.5);
        z-index: 999;
        opacity: 0;
        transition: opacity 0.3s ease;
    }

    .sidebar-overlay.active {
        display: block;
        opacity: 1;
    }

    /* Responsive Design */
    @media (max-width: 1024px) {
        :root {
            --sidebar-width: 260px;
        }
        
        .nav-link {
            padding: 0.9rem 1.3rem;
        }
        
        .sidebar-header {
            padding: 1.8rem 1.3rem;
        }
    }

    @media (max-width: 768px) {
        .main-content {
            margin-left: 0;
        }

        .admin-sidebar {
            left: -100%;
            width: 85%;
            max-width: 300px;
        }

        .admin-sidebar.mobile-open {
            left: 0;
        }

        .mobile-toggle {
            display: block;
        }

        .sidebar-overlay {
            display: block;
        }
    }

    @media (max-width: 480px) {
        .admin-sidebar {
            width: 90%;
        }
        
        .sidebar-header {
            padding: 1.5rem 1rem;
        }
        
        .sidebar-logo {
            font-size: 1.6rem;
        }
        
        .nav-link {
            padding: 0.8rem 1rem;
        }
        
        .main-content {
            padding: 0.8rem;
            padding-top: 4.5rem;
        }
    }

    @media (max-width: 320px) {
        .admin-sidebar {
            width: 95%;
        }
        
        .nav-link {
            padding: 0.7rem 0.8rem;
            font-size: 0.9rem;
        }
        
        .nav-icon {
            margin-right: 0.8rem;
        }
    }

    /* Focus states for accessibility */
    .nav-link:focus,
    .logout-link:focus,
    .mobile-toggle:focus {
        outline: 2px solid var(--secondary-color);
        outline-offset: 2px;
    }

    /* Hover effects for better user experience */
    @media (hover: hover) {
        .nav-link:hover {
            background: var(--hover-bg);
            color: var(--secondary-color);
            transform: translateX(5px);
        }
    }
</style>

<!-- Sidebar Overlay for mobile -->
<div class="sidebar-overlay" id="sidebarOverlay"></div>

<!-- Mobile Toggle Button -->
<button class="mobile-toggle d-md-none" id="mobileToggle" onclick="toggleSidebar()" aria-label="Toggle navigation">
    <span class="nav-icon">☰</span>
</button>

<!-- Sidebar -->
<div class="admin-sidebar" id="adminSidebar" role="navigation">
    <!-- Header -->
    <div class="sidebar-header">
        <a href="dashboard.php" class="sidebar-logo">FormationPro</a>
        <div class="sidebar-subtitle">Admin Panel</div>
    </div>

    <!-- User Info -->
    <div class="user-info">
        <div class="user-avatar">A</div>
        <div class="user-name">Administrateur</div>
        <div class="user-role">Admin Principal</div>
    </div>

    <!-- Navigation -->
    <nav class="sidebar-nav">
        <!-- Dashboard Section -->
        <div class="nav-section">Tableau de Bord</div>
        <div class="nav-item">
            <a href="../admin/dashboard.php" class="nav-link <?php echo ($current_page == 'dashboard.php') ? 'active' : ''; ?>" role="menuitem">
                <span class="nav-icon">📊</span>
                <span class="nav-text">Dashboard</span>
            </a>
        </div>

        <!-- Gestion des Contenus -->
        <div class="nav-section">Gestion Contenus</div>
        <div class="nav-item">
            <a href="../admin/domains.php" class="nav-link <?php echo ($current_page == 'domains.php') ? 'active' : ''; ?>" role="menuitem">
                <span class="nav-icon">🎯</span>
                <span class="nav-text">Domaines</span>
            </a>
        </div>
        <div class="nav-item">
            <a href="../admin/sujet.php" class="nav-link <?php echo ($current_page == 'sujet.php') ? 'active' : ''; ?>" role="menuitem">
                <span class="nav-icon">📚</span>
                <span class="nav-text">Sujets</span>
            </a>
        </div>
        <div class="nav-item">
            <a href="../admin/cours.php" class="nav-link <?php echo ($current_page == 'cours.php') ? 'active' : ''; ?>" role="menuitem">
                <span class="nav-icon">📖</span>
                <span class="nav-text">Cours</span>
            </a>
        </div>

        <!-- Gestion des Formations -->
        <div class="nav-section">Formations</div>
        <div class="nav-item">
            <a href="../admin/formation_admin.php" class="nav-link <?php echo ($current_page == 'formation_admin.php') ? 'active' : ''; ?>" role="menuitem">
                <span class="nav-icon">🎓</span>
                <span class="nav-text">Formations</span>
            </a>
        </div>
        <div class="nav-item">
            <a href="../admin/formateurs.php" class="nav-link <?php echo ($current_page == 'formateurs.php') ? 'active' : ''; ?>" role="menuitem">
                <span class="nav-icon">👨‍🏫</span>
                <span class="nav-text">Formateurs</span>
            </a>
        </div>

        <!-- Gestion Géographique -->
        <div class="nav-section">Géographie</div>
        <div class="nav-item">
            <a href="../admin/pays.php" class="nav-link <?php echo ($current_page == 'pays.php') ? 'active' : ''; ?>" role="menuitem">
                <span class="nav-icon">🌍</span>
                <span class="nav-text">Pays</span>
            </a>
        </div>
        <div class="nav-item">
            <a href="../admin/villes.php" class="nav-link <?php echo ($current_page == 'villes.php') ? 'active' : ''; ?>" role="menuitem">
                <span class="nav-icon">🏙️</span>
                <span class="nav-text">Villes</span>
            </a>
        </div>
    </nav>

    <!-- Footer -->
    <div class="sidebar-footer">
        <a href="../admin/logout.php" class="logout-link" onclick="return confirm('Êtes-vous sûr de vouloir vous déconnecter ?')" role="menuitem">
            <span class="nav-icon">🚪</span>
            <span class="nav-text">Déconnexion</span>
        </a>
    </div>
</div>

<script>
    class AdminSidebarManager {
        constructor() {
            this.mobileToggle = document.getElementById('mobileToggle');
            this.sidebar = document.getElementById('adminSidebar');
            this.sidebarOverlay = document.getElementById('sidebarOverlay');
            this.navLinks = document.querySelectorAll('.nav-link');
            
            this.init();
        }

        init() {
            this.setupToggle();
            this.setupOverlay();
            this.setupKeyboardNavigation();
            this.setupNavigation();
            this.handleWindowResize();
        }

        setupToggle() {
            if (this.mobileToggle && this.sidebar) {
                this.mobileToggle.addEventListener('click', () => {
                    this.toggleSidebar();
                });
            }
        }

        setupOverlay() {
            if (this.sidebarOverlay) {
                this.sidebarOverlay.addEventListener('click', () => {
                    this.closeMobileSidebar();
                });
            }
        }

        setupKeyboardNavigation() {
            document.addEventListener('keydown', (e) => {
                // Escape key closes mobile sidebar
                if (e.key === 'Escape' && this.isMobile() && this.sidebar.classList.contains('mobile-open')) {
                    this.closeMobileSidebar();
                }
            });
        }

        setupNavigation() {
            this.navLinks.forEach(link => {
                link.addEventListener('click', (e) => {
                    // Add loading effect
                    link.style.opacity = '0.7';
                    setTimeout(() => {
                        link.style.opacity = '1';
                    }, 200);
                    
                    // Close mobile sidebar if open
                    this.closeMobileSidebar();
                });
            });
        }

        toggleSidebar() {
            this.sidebar.classList.toggle('mobile-open');
            this.sidebarOverlay.classList.toggle('active');
            
            const icon = this.mobileToggle.querySelector('.nav-icon');
            icon.textContent = this.sidebar.classList.contains('mobile-open') ? '✕' : '☰';

            // Update ARIA attributes
            const isExpanded = this.sidebar.classList.contains('mobile-open');
            this.mobileToggle.setAttribute('aria-expanded', isExpanded);
        }

        closeMobileSidebar() {
            if (this.isMobile()) {
                this.sidebar.classList.remove('mobile-open');
                this.sidebarOverlay.classList.remove('active');
                
                const icon = this.mobileToggle.querySelector('.nav-icon');
                icon.textContent = '☰';
                
                this.mobileToggle.setAttribute('aria-expanded', 'false');
            }
        }

        handleWindowResize() {
            window.addEventListener('resize', () => {
                if (window.innerWidth > 768) {
                    this.sidebar.classList.remove('mobile-open');
                    this.sidebarOverlay.classList.remove('active');
                    
                    const icon = this.mobileToggle.querySelector('.nav-icon');
                    icon.textContent = '☰';
                }
            });
        }

        isMobile() {
            return window.innerWidth <= 768;
        }
    }

    // Legacy function for backward compatibility
    function toggleSidebar() {
        const sidebar = document.getElementById('adminSidebar');
        const overlay = document.getElementById('sidebarOverlay');
        sidebar.classList.toggle('mobile-open');
        overlay.classList.toggle('active');
    }

    // Initialize sidebar when DOM is loaded
    document.addEventListener('DOMContentLoaded', () => {
        new AdminSidebarManager();
    });

    // Close sidebar when clicking outside on mobile
    document.addEventListener('click', function(event) {
        const sidebar = document.getElementById('adminSidebar');
        const toggleButton = document.getElementById('mobileToggle');
        
        if (window.innerWidth <= 768 && 
            !sidebar.contains(event.target) && 
            !toggleButton.contains(event.target)) {
            sidebar.classList.remove('mobile-open');
            document.getElementById('sidebarOverlay').classList.remove('active');
        }
    });

    // Highlight active page on load
    document.addEventListener('DOMContentLoaded', function() {
        const currentPath = window.location.pathname;
        const fileName = currentPath.split('/').pop();
        
        document.querySelectorAll('.nav-link').forEach(link => {
            const linkHref = link.getAttribute('href');
            if (linkHref && linkHref.includes(fileName)) {
                link.classList.add('active');
            }
        });
    });
</script>