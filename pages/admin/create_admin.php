<?php
// create_admin.php - Script pour créer un utilisateur admin
require_once 'functions.php';

try {
    $conn = db_connect();
    
    // Données de l'admin test
    $nom = 'Admin Test';
    $email = 'admin@test.com';
    $mot_de_passe_clair = 'admin123'; // Changez ce mot de passe !
    $mot_de_passe_hash = password_hash($mot_de_passe_clair, PASSWORD_DEFAULT);
    $role = 'admin';
    $statut = 'actif';
    
    // Vérifier si l'email existe déjà
    $stmt = $conn->prepare("SELECT COUNT(*) FROM admins WHERE email = ?");
    $stmt->execute([$email]);
    
    if ($stmt->fetchColumn() > 0) {
        echo "Un admin avec cet email existe déjà.<br>";
        
        // Mettre à jour le mot de passe existant
        $stmt = $conn->prepare("UPDATE admins SET mot_de_passe = ?, statut = 'actif' WHERE email = ?");
        $stmt->execute([$mot_de_passe_hash, $email]);
        echo "Mot de passe mis à jour pour l'admin existant.<br>";
    } else {
        // Créer un nouvel admin
        $stmt = $conn->prepare("INSERT INTO admins (nom, email, mot_de_passe, role, statut) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$nom, $email, $mot_de_passe_hash, $role, $statut]);
        echo "Nouvel admin créé avec succès.<br>";
    }
    
    echo "<h3>Informations de connexion :</h3>";
    echo "<strong>Email :</strong> {$email}<br>";
    echo "<strong>Mot de passe :</strong> {$mot_de_passe_clair}<br>";
    echo "<p><em>Changez ce mot de passe après la première connexion !</em></p>";
    
    // Vérifier la structure de la table
    echo "<h3>Structure actuelle de la table admins :</h3>";
    $stmt = $conn->query("DESCRIBE admins");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<table border='1'>";
    echo "<tr><th>Champ</th><th>Type</th><th>Null</th><th>Clé</th><th>Défaut</th></tr>";
    foreach ($columns as $column) {
        echo "<tr>";
        echo "<td>{$column['Field']}</td>";
        echo "<td>{$column['Type']}</td>";
        echo "<td>{$column['Null']}</td>";
        echo "<td>{$column['Key']}</td>";
        echo "<td>{$column['Default']}</td>";
        echo "</tr>";
    }
    echo "</table>";
    
} catch (PDOException $e) {
    echo "Erreur : " . $e->getMessage();
}
?>