<?php
// functions.php - Système de gestion de formations avec authentification et logique hiérarchique

// ==================== FONCTIONS DE CONNEXION ET AUTHENTIFICATION ====================

function db_connect() {
    $host = "localhost";
    $dbname = "formationsdb";
    $username = "root";
    $password = "";
    
    try {
        $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $conn;
    } catch (PDOException $e) {
        die("Erreur de connexion à la base de données : " . $e->getMessage());
    }
}



// Fonction pour vérifier si l'admin est connecté
function isAdminLoggedIn() {
    return isset($_SESSION['admin_id']) && !empty($_SESSION['admin_id']);
}

// Fonction pour rediriger vers la page de connexion si non connecté
function requireAdminLogin() {
    if (!isAdminLoggedIn()) {
        header('Location: login.php');
        exit();
    }
}

// Fonction pour nettoyer les données d'entrée
function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Fonction pour enregistrer les tentatives de connexion
function logLoginAttempt($conn, $email, $ip_address, $user_agent, $success) {
    try {
        $stmt = $conn->prepare("INSERT INTO admin_log (email, ip_address, user_agent, login_success, attempt_time) VALUES (?, ?, ?, ?, NOW())");
        $stmt->execute([$email, $ip_address, $user_agent, $success ? 1 : 0]);
    } catch (PDOException $e) {
        error_log("Erreur lors de l'enregistrement du log : " . $e->getMessage());
    }
}

// Fonction pour obtenir l'adresse IP du client
function getClientIP() {
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        return $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        return $_SERVER['HTTP_X_FORWARDED_FOR'];
    } else {
        return $_SERVER['REMOTE_ADDR'];
    }
}

// ==================== FONCTIONS CRUD DE BASE (AMÉLIORÉES) ====================

function ajouter($conn, $table, $nom) {
    $stmt = $conn->prepare("INSERT INTO $table (nom) VALUES (?)");
    $stmt->execute([$nom]);
}

function modifier($conn, $table, $id, $nom) {
    $stmt = $conn->prepare("UPDATE $table SET nom = ? WHERE id = ?");
    $stmt->execute([$nom, $id]);
}

function supprimer($conn, $table, $id) {
    $stmt = $conn->prepare("DELETE FROM $table WHERE id = ?");
    $stmt->execute([$id]);
}

function getAll($conn, $table) {
    return $conn->query("SELECT * FROM $table")->fetchAll(PDO::FETCH_ASSOC);
}

// ==================== FONCTIONS DE VALIDATION HIÉRARCHIQUE ====================

/**
 * Vérifier si un pays peut être supprimé (pas de villes associées)
 */
function canDeletePays($pdo, $paysId) {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM villes WHERE pays_id = ?");
    $stmt->execute([$paysId]);
    return $stmt->fetchColumn() == 0;
}

/**
 * Vérifier si une ville peut être supprimée (pas de formations associées)
 */
function canDeleteVille($pdo, $villeId) {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM formations WHERE ville_id = ?");
    $stmt->execute([$villeId]);
    return $stmt->fetchColumn() == 0;
}

/**
 * Vérifier si un domaine peut être supprimé (pas de sujets associés)
 */
function canDeleteDomaine($pdo, $domaineId) {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM sujets WHERE domaine_id = ?");
    $stmt->execute([$domaineId]);
    return $stmt->fetchColumn() == 0;
}

/**
 * Vérifier si un sujet peut être supprimé (pas de cours associés)
 */
function canDeleteSujet($pdo, $sujetId) {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM cours WHERE sujet_id = ?");
    $stmt->execute([$sujetId]);
    return $stmt->fetchColumn() == 0;
}

/**
 * Vérifier si un cours peut être supprimé (pas de formations associées)
 */
function canDeleteCours($pdo, $coursId) {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM formations WHERE cours_id = ?");
    $stmt->execute([$coursId]);
    return $stmt->fetchColumn() == 0;
}

