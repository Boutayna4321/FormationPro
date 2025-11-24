<?php
require_once '../../config.php';           // الاتصال بقاعدة البيانات
require_once 'functions.php';      // فيه logLoginAttempt() و sanitizeInput()


$error_message = "";

// Auto-login (pré-remplir email/mot de passe si remember me)
$remembered_email = $_COOKIE['remember_email'] ?? '';
$remembered_password = $_COOKIE['remember_password'] ?? '';

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['login'])) {
    $email = sanitizeInput($_POST['email']);
    $password = $_POST['mot_de_passe'];
    $remember = isset($_POST['remember']);

    $ip_address = $_SERVER['REMOTE_ADDR'] ?? 'inconnu';
    $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'navigateur inconnu';

    if (empty($email) || empty($password)) {
        $error_message = "Veuillez remplir tous les champs.";
        logLoginAttempt($conn, $email, $ip_address, $user_agent, false);
    } else {
        $stmt = $conn->prepare("SELECT * FROM admins WHERE email = ? AND actif = 1");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['mot_de_passe'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_name'] = $user['nom_admin'];
            $_SESSION['user_role'] = $user['role'];
            $_SESSION['logged_in'] = true;

            logLoginAttempt($conn, $email, $ip_address, $user_agent, true);

            // Remember Me - set cookies for 7 days
            if ($remember) {
                setcookie('remember_email', $email, time() + (86400 * 7), "/");
                setcookie('remember_password', $password, time() + (86400 * 7), "/");
            } else {
                setcookie('remember_email', '', time() - 3600, "/");
                setcookie('remember_password', '', time() - 3600, "/");
            }

            header("Location: dashboard.php");
            exit();
        } else {
            $error_message = "Email ou mot de passe incorrect.";
            logLoginAttempt($conn, $email, $ip_address, $user_agent, false);
        }
    }
}

// Si déjà connecté
if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
    header("Location: dashboard.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Connexion - Style Amélioré</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
  <style>
/* Background avec une seule couleur et points blancs animés */
body {
  font-family: 'Segoe UI', sans-serif;
  background: linear-gradient(135deg, #2c3e50, #34495e);
  display: flex;
  align-items: center;
  justify-content: center;
  height: 100vh;
  margin: 0;
  position: relative;
  overflow: hidden;
}

/* Points blancs flottants simples */
body::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background-image: 
    radial-gradient(circle at 10% 20%, rgba(255, 255, 255, 0.1) 1px, transparent 1px),
    radial-gradient(circle at 80% 80%, rgba(255, 255, 255, 0.08) 1px, transparent 1px),
    radial-gradient(circle at 40% 40%, rgba(255, 255, 255, 0.06) 1px, transparent 1px),
    radial-gradient(circle at 90% 10%, rgba(255, 255, 255, 0.1) 1px, transparent 1px),
    radial-gradient(circle at 20% 90%, rgba(255, 255, 255, 0.07) 1px, transparent 1px);
  background-size: 150px 150px, 200px 200px, 250px 250px, 180px 180px, 220px 220px;
  animation: floatPoints 25s linear infinite;
  pointer-events: none;
}

@keyframes floatPoints {
  0% { transform: translateY(0px); }
  100% { transform: translateY(-50px); }
}

.logo {
  font-size: 2rem;
  font-weight: bold;
  text-align: center;
  margin-bottom: 15px;
  color: #2c3e50;
}

.login-box {
  background: rgba(255, 255, 255, 0.95);
  backdrop-filter: blur(10px);
  padding: 40px 30px;
  border-radius: 15px;
  max-width: 400px;
  width: 100%;
  box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1), 0 5px 15px rgba(0, 0, 0, 0.08);
  animation: fadeIn 1s ease-out;
  position: relative;
}

.login-box h2 {
  text-align: center;
  margin-bottom: 25px;
  background: linear-gradient(135deg, #2c3e50, #3498db);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
  font-size: 1.8rem;
}

.form-group {
  margin-bottom: 20px;
  position: relative;
}

.form-group label {
  font-weight: 600;
  margin-bottom: 8px;
  display: block;
  color: #2c3e50;
  transition: color 0.3s ease;
}

.form-group input {
  width: 100%;
  padding: 12px;
  border: 2px solid #e0e0e0;
  border-radius: 8px;
  font-size: 1rem;
  transition: all 0.3s ease;
  background: rgba(255, 255, 255, 0.9);
}

.form-group input:focus {
  border-color: #3498db;
  outline: none;
  box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.1);
  background: rgba(255, 255, 255, 1);
}

