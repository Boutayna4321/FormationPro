<?php
session_start();

// Inclure la configuration
include(__DIR__ . '../config.php');



// Redirection si déjà connecté
if (isset($_SESSION['admin_id'])) {
    header('Location: dashboard.php');
    exit();
}

$message = '';
$messageType = 'error';

// Traitement du formulaire
if ($_POST) {
    $email = sanitizeInput($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($email) || empty($password)) {
        $message = 'Tous les champs sont obligatoires.';
    } else {
        try {
            // Recherche de l'admin dans la base
            $stmt = $conn->prepare("SELECT id, nom, email, mot_de_passe, role, statut FROM admins WHERE email = ?");
            $stmt->execute([$email]);
            $admin = $stmt->fetch();
            
            // Vérification de l'admin et du statut
            if (!$admin) {
                $message = 'Aucun compte trouvé avec cet email.';
            } elseif ($admin['statut'] !== 'actif') {
                $message = 'Votre compte est inactif. Contactez l\'administrateur.';
            } elseif (password_verify($password, $admin['mot_de_passe'])) {
                // ✅ Connexion réussie - Création de la session
                $_SESSION['admin_id'] = $admin['id'];
                $_SESSION['admin_nom'] = $admin['nom'];
                $_SESSION['admin_email'] = $admin['email'];
                $_SESSION['admin_role'] = $admin['role'];
                
                // Enregistrer tentative de connexion réussie
                $logStmt = $conn->prepare("INSERT INTO admin_log (email, ip_address, user_agent, login_success, attempt_time) VALUES (?, ?, ?, 1, NOW())");
                $logStmt->execute([
                    $email,
                    $_SERVER['REMOTE_ADDR'] ?? 'unknown',
                    $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
                ]);
                
                // Mise à jour de la dernière connexion
                $updateStmt = $conn->prepare("UPDATE admins SET derniere_connexion = NOW() WHERE id = ?");
                $updateStmt->execute([$admin['id']]);
                
                // Message de succès et redirection
                $message = 'Connexion réussie ! Redirection...';
                $messageType = 'success';
                
                // Redirection avec JavaScript pour voir le message
                echo "<script>
                    setTimeout(function() {
                        window.location.href = 'dashboard.php';
                    }, 1500);
                </script>";
            } else {
                // Enregistrer tentative de connexion échouée
                $logStmt = $conn->prepare("INSERT INTO admin_log (email, ip_address, user_agent, login_success, attempt_time) VALUES (?, ?, ?, 0, NOW())");
                $logStmt->execute([
                    $email,
                    $_SERVER['REMOTE_ADDR'] ?? 'unknown',
                    $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
                ]);
                
                $message = 'Mot de passe incorrect.';
            }
        } catch (PDOException $e) {
            $message = 'Erreur système. Veuillez réessayer.';
            error_log("Erreur login: " . $e->getMessage());
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion Admin - Formation DB</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .login-container {
            background: white;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.1);
            width: 100%;
            max-width: 420px;
            position: relative;
        }

        .login-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .login-header .icon {
            font-size: 48px;
            margin-bottom: 15px;
        }

        .login-header h1 {
            color: #333;
            margin-bottom: 8px;
            font-size: 28px;
        }

        .login-header p {
            color: #666;
            font-size: 14px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: 600;
            font-size: 14px;
        }

        .form-group input {
            width: 100%;
            padding: 15px;
            border: 2px solid #e1e1e1;
            border-radius: 8px;
            font-size: 16px;
            transition: all 0.3s ease;
            background: #f8f9fa;
        }

        .form-group input:focus {
            outline: none;
            border-color: #667eea;
            background: white;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .login-btn {
            width: 100%;
            padding: 15px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .login-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.3);
        }

        .login-btn:active {
            transform: translateY(0);
        }

        .message {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .message.error {
            background: #fee;
            color: #c53030;
            border: 1px solid #fed7d7;
        }

        .message.success {
            background: #f0fff4;
            color: #38a169;
            border: 1px solid #c6f6d5;
        }

        .footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e1e1e1;
            color: #666;
            font-size: 12px;
        }

        .loading {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 2px solid rgba(255,255,255,0.3);
            border-radius: 50%;
            border-top: 2px solid white;
            animation: spin 1s linear infinite;
            margin-right: 10px;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .btn-loading {
            pointer-events: none;
            opacity: 0.8;
        }

        @media (max-width: 480px) {
            .login-container {
                padding: 30px 20px;
                margin: 10px;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <div class="icon">🔐</div>
            <h1>Administration</h1>
            <p>Système de gestion des formations</p>
        </div>

        <?php if ($message): ?>
            <div class="message <?php echo $messageType; ?>">
                <span><?php echo $messageType === 'success' ? '✅' : '❌'; ?></span>
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <form method="POST" id="loginForm">
            <div class="form-group">
                <label for="email">📧 Adresse email</label>
                <input type="email" 
                       id="email" 
                       name="email" 
                       required 
                       placeholder="admin@exemple.com"
                       value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
            </div>

            <div class="form-group">
                <label for="password">🔑 Mot de passe</label>
                <input type="password" 
                       id="password" 
                       name="password" 
                       required 
                       placeholder="••••••••••••">
            </div>

            <button type="submit" class="login-btn" id="submitBtn">
                <span class="btn-text">Se connecter</span>
            </button>
        </form>

        <div class="footer">
            <p>© 2025 - Système sécurisé</p>
            <p>Base de données: <?php echo htmlspecialchars($dbname); ?></p>
        </div>
    </div>

    <script>
        document.getElementById('loginForm').addEventListener('submit', function(e) {
            const btn = document.getElementById('submitBtn');
            const btnText = btn.querySelector('.btn-text');
            
            // Animation de chargement
            btn.classList.add('btn-loading');
            btn.innerHTML = '<span class="loading"></span>Connexion...';
            
            // Permettre la soumission
            return true;
        });

        // Animation d'entrée
        window.addEventListener('load', function() {
            const container = document.querySelector('.login-container');
            container.style.opacity = '0';
            container.style.transform = 'translateY(30px)';
            container.style.transition = 'all 0.6s ease';
            
            setTimeout(() => {
                container.style.opacity = '1';
                container.style.transform = 'translateY(0)';
            }, 100);
        });
    </script>
</body>
</html>