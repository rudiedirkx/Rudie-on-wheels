-- phpMyAdmin SQL Dump
-- version 3.2.0.1
-- http://www.phpmyadmin.net
--
-- Machine: localhost
-- Genereertijd: 17 Jan 2011 om 18:01
-- Serverversie: 5.1.36
-- PHP-Versie: 5.3.0

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `blog`
--

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `comments`
--

CREATE TABLE IF NOT EXISTS `comments` (
  `comment_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `author_id` int(10) unsigned NOT NULL,
  `post_id` int(10) unsigned NOT NULL DEFAULT '0',
  `comment` text NOT NULL,
  `created_on` int(10) unsigned NOT NULL,
  PRIMARY KEY (`comment_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;

--
-- Gegevens worden uitgevoerd voor tabel `comments`
--

INSERT INTO `comments` (`comment_id`, `author_id`, `post_id`, `comment`, `created_on`) VALUES
(1, 3, 3, 'ik kan wel genieten van een\r\n\r\nopen regeltje\r\n\r\nhier en daar =)', 0);

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `following_posts`
--

CREATE TABLE IF NOT EXISTS `following_posts` (
  `user_id` int(10) unsigned NOT NULL,
  `post_id` int(10) unsigned NOT NULL,
  `started_on` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`user_id`,`post_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Gegevens worden uitgevoerd voor tabel `following_posts`
--


-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `following_users`
--

CREATE TABLE IF NOT EXISTS `following_users` (
  `user_id` int(10) unsigned NOT NULL,
  `followee_user_id` int(10) unsigned NOT NULL,
  `started_on` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`user_id`,`followee_user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Gegevens worden uitgevoerd voor tabel `following_users`
--


-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `posts`
--

CREATE TABLE IF NOT EXISTS `posts` (
  `post_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `author_id` int(10) unsigned NOT NULL,
  `title` varchar(222) NOT NULL DEFAULT '',
  `body` text NOT NULL,
  `created_on` int(10) unsigned NOT NULL DEFAULT '0',
  `is_published` tinyint(3) unsigned NOT NULL DEFAULT '1',
  PRIMARY KEY (`post_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=4 ;

--
-- Gegevens worden uitgevoerd voor tabel `posts`
--

INSERT INTO `posts` (`post_id`, `author_id`, `title`, `body`, `created_on`, `is_published`) VALUES
(1, 1, 'testbericht', 'dit is een testbericht wihii', 0, 1),
(2, 1, 'nog ene', 'nog een testbericht', 500, 0),
(3, 1, 'numero drei', 'een testbericht met  \r\nnieuwe regels  \r\ner\r\n\r\nin', 1000, 1);

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `users`
--

CREATE TABLE IF NOT EXISTS `users` (
  `user_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(200) NOT NULL DEFAULT '',
  `full_name` varchar(200) NOT NULL DEFAULT '',
  `bio` text NOT NULL,
  `access` text NOT NULL,
  PRIMARY KEY (`user_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=5 ;

--
-- Gegevens worden uitgevoerd voor tabel `users`
--

INSERT INTO `users` (`user_id`, `username`, `full_name`, `bio`, `access`) VALUES
(1, 'root', 'root user', '', ''),
(2, 'jaap', 'Jaap de Koning', '', 'blog publish, blog read unpublished'),
(3, 'bert', 'Bert v.d. Zaagweg', 'Ik ben Bert! =)', 'blog delete hidden comments, blog unpublish'),
(4, 'janneke', 'Janneke Meerzeit', '', 'blog flag as spam, blog flag as inappropriate, blog hide comment');
