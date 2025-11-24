-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1
-- Généré le : lun. 16 juin 2025 à 01:50
-- Version du serveur : 10.4.32-MariaDB
-- Version de PHP : 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `formationsdb`
--

-- --------------------------------------------------------

--
-- Structure de la table `admins`
--

CREATE TABLE `admins` (
  `id` int(11) NOT NULL,
  `nom_admin` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `mot_de_passe` varchar(255) NOT NULL,
  `role` varchar(50) DEFAULT 'admin',
  `actif` tinyint(1) DEFAULT 1,
  `date_creation` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `admins`
--

INSERT INTO `admins` (`id`, `nom_admin`, `email`, `mot_de_passe`, `role`, `actif`, `date_creation`) VALUES
(1, 'Super Admin', 'admin@exemple.com', '$2y$10$dvScUR1wwHK1O/397dvKUOiZWKaTRTu8H36eFcQVj3C96G6ES1.8a', 'admin', 1, '2025-06-15 19:17:54');

-- --------------------------------------------------------

--
-- Structure de la table `admin_log`
--

CREATE TABLE `admin_log` (
  `id` int(11) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `ip_address` varchar(100) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `login_success` tinyint(1) DEFAULT NULL,
  `attempt_time` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `admin_log`
--

INSERT INTO `admin_log` (`id`, `email`, `ip_address`, `user_agent`, `login_success`, `attempt_time`) VALUES
(1, NULL, NULL, NULL, 1, '2025-06-15 19:31:09'),
(2, 'admin@exemple.com', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', 1, '2025-06-15 19:37:13'),
(3, 'admin@exemple.com', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', 1, '2025-06-15 19:46:45'),
(4, 'admin@exemple.com', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', 1, '2025-06-15 19:59:40'),
(5, 'admin@exemple.com', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', 1, '2025-06-15 20:14:19'),
(6, 'admin@exemple.com', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', 1, '2025-06-15 20:19:03');

-- --------------------------------------------------------

--
-- Structure de la table `cours`
--

CREATE TABLE `cours` (
  `id` int(11) NOT NULL,
  `nom_cours` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `sujet_id` int(11) NOT NULL,
  `duree_jours` int(11) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `cours`
--

INSERT INTO `cours` (`id`, `nom_cours`, `description`, `sujet_id`, `duree_jours`, `created_at`, `updated_at`) VALUES
(1, 'Scrum', 'Formation Scrum Master certification', 1, 3, '2025-06-08 16:55:21', '2025-06-08 16:55:21'),
(2, 'Prince 2', 'Formation Prince 2 Foundation', 1, 5, '2025-06-08 16:55:21', '2025-06-08 16:55:21'),
(3, 'ITIL', 'Formation ITIL Foundation', 2, 3, '2025-06-08 16:55:21', '2025-06-08 16:55:21'),
(4, 'COBIT', 'Formation COBIT 5 Foundation', 2, 4, '2025-06-08 16:55:21', '2025-06-08 16:55:21'),
(5, 'JEE', 'Développement Java Enterprise Edition', 3, 10, '2025-06-08 16:55:21', '2025-06-08 16:55:21'),
(6, 'Web Technologies', 'HTML, CSS, JavaScript, PHP', 3, 8, '2025-06-08 16:55:21', '2025-06-08 16:55:21'),
(7, 'Hadoop', 'Apache Hadoop pour Big Data', 4, 7, '2025-06-08 16:55:21', '2025-06-08 16:55:21'),
(8, 'Spark', 'Apache Spark pour traitement de données', 4, 5, '2025-06-08 16:55:21', '2025-06-08 16:55:21'),
(9, 'CISCO', 'Certification CISCO CCNA', 5, 15, '2025-06-08 16:55:21', '2025-06-08 16:55:21'),
(10, 'SEO/SEM', 'Référencement et marketing digital', 6, 4, '2025-06-08 16:55:21', '2025-06-08 16:55:21'),
(11, 'Réseaux Sociaux', 'Gestion des réseaux sociaux', 7, 3, '2025-06-08 16:55:21', '2025-06-08 16:55:21'),
(12, 'Comptabilité Générale', 'Principes de comptabilité', 8, 6, '2025-06-08 16:55:21', '2025-06-08 16:55:21'),
(13, 'Analyse des États Financiers', 'Analyse et interprétation', 9, 4, '2025-06-08 16:55:21', '2025-06-08 16:55:21');

-- --------------------------------------------------------

--
-- Structure de la table `domaines`
--

CREATE TABLE `domaines` (
  `id` int(11) NOT NULL,
  `nom_domaine` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `domaines`
--

INSERT INTO `domaines` (`id`, `nom_domaine`, `description`, `created_at`, `updated_at`) VALUES
(1, 'Management', 'Formations en gestion et management', '2025-06-08 16:55:21', '2025-06-08 16:55:21'),
(2, 'Computer Science', 'Formations en informatique et technologies', '2025-06-08 16:55:21', '2025-06-08 16:55:21'),
(3, 'Marketing', 'Formations en marketing et communication', '2025-06-08 16:55:21', '2025-06-08 16:55:21'),
(4, 'Finance', 'Formations en finance et comptabilité', '2025-06-08 16:55:21', '2025-06-08 16:55:21'),
(5, 'Cyber security', NULL, '2025-06-15 20:19:32', '2025-06-15 20:19:32');

-- --------------------------------------------------------

--
-- Structure de la table `formateurs`
--

CREATE TABLE `formateurs` (
  `id` int(11) NOT NULL,
  `nom_formateur` varchar(100) NOT NULL,
  `email_formateur` varchar(100) NOT NULL,
  `telephone` varchar(20) DEFAULT NULL,
  `specialite` varchar(200) DEFAULT NULL,
  `experience_annees` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `formateurs`
--

INSERT INTO `formateurs` (`id`, `nom_formateur`, `email_formateur`, `telephone`, `specialite`, `experience_annees`, `created_at`, `updated_at`) VALUES
(1, 'Ahmed Bennani', 'ahmed.bennani@formation.ma', '0661234567', 'Management de Projet', 8, '2025-06-08 16:55:21', '2025-06-08 16:55:21'),
(2, 'Sarah Dubois', 'sarah.dubois@formation.fr', '+33123456789', 'Technologies Web', 6, '2025-06-08 16:55:21', '2025-06-08 16:55:21'),
(3, 'Mohammed Alami', 'mohammed.alami@formation.ma', '0662345678', 'Big Data et Analytics', 10, '2025-06-08 16:55:21', '2025-06-08 16:55:21'),
(4, 'Elena Rodriguez', 'elena.rodriguez@formation.es', '+34987654321', 'Marketing Digital', 5, '2025-06-08 16:55:21', '2025-06-08 16:55:21'),
(5, 'Youssef Tazi', 'youssef.tazi@formation.ma', '0663456789', 'Réseaux et Sécurité', 12, '2025-06-08 16:55:21', '2025-06-08 16:55:21'),
(6, 'Fatima Zahra', 'fatima.zahra@formation.ma', '0664567890', 'Finance et Comptabilité', 7, '2025-06-08 16:55:21', '2025-06-08 16:55:21');

-- --------------------------------------------------------

--
-- Structure de la table `formations`
--

CREATE TABLE `formations` (
  `id` int(11) NOT NULL,
  `cours_id` int(11) NOT NULL,
  `ville_id` int(11) NOT NULL,
  `formateur_id` int(11) NOT NULL,
  `date_formation` date NOT NULL,
  `heure_debut` time DEFAULT '09:00:00',
  `heure_fin` time DEFAULT '17:00:00',
  `prix` decimal(10,2) NOT NULL,
  `prix_devise` varchar(3) DEFAULT 'MAD',
  `type_formation` enum('presentiel','distanciel') NOT NULL DEFAULT 'presentiel',
  `nombre_places_max` int(11) DEFAULT 20,
  `nombre_inscrits` int(11) DEFAULT 0,
  `statut` enum('planifiee','en_cours','terminee','annulee') DEFAULT 'planifiee',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `formations`
--

INSERT INTO `formations` (`id`, `cours_id`, `ville_id`, `formateur_id`, `date_formation`, `heure_debut`, `heure_fin`, `prix`, `prix_devise`, `type_formation`, `nombre_places_max`, `nombre_inscrits`, `statut`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 1, '2025-07-15', '09:00:00', '17:00:00', 2500.00, 'MAD', 'presentiel', 20, 0, 'planifiee', '2025-06-08 16:55:21', '2025-06-12 20:58:03'),
(2, 2, 2, 1, '2025-08-10', '09:00:00', '17:00:00', 3200.00, 'MAD', 'presentiel', 15, 0, 'planifiee', '2025-06-08 16:55:21', '2025-06-08 16:55:21'),
(3, 5, 1, 2, '2025-07-22', '09:00:00', '17:00:00', 4500.00, 'MAD', 'presentiel', 12, 0, 'planifiee', '2025-06-08 16:55:21', '2025-06-08 16:55:21'),
(4, 6, 3, 2, '2025-08-05', '09:00:00', '17:00:00', 3800.00, 'MAD', 'distanciel', 25, 0, 'planifiee', '2025-06-08 16:55:21', '2025-06-08 16:55:21'),
(5, 7, 1, 3, '2025-09-01', '09:00:00', '17:00:00', 5200.00, 'MAD', 'presentiel', 10, 0, 'planifiee', '2025-06-08 16:55:21', '2025-06-08 16:55:21'),
(6, 10, 4, 4, '2025-07-28', '09:00:00', '17:00:00', 2800.00, 'MAD', 'distanciel', 30, 0, 'planifiee', '2025-06-08 16:55:21', '2025-06-08 16:55:21');

-- --------------------------------------------------------

--
-- Structure de la table `inscriptions`
--

CREATE TABLE `inscriptions` (
  `id` int(11) NOT NULL,
  `formation_id` int(11) DEFAULT NULL,
  `nom` varchar(100) DEFAULT NULL,
  `prenom` varchar(100) DEFAULT NULL,
  `email` varchar(150) DEFAULT NULL,
  `telephone` varchar(20) DEFAULT NULL,
  `entreprise` varchar(100) DEFAULT NULL,
  `poste` varchar(100) DEFAULT NULL,
  `commentaires` text DEFAULT NULL,
  `date_inscription` datetime DEFAULT NULL,
  `statut` enum('en_attente','confirmee','annulee') DEFAULT 'en_attente'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `inscriptions`
--

INSERT INTO `inscriptions` (`id`, `formation_id`, `nom`, `prenom`, `email`, `telephone`, `entreprise`, `poste`, `commentaires`, `date_inscription`, `statut`) VALUES
(1, 3, 'mohammed', 'moha', 'mohammed.moha@gmail.com', '06 14 24 35 67', 'IBEM', 'superieur', 'hello first try', '2025-06-10 22:06:20', 'en_attente');

-- --------------------------------------------------------

--
-- Structure de la table `pays`
--

CREATE TABLE `pays` (
  `id` int(11) NOT NULL,
  `nom_pays` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `pays`
--

INSERT INTO `pays` (`id`, `nom_pays`, `created_at`, `updated_at`) VALUES
(1, 'Maroc', '2025-06-08 16:55:21', '2025-06-08 16:55:21'),
(2, 'France', '2025-06-08 16:55:21', '2025-06-08 16:55:21'),
(3, 'Allemagne', '2025-06-08 16:55:21', '2025-06-08 16:55:21'),
(4, 'Espagne', '2025-06-08 16:55:21', '2025-06-08 16:55:21'),
(5, 'Tunisie', '2025-06-08 16:55:21', '2025-06-08 16:55:21');

-- --------------------------------------------------------

--
-- Structure de la table `sujets`
--

CREATE TABLE `sujets` (
  `id` int(11) NOT NULL,
  `nom_sujet` varchar(100) NOT NULL,
  `domaine_id` int(11) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `sujets`
--

INSERT INTO `sujets` (`id`, `nom_sujet`, `domaine_id`, `description`, `created_at`, `updated_at`) VALUES
(1, 'Management de Projet', 1, NULL, '2025-06-08 16:55:21', '2025-06-08 16:55:21'),
(2, 'Management de Services', 1, NULL, '2025-06-08 16:55:21', '2025-06-08 16:55:21'),
(3, 'IT', 2, NULL, '2025-06-08 16:55:21', '2025-06-08 16:55:21'),
(4, 'Big Data', 2, NULL, '2025-06-08 16:55:21', '2025-06-08 16:55:21'),
(5, 'Reseau', 2, NULL, '2025-06-08 16:55:21', '2025-06-08 16:55:21'),
(6, 'Marketing Digital', 3, NULL, '2025-06-08 16:55:21', '2025-06-08 16:55:21'),
(7, 'Communication', 3, NULL, '2025-06-08 16:55:21', '2025-06-08 16:55:21'),
(8, 'Comptabilité', 4, NULL, '2025-06-08 16:55:21', '2025-06-08 16:55:21'),
(9, 'Analyse Financière', 4, NULL, '2025-06-08 16:55:21', '2025-06-08 16:55:21');

-- --------------------------------------------------------

--
-- Structure de la table `villes`
--

CREATE TABLE `villes` (
  `id` int(11) NOT NULL,
  `nom_ville` varchar(100) NOT NULL,
  `pays_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `villes`
--

INSERT INTO `villes` (`id`, `nom_ville`, `pays_id`, `created_at`, `updated_at`) VALUES
(1, 'Casablanca', 1, '2025-06-08 16:55:21', '2025-06-08 16:55:21'),
(2, 'Rabat', 1, '2025-06-08 16:55:21', '2025-06-08 16:55:21'),
(3, 'Fès', 1, '2025-06-08 16:55:21', '2025-06-08 16:55:21'),
(4, 'Marrakech', 1, '2025-06-08 16:55:21', '2025-06-08 16:55:21'),
(5, 'Paris', 2, '2025-06-08 16:55:21', '2025-06-08 16:55:21'),
(6, 'Lyon', 2, '2025-06-08 16:55:21', '2025-06-08 16:55:21'),
(7, 'Berlin', 3, '2025-06-08 16:55:21', '2025-06-08 16:55:21'),
(8, 'Madrid', 4, '2025-06-08 16:55:21', '2025-06-08 16:55:21'),
(9, 'Tunis', 5, '2025-06-08 16:55:21', '2025-06-08 16:55:21');

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Index pour la table `admin_log`
--
ALTER TABLE `admin_log`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `cours`
--
ALTER TABLE `cours`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_cours_sujet` (`nom_cours`,`sujet_id`),
  ADD KEY `sujet_id` (`sujet_id`);

--
-- Index pour la table `domaines`
--
ALTER TABLE `domaines`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nom_domaine` (`nom_domaine`);

--
-- Index pour la table `formateurs`
--
ALTER TABLE `formateurs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email_formateur` (`email_formateur`);

--
-- Index pour la table `formations`
--
ALTER TABLE `formations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `cours_id` (`cours_id`),
  ADD KEY `ville_id` (`ville_id`),
  ADD KEY `formateur_id` (`formateur_id`),
  ADD KEY `idx_date_formation` (`date_formation`),
  ADD KEY `idx_statut` (`statut`);

--
-- Index pour la table `inscriptions`
--
ALTER TABLE `inscriptions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `formation_id` (`formation_id`);

--
-- Index pour la table `pays`
--
ALTER TABLE `pays`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nom_pays` (`nom_pays`);

--
-- Index pour la table `sujets`
--
ALTER TABLE `sujets`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_sujet_domaine` (`nom_sujet`,`domaine_id`),
  ADD KEY `domaine_id` (`domaine_id`);

--
-- Index pour la table `villes`
--
ALTER TABLE `villes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_ville_pays` (`nom_ville`,`pays_id`),
  ADD KEY `pays_id` (`pays_id`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `admins`
--
ALTER TABLE `admins`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT pour la table `admin_log`
--
ALTER TABLE `admin_log`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT pour la table `cours`
--
ALTER TABLE `cours`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT pour la table `domaines`
--
ALTER TABLE `domaines`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT pour la table `formateurs`
--
ALTER TABLE `formateurs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT pour la table `formations`
--
ALTER TABLE `formations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT pour la table `inscriptions`
--
ALTER TABLE `inscriptions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT pour la table `pays`
--
ALTER TABLE `pays`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT pour la table `sujets`
--
ALTER TABLE `sujets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT pour la table `villes`
--
ALTER TABLE `villes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `cours`
--
ALTER TABLE `cours`
  ADD CONSTRAINT `cours_ibfk_1` FOREIGN KEY (`sujet_id`) REFERENCES `sujets` (`id`);

--
-- Contraintes pour la table `formations`
--
ALTER TABLE `formations`
  ADD CONSTRAINT `formations_ibfk_1` FOREIGN KEY (`cours_id`) REFERENCES `cours` (`id`),
  ADD CONSTRAINT `formations_ibfk_2` FOREIGN KEY (`ville_id`) REFERENCES `villes` (`id`),
  ADD CONSTRAINT `formations_ibfk_3` FOREIGN KEY (`formateur_id`) REFERENCES `formateurs` (`id`);

--
-- Contraintes pour la table `inscriptions`
--
ALTER TABLE `inscriptions`
  ADD CONSTRAINT `inscriptions_ibfk_1` FOREIGN KEY (`formation_id`) REFERENCES `formations` (`id`);

--
-- Contraintes pour la table `sujets`
--
ALTER TABLE `sujets`
  ADD CONSTRAINT `sujets_ibfk_1` FOREIGN KEY (`domaine_id`) REFERENCES `domaines` (`id`);

--
-- Contraintes pour la table `villes`
--
ALTER TABLE `villes`
  ADD CONSTRAINT `villes_ibfk_1` FOREIGN KEY (`pays_id`) REFERENCES `pays` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
