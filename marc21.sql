-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Erstellungszeit: 29. Jun 2024 um 17:47
-- Server-Version: 8.4.0
-- PHP-Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Datenbank: `marc21`
--

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `ddc`
--

CREATE TABLE `ddc` (
  `id` int NOT NULL,
  `ddc` char(3) COLLATE utf8mb4_general_ci NOT NULL,
  `isolang` char(2) COLLATE utf8mb4_general_ci NOT NULL,
  `descript` varchar(512) COLLATE utf8mb4_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `search`
--

CREATE TABLE `search` (
  `id` int NOT NULL,
  `titleid` int NOT NULL,
  `colname` char(16) COLLATE utf8mb4_general_ci NOT NULL,
  `what` text CHARACTER SET utf8mb4 COLLATE utf8mb4_german2_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_as_ci;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `sources`
--

CREATE TABLE `sources` (
  `id` int NOT NULL,
  `path` varchar(256) COLLATE utf8mb4_general_ci NOT NULL,
  `file` varchar(256) COLLATE utf8mb4_general_ci NOT NULL,
  `created` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `tags`
--

CREATE TABLE `tags` (
  `id` int NOT NULL,
  `titleid` int NOT NULL,
  `tag` char(3) COLLATE utf8mb4_general_ci NOT NULL,
  `seq` int NOT NULL,
  `indicator` char(2) COLLATE utf8mb4_general_ci NOT NULL,
  `subfieldcode` char(2) COLLATE utf8mb4_general_ci NOT NULL,
  `subfielddata` text COLLATE utf8mb4_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `titles`
--

CREATE TABLE `titles` (
  `id` int NOT NULL,
  `sourceid` int NOT NULL,
  `offset` int NOT NULL COMMENT 'byte offset inside the source file\r\n where this record starts.',
  `active` int NOT NULL DEFAULT '0' COMMENT 'flag to indicate if title is usable etc ...',
  `ddc` char(6) COLLATE utf8mb4_general_ci NOT NULL COMMENT 'DDC title belongs to'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indizes der exportierten Tabellen
--

--
-- Indizes für die Tabelle `ddc`
--
ALTER TABLE `ddc`
  ADD PRIMARY KEY (`id`),
  ADD KEY `code-lang` (`ddc`,`isolang`);

--
-- Indizes für die Tabelle `search`
--
ALTER TABLE `search`
  ADD PRIMARY KEY (`id`),
  ADD KEY `delserach` (`titleid`);
ALTER TABLE `search` ADD FULLTEXT KEY `fulltext` (`what`);

--
-- Indizes für die Tabelle `sources`
--
ALTER TABLE `sources`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `path` (`path`,`file`);

--
-- Indizes für die Tabelle `tags`
--
ALTER TABLE `tags`
  ADD PRIMARY KEY (`id`),
  ADD KEY `deltags` (`titleid`),
  ADD KEY `tag` (`tag`);

--
-- Indizes für die Tabelle `titles`
--
ALTER TABLE `titles`
  ADD PRIMARY KEY (`id`),
  ADD KEY `deltitles` (`sourceid`);

--
-- AUTO_INCREMENT für exportierte Tabellen
--

--
-- AUTO_INCREMENT für Tabelle `ddc`
--
ALTER TABLE `ddc`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT für Tabelle `search`
--
ALTER TABLE `search`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT für Tabelle `sources`
--
ALTER TABLE `sources`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT für Tabelle `tags`
--
ALTER TABLE `tags`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT für Tabelle `titles`
--
ALTER TABLE `titles`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- Constraints der exportierten Tabellen
--

--
-- Constraints der Tabelle `search`
--
ALTER TABLE `search`
  ADD CONSTRAINT `delserach` FOREIGN KEY (`titleid`) REFERENCES `titles` (`id`) ON DELETE CASCADE;

--
-- Constraints der Tabelle `tags`
--
ALTER TABLE `tags`
  ADD CONSTRAINT `deltags` FOREIGN KEY (`titleid`) REFERENCES `titles` (`id`) ON DELETE CASCADE;

--
-- Constraints der Tabelle `titles`
--
ALTER TABLE `titles`
  ADD CONSTRAINT `deltitles` FOREIGN KEY (`sourceid`) REFERENCES `sources` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
