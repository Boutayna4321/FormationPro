<?php
// Configuration de la base de données
$host = "localhost";
$dbname = "formationsdb";
$username = "root";
$password = "";

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("❌ Erreur de connexion à la base de données : " . $e->getMessage());
}

// Fonctions utilitaires
if (!function_exists('sanitizeInput')) {
    function sanitizeInput($data) {
        return htmlspecialchars(strip_tags(trim($data)));
    }
}