/**
 * Vérifier si un formateur peut être supprimé (pas de formations associées)
 */
function canDeleteFormateur($pdo, $formateurId) {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM formations WHERE formateur_id = ?");
    $stmt->execute([$formateurId]);
    return $stmt->fetchColumn() == 0;
}

// ==================== FONCTIONS DE RÉCUPÉRATION HIÉRARCHIQUE ====================

/**
 * Récupérer les villes d'un pays spécifique
 */
function getVillesByPays($pdo, $paysId) {
    $stmt = $pdo->prepare("
        SELECT v.*, p.nom_pays 
        FROM villes v 
        JOIN pays p ON v.pays_id = p.id 
        WHERE v.pays_id = ? 
        ORDER BY v.nom_ville
    ");
    $stmt->execute([$paysId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Récupérer les sujets d'un domaine spécifique
 */
function getSujetsByDomaine($pdo, $domaineId) {
    $stmt = $pdo->prepare("
        SELECT s.*, d.nom_domaine 
        FROM sujets s 
        JOIN domaines d ON s.domaine_id = d.id 
        WHERE s.domaine_id = ? 
        ORDER BY s.nom_sujet
    ");
    $stmt->execute([$domaineId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Récupérer les cours d'un sujet spécifique
 */
function getCoursBySujet($pdo, $sujetId) {
    $stmt = $pdo->prepare("
        SELECT c.*, s.nom_sujet, d.nom_domaine 
        FROM cours c 
        JOIN sujets s ON c.sujet_id = s.id 
        JOIN domaines d ON s.domaine_id = d.id 
        WHERE c.sujet_id = ? 
        ORDER BY c.nom_cours
    ");
    $stmt->execute([$sujetId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Récupérer les formations d'un cours spécifique
 */
function getFormationsByCours($pdo, $coursId) {
    $stmt = $pdo->prepare("
        SELECT f.*, c.nom_cours, v.nom_ville, p.nom_pays, fo.nom_formateur
        FROM formations f 
        JOIN cours c ON f.cours_id = c.id 
        JOIN villes v ON f.ville_id = v.id 
        JOIN pays p ON v.pays_id = p.id 
        JOIN formateurs fo ON f.formateur_id = fo.id 
        WHERE f.cours_id = ? 
        ORDER BY f.date_formation
    ");
    $stmt->execute([$coursId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// ==================== FONCTIONS POUR LES LISTES DÉROULANTES ====================

/**
 * Récupérer tous les pays pour les listes déroulantes
 */
function getAllPays($pdo) {
    $stmt = $pdo->query("SELECT * FROM pays ORDER BY nom_pays");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Récupérer toutes les villes pour les listes déroulantes
 */
function getAllVilles($pdo) {
    $stmt = $pdo->query("
        SELECT v.*, p.nom_pays 
        FROM villes v 
        JOIN pays p ON v.pays_id = p.id 
        ORDER BY p.nom_pays, v.nom_ville
    ");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Récupérer tous les domaines pour les listes déroulantes
 */
function getAllDomaines($pdo) {
    $stmt = $pdo->query("SELECT * FROM domaines ORDER BY nom_domaine");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Récupérer tous les sujets pour les listes déroulantes
 */
function getAllSujets($pdo) {
    $stmt = $pdo->query("
        SELECT s.*, d.nom_domaine 
        FROM sujets s 
        JOIN domaines d ON s.domaine_id = d.id 
        ORDER BY d.nom_domaine, s.nom_sujet
    ");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Récupérer tous les cours pour les listes déroulantes
 */
function getAllCours($pdo) {
    $stmt = $pdo->query("
        SELECT c.*, s.nom_sujet, d.nom_domaine 
        FROM cours c 
        JOIN sujets s ON c.sujet_id = s.id 
        JOIN domaines d ON s.domaine_id = d.id 
        ORDER BY d.nom_domaine, s.nom_sujet, c.nom_cours
    ");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Récupérer tous les formateurs pour les listes déroulantes
 */
function getAllFormateurs($pdo) {
    $stmt = $pdo->query("SELECT * FROM formateurs ORDER BY nom_formateur");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// ==================== FONCTIONS DE VALIDATION POUR CRÉATION ====================

/**
 * Valider la création d'une ville (le pays doit exister)
 */
function validateVilleCreation($pdo, $paysId) {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM pays WHERE id = ?");
    $stmt->execute([$paysId]);
    return $stmt->fetchColumn() > 0;
}

/**
 * Valider la création d'un sujet (le domaine doit exister)
 */
function validateSujetCreation($pdo, $domaineId) {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM domaines WHERE id = ?");
    $stmt->execute([$domaineId]);
    return $stmt->fetchColumn() > 0;
}

/**
 * Valider la création d'un cours (le sujet doit exister)
 */
function validateCoursCreation($pdo, $sujetId) {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM sujets WHERE id = ?");
    $stmt->execute([$sujetId]);
    return $stmt->fetchColumn() > 0;
}

/**
 * Valider la création d'une formation (cours, ville, formateur doivent exister)
 */
function validateFormationCreation($pdo, $coursId, $villeId, $formateurId) {
    // Vérifier que le cours existe
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM cours WHERE id = ?");
    $stmt->execute([$coursId]);
    if ($stmt->fetchColumn() == 0) return false;
    
    // Vérifier que la ville existe
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM villes WHERE id = ?");
    $stmt->execute([$villeId]);
    if ($stmt->fetchColumn() == 0) return false;
    
    // Vérifier que le formateur existe
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM formateurs WHERE id = ?");
    $stmt->execute([$formateurId]);
    if ($stmt->fetchColumn() == 0) return false;
    
    return true;
}

// ==================== FONCTIONS UTILITAIRES ====================

/**
 * Générer un message d'erreur pour suppression impossible
 */
function getDeleteErrorMessage($entity, $dependencies) {
    return "Impossible de supprimer ce/cette {$entity} car il/elle est associé(e) à des {$dependencies}.";
}

/**
 * Vérifier l'unicité d'un nom dans une table
 */
function checkUniqueName($pdo, $table, $nameField, $name, $excludeId = null) {
    $sql = "SELECT COUNT(*) FROM {$table} WHERE {$nameField} = ?";
    $params = [$name];
    
    if ($excludeId) {
        $sql .= " AND id != ?";
        $params[] = $excludeId;
    }
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchColumn() == 0;
}

/**
 * Obtenir les informations complètes d'une formation
 */
function getFormationComplete($pdo, $formationId) {
    $stmt = $pdo->prepare("
        SELECT f.*, 
               c.nom_cours, 
               s.nom_sujet, 
               d.nom_domaine,
               v.nom_ville, 
               p.nom_pays,
               fo.nom_formateur, fo.email_formateur
        FROM formations f 
        JOIN cours c ON f.cours_id = c.id 
        JOIN sujets s ON c.sujet_id = s.id 
        JOIN domaines d ON s.domaine_id = d.id 
        JOIN villes v ON f.ville_id = v.id 
        JOIN pays p ON v.pays_id = p.id 
        JOIN formateurs fo ON f.formateur_id = fo.id 
        WHERE f.id = ?
    ");
    $stmt->execute([$formationId]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

// ==================== FONCTIONS CRUD SPÉCIALISÉES POUR CHAQUE ENTITÉ ====================

/**
 * Ajouter un pays avec validation d'unicité
 */
function ajouterPays($conn, $nomPays) {
    $nomPays = sanitizeInput($nomPays);
    
    if (empty($nomPays)) {
        return ["success" => false, "message" => "Le nom du pays ne peut pas être vide."];
    }
    
    if (!checkUniqueName($conn, 'pays', 'nom_pays', $nomPays)) {
        return ["success" => false, "message" => "Ce nom de pays existe déjà."];
    }
    
    try {
        $stmt = $conn->prepare("INSERT INTO pays (nom_pays) VALUES (?)");
        $stmt->execute([$nomPays]);
        return ["success" => true, "message" => "Pays ajouté avec succès."];
    } catch (PDOException $e) {
        return ["success" => false, "message" => "Erreur lors de l'ajout : " . $e->getMessage()];
    }
}

/**
 * Ajouter une ville avec validation hiérarchique
 */
function ajouterVille($conn, $nomVille, $paysId) {
    $nomVille = sanitizeInput($nomVille);
    
    if (empty($nomVille)) {
        return ["success" => false, "message" => "Le nom de la ville ne peut pas être vide."];
    }
    
    if (!validateVilleCreation($conn, $paysId)) {
        return ["success" => false, "message" => "Le pays sélectionné n'existe pas."];
    }
    
    if (!checkUniqueName($conn, 'villes', 'nom_ville', $nomVille)) {
        return ["success" => false, "message" => "Cette ville existe déjà."];
    }
    
    try {
        $stmt = $conn->prepare("INSERT INTO villes (nom_ville, pays_id) VALUES (?, ?)");
        $stmt->execute([$nomVille, $paysId]);
        return ["success" => true, "message" => "Ville ajoutée avec succès."];
    } catch (PDOException $e) {
        return ["success" => false, "message" => "Erreur lors de l'ajout : " . $e->getMessage()];
    }
}

/**
 * Ajouter un domaine avec validation d'unicité
 */
function ajouterDomaine($conn, $nomDomaine) {
    $nomDomaine = sanitizeInput($nomDomaine);
    
    if (empty($nomDomaine)) {
        return ["success" => false, "message" => "Le nom du domaine ne peut pas être vide."];
    }
    
    if (!checkUniqueName($conn, 'domaines', 'nom_domaine', $nomDomaine)) {
        return ["success" => false, "message" => "Ce nom de domaine existe déjà."];
    }
    
    try {
        $stmt = $conn->prepare("INSERT INTO domaines (nom_domaine) VALUES (?)");
        $stmt->execute([$nomDomaine]);
        return ["success" => true, "message" => "Domaine ajouté avec succès."];
    } catch (PDOException $e) {
        return ["success" => false, "message" => "Erreur lors de l'ajout : " . $e->getMessage()];
    }
}

/**
 * Ajouter un sujet avec validation hiérarchique
 */
function ajouterSujet($conn, $nomSujet, $domaineId) {
    $nomSujet = sanitizeInput($nomSujet);
    
    if (empty($nomSujet)) {
        return ["success" => false, "message" => "Le nom du sujet ne peut pas être vide."];
    }
    
    if (!validateSujetCreation($conn, $domaineId)) {
        return ["success" => false, "message" => "Le domaine sélectionné n'existe pas."];
    }
    
    if (!checkUniqueName($conn, 'sujets', 'nom_sujet', $nomSujet)) {
        return ["success" => false, "message" => "Ce nom de sujet existe déjà."];
    }
    
    try {
        $stmt = $conn->prepare("INSERT INTO sujets (nom_sujet, domaine_id) VALUES (?, ?)");
        $stmt->execute([$nomSujet, $domaineId]);
        return ["success" => true, "message" => "Sujet ajouté avec succès."];
    } catch (PDOException $e) {
        return ["success" => false, "message" => "Erreur lors de l'ajout : " . $e->getMessage()];
    }
}

/**
 * Ajouter un cours avec validation hiérarchique
 */
function ajouterCours($conn, $nomCours, $description, $sujetId) {
    $nomCours = sanitizeInput($nomCours);
    $description = sanitizeInput($description);
    
    if (empty($nomCours)) {
        return ["success" => false, "message" => "Le nom du cours ne peut pas être vide."];
    }
    
    if (!validateCoursCreation($conn, $sujetId)) {
        return ["success" => false, "message" => "Le sujet sélectionné n'existe pas."];
    }
    
    if (!checkUniqueName($conn, 'cours', 'nom_cours', $nomCours)) {
        return ["success" => false, "message" => "Ce nom de cours existe déjà."];
    }
    
    try {
        $stmt = $conn->prepare("INSERT INTO cours (nom_cours, description, sujet_id) VALUES (?, ?, ?)");
        $stmt->execute([$nomCours, $description, $sujetId]);
        return ["success" => true, "message" => "Cours ajouté avec succès."];
    } catch (PDOException $e) {
        return ["success" => false, "message" => "Erreur lors de l'ajout : " . $e->getMessage()];
    }
}

/**
 * Ajouter un formateur
 */
function ajouterFormateur($conn, $nomFormateur, $emailFormateur, $specialite = null) {
    $nomFormateur = sanitizeInput($nomFormateur);
    $emailFormateur = sanitizeInput($emailFormateur);
    $specialite = sanitizeInput($specialite);
    
    if (empty($nomFormateur) || empty($emailFormateur)) {
        return ["success" => false, "message" => "Le nom et l'email du formateur sont obligatoires."];
    }
    
    if (!filter_var($emailFormateur, FILTER_VALIDATE_EMAIL)) {
        return ["success" => false, "message" => "Format d'email invalide."];
    }
    
    if (!checkUniqueName($conn, 'formateurs', 'email_formateur', $emailFormateur)) {
        return ["success" => false, "message" => "Cet email existe déjà."];
    }
    
    try {
        $stmt = $conn->prepare("INSERT INTO formateurs (nom_formateur, email_formateur, specialite) VALUES (?, ?, ?)");
        $stmt->execute([$nomFormateur, $emailFormateur, $specialite]);
        return ["success" => true, "message" => "Formateur ajouté avec succès."];
    } catch (PDOException $e) {
        return ["success" => false, "message" => "Erreur lors de l'ajout : " . $e->getMessage()];
    }
}

/**
 * Ajouter une formation avec validation complète
 */
function ajouterFormation($conn, $coursId, $villeId, $formateurId, $dateFormation, $prix, $typeFormation) {
    $dateFormation = sanitizeInput($dateFormation);
    $prix = floatval($prix);
    $typeFormation = sanitizeInput($typeFormation);
    
    if (empty($dateFormation) || $prix <= 0) {
        return ["success" => false, "message" => "La date et le prix sont obligatoires et valides."];
    }
    
    if (!in_array($typeFormation, ['presentiel', 'distanciel'])) {
        return ["success" => false, "message" => "Le type de formation doit être 'presentiel' ou 'distanciel'."];
    }
    
    if (!validateFormationCreation($conn, $coursId, $villeId, $formateurId)) {
        return ["success" => false, "message" => "Erreur : Vérifiez que le cours, la ville et le formateur existent."];
    }
    
    // Vérifier que la date n'est pas dans le passé
    if (strtotime($dateFormation) < strtotime('today')) {
        return ["success" => false, "message" => "La date de formation ne peut pas être dans le passé."];
    }
    
    try {
        $stmt = $conn->prepare("
            INSERT INTO formations (cours_id, ville_id, formateur_id, date_formation, prix, type_formation) 
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([$coursId, $villeId, $formateurId, $dateFormation, $prix, $typeFormation]);
        return ["success" => true, "message" => "Formation créée avec succès."];
    } catch (PDOException $e) {
        return ["success" => false, "message" => "Erreur lors de l'ajout : " . $e->getMessage()];
    }
}

// ==================== FONCTIONS DE SUPPRESSION SÉCURISÉES ====================

/**
 * Supprimer un pays avec vérification
 */
function supprimerPays($conn, $paysId) {
    if (!canDeletePays($conn, $paysId)) {
        return ["success" => false, "message" => getDeleteErrorMessage("pays", "villes")];
    }
    
    try {
        $stmt = $conn->prepare("DELETE FROM pays WHERE id = ?");
        $stmt->execute([$paysId]);
        return ["success" => true, "message" => "Pays supprimé avec succès."];
    } catch (PDOException $e) {
        return ["success" => false, "message" => "Erreur lors de la suppression : " . $e->getMessage()];
    }
}

/**
 * Supprimer une ville avec vérification
 */
function supprimerVille($conn, $villeId) {
    if (!canDeleteVille($conn, $villeId)) {
        return ["success" => false, "message" => getDeleteErrorMessage("ville", "formations")];
    }
    
    try {
        $stmt = $conn->prepare("DELETE FROM villes WHERE id = ?");
        $stmt->execute([$villeId]);
        return ["success" => true, "message" => "Ville supprimée avec succès."];
    } catch (PDOException $e) {
        return ["success" => false, "message" => "Erreur lors de la suppression : " . $e->getMessage()];
    }
}

/**
 * Supprimer un domaine avec vérification
 */
function supprimerDomaine($conn, $domaineId) {
    if (!canDeleteDomaine($conn, $domaineId)) {
        return ["success" => false, "message" => getDeleteErrorMessage("domaine", "sujets")];
    }
    
    try {
        $stmt = $conn->prepare("DELETE FROM domaines WHERE id = ?");
        $stmt->execute([$domaineId]);
        return ["success" => true, "message" => "Domaine supprimé avec succès."];
    } catch (PDOException $e) {
        return ["success" => false, "message" => "Erreur lors de la suppression : " . $e->getMessage()];
    }
}

/**
 * Supprimer un sujet avec vérification
 */
function supprimerSujet($conn, $sujetId) {
    if (!canDeleteSujet($conn, $sujetId)) {
        return ["success" => false, "message" => getDeleteErrorMessage("sujet", "cours")];
    }
    
    try {
        $stmt = $conn->prepare("DELETE FROM sujets WHERE id = ?");
        $stmt->execute([$sujetId]);
        return ["success" => true, "message" => "Sujet supprimé avec succès."];
    } catch (PDOException $e) {
        return ["success" => false, "message" => "Erreur lors de la suppression : " . $e->getMessage()];
    }
}

/**
 * Supprimer un cours avec vérification
 */
function supprimerCours($conn, $coursId) {
    if (!canDeleteCours($conn, $coursId)) {
        return ["success" => false, "message" => getDeleteErrorMessage("cours", "formations")];
    }
    
    try {
        $stmt = $conn->prepare("DELETE FROM cours WHERE id = ?");
        $stmt->execute([$coursId]);
        return ["success" => true, "message" => "Cours supprimé avec succès."];
    } catch (PDOException $e) {
        return ["success" => false, "message" => "Erreur lors de la suppression : " . $e->getMessage()];
    }
}

/**
 * Supprimer un formateur avec vérification
 */
function supprimerFormateur($conn, $formateurId) {
    if (!canDeleteFormateur($conn, $formateurId)) {
        return ["success" => false, "message" => getDeleteErrorMessage("formateur", "formations")];
    }
    
    try {
        $stmt = $conn->prepare("DELETE FROM formateurs WHERE id = ?");
        $stmt->execute([$formateurId]);
        return ["success" => true, "message" => "Formateur supprimé avec succès."];
    } catch (PDOException $e) {
        return ["success" => false, "message" => "Erreur lors de la suppression : " . $e->getMessage()];
    }
}

/**
 * Supprimer une formation
 */
function supprimerFormation($conn, $formationId) {
    try {
        $stmt = $conn->prepare("DELETE FROM formations WHERE id = ?");
        $stmt->execute([$formationId]);
        return ["success" => true, "message" => "Formation supprimée avec succès."];
    } catch (PDOException $e) {
        return ["success" => false, "message" => "Erreur lors de la suppression : " . $e->getMessage()];
    }
}