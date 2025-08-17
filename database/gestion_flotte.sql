-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1
-- Généré le : dim. 17 août 2025 à 21:54
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
-- Base de données : `gestion_flotte`
--

-- --------------------------------------------------------

--
-- Structure de la table `alertes_documents`
--

CREATE TABLE `alertes_documents` (
  `id_alerte` bigint(20) UNSIGNED NOT NULL,
  `id_document` bigint(20) UNSIGNED DEFAULT NULL,
  `type_alerte` enum('2_mois','1_mois','1_semaine') NOT NULL,
  `date_alerte` date NOT NULL,
  `statut` enum('active','vue','traitee') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `alertes_documents`
--

INSERT INTO `alertes_documents` (`id_alerte`, `id_document`, `type_alerte`, `date_alerte`, `statut`, `created_at`, `updated_at`) VALUES
(4, 6, '1_semaine', '2025-02-27', 'active', '2025-02-21 09:12:42', '2025-02-21 09:12:42'),
(14, 13, '2_mois', '2025-08-08', 'active', '2025-02-28 15:45:45', '2025-02-28 15:45:45'),
(15, 13, '1_mois', '2025-09-07', 'active', '2025-02-28 15:45:45', '2025-02-28 15:45:45'),
(16, 13, '1_semaine', '2025-09-30', 'active', '2025-02-28 15:45:45', '2025-02-28 15:45:45'),
(17, 14, '1_semaine', '2025-03-02', 'active', '2025-02-28 16:31:54', '2025-02-28 16:31:54'),
(18, 15, '1_semaine', '2025-03-21', 'active', '2025-02-28 16:44:19', '2025-02-28 16:44:19'),
(19, 17, '1_semaine', '2025-03-30', 'active', '2025-03-10 09:43:32', '2025-03-10 09:43:32');

-- --------------------------------------------------------

--
-- Structure de la table `approvisionnements_carburant`
--

CREATE TABLE `approvisionnements_carburant` (
  `id_approvisionnement` bigint(20) UNSIGNED NOT NULL,
  `id_vehicule` bigint(20) UNSIGNED DEFAULT NULL,
  `id_chauffeur` bigint(20) UNSIGNED DEFAULT NULL,
  `date_approvisionnement` datetime NOT NULL,
  `quantite_litres` decimal(10,2) NOT NULL,
  `prix_unitaire` int(11) NOT NULL,
  `prix_total` int(11) NOT NULL,
  `kilometrage` int(11) NOT NULL,
  `station_service` varchar(100) DEFAULT NULL,
  `type_carburant` enum('essence','diesel','hybride') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `approvisionnements_carburant`
--

INSERT INTO `approvisionnements_carburant` (`id_approvisionnement`, `id_vehicule`, `id_chauffeur`, `date_approvisionnement`, `quantite_litres`, `prix_unitaire`, `prix_total`, `kilometrage`, `station_service`, `type_carburant`) VALUES
(8, 14, NULL, '2025-02-20 10:53:00', 1.40, 715, 1000, 60, '', 'diesel'),
(9, 14, NULL, '2025-02-20 10:57:00', 11.19, 715, 8000, 60, 'Shell', 'diesel'),
(10, 14, NULL, '2025-02-20 15:50:00', 6.99, 715, 5000, 60, 'total', 'diesel'),
(11, 14, NULL, '2025-02-21 17:35:00', 6.99, 715, 5000, 60, 'total', 'diesel'),
(12, 16, NULL, '2025-02-22 12:10:00', 5.71, 875, 5000, 120, '', 'essence'),
(13, 17, NULL, '2025-02-25 10:49:00', 11.43, 875, 10000, 150, 'total', 'essence'),
(14, 9, NULL, '2025-02-26 08:19:00', 9.14, 875, 8000, 250, '', 'essence'),
(15, 9, NULL, '2025-02-26 08:19:00', 11.43, 875, 10000, 250, '', 'essence'),
(16, 14, 6, '2025-03-04 06:50:00', 62.94, 715, 45000, 60, 'Total', 'diesel'),
(17, 14, NULL, '2025-03-04 06:50:00', 4.20, 715, 3000, 60, '', 'diesel');

-- --------------------------------------------------------

--
-- Structure de la table `chauffeurs`
--

CREATE TABLE `chauffeurs` (
  `id_chauffeur` bigint(20) UNSIGNED NOT NULL,
  `id_utilisateur` bigint(20) UNSIGNED DEFAULT NULL,
  `nom` varchar(255) NOT NULL,
  `prenoms` varchar(255) NOT NULL,
  `date_naissance` date NOT NULL,
  `telephone` varchar(20) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `adresse` text NOT NULL,
  `photo_profil` varchar(255) NOT NULL,
  `numero_permis` varchar(50) NOT NULL,
  `type_permis` varchar(20) NOT NULL,
  `date_delivrance_permis` date DEFAULT NULL,
  `date_expiration_permis` date DEFAULT NULL,
  `photo_permis` varchar(255) NOT NULL,
  `date_embauche` date NOT NULL,
  `specialisation` text DEFAULT NULL,
  `statut` enum('disponible','en_course','conge','indisponible') DEFAULT 'disponible',
  `statut_permis` enum('valide','expire','permanant') NOT NULL,
  `vehicule_attribue` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `chauffeurs`
--

INSERT INTO `chauffeurs` (`id_chauffeur`, `id_utilisateur`, `nom`, `prenoms`, `date_naissance`, `telephone`, `email`, `adresse`, `photo_profil`, `numero_permis`, `type_permis`, `date_delivrance_permis`, `date_expiration_permis`, `photo_permis`, `date_embauche`, `specialisation`, `statut`, `statut_permis`, `vehicule_attribue`, `created_at`, `updated_at`) VALUES
(5, NULL, 'KOUASSI', 'JEAN IVES', '2001-05-19', '07 98 54 12 36', 'jean@gmail.com', 'Abobo', '67b73e639d83f_profil-1.jpg', '123456-B', 'A,B,C,D', '2025-02-11', '2025-02-20', '67b73e639daab_permis_conduire.jpg', '2025-01-28', 'Transport de personne VIP', 'en_course', 'expire', 0, '2025-02-20 14:38:27', '2025-02-20 14:39:50'),
(6, NULL, 'koffi', 'SERGE', '1995-02-19', '07 84 52 36 98', 'serg@gmail.com', '8 boulevard Cocody', '67b8bd987afb7_profil-2.jpg', '456789-A', 'A,B', '2023-02-21', '2025-03-01', '67b8bd987b41e_permis_conduire.jpg', '2025-02-06', 'Transport de personne VIP', 'en_course', 'valide', 0, '2025-02-21 17:53:28', '2025-02-21 17:53:28'),
(7, NULL, 'Traore', 'Nouveau', '0000-00-00', '0799791509', 'dym.nouveau@gmail.com', 'Cocody', '67c6a9bf4f848_profil-3.jpg', '12345', 'A,B,C,D,E', '2025-02-12', '2030-02-12', '67c6a98c1de62_permis_conduire.jpg', '0000-00-00', 'Transport de personne VIP', 'en_course', 'valide', 0, '2025-03-04 07:19:40', '2025-03-04 07:20:31');

-- --------------------------------------------------------

--
-- Structure de la table `documents_administratifs`
--

CREATE TABLE `documents_administratifs` (
  `id_document` bigint(20) UNSIGNED NOT NULL,
  `id_vehicule` bigint(20) UNSIGNED DEFAULT NULL,
  `id_chauffeur` bigint(20) UNSIGNED DEFAULT NULL,
  `id_utilisateur` bigint(20) UNSIGNED DEFAULT NULL,
  `type_document` enum('carte_transport','carte_grise','visite_technique','assurance','carte_stationnement') NOT NULL,
  `numero_document` varchar(50) DEFAULT NULL,
  `date_emission` date NOT NULL,
  `date_expiration` date DEFAULT NULL,
  `fournisseur` varchar(100) DEFAULT NULL,
  `prix` decimal(15,2) DEFAULT NULL,
  `frequence_renouvellement` int(11) NOT NULL,
  `fichier_url` varchar(255) DEFAULT NULL,
  `statut` enum('valide','expire','a_renouveler') NOT NULL,
  `note` text DEFAULT NULL,
  `date_upload` timestamp NOT NULL DEFAULT current_timestamp(),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `documents_administratifs`
--

INSERT INTO `documents_administratifs` (`id_document`, `id_vehicule`, `id_chauffeur`, `id_utilisateur`, `type_document`, `numero_document`, `date_emission`, `date_expiration`, `fournisseur`, `prix`, `frequence_renouvellement`, `fichier_url`, `statut`, `note`, `date_upload`, `created_at`, `updated_at`) VALUES
(6, 9, NULL, NULL, 'carte_transport', '123', '2025-01-28', '2025-03-06', 'Dym', 85000.00, 0, 'doc_67b8438a2daaa_20250221.jpg', 'expire', 'Note 1', '2025-02-21 09:12:42', '2025-02-21 09:12:42', '2025-02-28 13:54:55'),
(11, 9, NULL, 29, 'carte_transport', '123', '2025-02-28', NULL, 'Dym', 45000.00, 0, 'doc_67c1c02f2b488_20250228.jpg', 'valide', 'Note 1', '2025-02-28 13:54:55', '2025-02-28 13:54:55', '2025-02-28 13:54:55'),
(13, 9, NULL, 29, 'carte_grise', '1234', '2019-06-19', '2025-10-07', 'Agence de Gestion des Infractions Routières et de la Sécurité Routière (AGEROUTE)', 27500.00, 1, 'doc_67c1da2954b05_20250228.jpg', 'valide', 'Il est important de noter que certaines étapes préalables, comme le contrôle technique du véhicule, doivent être réalisées avant de pouvoir obtenir la carte grise. De plus, des documents spécifiques, tels que le certificat de conformité, la facture d&#39;achat, et une pièce d&#39;identité, sont généralement requis pour compléter la demande.', '2025-02-28 15:45:45', '2025-02-28 15:45:45', '2025-02-28 15:45:45'),
(14, 9, NULL, 29, 'carte_transport', '1234', '2025-01-27', '2025-03-09', 'Agence de Gestion des Infractions Routières et de la Sécurité Routière (AGEROUTE)', 50000.00, 1, 'doc_67c1e4fad8d89_20250228.jpg', 'expire', 'Il est important de noter que certaines étapes préalables, comme le contrôle technique du véhicule, doivent être réalisées avant de pouvoir obtenir la carte grise. De plus, des documents spécifiques, tels que le certificat de conformité, la facture d&#39;achat, et une pièce d&#39;identité, sont généralement requis pour compléter la demande.', '2025-02-28 16:31:54', '2025-02-28 16:31:54', '2025-02-28 16:44:19'),
(15, 9, NULL, 29, 'carte_transport', '1234', '2025-02-28', '2025-03-28', 'Agence de Gestion des Infractions Routières et de la Sécurité Routière (AGEROUTE)', 45700.00, 1, 'doc_67c1e7e32f364_20250228.jpg', 'expire', 'Il est important de noter que certaines étapes préalables, comme le contrôle technique du véhicule, doivent être réalisées avant de pouvoir obtenir la carte grise. De plus, des documents spécifiques, tels que le certificat de conformité, la facture d&#38;#39;achat, et une pièce d&#38;#39;identité, sont généralement requis pour compléter la demande.', '2025-02-28 16:44:19', '2025-02-28 16:44:19', '2025-03-01 07:47:20'),
(16, 9, NULL, 29, 'carte_transport', '1234', '2025-03-01', NULL, 'Agence de Gestion des Infractions Routières et de la Sécurité Routière (AGEROUTE)', 45700.00, 0, 'doc_67c2bb8883cdd_20250301.jpg', 'valide', 'Il est important de noter que certaines étapes préalables, comme le contrôle technique du véhicule, doivent être réalisées avant de pouvoir obtenir la carte grise. De plus, des documents spécifiques, tels que le certificat de conformité, la facture d&#38;#38;#39;achat, et une pièce d&#38;#38;#39;identité, sont généralement requis pour compléter la demande.', '2025-03-01 07:47:20', '2025-03-01 07:47:20', '2025-03-01 07:47:20'),
(17, 8, NULL, 29, 'carte_grise', '123456', '2025-02-24', '2025-04-06', 'CGI', NULL, 1, 'doc_67ceb4444456b_20250310.jpg', 'a_renouveler', 'Note', '2025-03-10 09:43:32', '2025-03-10 09:43:32', '2025-03-10 09:43:32');

-- --------------------------------------------------------

--
-- Structure de la table `itineraires`
--

CREATE TABLE `itineraires` (
  `id_itineraire` bigint(20) UNSIGNED NOT NULL,
  `id_reservation` bigint(20) UNSIGNED DEFAULT NULL,
  `point_depart` varchar(255) NOT NULL,
  `point_arrivee` varchar(255) NOT NULL,
  `distance_prevue` decimal(10,2) DEFAULT NULL,
  `temps_trajet_prevu` int(11) DEFAULT NULL,
  `points_intermediaires` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `itineraires`
--

INSERT INTO `itineraires` (`id_itineraire`, `id_reservation`, `point_depart`, `point_arrivee`, `distance_prevue`, `temps_trajet_prevu`, `points_intermediaires`) VALUES
(22, 22, 'DYM Manufacture, Cocody', 'Plateau, Abidjan', 14.90, 19, NULL);

-- --------------------------------------------------------

--
-- Structure de la table `journal_activites`
--

CREATE TABLE `journal_activites` (
  `id_activite` bigint(20) UNSIGNED NOT NULL,
  `id_utilisateur` bigint(20) UNSIGNED NOT NULL,
  `type_activite` varchar(50) NOT NULL,
  `description` text NOT NULL,
  `date_activite` datetime DEFAULT current_timestamp(),
  `ip_address` varchar(45) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `journal_activites`
--

INSERT INTO `journal_activites` (`id_activite`, `id_utilisateur`, `type_activite`, `description`, `date_activite`, `ip_address`) VALUES
(110, 29, 'inscription', 'Nouvel utilisateur inscrit', '2025-02-24 14:49:01', '127.0.0.1'),
(113, 29, 'connexion', 'Connexion réussie', '2025-02-24 14:54:31', '127.0.0.1'),
(114, 29, 'deconnexion', 'Déconnexion utilisateur', '2025-02-24 15:16:12', '127.0.0.1'),
(118, 29, 'connexion', 'Connexion réussie', '2025-02-24 15:39:07', '127.0.0.1'),
(119, 29, 'suppression', 'Suppression de l\'utilisateur #17', '2025-02-24 15:39:28', '127.0.0.1'),
(120, 29, 'suppression', 'Suppression de l\'utilisateur #16', '2025-02-24 15:39:33', '127.0.0.1'),
(121, 29, 'suppression', 'Suppression de l\'utilisateur #14', '2025-02-24 15:39:38', '127.0.0.1'),
(122, 29, 'suppression', 'Suppression de l\'utilisateur #13', '2025-02-24 15:39:46', '127.0.0.1'),
(123, 29, 'suppression', 'Suppression de l\'utilisateur #12', '2025-02-24 15:39:51', '127.0.0.1'),
(124, 29, 'suppression', 'Suppression de l\'utilisateur #31', '2025-02-24 15:39:55', '127.0.0.1'),
(125, 29, 'suppression', 'Suppression de l\'utilisateur #30', '2025-02-24 15:39:59', '127.0.0.1'),
(126, 29, 'suppression', 'Suppression de l\'utilisateur #11', '2025-02-24 15:40:02', '127.0.0.1'),
(127, 29, 'suppression', 'Suppression de l\'utilisateur #10', '2025-02-24 15:40:06', '127.0.0.1'),
(128, 29, 'suppression', 'Suppression de l\'utilisateur #9', '2025-02-24 15:40:09', '127.0.0.1'),
(129, 29, 'suppression', 'Suppression de l\'utilisateur #8', '2025-02-24 15:40:13', '127.0.0.1'),
(130, 29, 'création', 'Création de l\'utilisateur Konan Cédric', '2025-02-24 15:41:23', '127.0.0.1'),
(131, 29, 'deconnexion', 'Déconnexion utilisateur', '2025-02-24 15:41:33', '127.0.0.1'),
(134, 29, 'connexion', 'Connexion réussie', '2025-02-24 16:53:41', '127.0.0.1'),
(135, 29, 'connexion', 'Connexion réussie', '2025-02-24 16:54:07', '127.0.0.1'),
(136, 29, 'modification', 'Modification de l\'utilisateur #32', '2025-02-24 16:59:08', '127.0.0.1'),
(137, 29, 'deconnexion', 'Déconnexion utilisateur', '2025-02-24 16:59:24', '127.0.0.1'),
(140, 29, 'connexion', 'Connexion réussie', '2025-02-24 17:20:25', '127.0.0.1'),
(141, 29, 'modification', 'Modification de l\'utilisateur #32', '2025-02-24 17:20:39', '127.0.0.1'),
(142, 29, 'deconnexion', 'Déconnexion utilisateur', '2025-02-24 17:20:51', '127.0.0.1'),
(145, 29, 'connexion', 'Connexion réussie', '2025-02-24 17:24:51', '127.0.0.1'),
(146, 29, 'modification', 'Modification de l\'utilisateur #32', '2025-02-24 17:25:08', '127.0.0.1'),
(147, 29, 'deconnexion', 'Déconnexion utilisateur', '2025-02-24 17:25:16', '127.0.0.1'),
(150, 29, 'connexion', 'Connexion réussie', '2025-02-24 17:46:19', '127.0.0.1'),
(153, 29, 'connexion', 'Connexion réussie', '2025-02-25 06:44:56', '127.0.0.1'),
(154, 29, 'deconnexion', 'Déconnexion utilisateur', '2025-02-25 06:45:25', '127.0.0.1'),
(157, 29, 'connexion', 'Connexion réussie', '2025-02-25 07:47:49', '127.0.0.1'),
(158, 29, 'modification', 'Modification de l\'utilisateur #32', '2025-02-25 07:48:11', '127.0.0.1'),
(159, 29, 'deconnexion', 'Déconnexion utilisateur', '2025-02-25 07:48:40', '127.0.0.1'),
(162, 29, 'connexion', 'Connexion réussie', '2025-02-25 07:56:36', '127.0.0.1'),
(163, 29, 'modification', 'Modification de l\'utilisateur #32', '2025-02-25 07:56:50', '127.0.0.1'),
(164, 29, 'deconnexion', 'Déconnexion utilisateur', '2025-02-25 07:56:55', '127.0.0.1'),
(167, 29, 'connexion', 'Connexion réussie', '2025-02-25 08:00:03', '127.0.0.1'),
(168, 29, 'modification', 'Modification de l\'utilisateur #32', '2025-02-25 08:14:08', '127.0.0.1'),
(169, 29, 'deconnexion', 'Déconnexion utilisateur', '2025-02-25 08:24:17', '127.0.0.1'),
(170, 29, 'connexion', 'Connexion réussie', '2025-02-25 08:24:36', '127.0.0.1'),
(171, 29, 'modification', 'Modification de l\'utilisateur #32', '2025-02-25 08:25:18', '127.0.0.1'),
(172, 29, 'deconnexion', 'Déconnexion utilisateur', '2025-02-25 08:25:35', '127.0.0.1'),
(175, 29, 'connexion', 'Connexion réussie', '2025-02-25 08:57:13', '127.0.0.1'),
(176, 29, 'modification', 'Modification de l\'utilisateur #32', '2025-02-25 08:57:31', '127.0.0.1'),
(177, 29, 'deconnexion', 'Déconnexion utilisateur', '2025-02-25 08:57:36', '127.0.0.1'),
(180, 29, 'connexion', 'Connexion réussie', '2025-02-25 09:23:59', '127.0.0.1'),
(181, 29, 'deconnexion', 'Déconnexion utilisateur', '2025-02-25 09:36:17', '127.0.0.1'),
(182, 29, 'connexion', 'Connexion réussie', '2025-02-25 09:36:50', '127.0.0.1'),
(183, 29, 'modification', 'Modification de l\'utilisateur #32', '2025-02-25 09:37:07', '127.0.0.1'),
(184, 29, 'deconnexion', 'Déconnexion utilisateur', '2025-02-25 09:37:12', '127.0.0.1'),
(187, 29, 'connexion', 'Connexion réussie', '2025-02-25 10:36:23', '127.0.0.1'),
(188, 29, 'ajout_vehicule', 'Ajout d\'un nouveau véhicule : Ford Ford602 (Immatriculation: FORD602CI001)', '2025-02-25 10:47:08', '127.0.0.1'),
(189, 29, 'maintenance', 'Ajout d\'une maintenance preventive pour le véhicule ID:17', '2025-02-25 10:47:44', '127.0.0.1'),
(190, 29, 'maintenance', 'Fin de maintenance ID:22 pour le véhicule ID:17 - Coût final: 8000 FCFA', '2025-02-25 10:48:01', '127.0.0.1'),
(191, 29, 'approvisionnement_carburant', 'Approvisionnement de 11.43 litres pour le véhicule Ford Ford602 (FORD602CI001)', '2025-02-25 10:49:16', '127.0.0.1'),
(192, 29, 'delete_document', 'Suppression du document #9 - Carte grise pour le véhicule Ford Ford602 (FORD602CI001)', '2025-02-25 11:04:54', '127.0.0.1'),
(193, 29, 'ajout_document', 'Ajout d\'un document : carte_transport pour le véhicule #17', '2025-02-25 11:05:47', '127.0.0.1'),
(194, 29, 'creation_reservation', 'Nouvelle réservation créée #1 pour Bahi kipre', '2025-02-25 12:06:11', NULL),
(195, 29, 'creation_reservation', 'Nouvelle réservation créée #2 pour Bahi kipre', '2025-02-25 14:35:53', NULL),
(196, 29, 'reservation_validation', 'Validation de la réservation #1', '2025-02-25 14:37:07', '127.0.0.1'),
(197, 29, 'reservation_validation', 'Validation de la réservation #2', '2025-02-25 15:03:17', '127.0.0.1'),
(198, 29, 'creation_reservation', 'Nouvelle réservation créée #3 pour Bahi kipre', '2025-02-25 15:14:19', NULL),
(199, 29, 'reservation_validation', 'Validation de la réservation #3', '2025-02-25 15:16:08', '127.0.0.1'),
(200, 29, 'annulation_course', 'Annulation de la réservation #1 - Motif: autre - Détails: Véhicule en maintenance', '2025-02-25 15:20:29', '127.0.0.1'),
(201, 29, 'creation_reservation', 'Nouvelle réservation créée #4 pour Bahi kipre', '2025-02-25 16:53:18', NULL),
(202, 29, 'debut_course', 'Début de la course #3 - Kilométrage de départ: 75 km', '2025-02-25 16:59:23', '127.0.0.1'),
(203, 29, 'debut_course', 'Début de la course #2 - Kilométrage de départ: 85 km', '2025-02-25 17:10:06', '127.0.0.1'),
(204, 29, 'fin_course', 'Fin de la course #3 - Kilométrage de départ: 75 km, Kilométrage de retour: 85 km, Distance parcourue: 10 km', '2025-02-25 17:10:49', '127.0.0.1'),
(205, 29, 'deconnexion', 'Déconnexion utilisateur', '2025-02-25 17:22:42', '127.0.0.1'),
(206, 29, 'connexion', 'Connexion réussie', '2025-02-25 17:23:58', '127.0.0.1'),
(207, 29, 'modification', 'Modification de l\'utilisateur #32', '2025-02-25 17:24:12', '127.0.0.1'),
(208, 29, 'deconnexion', 'Déconnexion utilisateur', '2025-02-25 17:24:17', '127.0.0.1'),
(212, 29, 'connexion', 'Connexion réussie', '2025-02-25 17:40:00', '127.0.0.1'),
(213, 29, 'modification', 'Modification de l\'utilisateur #32', '2025-02-25 17:40:11', '127.0.0.1'),
(214, 29, 'deconnexion', 'Déconnexion utilisateur', '2025-02-25 17:40:16', '127.0.0.1'),
(218, 29, 'connexion', 'Connexion réussie', '2025-02-25 17:46:16', '127.0.0.1'),
(219, 29, 'modification', 'Modification de l\'utilisateur #32', '2025-02-25 17:46:35', '127.0.0.1'),
(220, 29, 'deconnexion', 'Déconnexion utilisateur', '2025-02-25 17:46:40', '127.0.0.1'),
(224, 29, 'connexion', 'Connexion réussie', '2025-02-25 17:48:54', '127.0.0.1'),
(225, 29, 'modification', 'Modification de l\'utilisateur #32', '2025-02-25 17:49:04', '127.0.0.1'),
(226, 29, 'deconnexion', 'Déconnexion utilisateur', '2025-02-25 17:49:07', '127.0.0.1'),
(229, 29, 'connexion', 'Connexion réussie', '2025-02-25 17:51:08', '127.0.0.1'),
(230, 29, 'création', 'Création de l\'utilisateur koffi koffi', '2025-02-25 17:52:50', '127.0.0.1'),
(231, 29, 'deconnexion', 'Déconnexion utilisateur', '2025-02-25 17:53:03', '127.0.0.1'),
(242, 29, 'connexion', 'Connexion réussie', '2025-02-25 18:15:41', '127.0.0.1'),
(243, 29, 'modification', 'Modification de l\'utilisateur #33', '2025-02-25 18:15:59', '127.0.0.1'),
(244, 29, 'deconnexion', 'Déconnexion utilisateur', '2025-02-25 18:16:02', '127.0.0.1'),
(269, 29, 'connexion', 'Connexion réussie', '2025-02-25 19:09:07', '127.0.0.1'),
(270, 29, 'connexion', 'Connexion réussie', '2025-02-26 07:16:18', '127.0.0.1'),
(271, 29, 'deconnexion', 'Déconnexion utilisateur', '2025-02-26 08:08:05', '127.0.0.1'),
(285, 29, 'connexion', 'Connexion réussie', '2025-02-26 08:16:06', '127.0.0.1'),
(286, 29, 'approvisionnement_carburant', 'Approvisionnement de 9.14 litres pour le véhicule Peugeot 308 (2345CD01)', '2025-02-26 08:19:39', '127.0.0.1'),
(287, 29, 'approvisionnement_carburant', 'Approvisionnement de 11.43 litres pour le véhicule Peugeot 308 (2345CD01)', '2025-02-26 08:19:53', '127.0.0.1'),
(288, 29, 'creation_reservation', 'Nouvelle réservation créée #11 pour Bahi kipre', '2025-02-26 09:19:38', NULL),
(289, 29, 'reservation_validation', 'Validation de la réservation #11', '2025-02-26 09:21:02', '127.0.0.1'),
(290, 29, 'creation_reservation', 'Nouvelle réservation créée #12 pour Bahi kipre', '2025-02-26 09:33:57', NULL),
(291, 29, 'deconnexion', 'Déconnexion utilisateur', '2025-02-26 15:34:11', '127.0.0.1'),
(294, 29, 'connexion', 'Connexion réussie', '2025-02-26 16:07:31', '127.0.0.1'),
(295, 29, 'ajout_vehicule', 'Ajout d\'un nouveau véhicule : Nissan NissanCI092 (Immatriculation: NIS741-KL02)', '2025-02-26 16:09:04', '127.0.0.1'),
(296, 29, 'deconnexion', 'Déconnexion utilisateur', '2025-02-26 16:10:30', '127.0.0.1'),
(300, 29, 'connexion', 'Connexion réussie', '2025-02-27 07:10:24', '127.0.0.1'),
(301, 29, 'deconnexion', 'Déconnexion utilisateur', '2025-02-27 08:19:31', '127.0.0.1'),
(302, 29, 'connexion', 'Connexion réussie', '2025-02-27 09:37:09', '127.0.0.1'),
(303, 29, 'creation_reservation', 'Nouvelle réservation créée #14 pour Bahi kipre', '2025-02-27 09:49:32', NULL),
(304, 29, 'deconnexion', 'Déconnexion utilisateur', '2025-02-27 09:50:23', '127.0.0.1'),
(308, 29, 'connexion', 'Connexion réussie', '2025-02-27 10:19:14', '127.0.0.1'),
(309, 29, 'deconnexion', 'Déconnexion utilisateur', '2025-02-27 10:32:20', '127.0.0.1'),
(312, 29, 'connexion', 'Connexion réussie', '2025-02-27 11:02:43', '127.0.0.1'),
(313, 29, 'creation_reservation', 'Nouvelle réservation créée #15 pour Himra', '2025-02-27 11:03:53', NULL),
(314, 29, 'reservation_validation', 'Validation de la réservation #15', '2025-02-27 11:37:20', '127.0.0.1'),
(315, 29, 'fin_course', 'Fin de la course #11 - Kilométrage de départ: 150 km, Kilométrage de retour: 200 km, Distance parcourue: 50 km', '2025-02-27 11:37:54', '127.0.0.1'),
(316, 29, 'debut_course', 'Début de la course #12 - Kilométrage de départ: 45 km', '2025-02-27 11:39:17', '127.0.0.1'),
(317, 29, 'debut_course', 'Début de la course #15 - Kilométrage de départ: 120 km', '2025-02-27 11:39:36', '127.0.0.1'),
(318, 29, 'fin_course', 'Fin de la course #15 - Kilométrage de départ: 120 km, Kilométrage de retour: 200 km, Distance parcourue: 80 km', '2025-02-27 11:39:50', '127.0.0.1'),
(319, 29, 'creation_reservation', 'Nouvelle réservation créée #16 pour Bahi kipre', '2025-02-27 11:54:15', NULL),
(320, 29, 'reservation_validation', 'Validation de la réservation #16', '2025-02-27 11:54:49', '127.0.0.1'),
(321, 29, 'debut_course', 'Début de la course #16 - Kilométrage de départ: 250 km - Matériel: fer, outil de travail', '2025-02-27 13:39:15', '127.0.0.1'),
(322, 29, 'fin_course', 'Fin de la course #16 - Kilométrage de départ: 250 km, Kilométrage de retour: 300 km, Distance parcourue: 50 km', '2025-02-27 13:40:19', '127.0.0.1'),
(323, 29, 'deconnexion', 'Déconnexion utilisateur', '2025-02-27 14:46:52', '127.0.0.1'),
(340, 29, 'connexion', 'Connexion réussie', '2025-02-28 07:05:00', '127.0.0.1'),
(341, 29, 'deconnexion', 'Déconnexion utilisateur', '2025-02-28 07:07:40', '127.0.0.1'),
(342, 29, 'demande_reset', 'Demande de réinitialisation de mot de passe', '2025-02-28 08:10:49', '127.0.0.1'),
(343, 29, 'connexion', 'Connexion réussie', '2025-02-28 08:12:55', '127.0.0.1'),
(344, 29, 'deconnexion', 'Déconnexion utilisateur', '2025-02-28 08:13:11', '127.0.0.1'),
(349, 37, 'inscription', 'Nouvel utilisateur inscrit', '2025-02-28 10:00:44', '127.0.0.1'),
(350, 37, 'connexion', 'Connexion réussie', '2025-02-28 10:01:21', '127.0.0.1'),
(351, 37, 'deconnexion', 'Déconnexion utilisateur', '2025-02-28 10:01:36', '127.0.0.1'),
(352, 37, 'connexion', 'Connexion réussie', '2025-02-28 10:03:06', '127.0.0.1'),
(353, 37, 'deconnexion', 'Déconnexion utilisateur', '2025-02-28 10:03:24', '127.0.0.1'),
(354, 29, 'connexion', 'Connexion réussie', '2025-02-28 10:04:14', '127.0.0.1'),
(355, 29, 'deconnexion', 'Déconnexion utilisateur', '2025-02-28 10:05:31', '127.0.0.1'),
(356, 38, 'inscription', 'Nouvel utilisateur inscrit', '2025-02-28 10:12:11', '127.0.0.1'),
(357, 38, 'connexion', 'Connexion réussie', '2025-02-28 10:13:11', '127.0.0.1'),
(358, 38, 'deconnexion', 'Déconnexion utilisateur', '2025-02-28 10:13:35', '127.0.0.1'),
(359, 29, 'connexion', 'Connexion réussie', '2025-02-28 10:16:13', '127.0.0.1'),
(360, 29, 'deconnexion', 'Déconnexion utilisateur', '2025-02-28 10:24:21', '127.0.0.1'),
(361, 29, 'connexion', 'Connexion réussie', '2025-02-28 10:27:59', '127.0.0.1'),
(362, 29, 'deconnexion', 'Déconnexion utilisateur', '2025-02-28 10:28:19', '127.0.0.1'),
(363, 29, 'connexion', 'Connexion réussie', '2025-02-28 10:31:45', '127.0.0.1'),
(364, 29, 'deconnexion', 'Déconnexion utilisateur', '2025-02-28 11:41:54', '127.0.0.1'),
(365, 37, 'connexion', 'Connexion réussie', '2025-02-28 11:42:22', '127.0.0.1'),
(366, 37, 'deconnexion', 'Déconnexion utilisateur', '2025-02-28 11:49:50', '127.0.0.1'),
(367, 29, 'connexion', 'Connexion réussie', '2025-02-28 11:50:04', '127.0.0.1'),
(368, 29, 'view_renew_document', 'Consultation pour renouvellement du document #6 - Carte transport', '2025-02-28 12:12:09', '127.0.0.1'),
(369, 29, 'view_renew_document', 'Consultation pour renouvellement du document #6 - Carte transport', '2025-02-28 12:12:16', '127.0.0.1'),
(370, 29, 'view_renew_document', 'Consultation pour renouvellement du document #6 - Carte transport', '2025-02-28 12:12:19', '127.0.0.1'),
(371, 29, 'view_renew_document', 'Consultation pour renouvellement du document #6 - Carte transport', '2025-02-28 12:12:20', '127.0.0.1'),
(372, 29, 'view_renew_document', 'Consultation pour renouvellement du document #6 - Carte transport', '2025-02-28 12:12:22', '127.0.0.1'),
(373, 29, 'view_renew_document', 'Consultation pour renouvellement du document #6 - Carte transport', '2025-02-28 12:12:23', '127.0.0.1'),
(374, 29, 'view_renew_document', 'Consultation pour renouvellement du document #6 - Carte transport', '2025-02-28 12:12:24', '127.0.0.1'),
(375, 29, 'view_renew_document', 'Consultation pour renouvellement du document #10 - Carte transport', '2025-02-28 12:12:45', '127.0.0.1'),
(376, 29, 'view_renew_document', 'Consultation pour renouvellement du document #10 - Carte transport', '2025-02-28 12:12:50', '127.0.0.1'),
(377, 29, 'view_renew_document', 'Consultation pour renouvellement du document #6 - Carte transport', '2025-02-28 13:11:10', '127.0.0.1'),
(378, 29, 'view_renew_document', 'Consultation pour renouvellement du document #6 - Carte transport', '2025-02-28 13:12:50', '127.0.0.1'),
(379, 29, 'view_renew_document', 'Consultation pour renouvellement du document #6 - Carte transport', '2025-02-28 13:14:02', '127.0.0.1'),
(380, 29, 'view_renew_document', 'Consultation pour renouvellement du document #6 - Carte transport', '2025-02-28 13:18:37', '127.0.0.1'),
(381, 29, 'view_renew_document', 'Consultation pour renouvellement du document #6 - Carte transport', '2025-02-28 13:18:38', '127.0.0.1'),
(382, 29, 'view_renew_document', 'Consultation pour renouvellement du document #6 - Carte transport', '2025-02-28 13:19:46', '127.0.0.1'),
(383, 29, 'view_renew_document', 'Consultation pour renouvellement du document #6 - Carte transport', '2025-02-28 13:20:54', '127.0.0.1'),
(384, 29, 'view_renew_document', 'Consultation pour renouvellement du document #6 - Carte transport', '2025-02-28 13:21:55', '127.0.0.1'),
(385, 29, 'view_renew_document', 'Consultation pour renouvellement du document #6 - Carte transport', '2025-02-28 13:38:50', '127.0.0.1'),
(386, 29, 'view_renew_document', 'Consultation pour renouvellement du document #6 - Carte transport', '2025-02-28 13:39:07', '127.0.0.1'),
(387, 29, 'view_renew_document', 'Consultation pour renouvellement du document #6 - Carte transport', '2025-02-28 13:40:34', '127.0.0.1'),
(388, 29, 'view_renew_document', 'Consultation pour renouvellement du document #6 - Carte transport', '2025-02-28 13:40:53', '127.0.0.1'),
(389, 29, 'view_renew_document', 'Consultation pour renouvellement du document #6 - Carte transport', '2025-02-28 13:51:12', '127.0.0.1'),
(390, 29, 'view_renew_document', 'Consultation pour renouvellement du document #6 - Carte transport', '2025-02-28 13:52:21', '127.0.0.1'),
(391, 29, 'view_renew_document', 'Consultation pour renouvellement du document #6 - Carte transport', '2025-02-28 13:53:15', '127.0.0.1'),
(392, 29, 'view_renew_document', 'Consultation pour renouvellement du document #6 - Carte transport', '2025-02-28 13:54:44', '127.0.0.1'),
(393, 29, 'renew_document', 'Renouvellement du document #6 - Carte transport pour le véhicule Peugeot 308 (2345CD01)', '2025-02-28 13:54:55', '127.0.0.1'),
(394, 29, 'delete_document', 'Suppression du document #7 - Visite technique pour le véhicule Renault Clio IV (1234AB01)', '2025-02-28 13:56:03', '127.0.0.1'),
(395, 29, 'view_renew_document', 'Consultation pour renouvellement du document #10 - Carte transport', '2025-02-28 14:22:08', '127.0.0.1'),
(396, 29, 'view_document', 'Consultation du document #11 - Carte transport', '2025-02-28 14:32:31', '127.0.0.1'),
(397, 29, 'view_document', 'Consultation du document #11 - Carte transport', '2025-02-28 14:33:33', '127.0.0.1'),
(398, 29, 'view_renew_document', 'Consultation pour renouvellement du document #11 - Carte transport', '2025-02-28 14:34:47', '127.0.0.1'),
(399, 29, 'view_renew_document', 'Consultation pour renouvellement du document #11 - Carte transport', '2025-02-28 14:34:47', '127.0.0.1'),
(400, 29, 'view_document', 'Consultation du document #10 - Carte transport', '2025-02-28 14:34:55', '127.0.0.1'),
(401, 29, 'view_document', 'Consultation du document #11 - Carte transport', '2025-02-28 14:35:27', '127.0.0.1'),
(402, 29, 'view_document', 'Consultation du document #11 - Carte transport', '2025-02-28 14:39:13', '127.0.0.1'),
(403, 29, 'view_document', 'Consultation du document #10 - Carte transport', '2025-02-28 14:39:25', '127.0.0.1'),
(404, 29, 'view_document', 'Consultation du document #11 - Carte transport', '2025-02-28 14:39:29', '127.0.0.1'),
(405, 29, 'view_document', 'Consultation du document #8 - Carte transport', '2025-02-28 14:39:33', '127.0.0.1'),
(406, 29, 'view_document', 'Consultation du document #10 - Carte transport', '2025-02-28 14:39:40', '127.0.0.1'),
(407, 29, 'view_document', 'Consultation du document #10 - Carte transport', '2025-02-28 14:40:58', '127.0.0.1'),
(408, 29, 'view_document', 'Consultation du document #11 - Carte transport', '2025-02-28 14:41:55', '127.0.0.1'),
(409, 29, 'view_document', 'Consultation du document #10 - Carte transport', '2025-02-28 14:44:30', '127.0.0.1'),
(410, 29, 'view_document', 'Consultation du document #11 - Carte transport', '2025-02-28 14:46:05', '127.0.0.1'),
(411, 29, 'view_document', 'Consultation du document #11 - Carte transport', '2025-02-28 14:46:17', '127.0.0.1'),
(412, 29, 'view_document', 'Consultation du document #10 - Carte transport', '2025-02-28 14:46:52', '127.0.0.1'),
(413, 29, 'view_renew_document', 'Consultation pour renouvellement du document #10 - Carte transport', '2025-02-28 14:47:20', '127.0.0.1'),
(414, 29, 'view_renew_document', 'Consultation pour renouvellement du document #10 - Carte transport', '2025-02-28 14:50:05', '127.0.0.1'),
(415, 29, 'view_renew_document', 'Consultation pour renouvellement du document #10 - Carte transport', '2025-02-28 14:52:05', '127.0.0.1'),
(416, 29, 'view_renew_document', 'Consultation pour renouvellement du document #10 - Carte transport', '2025-02-28 14:52:11', '127.0.0.1'),
(417, 29, 'ajout_document', 'Ajout d\'un document : carte_transport pour le véhicule #8', '2025-02-28 15:07:46', '127.0.0.1'),
(418, 29, 'view_document', 'Consultation du document #10 - Carte transport', '2025-02-28 15:25:29', '127.0.0.1'),
(419, 29, 'ajout_document', 'Ajout d\'un document : carte_grise pour le véhicule #9', '2025-02-28 15:45:45', '127.0.0.1'),
(420, 29, 'view_renew_document', 'Consultation pour renouvellement du document #13 - Carte grise', '2025-02-28 15:46:01', '127.0.0.1'),
(421, 29, 'view_renew_document', 'Consultation pour renouvellement du document #13 - Carte grise', '2025-02-28 15:46:06', '127.0.0.1'),
(422, 29, 'view_renew_document', 'Consultation pour renouvellement du document #13 - Carte grise', '2025-02-28 15:46:12', '127.0.0.1'),
(423, 29, 'view_renew_document', 'Consultation pour renouvellement du document #13 - Carte grise', '2025-02-28 15:47:04', '127.0.0.1'),
(424, 29, 'view_renew_document', 'Consultation pour renouvellement du document #13 - Carte grise', '2025-02-28 15:47:08', '127.0.0.1'),
(425, 29, 'view_renew_document', 'Consultation pour renouvellement du document #13 - Carte grise', '2025-02-28 15:47:09', '127.0.0.1'),
(426, 29, 'view_renew_document', 'Consultation pour renouvellement du document #13 - Carte grise', '2025-02-28 15:47:10', '127.0.0.1'),
(427, 29, 'view_document', 'Consultation du document #13 - Carte grise', '2025-02-28 15:47:20', '127.0.0.1'),
(428, 29, 'view_document', 'Consultation du document #6 - Carte transport', '2025-02-28 15:48:12', '127.0.0.1'),
(429, 29, 'view_document', 'Consultation du document #6 - Carte transport', '2025-02-28 15:48:19', '127.0.0.1'),
(430, 29, 'ajout_document', 'Ajout d\'un document : carte_transport pour le véhicule #9', '2025-02-28 16:31:54', '127.0.0.1'),
(431, 29, 'view_renew_document', 'Consultation pour renouvellement du document #13 - Carte grise', '2025-02-28 16:32:40', '127.0.0.1'),
(432, 29, 'view_renew_document', 'Consultation pour renouvellement du document #14 - Carte transport', '2025-02-28 16:32:44', '127.0.0.1'),
(433, 29, 'view_renew_document', 'Consultation pour renouvellement du document #13 - Carte grise', '2025-02-28 16:33:24', '127.0.0.1'),
(434, 29, 'view_renew_document', 'Consultation pour renouvellement du document #13 - Carte grise', '2025-02-28 16:35:15', '127.0.0.1'),
(435, 29, 'view_document', 'Consultation du document #14 - Carte transport', '2025-02-28 16:38:34', '127.0.0.1'),
(436, 29, 'view_renew_document', 'Consultation pour renouvellement du document #14 - Carte transport', '2025-02-28 16:43:32', '127.0.0.1'),
(437, 29, 'renew_document', 'Renouvellement du document #14 - Carte transport pour le véhicule Peugeot 308 (2345CD01)', '2025-02-28 16:44:19', '127.0.0.1'),
(438, 29, 'view_renew_document', 'Consultation pour renouvellement du document #15 - Carte transport', '2025-02-28 16:44:24', '127.0.0.1'),
(439, 29, 'delete_document', 'Suppression du document #8 - Carte transport pour le véhicule Kia Kia01 (12547)', '2025-02-28 16:44:59', '127.0.0.1'),
(440, 29, 'view_document', 'Consultation du document #15 - Carte transport', '2025-02-28 16:45:11', '127.0.0.1'),
(441, 29, 'view_document', 'Consultation du document #10 - Carte transport', '2025-02-28 16:46:15', '127.0.0.1'),
(442, 29, 'view_document', 'Consultation du document #15 - Carte transport', '2025-02-28 16:47:35', '127.0.0.1'),
(443, 29, 'view_document', 'Consultation du document #10 - Carte transport', '2025-02-28 16:48:27', '127.0.0.1'),
(444, 29, 'view_document', 'Consultation du document #11 - Carte transport', '2025-02-28 17:04:38', '127.0.0.1'),
(445, 29, 'view_document', 'Consultation du document #15 - Carte transport', '2025-02-28 17:04:47', '127.0.0.1'),
(446, 29, 'view_document', 'Consultation du document #11 - Carte transport', '2025-02-28 17:04:53', '127.0.0.1'),
(447, 29, 'view_document', 'Consultation du document #13 - Carte grise', '2025-02-28 17:05:02', '127.0.0.1'),
(448, 29, 'view_document', 'Consultation du document #12 - Carte transport', '2025-02-28 17:05:05', '127.0.0.1'),
(449, 29, 'view_document', 'Consultation du document #15 - Carte transport', '2025-02-28 17:06:41', '127.0.0.1'),
(450, 29, 'view_renew_document', 'Consultation pour renouvellement du document #13 - Carte grise', '2025-02-28 17:07:25', '127.0.0.1'),
(451, 29, 'view_document', 'Consultation du document #14 - Carte transport', '2025-02-28 17:08:11', '127.0.0.1'),
(452, 29, 'view_document', 'Consultation du document #6 - Carte transport', '2025-02-28 17:08:24', '127.0.0.1'),
(453, 29, 'view_document', 'Consultation du document #13 - Carte grise', '2025-02-28 17:09:24', '127.0.0.1'),
(454, 29, 'connexion', 'Connexion réussie', '2025-03-01 07:45:48', '127.0.0.1'),
(455, 29, 'view_renew_document', 'Consultation pour renouvellement du document #15 - Carte transport', '2025-03-01 07:45:58', '127.0.0.1'),
(456, 29, 'view_document', 'Consultation du document #15 - Carte transport', '2025-03-01 07:46:14', '127.0.0.1'),
(457, 29, 'view_document', 'Consultation du document #12 - Carte transport', '2025-03-01 07:46:26', '127.0.0.1'),
(458, 29, 'view_renew_document', 'Consultation pour renouvellement du document #15 - Carte transport', '2025-03-01 07:46:48', '127.0.0.1'),
(459, 29, 'renew_document', 'Renouvellement du document #15 - Carte transport pour le véhicule Peugeot 308 (2345CD01)', '2025-03-01 07:47:20', '127.0.0.1'),
(460, 29, 'view_document', 'Consultation du document #10 - Carte transport', '2025-03-01 07:47:26', '127.0.0.1'),
(461, 29, 'delete_document', 'Suppression du document #10 - Carte transport pour le véhicule Ford Ford602 (FORD602CI001)', '2025-03-01 07:47:34', '127.0.0.1'),
(462, 29, 'view_document', 'Consultation du document #15 - Carte transport', '2025-03-01 07:48:00', '127.0.0.1'),
(463, 29, 'delete_document', 'Suppression du document #12 - Carte transport pour le véhicule Renault Clio IV (1234AB01)', '2025-03-01 07:48:30', '127.0.0.1'),
(464, 29, 'view_document', 'Consultation du document #15 - Carte transport', '2025-03-01 07:48:55', '127.0.0.1'),
(465, 29, 'view_document', 'Consultation du document #14 - Carte transport', '2025-03-01 07:49:02', '127.0.0.1'),
(466, 29, 'view_document', 'Consultation du document #11 - Carte transport', '2025-03-01 07:49:21', '127.0.0.1'),
(467, 29, 'deconnexion', 'Déconnexion utilisateur', '2025-03-01 09:34:11', '127.0.0.1'),
(468, 29, 'connexion', 'Connexion réussie', '2025-03-03 13:41:47', '127.0.0.1'),
(469, 29, 'creation_reservation', 'Nouvelle réservation créée #18 pour Malick', '2025-03-03 13:43:20', NULL),
(470, 29, 'creation_reservation', 'Nouvelle réservation créée #19 pour Malick', '2025-03-03 14:16:42', NULL),
(471, 29, 'reservation_validation', 'Validation de la réservation #19', '2025-03-03 14:49:45', '127.0.0.1'),
(472, 29, 'deconnexion', 'Déconnexion utilisateur', '2025-03-03 15:43:05', '127.0.0.1'),
(473, 29, 'connexion', 'Connexion réussie', '2025-03-03 16:03:16', '127.0.0.1'),
(474, 29, 'deconnexion', 'Déconnexion utilisateur', '2025-03-03 16:33:58', '127.0.0.1'),
(475, 29, 'connexion', 'Connexion réussie', '2025-03-03 16:42:28', '127.0.0.1'),
(476, 29, 'connexion', 'Connexion réussie', '2025-03-04 06:50:05', '127.0.0.1'),
(477, 29, 'approvisionnement_carburant', 'Approvisionnement de 62.94 litres pour le véhicule BMW Série 1 (7890MN01)', '2025-03-04 06:50:39', '127.0.0.1'),
(478, 29, 'approvisionnement_carburant', 'Approvisionnement de 4.2 litres pour le véhicule BMW Série 1 (7890MN01)', '2025-03-04 06:51:17', '127.0.0.1'),
(479, 29, 'maintenance', 'Fin de maintenance ID:21 pour le véhicule ID:14 - Coût final:  FCFA', '2025-03-04 09:17:04', '127.0.0.1'),
(480, 29, 'maintenance', 'Ajout d\'une maintenance preventive pour le véhicule ID:14', '2025-03-04 09:26:16', '127.0.0.1'),
(481, 29, 'maintenance', 'Fin de maintenance ID:23 pour le véhicule ID:14 - Coût final: 5000 FCFA', '2025-03-04 10:30:39', '127.0.0.1'),
(482, 29, 'maintenance', 'Ajout d\'une maintenance preventive pour le véhicule ID:14', '2025-03-04 10:31:30', '127.0.0.1'),
(483, 29, 'maintenance', 'Fin de maintenance ID:24 pour le véhicule ID:14 - Coût final: 1200 FCFA', '2025-03-04 10:31:56', '127.0.0.1'),
(484, 29, 'maintenance', 'Ajout d\'une maintenance corrective pour le véhicule ID:14', '2025-03-04 10:35:58', '127.0.0.1'),
(485, 29, 'maintenance', 'Fin de maintenance ID:25 pour le véhicule ID:14 - Coût final: 7500 FCFA', '2025-03-04 10:36:18', '127.0.0.1'),
(486, 29, 'maintenance', 'Ajout d\'une maintenance revision pour le véhicule ID:14', '2025-03-04 10:40:39', '127.0.0.1'),
(487, 29, 'maintenance', 'Annulation de maintenance ID:26 pour le véhicule ID:14', '2025-03-04 10:41:05', '127.0.0.1'),
(488, 29, 'suppression', 'Suppression de la zone: Cocody (ID: 3)', '2025-03-04 11:48:46', '127.0.0.1'),
(489, 29, 'connexion', 'Connexion réussie', '2025-03-05 13:32:02', '127.0.0.1'),
(490, 29, 'connexion', 'Connexion réussie', '2025-03-06 07:12:52', '127.0.0.1'),
(491, 29, 'connexion', 'Connexion réussie', '2025-03-10 06:59:50', '127.0.0.1'),
(492, 29, 'ajout_document', 'Ajout d\'un document : carte_grise pour le véhicule #8', '2025-03-10 09:43:32', '127.0.0.1'),
(493, 29, 'view_document', 'Consultation du document #17 - Carte grise', '2025-03-10 09:46:43', '127.0.0.1'),
(494, 29, 'view_document', 'Consultation du document #17 - Carte grise', '2025-03-10 09:50:30', '127.0.0.1'),
(495, 29, 'view_document', 'Consultation du document #11 - Carte transport', '2025-03-10 09:50:45', '127.0.0.1'),
(496, 29, 'creation_reservation', 'Nouvelle réservation créée #20 pour Malick', '2025-03-10 10:03:37', NULL),
(497, 29, 'reservation_validation', 'Validation de la réservation #20', '2025-03-10 10:03:53', '127.0.0.1'),
(498, 29, 'debut_course', 'Début de la course #20 - Kilométrage de départ: 60 km - Matériel: porte en fer\nlorem\nlorem - Acteurs: Kouadio\nLeo', '2025-03-10 10:57:50', '127.0.0.1'),
(499, 29, 'creation_reservation', 'Nouvelle réservation créée #21 pour Malick', '2025-03-10 11:07:02', NULL),
(500, 29, 'reservation_validation', 'Validation de la réservation #21', '2025-03-10 11:08:24', '127.0.0.1'),
(501, 29, 'debut_course', 'Début de la course #21 - Kilométrage de départ: 200 km', '2025-03-10 11:09:57', '127.0.0.1'),
(502, 29, 'connexion', 'Connexion réussie', '2025-03-11 06:49:07', '127.0.0.1'),
(503, 29, 'connexion', 'Connexion réussie', '2025-03-12 15:24:59', '127.0.0.1'),
(504, 29, 'creation_reservation', 'Nouvelle réservation créée #22 pour Malick', '2025-03-12 15:46:38', NULL),
(505, 29, 'reservation_validation', 'Validation de la réservation #22', '2025-03-12 15:46:59', '127.0.0.1'),
(506, 29, 'debut_course', 'Début de la course #22 - Kilométrage de départ: 60 km', '2025-03-12 15:47:22', '127.0.0.1'),
(507, 29, 'connexion', 'Connexion réussie', '2025-03-18 08:54:43', '127.0.0.1'),
(508, 29, 'connexion', 'Connexion réussie', '2025-03-19 14:17:24', '127.0.0.1'),
(509, 29, 'connexion', 'Connexion réussie', '2025-03-19 15:15:47', '192.168.100.166'),
(510, 29, 'connexion', 'Connexion réussie', '2025-03-20 07:05:18', '127.0.0.1'),
(511, 29, 'view_document', 'Consultation du document #11 - Carte transport', '2025-03-20 07:06:14', '127.0.0.1'),
(512, 29, 'view_renew_document', 'Consultation pour renouvellement du document #17 - Carte grise', '2025-03-20 07:06:21', '127.0.0.1'),
(513, 29, 'view_document', 'Consultation du document #15 - Carte transport', '2025-03-20 07:06:38', '127.0.0.1'),
(514, 29, 'view_document', 'Consultation du document #6 - Carte transport', '2025-03-20 07:06:52', '127.0.0.1'),
(515, 29, 'deconnexion', 'Déconnexion utilisateur', '2025-03-20 08:54:50', '127.0.0.1'),
(516, 29, 'connexion', 'Connexion réussie', '2025-03-20 08:55:00', '127.0.0.1'),
(517, 29, 'connexion', 'Connexion réussie', '2025-03-20 09:03:26', '127.0.0.1'),
(518, 29, 'connexion', 'Connexion réussie', '2025-03-21 09:07:17', '127.0.0.1'),
(519, 29, 'connexion', 'Connexion réussie', '2025-04-01 14:59:25', '127.0.0.1'),
(520, 29, 'connexion', 'Connexion réussie', '2025-08-17 19:35:44', '127.0.0.1');

-- --------------------------------------------------------

--
-- Structure de la table `maintenances`
--

CREATE TABLE `maintenances` (
  `id_maintenance` bigint(20) UNSIGNED NOT NULL,
  `id_vehicule` bigint(20) UNSIGNED DEFAULT NULL,
  `type_maintenance` enum('preventive','corrective','revision') NOT NULL,
  `description` text NOT NULL,
  `date_debut` date NOT NULL,
  `date_demarrage` timestamp NULL DEFAULT NULL,
  `date_fin_prevue` date NOT NULL,
  `date_fin_effective` date DEFAULT NULL,
  `cout` decimal(15,2) DEFAULT NULL,
  `kilometrage` int(11) DEFAULT NULL,
  `prestataire` varchar(100) DEFAULT NULL,
  `statut` enum('planifiee','en_cours','terminee','annulee') DEFAULT 'planifiee',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `maintenances`
--

INSERT INTO `maintenances` (`id_maintenance`, `id_vehicule`, `type_maintenance`, `description`, `date_debut`, `date_demarrage`, `date_fin_prevue`, `date_fin_effective`, `cout`, `kilometrage`, `prestataire`, `statut`, `created_at`, `updated_at`) VALUES
(19, 14, 'preventive', 'Nettoyage du moteur\n\nNotes finales (20/02/2025): Bien 10/10', '2025-02-20', '2025-02-20 09:44:15', '2025-02-21', '2025-02-20', 15000.00, 60, 'Mécanicien Touré', 'terminee', '2025-02-20 09:44:09', '2025-02-20 09:44:36'),
(20, 9, 'corrective', 'Visite \n\nNotes finales (20/02/2025): Mauvais travail', '2025-02-20', '2025-02-20 09:45:31', '2025-02-27', '2025-02-20', 8000.00, 65, 'Mécanicien Touré', 'terminee', '2025-02-20 09:45:26', '2025-02-20 09:45:46'),
(21, 14, 'preventive', 'Visite garage', '2025-02-21', '2025-02-21 17:34:45', '2025-02-28', '2025-03-04', NULL, 60, 'Mécanicien Touré', 'terminee', '2025-02-21 17:34:07', '2025-03-04 09:17:04'),
(22, 17, 'preventive', 'lorem\n\nNotes finales (25/02/2025): Bien fait 10/10', '2025-02-25', '2025-02-25 10:47:48', '2025-03-04', '2025-02-25', 8000.00, 150, 'Mécanicien Touré', 'terminee', '2025-02-25 10:47:44', '2025-02-25 10:48:01'),
(23, 14, 'preventive', 'visite\n\nNotes finales (04/03/2025): bien', '2025-03-04', '2025-03-04 09:27:14', '2025-03-11', '2025-03-04', 5000.00, 60, 'Mécanicien Traoré', 'terminee', '2025-03-04 09:26:16', '2025-03-04 10:30:39'),
(24, 14, 'preventive', 'visite\n\nNotes finales (04/03/2025): bien', '2025-03-04', '2025-03-04 10:31:34', '2025-03-11', '2025-03-04', 1200.00, 60, 'Mécanicien Traoré', 'terminee', '2025-03-04 10:31:30', '2025-03-04 10:31:56'),
(25, 14, 'corrective', 'visite\n\nNotes finales (04/03/2025): bien', '2025-03-04', NULL, '2025-03-11', '2025-03-04', 7500.00, 60, 'Mécanicien Traoré', 'terminee', '2025-03-04 10:35:58', '2025-03-04 10:36:18'),
(26, 14, 'revision', 'viste', '2025-03-04', '2025-03-04 10:40:44', '2025-03-11', NULL, NULL, 60, 'Mécanicien Traoré', 'annulee', '2025-03-04 10:40:39', '2025-03-04 10:41:05');

-- --------------------------------------------------------

--
-- Structure de la table `notifications_lues`
--

CREATE TABLE `notifications_lues` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `id_utilisateur` bigint(20) UNSIGNED NOT NULL,
  `type_notification` varchar(50) NOT NULL,
  `id_reference` bigint(20) UNSIGNED NOT NULL,
  `date_lecture` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `notifications_lues`
--

INSERT INTO `notifications_lues` (`id`, `id_utilisateur`, `type_notification`, `id_reference`, `date_lecture`) VALUES
(1, 29, 'reservation', 11, '2025-02-26 09:20:35'),
(2, 29, 'reservation', 12, '2025-02-26 09:34:42'),
(4, 29, 'reservation', 14, '2025-02-27 11:38:01'),
(7, 29, 'document', 0, '2025-03-10 09:51:24'),
(8, 29, 'reservation', 4, '2025-03-01 07:49:58'),
(9, 29, 'maintenance', 0, '2025-03-01 07:50:03'),
(10, 29, 'reservation', 18, '2025-03-05 13:33:21'),
(12, 29, 'deplacement', 0, '2025-03-12 15:49:49');

-- --------------------------------------------------------

--
-- Structure de la table `parametres_systeme`
--

CREATE TABLE `parametres_systeme` (
  `id_parametre` bigint(20) UNSIGNED NOT NULL,
  `cle` varchar(50) NOT NULL,
  `valeur` text NOT NULL,
  `description` text DEFAULT NULL,
  `date_modification` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `parametres_systeme`
--

INSERT INTO `parametres_systeme` (`id_parametre`, `cle`, `valeur`, `description`, `date_modification`) VALUES
(1, 'delai_alerte_document_1', '60', 'Délai d\'alerte pour les documents (en jours) - 2 mois', '2025-02-17 07:49:41'),
(2, 'delai_alerte_document_2', '30', 'Délai d\'alerte pour les documents (en jours) - 1 mois', '2025-02-17 07:49:41'),
(3, 'delai_alerte_document_3', '7', 'Délai d\'alerte pour les documents (en jours) - 1 semaine', '2025-02-17 07:49:41');

-- --------------------------------------------------------

--
-- Structure de la table `password_resets`
--

CREATE TABLE `password_resets` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `telephone` varchar(20) DEFAULT NULL,
  `token` varchar(255) DEFAULT NULL,
  `code` varchar(255) DEFAULT NULL,
  `expiry` datetime NOT NULL,
  `used` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `password_resets`
--

INSERT INTO `password_resets` (`id`, `email`, `telephone`, `token`, `code`, `expiry`, `used`, `created_at`) VALUES
(1, 'bahii02.example@gmail.com', NULL, '83892c7f14ea20554765d6df81e7a8b9e4f0b5e148a3cd46d4001e3e60b1fc52', NULL, '2025-02-28 09:10:46', 0, '2025-02-28 08:10:46');

-- --------------------------------------------------------

--
-- Structure de la table `reservations_vehicules`
--

CREATE TABLE `reservations_vehicules` (
  `id_reservation` bigint(20) UNSIGNED NOT NULL,
  `id_utilisateur` bigint(20) UNSIGNED DEFAULT NULL,
  `id_vehicule` bigint(20) UNSIGNED DEFAULT NULL,
  `id_chauffeur` bigint(20) UNSIGNED DEFAULT NULL,
  `demandeur` varchar(255) NOT NULL,
  `date_demande` datetime DEFAULT current_timestamp(),
  `date_depart` datetime NOT NULL,
  `date_debut_effective` datetime DEFAULT NULL,
  `date_retour_prevue` datetime NOT NULL,
  `date_retour_effective` datetime DEFAULT NULL,
  `nombre_passagers` int(11) NOT NULL,
  `type_chargement` text DEFAULT NULL,
  `km_depart` int(11) DEFAULT NULL,
  `km_retour` int(11) DEFAULT NULL,
  `statut` enum('en_attente','validee','en_cours','terminee','annulee') DEFAULT 'en_attente',
  `priorite` int(11) DEFAULT 0,
  `note` text DEFAULT NULL,
  `acteurs` text DEFAULT NULL,
  `objet_demande` varchar(255) NOT NULL,
  `materiel` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `reservations_vehicules`
--

INSERT INTO `reservations_vehicules` (`id_reservation`, `id_utilisateur`, `id_vehicule`, `id_chauffeur`, `demandeur`, `date_demande`, `date_depart`, `date_debut_effective`, `date_retour_prevue`, `date_retour_effective`, `nombre_passagers`, `type_chargement`, `km_depart`, `km_retour`, `statut`, `priorite`, `note`, `acteurs`, `objet_demande`, `materiel`) VALUES
(22, 29, 14, 5, 'Malick', '2025-03-12 15:46:38', '2025-03-12 15:48:00', '2025-03-12 15:47:22', '2025-03-12 19:46:00', NULL, 4, NULL, 60, NULL, 'en_cours', 1, '', '', 'visite', '');

-- --------------------------------------------------------

--
-- Structure de la table `utilisateurs`
--

CREATE TABLE `utilisateurs` (
  `id_utilisateur` bigint(20) UNSIGNED NOT NULL,
  `nom` varchar(50) NOT NULL,
  `prenom` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `mot_de_passe` varchar(255) NOT NULL,
  `telephone` varchar(20) DEFAULT NULL,
  `role` enum('administrateur','gestionnaire','validateur','utilisateur') NOT NULL,
  `date_creation` datetime DEFAULT current_timestamp(),
  `actif` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `utilisateurs`
--

INSERT INTO `utilisateurs` (`id_utilisateur`, `nom`, `prenom`, `email`, `mot_de_passe`, `telephone`, `role`, `date_creation`, `actif`) VALUES
(29, 'Bahi', 'Kipre', 'bahii02.example@gmail.com', '$2y$10$W4U.9T700c1VlUVJQjDcnuLLfBdca/CmXW.QXqMfgolvDVMp/eu8W', '0767376920', 'administrateur', '2025-02-24 14:49:01', 1),
(37, 'Andre', 'Lui', 'andre@gmail.com', '$2y$10$OKQc4yMGgQepB3c.UE8nh.arbH5Lh9lUFjA9JYwrBkpBTeeFgIHAG', '0707782476', 'utilisateur', '2025-02-28 10:00:44', 1),
(38, 'Naro', 'Vanessa', '', '$2y$10$Yydz7CT7kh.wWkSw3wH7vuj7By.FbIhcWmf9aS6HhZzchbvbXwhBy', '0712345678', 'validateur', '2025-02-28 10:12:11', 1);

-- --------------------------------------------------------

--
-- Structure de la table `vehicules`
--

CREATE TABLE `vehicules` (
  `id_vehicule` bigint(20) UNSIGNED NOT NULL,
  `id_zone` bigint(20) UNSIGNED NOT NULL,
  `immatriculation` varchar(20) NOT NULL,
  `marque` varchar(50) NOT NULL,
  `modele` varchar(50) NOT NULL,
  `logo_marque_vehicule` varchar(255) NOT NULL,
  `annee_mise_en_service` int(11) DEFAULT NULL,
  `type_vehicule` enum('utilitaire','berline','camion','bus') NOT NULL,
  `capacite_passagers` int(11) NOT NULL,
  `capacite_charge_kg` decimal(10,2) DEFAULT NULL,
  `type_carburant` enum('Super','Gasoil') NOT NULL,
  `kilometrage_actuel` int(11) DEFAULT 0,
  `statut` enum('disponible','en_course','maintenance','hors_service') DEFAULT 'disponible',
  `date_acquisition` date DEFAULT NULL,
  `prix_acquisition` decimal(15,2) DEFAULT NULL,
  `note` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `vehicules`
--

INSERT INTO `vehicules` (`id_vehicule`, `id_zone`, `immatriculation`, `marque`, `modele`, `logo_marque_vehicule`, `annee_mise_en_service`, `type_vehicule`, `capacite_passagers`, `capacite_charge_kg`, `type_carburant`, `kilometrage_actuel`, `statut`, `date_acquisition`, `prix_acquisition`, `note`, `created_at`, `updated_at`) VALUES
(8, 2, '1234AB01', 'Renault', 'Clio IV', 'logo_67b60131a9312.png', NULL, 'utilitaire', 5, NULL, 'Super', 2750, 'disponible', NULL, NULL, NULL, '2025-02-19 16:05:05', '2025-02-20 09:35:51'),
(9, 1, '2345CD01', 'Peugeot', '308', 'logo_67b602573c8e1.png', NULL, 'berline', 5, NULL, 'Super', 300, 'disponible', NULL, NULL, NULL, '2025-02-19 16:09:59', '2025-02-26 08:19:53'),
(10, 4, '3456EF01', 'Citroën', 'C3', 'logo_67b602dfa09c1.jpg', NULL, 'camion', 10, NULL, 'Gasoil', 50, 'disponible', NULL, NULL, NULL, '2025-02-19 16:12:15', '2025-02-20 10:03:26'),
(11, 5, '4567GH01', 'Ford', 'Focus', 'logo_67b60333b742c.png', NULL, 'bus', 30, NULL, 'Gasoil', 90, 'disponible', NULL, NULL, NULL, '2025-02-19 16:13:39', '2025-02-19 16:13:39'),
(12, 6, '5678IJ01', 'Volkswagen', 'Golf', 'logo_67b603beb9c96.jpg', NULL, 'berline', 5, NULL, 'Gasoil', 200, 'en_course', NULL, NULL, NULL, '2025-02-19 16:15:58', '2025-02-19 16:15:58'),
(13, 7, '6789KL01', 'Mercedes', 'Classe A', 'logo_67b60419a3922.jpeg', NULL, 'utilitaire', 5, NULL, 'Super', 60, 'disponible', NULL, NULL, NULL, '2025-02-19 16:17:29', '2025-02-19 16:17:29'),
(14, 8, '7890MN01', 'BMW', 'Série 1', 'logo_67b6049522217.jpg', NULL, 'berline', 5, NULL, 'Gasoil', 60, 'en_course', NULL, NULL, NULL, '2025-02-19 16:19:33', '2025-03-04 10:41:05'),
(16, 2, '12547', 'Kia', 'Kia01', 'logo_67b8bb853110f.png', NULL, 'utilitaire', 6, NULL, 'Super', 270, 'disponible', NULL, NULL, NULL, '2025-02-21 17:44:37', '2025-02-22 12:10:11'),
(17, 2, 'FORD602CI001', 'Ford', 'Ford602', 'logo_67bd9facec949.png', NULL, 'utilitaire', 5, NULL, 'Super', 150, 'disponible', NULL, NULL, NULL, '2025-02-25 10:47:08', '2025-02-25 10:49:16'),
(18, 2, 'NIS741-KL02', 'Nissan', 'NissanCI092', 'logo_67bf3ca0a9b0c.jpg', NULL, 'utilitaire', 5, NULL, 'Super', 20, 'disponible', NULL, NULL, NULL, '2025-02-26 16:09:04', '2025-02-26 16:09:04');

-- --------------------------------------------------------

--
-- Structure de la table `zone_vehicules`
--

CREATE TABLE `zone_vehicules` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `nom_zone` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `zone_vehicules`
--

INSERT INTO `zone_vehicules` (`id`, `nom_zone`, `description`, `created_at`, `updated_at`) VALUES
(1, 'Abobo', NULL, '2025-02-17 15:29:34', '2025-02-17 15:29:34'),
(2, 'Dym CHU Angré', NULL, '2025-02-18 07:01:15', '2025-02-18 07:01:15'),
(4, 'Plateau', NULL, '2025-02-18 11:13:10', '2025-02-18 11:13:10'),
(5, 'Cocody Faya', NULL, '2025-02-19 16:13:36', '2025-02-19 16:13:36'),
(6, 'Yopougon', NULL, '2025-02-19 16:15:55', '2025-02-19 16:15:55'),
(7, 'Plateau Dodui', NULL, '2025-02-19 16:17:26', '2025-02-19 16:17:26'),
(8, 'San-Pédro', NULL, '2025-02-19 16:19:30', '2025-02-19 16:19:30'),
(9, 'Oume', NULL, '2025-02-20 16:22:13', '2025-02-20 16:22:13'),
(10, 'ananeraie', NULL, '2025-02-20 16:26:03', '2025-02-20 16:26:03');

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `alertes_documents`
--
ALTER TABLE `alertes_documents`
  ADD PRIMARY KEY (`id_alerte`),
  ADD KEY `alertes_documents_ibfk_1` (`id_document`),
  ADD KEY `idx_alertes_statut` (`statut`),
  ADD KEY `idx_alertes_date` (`date_alerte`);

--
-- Index pour la table `approvisionnements_carburant`
--
ALTER TABLE `approvisionnements_carburant`
  ADD PRIMARY KEY (`id_approvisionnement`),
  ADD KEY `approvisionnements_carburant_ibfk_1` (`id_chauffeur`),
  ADD KEY `approvisionnements_carburant_ibfk_2` (`id_vehicule`);

--
-- Index pour la table `chauffeurs`
--
ALTER TABLE `chauffeurs`
  ADD PRIMARY KEY (`id_chauffeur`),
  ADD UNIQUE KEY `numero_permis` (`numero_permis`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_chauffeurs_statut` (`statut`),
  ADD KEY `id_utilisateur` (`id_utilisateur`);

--
-- Index pour la table `documents_administratifs`
--
ALTER TABLE `documents_administratifs`
  ADD PRIMARY KEY (`id_document`),
  ADD KEY `idx_documents_expiration` (`date_expiration`),
  ADD KEY `documents_administratifs_ibfk_1` (`id_chauffeur`),
  ADD KEY `documents_administratifs_ibfk_3` (`id_vehicule`),
  ADD KEY `id_utilisateur` (`id_utilisateur`);

--
-- Index pour la table `itineraires`
--
ALTER TABLE `itineraires`
  ADD PRIMARY KEY (`id_itineraire`),
  ADD KEY `id_reservation` (`id_reservation`);

--
-- Index pour la table `journal_activites`
--
ALTER TABLE `journal_activites`
  ADD PRIMARY KEY (`id_activite`),
  ADD KEY `id_utilisateur` (`id_utilisateur`);

--
-- Index pour la table `maintenances`
--
ALTER TABLE `maintenances`
  ADD PRIMARY KEY (`id_maintenance`),
  ADD KEY `maintenances_ibfk_1` (`id_vehicule`);

--
-- Index pour la table `notifications_lues`
--
ALTER TABLE `notifications_lues`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `utilisateur_notification` (`id_utilisateur`,`type_notification`,`id_reference`),
  ADD KEY `id_utilisateur` (`id_utilisateur`);

--
-- Index pour la table `parametres_systeme`
--
ALTER TABLE `parametres_systeme`
  ADD PRIMARY KEY (`id_parametre`),
  ADD UNIQUE KEY `cle` (`cle`);

--
-- Index pour la table `password_resets`
--
ALTER TABLE `password_resets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_email` (`email`),
  ADD KEY `idx_telephone` (`telephone`),
  ADD KEY `idx_token` (`token`),
  ADD KEY `idx_expiry` (`expiry`);

--
-- Index pour la table `reservations_vehicules`
--
ALTER TABLE `reservations_vehicules`
  ADD PRIMARY KEY (`id_reservation`),
  ADD KEY `idx_reservations_dates` (`date_depart`,`date_retour_prevue`),
  ADD KEY `reservations_ibfk_2` (`id_utilisateur`),
  ADD KEY `reservations_ibfk_3` (`id_vehicule`),
  ADD KEY `reservations_ibfk_1` (`id_chauffeur`);

--
-- Index pour la table `utilisateurs`
--
ALTER TABLE `utilisateurs`
  ADD PRIMARY KEY (`id_utilisateur`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `telephone` (`telephone`);

--
-- Index pour la table `vehicules`
--
ALTER TABLE `vehicules`
  ADD PRIMARY KEY (`id_vehicule`),
  ADD UNIQUE KEY `immatriculation` (`immatriculation`),
  ADD KEY `idx_vehicules_statut` (`statut`),
  ADD KEY `id_zone` (`id_zone`) USING BTREE;

--
-- Index pour la table `zone_vehicules`
--
ALTER TABLE `zone_vehicules`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `alertes_documents`
--
ALTER TABLE `alertes_documents`
  MODIFY `id_alerte` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT pour la table `approvisionnements_carburant`
--
ALTER TABLE `approvisionnements_carburant`
  MODIFY `id_approvisionnement` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT pour la table `chauffeurs`
--
ALTER TABLE `chauffeurs`
  MODIFY `id_chauffeur` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT pour la table `documents_administratifs`
--
ALTER TABLE `documents_administratifs`
  MODIFY `id_document` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT pour la table `itineraires`
--
ALTER TABLE `itineraires`
  MODIFY `id_itineraire` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT pour la table `journal_activites`
--
ALTER TABLE `journal_activites`
  MODIFY `id_activite` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=521;

--
-- AUTO_INCREMENT pour la table `maintenances`
--
ALTER TABLE `maintenances`
  MODIFY `id_maintenance` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT pour la table `notifications_lues`
--
ALTER TABLE `notifications_lues`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT pour la table `parametres_systeme`
--
ALTER TABLE `parametres_systeme`
  MODIFY `id_parametre` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT pour la table `password_resets`
--
ALTER TABLE `password_resets`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT pour la table `reservations_vehicules`
--
ALTER TABLE `reservations_vehicules`
  MODIFY `id_reservation` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT pour la table `utilisateurs`
--
ALTER TABLE `utilisateurs`
  MODIFY `id_utilisateur` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=39;

--
-- AUTO_INCREMENT pour la table `vehicules`
--
ALTER TABLE `vehicules`
  MODIFY `id_vehicule` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT pour la table `zone_vehicules`
--
ALTER TABLE `zone_vehicules`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `alertes_documents`
--
ALTER TABLE `alertes_documents`
  ADD CONSTRAINT `alertes_documents_ibfk_1` FOREIGN KEY (`id_document`) REFERENCES `documents_administratifs` (`id_document`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Contraintes pour la table `approvisionnements_carburant`
--
ALTER TABLE `approvisionnements_carburant`
  ADD CONSTRAINT `approvisionnements_carburant_ibfk_1` FOREIGN KEY (`id_chauffeur`) REFERENCES `chauffeurs` (`id_chauffeur`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `approvisionnements_carburant_ibfk_2` FOREIGN KEY (`id_vehicule`) REFERENCES `vehicules` (`id_vehicule`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Contraintes pour la table `chauffeurs`
--
ALTER TABLE `chauffeurs`
  ADD CONSTRAINT `chauffeurs_ibfk_1` FOREIGN KEY (`id_utilisateur`) REFERENCES `utilisateurs` (`id_utilisateur`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Contraintes pour la table `documents_administratifs`
--
ALTER TABLE `documents_administratifs`
  ADD CONSTRAINT `documents_administratifs_ibfk_1` FOREIGN KEY (`id_chauffeur`) REFERENCES `chauffeurs` (`id_chauffeur`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `documents_administratifs_ibfk_2` FOREIGN KEY (`id_utilisateur`) REFERENCES `utilisateurs` (`id_utilisateur`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `documents_administratifs_ibfk_3` FOREIGN KEY (`id_vehicule`) REFERENCES `vehicules` (`id_vehicule`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Contraintes pour la table `itineraires`
--
ALTER TABLE `itineraires`
  ADD CONSTRAINT `itineraires_ibfk_1` FOREIGN KEY (`id_reservation`) REFERENCES `reservations_vehicules` (`id_reservation`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Contraintes pour la table `journal_activites`
--
ALTER TABLE `journal_activites`
  ADD CONSTRAINT `journal_activites_ibfk_1` FOREIGN KEY (`id_utilisateur`) REFERENCES `utilisateurs` (`id_utilisateur`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Contraintes pour la table `maintenances`
--
ALTER TABLE `maintenances`
  ADD CONSTRAINT `maintenances_ibfk_1` FOREIGN KEY (`id_vehicule`) REFERENCES `vehicules` (`id_vehicule`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Contraintes pour la table `notifications_lues`
--
ALTER TABLE `notifications_lues`
  ADD CONSTRAINT `notifications_lues_ibfk_1` FOREIGN KEY (`id_utilisateur`) REFERENCES `utilisateurs` (`id_utilisateur`) ON DELETE CASCADE;

--
-- Contraintes pour la table `reservations_vehicules`
--
ALTER TABLE `reservations_vehicules`
  ADD CONSTRAINT `reservations_vehicules_ibfk_1` FOREIGN KEY (`id_chauffeur`) REFERENCES `chauffeurs` (`id_chauffeur`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `reservations_vehicules_ibfk_2` FOREIGN KEY (`id_utilisateur`) REFERENCES `utilisateurs` (`id_utilisateur`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `reservations_vehicules_ibfk_3` FOREIGN KEY (`id_vehicule`) REFERENCES `vehicules` (`id_vehicule`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Contraintes pour la table `vehicules`
--
ALTER TABLE `vehicules`
  ADD CONSTRAINT `vehicules_ibfk_1` FOREIGN KEY (`id_zone`) REFERENCES `zone_vehicules` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
