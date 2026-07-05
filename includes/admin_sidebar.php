<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

<?php
$current_page = basename($_SERVER['PHP_SELF']);
?>

<style>
    :root {
        --primary: #2c3e50;
        --secondary: #3498db;
        --accent: #667eea;
        --purple: #764ba2;
        --dark: #1a1a2e;
        --text: #2c3e50;
        --text-muted: #6c757d;
        --bg: #f0f2f5;
        --white: #ffffff;
        --border: #e9ecef;
        --shadow: 0 1px 3px rgba(0,0,0,0.08), 0 1px 2px rgba(0,0,0,0.06);
        --shadow-lg: 0 10px 40px rgba(0,0,0,0.08), 0 2px 8px rgba(0,0,0,0.06);
        --radius: 18px;
        --sidebar-w: 240px;
    }

    *, *::before, *::after { margin: 0; padding: 0; box-sizing: border-box; }

    body {
        font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
        background: var(--bg);
        color: var(--text);
        overflow-x: hidden;
    }

    /* Sidebar Toggle */
    .sidebar-toggle {
        display: none; position: fixed; top: 14px; left: 14px; z-index: 1002;
        width: 40px; height: 40px; border-radius: 10px;
        background: var(--white); border: 1px solid var(--border);
        font-size: 1.1rem; color: var(--text); cursor: pointer;
        box-shadow: var(--shadow); transition: all 0.2s;
    }
    .sidebar-toggle:hover { box-shadow: var(--shadow-lg); }

    .sidebar-overlay {
        display: block; position: fixed; inset: 0; background: rgba(0,0,0,0.4);
        z-index: 998; opacity: 0; pointer-events: none; transition: opacity 0.3s;
    }
    .sidebar-overlay.active { opacity: 1; pointer-events: all; }

    .admin-sidebar {
        position: fixed; top: 0; left: 0; width: var(--sidebar-w); height: 100vh;
        background: var(--white); z-index: 999;
        display: flex; flex-direction: column;
        border-right: 1px solid var(--border);
        box-shadow: 2px 0 20px rgba(0,0,0,0.06);
        transition: transform 0.3s ease;
    }

    .sidebar-header {
        padding: 20px 24px 12px; text-align: center; flex-shrink: 0;
    }
    .sidebar-logo {
        font-size: 1.4rem; font-weight: 800; text-decoration: none; display: block;
        background: linear-gradient(135deg, var(--primary), var(--secondary));
        -webkit-background-clip: text; -webkit-text-fill-color: transparent;
        background-clip: text; letter-spacing: -0.5px;
    }
    .sidebar-subtitle {
        font-size: 0.72rem; color: var(--text-muted); text-transform: uppercase;
        letter-spacing: 1px; margin-top: 2px;
    }

    .user-info {
        display: flex; align-items: center; gap: 10px;
        padding: 12px 20px 16px; margin: 0 14px;
        border-bottom: 1px solid var(--border); flex-shrink: 0;
    }
    .user-avatar {
        width: 36px; height: 36px; border-radius: 50%; flex-shrink: 0;
        background: linear-gradient(135deg, var(--accent), var(--purple));
        display: flex; align-items: center; justify-content: center;
        font-size: 0.85rem; font-weight: 700; color: var(--white);
    }
    .user-name { font-size: 0.85rem; font-weight: 600; color: var(--text); }
    .user-role { font-size: 0.72rem; color: var(--text-muted); }

    .sidebar-nav {
        flex: 1; overflow-y: auto; padding: 8px 0;
        scrollbar-width: thin; scrollbar-color: rgba(0,0,0,0.08) transparent;
    }
    .sidebar-nav::-webkit-scrollbar { width: 4px; }
    .sidebar-nav::-webkit-scrollbar-thumb { background: rgba(0,0,0,0.08); border-radius: 10px; }

    .nav-section {
        padding: 12px 24px 4px; font-size: 0.72rem; font-weight: 600;
        color: var(--text-muted); text-transform: uppercase; letter-spacing: 1px;
    }

    .nav-item { margin: 1px 0; }
    .nav-link {
        display: flex; align-items: center; height: 44px;
        padding: 0 18px; margin: 0 10px; border-radius: 10px;
        text-decoration: none; font-size: 15px; font-weight: 500;
        color: var(--text-muted); transition: all 0.2s ease;
        position: relative;
    }
    .nav-link:hover {
        background: rgba(52, 152, 219, 0.06);
        color: var(--text);
    }
    .nav-link.active {
        background: linear-gradient(135deg, rgba(102, 126, 234, 0.1), rgba(118, 75, 162, 0.1));
        color: var(--accent); font-weight: 600;
    }
    .nav-link.active::before {
        content: ''; position: absolute; left: -10px; top: 8px; bottom: 8px; width: 3px;
        background: linear-gradient(135deg, var(--accent), var(--purple));
        border-radius: 0 3px 3px 0;
    }
    .nav-icon { width: 18px; font-size: 18px; margin-right: 12px; text-align: center; flex-shrink: 0; }
    .nav-text { flex: 1; white-space: nowrap; }

    .sidebar-footer {
        padding: 8px 14px 14px; border-top: 1px solid var(--border);
        flex-shrink: 0;
    }
    .logout-link {
        display: flex; align-items: center; height: 44px;
        padding: 0 18px; border-radius: 10px; text-decoration: none;
        color: #e74c3c; font-size: 15px; font-weight: 500; transition: all 0.2s;
    }
    .logout-link:hover { background: rgba(231, 76, 60, 0.06); }
    .logout-link i { width: 18px; font-size: 18px; margin-right: 12px; text-align: center; }

    .main-content {
        margin-left: var(--sidebar-w); padding: 32px;
        min-height: 100vh; transition: margin-left 0.3s ease;
    }

    @media (max-width: 992px) {
        .admin-sidebar { transform: translateX(-100%); }
        .admin-sidebar.active { transform: translateX(0); }
        .sidebar-toggle { display: flex; align-items: center; justify-content: center; }
        .sidebar-overlay { display: block; }
        .main-content { margin-left: 0; padding: 20px 16px; padding-top: 70px; }
    }
