<?php
// debug_login.php - Script pour déboguer les problèmes de connexion
session_start();
require_once 'functions.php';

// Afficher les erreurs PHP
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "<h2>Débogage de la connexion admin</h2>";

try {
    $conn = db_connect();
    echo "✅ Connexion à la base de données réussie<br><br>";
    
    // Test 1: Vérifier si la table admins existe
    echo "<h3>Test 1: Vérification de la table admins</h3>";
    try {
        $stmt = $conn->query("SELECT COUNT(*) FROM admins");
        $count = $stmt->fetchColumn();
        echo "✅ Table admins existe avec {$count} enregistrement(s)<br>";
    } catch (PDOException $e) {
        echo "❌ Erreur avec la table admins: " . $e->getMessage() . "<br>";
        echo "Créez la table avec le script SQL fourni précédemment.<br>";
    }
    
    // Test 2: Lister tous les admins
    echo "<h3>Test 2: Liste des admins dans la base</h3>";
    try {
        $stmt = $conn->query("SELECT id, nom, email, role, statut FROM admins");
        $admins = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (empty($admins)) {
            echo "❌ Aucun admin trouvé dans la base de données<br>";
            echo "Utilisez le script create_admin.php pour créer un admin<br>";
        } else {
            echo "<table border='1'>";
            echo "<tr><th>ID</th><th>Nom</th><th>Email</th><th>Rôle</th><th>Statut</th></tr>";
            foreach ($admins as $admin) {
                echo "<tr>";
                echo "<td>{$admin['id']}</td>";
                echo "<td>{$admin['nom']}</td>";
                echo "<td>{$admin['email']}</td>";
                echo "<td>{$admin['role']}</td>";
                echo "<td>{$admin['statut']}</td>";
                echo "</tr>";
            }
            
            echo "</table>";
        }
    } catch (PDOException $e) {
        echo "❌ Erreur lors de la récupération des admins: " . $e->getMessage() . "<br>";
    }
    
    // Test 3: Simuler une connexion
    echo "<h3>Test 3: Simulation de connexion</h3>";
    
    $test_email = 'admin@test.com';
    $test_password = 'admin123';
    
    echo "Test avec email: {$test_email} et mot de passe: {$test_password}<br>";
    
    try {
        $stmt = $conn->prepare("SELECT id, nom, email, mot_de_passe, role, statut FROM admins WHERE email = ? AND statut = 'actif'");
        $stmt->execute([$test_email]);
        $admin = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($admin) {
            echo "✅ Admin trouvé dans la base<br>";
            echo "ID: {$admin['id']}<br>";
            echo "Nom: {$admin['nom']}<br>";
            echo "Email: {$admin['email']}<br>";
            echo "Rôle: {$admin['role']}<br>";
            echo "Statut: {$admin['statut']}<br>";
            
            // Tester le mot de passe
            if (password_verify($test_password, $admin['mot_de_passe'])) {
                echo "✅ Mot de passe correct<br>";
                echo "🎉 La connexion devrait fonctionner !<br>";
            } else {
                echo "❌ Mot de passe incorrect<br>";
                echo "Hash stocké: " . substr($admin['mot_de_passe'], 0, 20) . "...<br>";
                
                // Recréer un hash correct
                $new_hash = password_hash($test_password, PASSWORD_DEFAULT);
                $stmt = $conn->prepare("UPDATE admins SET mot_de_passe = ? WHERE id = ?");
                $stmt->execute([$new_hash, $admin['id']]);
                echo "✅ Mot de passe mis à jour, réessayez maintenant<br>";
            }
        } else {
            echo "❌ Aucun admin trouvé avec cet email ou compte inactif<br>";
        }
    } catch (PDOException $e) {
        echo "❌ Erreur lors de la vérification: " . $e->getMessage() . "<br>";
    }
    
    // Test 4: Vérifier les sessions
    echo "<h3>Test 4: Information sur les sessions</h3>";
    echo "Session ID: " . session_id() . "<br>";
    echo "Session démarrée: " . (session_status() === PHP_SESSION_ACTIVE ? "Oui" : "Non") . "<br>";
    
    if (isset($_SESSION['admin_id'])) {
        echo "Admin connecté - ID: " . $_SESSION['admin_id'] . "<br>";
        echo "Nom: " . ($_SESSION['admin_name'] ?? 'Non défini') . "<br>";
    } else {
        echo "Aucun admin connecté<br>";
    }
    
    // Test 5: Vérifier le fichier dashboard.php
    echo "<h3>Test 5: Vérification du fichier dashboard.php</h3>";
    if (file_exists('dashboard.php')) {
        echo "✅ Le fichier dashboard.php existe<br>";
    } else {
        echo "❌ Le fichier dashboard.php n'existe pas !<br>";
        echo "Créez ce fichier ou vérifiez le chemin<br>";
    }
    
} catch (PDOException $e) {
    echo "❌ Erreur de connexion à la base de données: " . $e->getMessage() . "<br>";
    echo "Vérifiez vos paramètres de connexion dans functions.php<br>";
}

echo "<br><h3>Actions recommandées:</h3>";
echo "<ol>";
echo "<li>Exécutez d'abord create_admin.php pour créer un utilisateur admin</li>";
echo "<li>Assurez-vous que dashboard.php existe</li>";
echo "<li>Testez la connexion avec les identifiants créés</li>";
echo "<li>Vérifiez les logs d'erreur PHP si le problème persiste</li>";
echo "</ol>";
?>