.form-group input:focus + label {
  color: #3498db;
}

.toggle-password {
  position: absolute;
  right: 12px;
  top: 38px;
  cursor: pointer;
  color: #888;
  font-size: 1rem;
  transition: color 0.3s ease;
}

.toggle-password:hover {
  color: #3498db;
}

.remember {
  display: flex;
  align-items: center;
  gap: 10px;
  margin-bottom: 20px;
  font-size: 0.95rem;
  color: #2c3e50;
}

.remember input[type="checkbox"] {
  width: 16px;
  height: 16px;
  accent-color: #3498db;
}

.btn {
  width: 100%;
  padding: 12px;
  background: linear-gradient(135deg, #3498db, #2980b9);
  color: white;
  border: none;
  border-radius: 8px;
  font-weight: 600;
  font-size: 1rem;
  cursor: pointer;
  transition: all 0.3s ease;
  position: relative;
  overflow: hidden;
}

.btn::before {
  content: '';
  position: absolute;
  top: 0;
  left: -100%;
  width: 100%;
  height: 100%;
  background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
  transition: left 0.6s ease;
}

.btn:hover {
  transform: translateY(-1px);
  box-shadow: 0 5px 15px rgba(52, 152, 219, 0.3);
}

.btn:hover::before {
  left: 100%;
}

.btn:active {
  transform: translateY(0);
}

.alert {
  background-color: #f8d7da;
  color: #721c24;
  border: 1px solid #f5c6cb;
  border-radius: 8px;
  padding: 10px;
  margin-bottom: 20px;
  text-align: center;
  animation: slideDown 0.4s ease-out;
}

@keyframes slideDown {
  from {
    opacity: 0;
    transform: translateY(-10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.footer {
  text-align: center;
  margin-top: 20px;
  font-size: 0.9em;
}

.footer a {
  color: #3498db;
  text-decoration: none;
  transition: all 0.3s ease;
  position: relative;
}

.footer a::after {
  content: '';
  position: absolute;
  bottom: -2px;
  left: 0;
  width: 0;
  height: 1px;
  background: #3498db;
  transition: width 0.3s ease;
}

.footer a:hover::after {
  width: 100%;
}

.footer a:hover {
  color: #2980b9;
}

/* Animation d'apparition simple */
@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

/* Responsive */
@media (max-width: 480px) {
  .login-box {
    margin: 20px;
    padding: 30px 20px;
  }
  
  .logo {
    font-size: 1.5rem;
  }
  
  .login-box h2 {
    font-size: 1.5rem;
  }
}

/* Effet subtil au hover sur la boîte */
.login-box:hover {
  box-shadow: 0 20px 40px rgba(0, 0, 0, 0.12), 0 8px 20px rgba(0, 0, 0, 0.1);
}
</style>
</head>
<body>
  
  <div class="login-box">
    <div class="logo">
      <span>Formation<span style="color:#3498db;">Pro</span></span>
    </div>
    <h2>Se connecter</h2>

    <!-- L'alerte ne s'affiche que s'il y a une erreur PHP -->
    <?php if (!empty($error_message)): ?>
      <div class="alert"><?php echo $error_message; ?></div>
    <?php endif; ?>

    <form method="POST">
      <div class="form-group">
        <label for="email">Adresse Email</label>
        <input type="email" name="email" id="email" required value="">
      </div>

      <div class="form-group">
        <label for="mot_de_passe">Mot de passe</label>
        <input type="password" name="mot_de_passe" id="mot_de_passe" required value="">
        <i class="fas fa-eye toggle-password" onclick="togglePassword()"></i>
      </div>

      <div class="remember">
        <input type="checkbox" id="remember" name="remember">
        <label for="remember">Se souvenir de moi</label>
      </div>

      <button type="submit" name="login" class="btn">Connexion</button>
    </form>

    <div class="footer">
      <a href="forgot-password.php">Mot de passe oublié ?</a>
    </div>
  </div>

  <script>
    function togglePassword() {
      const input = document.getElementById("mot_de_passe");
      const icon = document.querySelector(".toggle-password");
      if (input.type === "password") {
        input.type = "text";
        icon.classList.remove("fa-eye");
        icon.classList.add("fa-eye-slash");
      } else {
        input.type = "password";
        icon.classList.remove("fa-eye-slash");
        icon.classList.add("fa-eye");
      }
    }
  </script>
</body>
</html>