</style>

<button class="sidebar-toggle" id="mobileToggle" aria-label="Toggle sidebar">
    <i class="fas fa-bars"></i>
</button>

<div class="sidebar-overlay" id="sidebarOverlay"></div>

<div class="admin-sidebar" id="adminSidebar" role="navigation">
    <div class="sidebar-header">
        <a href="../admin/dashboard.php" class="sidebar-logo">FormationPro</a>
        <div class="sidebar-subtitle">Admin Panel</div>
    </div>

    <div class="user-info">
        <div class="user-avatar">A</div>
        <div>
            <div class="user-name">Administrateur</div>
            <div class="user-role">Admin Principal</div>
        </div>
    </div>

    <nav class="sidebar-nav">
        <div class="nav-section">Tableau de Bord</div>
        <div class="nav-item">
            <a href="../admin/dashboard.php" class="nav-link <?php echo ($current_page == 'dashboard.php') ? 'active' : ''; ?>" role="menuitem">
                <span class="nav-icon"><i class="fas fa-tachometer-alt"></i></span>
                <span class="nav-text">Dashboard</span>
            </a>
        </div>

        <div class="nav-section">Gestion Contenus</div>
        <div class="nav-item">
            <a href="../admin/domains.php" class="nav-link <?php echo ($current_page == 'domains.php') ? 'active' : ''; ?>" role="menuitem">
                <span class="nav-icon"><i class="fas fa-sitemap"></i></span>
                <span class="nav-text">Domaines</span>
            </a>
        </div>
        <div class="nav-item">
            <a href="../admin/sujet.php" class="nav-link <?php echo ($current_page == 'sujet.php') ? 'active' : ''; ?>" role="menuitem">
                <span class="nav-icon"><i class="fas fa-book"></i></span>
                <span class="nav-text">Sujets</span>
            </a>
        </div>
        <div class="nav-item">
            <a href="../admin/cours.php" class="nav-link <?php echo ($current_page == 'cours.php') ? 'active' : ''; ?>" role="menuitem">
                <span class="nav-icon"><i class="fas fa-file-alt"></i></span>
                <span class="nav-text">Cours</span>
            </a>
        </div>

        <div class="nav-section">Formations</div>
        <div class="nav-item">
            <a href="../admin/formation_admin.php" class="nav-link <?php echo ($current_page == 'formation_admin.php') ? 'active' : ''; ?>" role="menuitem">
                <span class="nav-icon"><i class="fas fa-graduation-cap"></i></span>
                <span class="nav-text">Formations</span>
            </a>
        </div>
        <div class="nav-item">
            <a href="../admin/formateurs.php" class="nav-link <?php echo ($current_page == 'formateurs.php') ? 'active' : ''; ?>" role="menuitem">
                <span class="nav-icon"><i class="fas fa-user-tie"></i></span>
                <span class="nav-text">Formateurs</span>
            </a>
        </div>

        <div class="nav-section">Géographie</div>
        <div class="nav-item">
            <a href="../admin/pays.php" class="nav-link <?php echo ($current_page == 'pays.php') ? 'active' : ''; ?>" role="menuitem">
                <span class="nav-icon"><i class="fas fa-globe"></i></span>
                <span class="nav-text">Pays</span>
            </a>
        </div>
        <div class="nav-item">
            <a href="../admin/villes.php" class="nav-link <?php echo ($current_page == 'villes.php') ? 'active' : ''; ?>" role="menuitem">
                <span class="nav-icon"><i class="fas fa-city"></i></span>
                <span class="nav-text">Villes</span>
            </a>
        </div>
    </nav>

    <div class="sidebar-footer">
        <a href="../admin/logout.php" class="logout-link" onclick="return confirm('Êtes-vous sûr de vouloir vous déconnecter ?')" role="menuitem">
            <i class="fas fa-sign-out-alt"></i>
            <span>Déconnexion</span>
        </a>
    </div>
</div>

<script>
    class AdminSidebarManager {
        constructor() {
            this.toggle = document.getElementById('mobileToggle');
            this.sidebar = document.getElementById('adminSidebar');
            this.overlay = document.getElementById('sidebarOverlay');
            this.init();
        }
        init() {
            if (!this.toggle || !this.sidebar) return;
            const close = () => {
                this.sidebar.classList.remove('active');
                if (this.overlay) this.overlay.classList.remove('active');
                this.toggle.querySelector('i').className = 'fas fa-bars';
            };
            this.toggle.addEventListener('click', () => {
                this.sidebar.classList.toggle('active');
                if (this.overlay) this.overlay.classList.toggle('active');
                const icon = this.toggle.querySelector('i');
                icon.className = this.sidebar.classList.contains('active') ? 'fas fa-times' : 'fas fa-bars';
            });
            if (this.overlay) this.overlay.addEventListener('click', close);
            document.querySelectorAll('#adminSidebar .nav-link').forEach(link => {
                link.addEventListener('click', () => { if (window.innerWidth <= 992) close(); });
            });
            window.addEventListener('resize', () => {
                if (window.innerWidth > 992 && this.sidebar) {
                    this.sidebar.classList.remove('active');
                    if (this.overlay) this.overlay.classList.remove('active');
                    if (this.toggle) this.toggle.querySelector('i').className = 'fas fa-bars';
                }
            });
        }
    }
    document.addEventListener('DOMContentLoaded', () => new AdminSidebarManager());
</script>