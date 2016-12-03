-- phpMyAdmin SQL Dump
-- version 4.0.10.14
-- http://www.phpmyadmin.net
--
-- Host: localhost:3306
-- Generation Time: Oct 01, 2016 at 08:02 PM
-- Server version: 5.5.49-MariaDB-cll-lve
-- PHP Version: 5.6.20

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `bbdb`
--
CREATE DATABASE IF NOT EXISTS `bbdb` DEFAULT CHARACTER SET latin1 COLLATE latin1_swedish_ci;
USE `bbdb`;

-- --------------------------------------------------------

--
-- Table structure for table `audittrail`
--

CREATE TABLE IF NOT EXISTS `audittrail` (
  `AuditTrailID` int(8) unsigned NOT NULL AUTO_INCREMENT,
  `ActionDate` datetime NOT NULL,
  `UserID` int(8) unsigned NOT NULL,
  `EntityID` int(6) NOT NULL,
  `EntityRefID` int(7) NOT NULL,
  `FieldID` int(6) DEFAULT NULL,
  `OldValue1` varchar(50) DEFAULT NULL,
  `OldValue2` varchar(50) DEFAULT NULL,
  `NewValue1` varchar(50) DEFAULT NULL,
  `NewValue2` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`AuditTrailID`),
  KEY `UserID` (`UserID`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `bb_api_call`
--

CREATE TABLE IF NOT EXISTS `bb_api_call` (
  `api_user_id` int(8) unsigned NOT NULL,
  `api_type_id` smallint(5) unsigned NOT NULL,
  `parameters` varchar(300) NOT NULL,
  `request_datetime` datetime DEFAULT NULL,
  `time_to_serve_ms` decimal(13,2) unsigned NOT NULL,
  KEY `fk_api_type_id` (`api_type_id`),
  KEY `fk_api_user_id` (`api_user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `bb_api_type`
--

CREATE TABLE IF NOT EXISTS `bb_api_type` (
  `api_type_id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `entity_name` varchar(100) NOT NULL,
  `entity_version` smallint(5) unsigned NOT NULL,
  `depracated` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`api_type_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `bb_api_user`
--

CREATE TABLE IF NOT EXISTS `bb_api_user` (
  `api_user_id` int(8) unsigned NOT NULL AUTO_INCREMENT,
  `api_key` char(32) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(200) DEFAULT NULL,
  PRIMARY KEY (`api_user_id`),
  UNIQUE KEY `api_key` (`api_key`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `bb_coach`
--

CREATE TABLE IF NOT EXISTS `bb_coach` (
  `coach_id` mediumint(7) unsigned NOT NULL AUTO_INCREMENT,
  `description` varchar(100) NOT NULL,
  `bb1_id` int(10) DEFAULT NULL,
  `user_id` mediumint(7) unsigned DEFAULT NULL,
  `domain_id` smallint(5) unsigned NOT NULL,
  PRIMARY KEY (`coach_id`),
  UNIQUE KEY `unique_index` (`description`,`domain_id`),
  KEY `domain_lookup` (`domain_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `bb_competition`
--

CREATE TABLE IF NOT EXISTS `bb_competition` (
  `domain_id` smallint(5) unsigned NOT NULL,
  `competition_id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `competition_type_id` tinyint(3) unsigned NOT NULL,
  `description` varchar(200) NOT NULL,
  `completed` bit(1) NOT NULL DEFAULT b'0',
  `short_description` varchar(6) DEFAULT NULL,
  `auto_enrol` tinyint(1) NOT NULL DEFAULT '0',
  `tiebreaker_id` tinyint(3) unsigned NOT NULL,
  PRIMARY KEY (`competition_id`),
  UNIQUE KEY `comp_name` (`domain_id`,`description`),
  UNIQUE KEY `comp_short_name` (`domain_id`,`short_description`),
  KEY `tiebreaker_id` (`tiebreaker_id`),
  KEY `competition_type_id` (`competition_type_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `bb_competition_team`
--

CREATE TABLE IF NOT EXISTS `bb_competition_team` (
  `competition_id` smallint(5) unsigned NOT NULL,
  `domain_id` smallint(5) unsigned NOT NULL,
  `team_id` mediumint(8) unsigned NOT NULL,
  `initial_group_id` tinyint(3) unsigned DEFAULT NULL,
  `performance` varchar(250) DEFAULT NULL,
  PRIMARY KEY (`competition_id`,`domain_id`,`team_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `bb_domain`
--

CREATE TABLE IF NOT EXISTS `bb_domain` (
  `domain_id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `description` varchar(200) NOT NULL,
  PRIMARY KEY (`domain_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `bb_lkp_ag`
--

CREATE TABLE IF NOT EXISTS `bb_lkp_ag` (
  `bb1_id` decimal(7,3) NOT NULL,
  `human_val` tinyint(3) unsigned NOT NULL,
  PRIMARY KEY (`bb1_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `bb_lkp_av`
--

CREATE TABLE IF NOT EXISTS `bb_lkp_av` (
  `bb1_id` decimal(7,3) NOT NULL,
  `human_val` tinyint(3) unsigned NOT NULL,
  PRIMARY KEY (`bb1_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `bb_lkp_award`
--

CREATE TABLE IF NOT EXISTS `bb_lkp_award` (
  `award_id` int(8) unsigned NOT NULL AUTO_INCREMENT,
  `award_type_id` mediumint(6) unsigned NOT NULL,
  `award_level_id` tinyint(3) unsigned NOT NULL,
  `criteria` int(8) DEFAULT NULL COMMENT 'Target required',
  PRIMARY KEY (`award_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `bb_lkp_award_category`
--

CREATE TABLE IF NOT EXISTS `bb_lkp_award_category` (
  `award_category_id` tinyint(3) unsigned NOT NULL AUTO_INCREMENT,
  `entity_name` varchar(100) NOT NULL,
  `description` varchar(100) NOT NULL,
  PRIMARY KEY (`award_category_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `bb_lkp_award_level`
--

CREATE TABLE IF NOT EXISTS `bb_lkp_award_level` (
  `award_level_id` tinyint(3) unsigned NOT NULL,
  `description` varchar(100) NOT NULL,
  `position` varchar(100) DEFAULT NULL,
  `rank_no` tinyint(3) unsigned NOT NULL,
  PRIMARY KEY (`award_level_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `bb_lkp_award_type`
--

CREATE TABLE IF NOT EXISTS `bb_lkp_award_type` (
  `award_type_id` mediumint(6) unsigned NOT NULL AUTO_INCREMENT,
  `award_category_id` tinyint(3) unsigned NOT NULL,
  `description` varchar(200) NOT NULL,
  `measured_value` varchar(75) NOT NULL,
  `table_id` smallint(5) unsigned DEFAULT NULL,
  PRIMARY KEY (`award_type_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `bb_lkp_block_dice`
--

CREATE TABLE IF NOT EXISTS `bb_lkp_block_dice` (
  `block_dice_id` tinyint(3) unsigned NOT NULL AUTO_INCREMENT,
  `description` varchar(100) NOT NULL,
  `bb1_desc` varchar(100) DEFAULT NULL,
  `img_src` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`block_dice_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `bb_lkp_block_dice_count`
--

CREATE TABLE IF NOT EXISTS `bb_lkp_block_dice_count` (
  `block_dice_count_id` tinyint(2) unsigned NOT NULL AUTO_INCREMENT,
  `dice_count` tinyint(2) unsigned NOT NULL,
  `description` varchar(100) NOT NULL,
  `against` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`block_dice_count_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `bb_lkp_block_dice_perm`
--

CREATE TABLE IF NOT EXISTS `bb_lkp_block_dice_perm` (
  `block_dice_perm_id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `block_dice_count_id` tinyint(3) unsigned NOT NULL,
  `block_dice_id_1` tinyint(3) unsigned NOT NULL,
  `block_dice_id_2` tinyint(3) unsigned DEFAULT NULL,
  `block_dice_id_3` tinyint(3) unsigned DEFAULT NULL,
  `short_description` varchar(10) NOT NULL,
  PRIMARY KEY (`block_dice_perm_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `bb_lkp_casualty`
--

CREATE TABLE IF NOT EXISTS `bb_lkp_casualty` (
  `casualty_id` tinyint(3) unsigned NOT NULL AUTO_INCREMENT,
  `bb1_id` tinyint(3) unsigned DEFAULT NULL,
  `description` varchar(100) NOT NULL,
  `effect_english` varchar(100) NOT NULL,
  PRIMARY KEY (`casualty_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `bb_lkp_casualty_status`
--

CREATE TABLE IF NOT EXISTS `bb_lkp_casualty_status` (
  `casualty_status_id` tinyint(3) unsigned NOT NULL AUTO_INCREMENT,
  `description` varchar(200) NOT NULL,
  PRIMARY KEY (`casualty_status_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `bb_lkp_competition_tiebreaker`
--

CREATE TABLE IF NOT EXISTS `bb_lkp_competition_tiebreaker` (
  `competition_tiebreaker_id` tinyint(3) unsigned NOT NULL AUTO_INCREMENT,
  `description` varchar(100) NOT NULL,
  PRIMARY KEY (`competition_tiebreaker_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `bb_lkp_competition_type`
--

CREATE TABLE IF NOT EXISTS `bb_lkp_competition_type` (
  `competition_type_id` tinyint(3) unsigned NOT NULL AUTO_INCREMENT,
  `description` varchar(200) NOT NULL,
  `start_league` tinyint(1) NOT NULL DEFAULT '0',
  `knockout_end` tinyint(1) NOT NULL DEFAULT '0',
  `start_group_count` tinyint(3) DEFAULT NULL,
  PRIMARY KEY (`competition_type_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `bb_lkp_dice_type`
--

CREATE TABLE IF NOT EXISTS `bb_lkp_dice_type` (
  `dice_type_id` tinyint(3) unsigned NOT NULL AUTO_INCREMENT,
  `description` varchar(100) NOT NULL,
  PRIMARY KEY (`dice_type_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `bb_lkp_event_outcome`
--

CREATE TABLE IF NOT EXISTS `bb_lkp_event_outcome` (
  `event_outcome_id` tinyint(3) unsigned NOT NULL,
  `description` varchar(100) NOT NULL,
  PRIMARY KEY (`event_outcome_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `bb_lkp_fail_turnover`
--

CREATE TABLE IF NOT EXISTS `bb_lkp_fail_turnover` (
  `fail_turnover_id` tinyint(3) unsigned NOT NULL,
  `description` varchar(100) NOT NULL,
  PRIMARY KEY (`fail_turnover_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='if the roll is failed, is it a turnover?';

-- --------------------------------------------------------

--
-- Table structure for table `bb_lkp_match_status`
--

CREATE TABLE IF NOT EXISTS `bb_lkp_match_status` (
  `match_status_id` tinyint(3) unsigned NOT NULL AUTO_INCREMENT,
  `description` varchar(100) NOT NULL,
  PRIMARY KEY (`match_status_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `bb_lkp_mv`
--

CREATE TABLE IF NOT EXISTS `bb_lkp_mv` (
  `bb1_id` decimal(25,22) NOT NULL,
  `human_val` tinyint(3) unsigned NOT NULL,
  PRIMARY KEY (`bb1_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `bb_lkp_player_status`
--

CREATE TABLE IF NOT EXISTS `bb_lkp_player_status` (
  `player_status_id` tinyint(3) unsigned NOT NULL AUTO_INCREMENT,
  `description` varchar(100) NOT NULL,
  `short_description` char(1) NOT NULL,
  PRIMARY KEY (`player_status_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `bb_lkp_player_supertype`
--

CREATE TABLE IF NOT EXISTS `bb_lkp_player_supertype` (
  `player_supertype_id` tinyint(3) unsigned NOT NULL AUTO_INCREMENT,
  `description` varchar(100) NOT NULL,
  PRIMARY KEY (`player_supertype_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `bb_lkp_player_type`
--

CREATE TABLE IF NOT EXISTS `bb_lkp_player_type` (
  `player_type_id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `player_supertype_id` tinyint(3) unsigned DEFAULT NULL,
  `bb1_id` smallint(5) DEFAULT NULL,
  `long_description` varchar(200) NOT NULL,
  `description` varchar(100) DEFAULT NULL,
  `short_description` varchar(5) DEFAULT NULL,
  `race_id` tinyint(3) unsigned DEFAULT NULL COMMENT 'NULL = star player',
  `mv` tinyint(3) unsigned DEFAULT NULL,
  `st` tinyint(3) unsigned DEFAULT NULL,
  `ag` tinyint(3) unsigned DEFAULT NULL,
  `av` tinyint(3) unsigned DEFAULT NULL,
  `price` mediumint(6) unsigned DEFAULT NULL,
  `max_quantity` tinyint(3) unsigned NOT NULL,
  PRIMARY KEY (`player_type_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `bb_lkp_player_type_skill`
--

CREATE TABLE IF NOT EXISTS `bb_lkp_player_type_skill` (
  `player_type_id` smallint(5) unsigned NOT NULL,
  `skill_id` tinyint(3) unsigned NOT NULL,
  PRIMARY KEY (`player_type_id`,`skill_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='Starting skills';

-- --------------------------------------------------------

--
-- Table structure for table `bb_lkp_player_type_skill_access`
--

CREATE TABLE IF NOT EXISTS `bb_lkp_player_type_skill_access` (
  `player_type_id` smallint(5) unsigned NOT NULL,
  `skill_category_id` tinyint(3) unsigned NOT NULL,
  `access_roll` char(1) NOT NULL DEFAULT 'U'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `bb_lkp_player_type_stats`
--

CREATE TABLE IF NOT EXISTS `bb_lkp_player_type_stats` (
  `player_type_id` smallint(5) unsigned NOT NULL,
  `ruleset_id` tinyint(3) unsigned NOT NULL,
  `mv` tinyint(3) unsigned NOT NULL,
  `st` tinyint(3) unsigned NOT NULL,
  `ag` tinyint(3) unsigned NOT NULL,
  `av` tinyint(3) unsigned NOT NULL,
  `price` mediumint(6) unsigned NOT NULL,
  PRIMARY KEY (`player_type_id`,`ruleset_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `bb_lkp_race`
--

CREATE TABLE IF NOT EXISTS `bb_lkp_race` (
  `race_id` tinyint(3) unsigned NOT NULL AUTO_INCREMENT,
  `bb1_id` tinyint(3) unsigned NOT NULL,
  `description` varchar(100) NOT NULL,
  `single_description` varchar(100) DEFAULT NULL COMMENT 'eg Orc rather than Orcs',
  `reroll_price` mediumint(6) NOT NULL,
  `short_description` varchar(6) DEFAULT NULL,
  PRIMARY KEY (`race_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `bb_lkp_reroll_type`
--

CREATE TABLE IF NOT EXISTS `bb_lkp_reroll_type` (
  `reroll_type_id` tinyint(3) unsigned NOT NULL,
  `description` varchar(100) NOT NULL,
  PRIMARY KEY (`reroll_type_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `bb_lkp_roll_aim`
--

CREATE TABLE IF NOT EXISTS `bb_lkp_roll_aim` (
  `roll_aim_id` tinyint(3) unsigned NOT NULL AUTO_INCREMENT,
  `description` varchar(100) NOT NULL,
  PRIMARY KEY (`roll_aim_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `bb_lkp_roll_modifiers`
--

CREATE TABLE IF NOT EXISTS `bb_lkp_roll_modifiers` (
  `modifier_id` tinyint(3) unsigned NOT NULL AUTO_INCREMENT,
  `description` varchar(100) NOT NULL,
  `bb1_log_desc` varchar(100) DEFAULT NULL,
  `fixed_value` tinyint(3) DEFAULT NULL,
  PRIMARY KEY (`modifier_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `bb_lkp_roll_outcome`
--

CREATE TABLE IF NOT EXISTS `bb_lkp_roll_outcome` (
  `outcome_id` tinyint(3) unsigned NOT NULL AUTO_INCREMENT,
  `description` varchar(300) NOT NULL,
  `turnover_flag` bit(1) NOT NULL DEFAULT b'0',
  PRIMARY KEY (`outcome_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `bb_lkp_roll_type`
--

CREATE TABLE IF NOT EXISTS `bb_lkp_roll_type` (
  `roll_type_id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `dice_type_id` tinyint(3) unsigned NOT NULL,
  `description` varchar(150) NOT NULL,
  `bb1_desc` varchar(100) DEFAULT NULL COMMENT 'Used for matching to log file',
  `fail_turnover_id` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT 'Does a failure mean a turnover?',
  `roll_aim_id` tinyint(3) unsigned DEFAULT NULL,
  `roll_modifier` tinyint(3) NOT NULL DEFAULT '0',
  `optional_modifier_flag` bit(1) NOT NULL DEFAULT b'0',
  `reroll_skill_id` tinyint(3) unsigned DEFAULT NULL,
  `modify_desc` varchar(200) DEFAULT NULL,
  PRIMARY KEY (`roll_type_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `bb_lkp_ruleset`
--

CREATE TABLE IF NOT EXISTS `bb_lkp_ruleset` (
  `ruleset_id` tinyint(3) unsigned NOT NULL AUTO_INCREMENT,
  `description` varchar(100) NOT NULL,
  PRIMARY KEY (`ruleset_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `bb_lkp_scoring_system`
--

CREATE TABLE IF NOT EXISTS `bb_lkp_scoring_system` (
  `scoring_system_id` tinyint(3) unsigned NOT NULL AUTO_INCREMENT,
  `description` varchar(200) NOT NULL,
  PRIMARY KEY (`scoring_system_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `bb_lkp_skill`
--

CREATE TABLE IF NOT EXISTS `bb_lkp_skill` (
  `skill_id` tinyint(3) unsigned NOT NULL,
  `bb1_desc` varchar(30) NOT NULL,
  `human_desc` varchar(50) NOT NULL,
  `skill_category_id` tinyint(3) unsigned DEFAULT NULL,
  `long_description` varchar(2000) DEFAULT NULL,
  UNIQUE KEY `skill_id` (`skill_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `bb_lkp_skill_category`
--

CREATE TABLE IF NOT EXISTS `bb_lkp_skill_category` (
  `skill_category_id` tinyint(3) unsigned NOT NULL,
  `skill_category` varchar(100) NOT NULL,
  PRIMARY KEY (`skill_category_id`),
  UNIQUE KEY `skill_category_id` (`skill_category_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `bb_lkp_spp_levels`
--

CREATE TABLE IF NOT EXISTS `bb_lkp_spp_levels` (
  `spp_level` tinyint(3) unsigned NOT NULL,
  `ruleset_id` tinyint(3) unsigned NOT NULL,
  `description` varchar(100) NOT NULL,
  `limit_spp` smallint(5) unsigned NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `bb_lkp_st`
--

CREATE TABLE IF NOT EXISTS `bb_lkp_st` (
  `bb1_id` tinyint(3) unsigned NOT NULL,
  `human_val` tinyint(3) unsigned NOT NULL,
  UNIQUE KEY `bb1_val` (`bb1_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `bb_lkp_star_player_race`
--

CREATE TABLE IF NOT EXISTS `bb_lkp_star_player_race` (
  `player_type_id` smallint(5) unsigned NOT NULL,
  `race_id` tinyint(3) unsigned NOT NULL,
  `ruleset_id` tinyint(3) unsigned NOT NULL,
  PRIMARY KEY (`player_type_id`,`race_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `bb_lkp_table_player`
--

CREATE TABLE IF NOT EXISTS `bb_lkp_table_player` (
  `table_id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `description` varchar(200) NOT NULL,
  `column_header` varchar(75) NOT NULL,
  `statistic_measured` varchar(200) NOT NULL,
  `default_chart_size` mediumint(6) DEFAULT '10',
  PRIMARY KEY (`table_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `bb_lkp_table_team`
--

CREATE TABLE IF NOT EXISTS `bb_lkp_table_team` (
  `table_id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `description` varchar(200) NOT NULL,
  `column_header` varchar(75) NOT NULL,
  `statistic_measured` varchar(200) NOT NULL,
  `default_chart_size` mediumint(6) DEFAULT NULL,
  PRIMARY KEY (`table_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `bb_lkp_turnover_type`
--

CREATE TABLE IF NOT EXISTS `bb_lkp_turnover_type` (
  `turnover_type_id` tinyint(3) unsigned NOT NULL AUTO_INCREMENT,
  `description` varchar(100) NOT NULL,
  PRIMARY KEY (`turnover_type_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `bb_lkp_turn_end_reason`
--

CREATE TABLE IF NOT EXISTS `bb_lkp_turn_end_reason` (
  `turn_end_reason` tinyint(3) unsigned NOT NULL AUTO_INCREMENT,
  `description` varchar(100) NOT NULL,
  `text_search` varchar(100) NOT NULL,
  PRIMARY KEY (`turn_end_reason`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `bb_lkp_user_activation_status`
--

CREATE TABLE IF NOT EXISTS `bb_lkp_user_activation_status` (
  `user_activation_status_id` tinyint(3) unsigned NOT NULL AUTO_INCREMENT,
  `description` varchar(100) NOT NULL,
  PRIMARY KEY (`user_activation_status_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `bb_lkp_weather`
--

CREATE TABLE IF NOT EXISTS `bb_lkp_weather` (
  `weather_id` tinyint(3) unsigned NOT NULL AUTO_INCREMENT,
  `description` varchar(100) NOT NULL,
  `long_description` varchar(1000) NOT NULL,
  `probability` decimal(21,20) NOT NULL,
  PRIMARY KEY (`weather_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `bb_match`
--

CREATE TABLE IF NOT EXISTS `bb_match` (
  `match_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `bb1_id` int(10) unsigned DEFAULT NULL,
  `domain_id` smallint(5) unsigned NOT NULL,
  `match_date` datetime NOT NULL,
  `home_team_id` mediumint(8) unsigned NOT NULL,
  `away_team_id` mediumint(8) unsigned NOT NULL,
  `home_touchdown_count` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `away_touchdown_count` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `spectators` mediumint(8) unsigned DEFAULT NULL,
  `rating` smallint(5) unsigned DEFAULT NULL,
  `match_status_id` tinyint(3) unsigned NOT NULL DEFAULT '1',
  `rulestype_id` tinyint(1) unsigned DEFAULT NULL,
  `overtime_flag` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`match_id`),
  KEY `domain_match` (`domain_id`,`match_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `bb_matchlog`
--

CREATE TABLE IF NOT EXISTS `bb_matchlog` (
  `matchlog_id` bigint(15) unsigned NOT NULL AUTO_INCREMENT,
  `event_id` smallint(5) DEFAULT NULL COMMENT 'An event is a linked series of dice from 1 decision, eg pass-intercept-catch, or block/av/casualty',
  `match_id` int(10) unsigned NOT NULL,
  `team_id` mediumint(8) unsigned DEFAULT NULL,
  `player_id` bigint(15) unsigned DEFAULT NULL,
  `oneoff_id` int(10) unsigned DEFAULT NULL,
  `target_player_id` bigint(15) unsigned DEFAULT NULL,
  `target_oneoff_id` int(10) unsigned DEFAULT NULL,
  `roll_type_id` smallint(5) unsigned DEFAULT NULL,
  `roll_target` tinyint(2) unsigned DEFAULT NULL,
  `roll_target_exact_flag` tinyint(1) DEFAULT NULL COMMENT 'If set to 0 then our roll target is only a guess - the log does not give us the info to give an exact number',
  `reroll_type_id` tinyint(3) unsigned DEFAULT NULL,
  `roll_value` tinyint(5) unsigned DEFAULT NULL,
  `roll_lookup_id` tinyint(3) unsigned DEFAULT NULL COMMENT 'the result of a roll, eg something on bb_lkp_weather or another bb_lkp_table',
  `outcome_id` tinyint(3) unsigned DEFAULT NULL,
  `raw_text` varchar(4000) DEFAULT NULL,
  `modifier_text` varchar(200) DEFAULT NULL,
  PRIMARY KEY (`matchlog_id`),
  KEY `ix_match_id` (`match_id`),
  KEY `ix_team_id` (`team_id`),
  KEY `ix_player_id` (`player_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `bb_match_competition`
--

CREATE TABLE IF NOT EXISTS `bb_match_competition` (
  `match_id` int(10) unsigned NOT NULL,
  `domain_id` smallint(5) unsigned NOT NULL DEFAULT '2',
  `competition_id` smallint(5) unsigned NOT NULL,
  `default_competition` bit(1) NOT NULL DEFAULT b'0',
  UNIQUE KEY `match_id_competition_id` (`match_id`,`competition_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `bb_match_team_stats`
--

CREATE TABLE IF NOT EXISTS `bb_match_team_stats` (
  `match_id` int(10) unsigned NOT NULL,
  `team_id` mediumint(8) unsigned NOT NULL,
  `cash_earned` mediumint(8) NOT NULL,
  `possession` tinyint(3) unsigned NOT NULL,
  `occupation_own` tinyint(3) unsigned NOT NULL,
  `occupation_their` tinyint(3) unsigned NOT NULL,
  `passes` tinyint(3) unsigned NOT NULL,
  `catches` tinyint(3) unsigned NOT NULL,
  `interceptions` tinyint(3) unsigned NOT NULL,
  `inflicted_knockdown` tinyint(3) unsigned NOT NULL COMMENT 'Successful armour breaks from blocks?',
  `inflicted_tackles` tinyint(3) unsigned NOT NULL,
  `inflicted_ko` tinyint(3) unsigned NOT NULL,
  `inflicted_stun` tinyint(3) unsigned DEFAULT NULL,
  `inflicted_injury` tinyint(3) unsigned NOT NULL,
  `inflicted_dead` tinyint(3) unsigned NOT NULL,
  `meters_run` smallint(5) NOT NULL,
  `meters_pass` smallint(5) NOT NULL,
  `block_success` smallint(4) unsigned DEFAULT NULL,
  `block_attempt` smallint(4) unsigned DEFAULT NULL,
  `dodge_count` smallint(4) unsigned DEFAULT NULL,
  `team_value` mediumint(6) unsigned DEFAULT NULL,
  `rerolls` tinyint(3) unsigned DEFAULT NULL,
  PRIMARY KEY (`match_id`,`team_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `bb_permission`
--

CREATE TABLE IF NOT EXISTS `bb_permission` (
  `permission_id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `description` varchar(200) NOT NULL,
  PRIMARY KEY (`permission_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `bb_player`
--

CREATE TABLE IF NOT EXISTS `bb_player` (
  `player_id` bigint(15) unsigned NOT NULL AUTO_INCREMENT,
  `bb1_id` int(10) unsigned DEFAULT NULL,
  `description` varchar(100) NOT NULL,
  `team_id` mediumint(8) unsigned NOT NULL,
  `race_id` tinyint(3) unsigned NOT NULL,
  `player_type_id` mediumint(5) unsigned NOT NULL,
  `mv` tinyint(3) unsigned NOT NULL,
  `st` tinyint(3) unsigned NOT NULL,
  `ag` tinyint(3) unsigned NOT NULL,
  `av` tinyint(3) unsigned NOT NULL,
  `level` tinyint(3) unsigned NOT NULL DEFAULT '1',
  `experience` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `base_value` mediumint(6) unsigned NOT NULL,
  `current_value` mediumint(6) unsigned NOT NULL,
  `squad_number` tinyint(3) unsigned NOT NULL,
  `player_status_id` tinyint(3) unsigned NOT NULL DEFAULT '1',
  `match_played` smallint(5) unsigned NOT NULL DEFAULT '0',
  `match_participated` smallint(5) unsigned NOT NULL DEFAULT '0',
  `mvp` smallint(5) unsigned NOT NULL DEFAULT '0',
  `passes` smallint(5) unsigned NOT NULL DEFAULT '0',
  `catches` smallint(5) unsigned NOT NULL DEFAULT '0',
  `interceptions` smallint(5) unsigned NOT NULL DEFAULT '0',
  `touchdowns` smallint(5) unsigned NOT NULL DEFAULT '0',
  `inflicted_knockdown` smallint(5) unsigned NOT NULL DEFAULT '0',
  `inflicted_tackles` smallint(5) unsigned NOT NULL DEFAULT '0',
  `inflicted_ko` smallint(5) unsigned NOT NULL DEFAULT '0',
  `inflicted_stun` smallint(5) unsigned NOT NULL DEFAULT '0',
  `inflicted_injury` smallint(5) unsigned NOT NULL DEFAULT '0',
  `inflicted_dead` smallint(5) unsigned NOT NULL DEFAULT '0',
  `meters_run` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `meters_pass` smallint(5) unsigned NOT NULL DEFAULT '0',
  `sustained_interception` smallint(5) unsigned NOT NULL DEFAULT '0',
  `sustained_knockdown` smallint(5) unsigned NOT NULL DEFAULT '0',
  `sustained_tackles` smallint(5) unsigned NOT NULL DEFAULT '0',
  `sustained_ko` smallint(5) unsigned NOT NULL DEFAULT '0',
  `sustained_stun` smallint(5) unsigned NOT NULL DEFAULT '0',
  `sustained_injury` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `sustained_dead` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `blocks_attempted` smallint(5) unsigned DEFAULT NULL,
  `dodges_made` smallint(5) unsigned DEFAULT NULL,
  `elfball_points` mediumint(6) unsigned NOT NULL COMMENT 'Points awarded for pass, dodge, catch etc',
  `bash_points` mediumint(6) unsigned NOT NULL COMMENT 'Points awarded for blocks, injuries etc. inflicted',
  `punching_bag_points` mediumint(6) unsigned NOT NULL COMMENT 'Points awarded for blocks, injuries etc. sustained',
  PRIMARY KEY (`player_id`),
  KEY `bb1_id` (`bb1_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `bb_player_award`
--

CREATE TABLE IF NOT EXISTS `bb_player_award` (
  `player_award_id` int(11) NOT NULL AUTO_INCREMENT,
  `award_id` int(8) unsigned NOT NULL,
  `player_id` bigint(15) unsigned NOT NULL,
  `awarded_datetime` datetime DEFAULT NULL,
  `domain_id` smallint(5) unsigned NOT NULL,
  `competition_id` smallint(5) unsigned DEFAULT NULL,
  `shared` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`player_award_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `bb_player_casualty`
--

CREATE TABLE IF NOT EXISTS `bb_player_casualty` (
  `player_id` bigint(15) unsigned NOT NULL,
  `casualty_id` tinyint(3) unsigned NOT NULL,
  `casualty_status_id` tinyint(3) unsigned NOT NULL,
  `match_id_sustained` int(10) unsigned DEFAULT NULL,
  `match_id_missed` int(10) unsigned DEFAULT NULL,
  `active` bit(1) NOT NULL DEFAULT b'0'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `bb_player_match_stats`
--

CREATE TABLE IF NOT EXISTS `bb_player_match_stats` (
  `player_id` bigint(15) unsigned NOT NULL DEFAULT '0',
  `match_id` int(10) unsigned NOT NULL,
  `oneoff_id` int(10) unsigned NOT NULL DEFAULT '0',
  `player_type_id` smallint(5) unsigned NOT NULL DEFAULT '0',
  `player_status_id` tinyint(3) unsigned NOT NULL DEFAULT '1',
  `match_played` tinyint(3) unsigned NOT NULL,
  `match_participated` tinyint(3) unsigned DEFAULT NULL COMMENT 'As fara s we can tell, did the player take to the field?',
  `mvp` tinyint(3) unsigned NOT NULL,
  `passes` tinyint(3) unsigned NOT NULL,
  `catches` tinyint(3) unsigned NOT NULL,
  `interceptions` tinyint(3) unsigned NOT NULL,
  `touchdowns` tinyint(3) unsigned NOT NULL,
  `inflicted_knockdown` tinyint(3) unsigned NOT NULL,
  `inflicted_tackles` tinyint(3) unsigned NOT NULL,
  `inflicted_ko` tinyint(3) unsigned NOT NULL,
  `inflicted_stun` tinyint(3) unsigned NOT NULL,
  `inflicted_injury` tinyint(3) unsigned NOT NULL,
  `inflicted_dead` tinyint(3) unsigned NOT NULL,
  `meters_run` smallint(5) NOT NULL,
  `meters_pass` smallint(5) NOT NULL,
  `sustained_interception` tinyint(3) unsigned NOT NULL,
  `sustained_knockdown` tinyint(3) unsigned NOT NULL,
  `sustained_tackles` tinyint(3) unsigned NOT NULL,
  `sustained_ko` tinyint(3) unsigned NOT NULL,
  `sustained_stun` tinyint(3) unsigned NOT NULL,
  `sustained_injury` tinyint(3) unsigned NOT NULL,
  `sustained_dead` tinyint(3) unsigned NOT NULL,
  `blocks_attempted` tinyint(3) unsigned DEFAULT NULL,
  `dodges_made` tinyint(3) unsigned DEFAULT NULL,
  `star_player_stats_updated` bit(1) NOT NULL DEFAULT b'0',
  KEY `match_index` (`match_id`),
  KEY `player` (`player_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `bb_player_oneoff`
--

CREATE TABLE IF NOT EXISTS `bb_player_oneoff` (
  `match_id` int(10) unsigned NOT NULL,
  `team_id` mediumint(8) unsigned NOT NULL,
  `bb1_id` int(10) unsigned NOT NULL,
  `player_type_id` smallint(5) unsigned NOT NULL,
  `player_status_id` tinyint(3) unsigned NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `mv` tinyint(2) unsigned DEFAULT NULL,
  `st` tinyint(2) unsigned DEFAULT NULL,
  `ag` tinyint(2) unsigned DEFAULT NULL,
  `av` tinyint(2) unsigned DEFAULT NULL,
  `salary` int(8) unsigned DEFAULT NULL,
  `value` int(8) unsigned DEFAULT NULL,
  `squad_number` tinyint(3) unsigned DEFAULT NULL,
  PRIMARY KEY (`match_id`,`team_id`,`bb1_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `bb_player_oneoff_skill`
--

CREATE TABLE IF NOT EXISTS `bb_player_oneoff_skill` (
  `match_id` int(10) unsigned NOT NULL,
  `team_id` mediumint(8) unsigned NOT NULL,
  `bb1_id` int(10) unsigned NOT NULL,
  `skill_id` tinyint(3) unsigned NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `bb_player_skill`
--

CREATE TABLE IF NOT EXISTS `bb_player_skill` (
  `player_id` smallint(5) unsigned NOT NULL,
  `skill_id` tinyint(3) unsigned NOT NULL,
  `skill_order` tinyint(3) DEFAULT NULL,
  `match_id_debut` int(10) unsigned DEFAULT NULL,
  KEY `almost_primary` (`player_id`,`skill_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `bb_role`
--

CREATE TABLE IF NOT EXISTS `bb_role` (
  `role_id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `description` varchar(200) NOT NULL,
  PRIMARY KEY (`role_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `bb_role_permission`
--

CREATE TABLE IF NOT EXISTS `bb_role_permission` (
  `role_id` smallint(5) unsigned NOT NULL,
  `permission_id` smallint(5) unsigned NOT NULL,
  PRIMARY KEY (`role_id`,`permission_id`),
  KEY `permission_id` (`permission_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `bb_stat_comp_table`
--

CREATE TABLE IF NOT EXISTS `bb_stat_comp_table` (
  `competition_id` smallint(5) unsigned NOT NULL,
  `domain_id` smallint(5) unsigned NOT NULL,
  `group_id` smallint(5) unsigned NOT NULL,
  `table_name` varchar(100) NOT NULL,
  `scoring_system_id` tinyint(3) unsigned NOT NULL DEFAULT '1',
  UNIQUE KEY `uniq` (`competition_id`,`domain_id`,`group_id`),
  KEY `fk_scoring_system` (`scoring_system_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `bb_stat_comp_table_rank`
--

CREATE TABLE IF NOT EXISTS `bb_stat_comp_table_rank` (
  `domain_id` smallint(5) unsigned NOT NULL,
  `competition_id` smallint(5) unsigned NOT NULL,
  `group_id` smallint(5) unsigned NOT NULL,
  `rank` smallint(5) unsigned NOT NULL,
  `team_id` mediumint(8) unsigned NOT NULL,
  `played` smallint(5) unsigned NOT NULL,
  `wins` smallint(5) unsigned NOT NULL,
  `draws` smallint(5) unsigned NOT NULL,
  `losses` smallint(5) unsigned NOT NULL,
  `touchdown_scored` smallint(5) unsigned NOT NULL,
  `touchdown_conceded` smallint(5) unsigned NOT NULL,
  `touchdown_diff` smallint(5) NOT NULL,
  `passes` smallint(5) unsigned NOT NULL,
  `casualties` smallint(5) unsigned NOT NULL,
  `points` mediumint(8) NOT NULL DEFAULT '0',
  `casualties_sustained` smallint(5) unsigned DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `bb_stat_star_player`
--

CREATE TABLE IF NOT EXISTS `bb_stat_star_player` (
  `domain_id` smallint(5) unsigned NOT NULL,
  `player_type_id` mediumint(5) unsigned NOT NULL,
  `level` tinyint(3) unsigned NOT NULL DEFAULT '1',
  `experience` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `match_played` smallint(5) unsigned NOT NULL DEFAULT '0',
  `match_participated` smallint(5) unsigned NOT NULL DEFAULT '0',
  `mvp` smallint(5) unsigned NOT NULL DEFAULT '0',
  `passes` smallint(5) unsigned NOT NULL DEFAULT '0',
  `catches` smallint(5) unsigned NOT NULL DEFAULT '0',
  `interceptions` smallint(5) unsigned NOT NULL DEFAULT '0',
  `touchdowns` smallint(5) unsigned NOT NULL DEFAULT '0',
  `inflicted_knockdown` smallint(5) unsigned NOT NULL DEFAULT '0',
  `inflicted_tackles` smallint(5) unsigned NOT NULL DEFAULT '0',
  `inflicted_ko` smallint(5) unsigned NOT NULL DEFAULT '0',
  `inflicted_stun` smallint(5) unsigned NOT NULL DEFAULT '0',
  `inflicted_injury` smallint(5) unsigned NOT NULL DEFAULT '0',
  `inflicted_dead` smallint(5) unsigned NOT NULL DEFAULT '0',
  `meters_run` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `meters_pass` smallint(5) unsigned NOT NULL DEFAULT '0',
  `sustained_interception` smallint(5) unsigned NOT NULL DEFAULT '0',
  `sustained_knockdown` smallint(5) unsigned NOT NULL DEFAULT '0',
  `sustained_tackles` smallint(5) unsigned NOT NULL DEFAULT '0',
  `sustained_ko` smallint(5) unsigned NOT NULL DEFAULT '0',
  `sustained_stun` smallint(5) unsigned NOT NULL DEFAULT '0',
  `sustained_injury` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `sustained_dead` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `blocks_attempted` smallint(5) unsigned DEFAULT NULL,
  `dodges_made` smallint(5) unsigned DEFAULT NULL,
  `elfball_points` mediumint(6) unsigned NOT NULL COMMENT 'Points awarded for pass, dodge, catch etc',
  `bash_points` mediumint(6) unsigned NOT NULL COMMENT 'Points awarded for blocks, injuries etc. inflicted',
  `punching_bag_points` mediumint(6) unsigned NOT NULL COMMENT 'Points awarded for blocks, injuries etc. sustained',
  PRIMARY KEY (`domain_id`,`player_type_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `bb_stat_table_player_competition_rank`
--

CREATE TABLE IF NOT EXISTS `bb_stat_table_player_competition_rank` (
  `table_id` smallint(5) unsigned NOT NULL,
  `competition_id` smallint(5) unsigned NOT NULL,
  `domain_id` smallint(5) unsigned NOT NULL,
  `rank` mediumint(5) NOT NULL,
  `player_id` bigint(15) NOT NULL,
  `score` mediumint(6) NOT NULL,
  `order_no` smallint(5) NOT NULL COMMENT 'if tied o rank, what order?',
  PRIMARY KEY (`domain_id`,`competition_id`,`table_id`,`order_no`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `bb_stat_table_team_competition_rank`
--

CREATE TABLE IF NOT EXISTS `bb_stat_table_team_competition_rank` (
  `table_id` smallint(5) unsigned NOT NULL,
  `competition_id` smallint(5) unsigned NOT NULL,
  `domain_id` smallint(5) unsigned NOT NULL,
  `rank` mediumint(5) NOT NULL,
  `team_id` mediumint(8) unsigned NOT NULL,
  `score` mediumint(6) NOT NULL,
  `order_no` smallint(5) NOT NULL COMMENT 'if tied o rank, what order?',
  PRIMARY KEY (`domain_id`,`competition_id`,`table_id`,`order_no`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `bb_stat_turnovers`
--

CREATE TABLE IF NOT EXISTS `bb_stat_turnovers` (
  `match_id` int(10) unsigned NOT NULL,
  `team_id` mediumint(8) unsigned NOT NULL,
  `player_id` bigint(15) unsigned DEFAULT NULL,
  `predicted_turn_no` tinyint(3) unsigned DEFAULT NULL,
  `matchlog_turnover_id` bigint(15) unsigned DEFAULT NULL,
  `turnover_type_id` tinyint(3) unsigned NOT NULL,
  `roll_type_id` smallint(5) unsigned DEFAULT NULL,
  `reroll_type_id` tinyint(3) unsigned DEFAULT NULL,
  `roll_value` tinyint(5) unsigned DEFAULT NULL,
  `prev_roll_value` tinyint(5) unsigned DEFAULT NULL,
  `roll_target` tinyint(2) unsigned DEFAULT NULL,
  `roll_target_exact_flag` bit(1) DEFAULT NULL,
  `prev_roll_type_id` smallint(5) unsigned DEFAULT NULL,
  `prev_reroll_type_id` tinyint(3) unsigned DEFAULT NULL,
  KEY `match_id` (`match_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='All endings of turns (that we can figure out)';

-- --------------------------------------------------------

--
-- Table structure for table `bb_table_player_competition`
--

CREATE TABLE IF NOT EXISTS `bb_table_player_competition` (
  `table_id` smallint(5) unsigned NOT NULL,
  `competition_id` smallint(5) unsigned NOT NULL,
  `domain_id` smallint(5) unsigned NOT NULL,
  `chart_size` mediumint(5) unsigned DEFAULT NULL,
  `description` varchar(250) DEFAULT NULL,
  `featured_chart_no` tinyint(3) unsigned DEFAULT NULL,
  `order_no` tinyint(3) unsigned DEFAULT NULL,
  PRIMARY KEY (`domain_id`,`competition_id`,`table_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `bb_table_team_competition`
--

CREATE TABLE IF NOT EXISTS `bb_table_team_competition` (
  `table_id` smallint(5) unsigned NOT NULL,
  `competition_id` smallint(5) unsigned NOT NULL,
  `domain_id` smallint(5) unsigned NOT NULL,
  `chart_size` mediumint(5) unsigned DEFAULT NULL,
  `description` varchar(250) DEFAULT NULL,
  `featured_chart_no` tinyint(3) unsigned DEFAULT NULL,
  `order_no` tinyint(3) unsigned DEFAULT NULL,
  KEY `competition_id` (`competition_id`,`domain_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `bb_team`
--

CREATE TABLE IF NOT EXISTS `bb_team` (
  `team_id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `bb1_id` int(8) unsigned NOT NULL,
  `description` varchar(100) NOT NULL,
  `race_id` tinyint(3) unsigned NOT NULL,
  `str_logo` varchar(100) DEFAULT NULL,
  `motto` varchar(300) DEFAULT NULL,
  `background` varchar(3000) DEFAULT NULL,
  `value` smallint(5) unsigned NOT NULL,
  `rerolls` tinyint(2) unsigned NOT NULL,
  `coach_id` smallint(5) unsigned DEFAULT NULL,
  `fan_factor` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `cheerleaders` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `apothecary` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `balms` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `cash` mediumint(6) unsigned NOT NULL DEFAULT '0',
  `assistant_coaches` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `match_played` smallint(5) unsigned NOT NULL,
  `mvp` smallint(5) unsigned NOT NULL,
  `passes` smallint(5) unsigned NOT NULL,
  `catches` smallint(5) unsigned NOT NULL,
  `interceptions` smallint(5) unsigned NOT NULL,
  `touchdowns` smallint(5) unsigned NOT NULL,
  `inflicted_knockdown` mediumint(7) unsigned NOT NULL,
  `inflicted_tackles` mediumint(7) unsigned NOT NULL,
  `inflicted_ko` mediumint(7) unsigned NOT NULL,
  `inflicted_stun` mediumint(7) unsigned DEFAULT NULL,
  `inflicted_injury` mediumint(7) unsigned NOT NULL,
  `inflicted_dead` smallint(5) unsigned NOT NULL,
  `meters_run` mediumint(7) unsigned NOT NULL,
  `meters_pass` mediumint(7) unsigned NOT NULL,
  `sustained_touchdowns` smallint(5) unsigned NOT NULL,
  `sustained_interception` smallint(5) unsigned NOT NULL,
  `sustained_knockdown` mediumint(7) unsigned NOT NULL,
  `sustained_tackles` smallint(5) unsigned NOT NULL,
  `sustained_ko` mediumint(7) unsigned NOT NULL,
  `sustained_stun` mediumint(7) unsigned DEFAULT NULL,
  `sustained_injury` smallint(5) unsigned NOT NULL,
  `sustained_dead` smallint(5) unsigned NOT NULL,
  `sustained_meters_run` mediumint(7) unsigned NOT NULL,
  `sustained_meters_pass` mediumint(7) unsigned NOT NULL,
  `wins` smallint(5) unsigned NOT NULL,
  `draws` smallint(5) unsigned NOT NULL,
  `losses` smallint(5) unsigned NOT NULL,
  `avg_possession` tinyint(3) unsigned NOT NULL,
  `avg_occupation_own` tinyint(3) unsigned NOT NULL,
  `avg_occupation_their` tinyint(3) unsigned NOT NULL,
  `total_cash` mediumint(7) unsigned NOT NULL,
  `total_spectators` mediumint(7) unsigned NOT NULL,
  PRIMARY KEY (`team_id`),
  UNIQUE KEY `bb1_id` (`bb1_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `bb_upload`
--

CREATE TABLE IF NOT EXISTS `bb_upload` (
  `upload_id` int(8) unsigned NOT NULL AUTO_INCREMENT,
  `match_id` int(8) unsigned DEFAULT NULL,
  `comment` varchar(500) DEFAULT NULL,
  `upload_started` datetime DEFAULT NULL,
  `upload_date` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `filename` varchar(500) NOT NULL,
  `transformed` tinyint(1) NOT NULL DEFAULT '0',
  `domain_id` smallint(5) unsigned NOT NULL,
  `default_competition_id` smallint(5) unsigned DEFAULT NULL,
  `staging_load_complete` datetime DEFAULT NULL,
  `user_id` mediumint(7) unsigned DEFAULT NULL,
  `upload_completed` datetime DEFAULT NULL,
  `extra_stuff_completed` datetime DEFAULT NULL,
  PRIMARY KEY (`upload_id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `bb_user`
--

CREATE TABLE IF NOT EXISTS `bb_user` (
  `user_id` mediumint(7) unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(64) NOT NULL,
  `pword_hash` varchar(255) DEFAULT NULL,
  `email_address` varchar(255) DEFAULT NULL,
  `hash_scheme` tinyint(3) unsigned NOT NULL DEFAULT '5',
  `user_activation_status_id` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `last_login` datetime DEFAULT NULL,
  `last_login_session_id` varchar(255) DEFAULT NULL,
  `default_domain_id` smallint(5) unsigned NOT NULL DEFAULT '2',
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `unique_username` (`username`),
  KEY `default_domain_id` (`default_domain_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `bb_user_role`
--

CREATE TABLE IF NOT EXISTS `bb_user_role` (
  `user_id` mediumint(7) unsigned NOT NULL,
  `role_id` smallint(5) unsigned NOT NULL,
  PRIMARY KEY (`user_id`,`role_id`),
  KEY `role_id` (`role_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `staging_Away_Player_Casualties`
--

CREATE TABLE IF NOT EXISTS `staging_Away_Player_Casualties` (
  `upload_id` int(11) DEFAULT NULL,
  `ID` int(11) DEFAULT NULL,
  `idPlayer_Listing` int(11) DEFAULT NULL,
  `idPlayer_Casualty_Types` int(11) DEFAULT NULL,
  KEY `upload_id` (`upload_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `staging_Away_Player_Listing`
--

CREATE TABLE IF NOT EXISTS `staging_Away_Player_Listing` (
  `upload_id` int(11) NOT NULL,
  `ID` int(11) NOT NULL,
  `idPlayer_Names` int(11) DEFAULT NULL,
  `strName` varchar(255) DEFAULT NULL,
  `idPlayer_Types` int(11) DEFAULT NULL,
  `idTeam_Listing` int(11) DEFAULT NULL,
  `idTeam_Listing_Previous` int(11) DEFAULT NULL,
  `idRaces` int(11) DEFAULT NULL,
  `iPlayerColor` int(11) DEFAULT NULL,
  `iSkinScalePercent` int(11) NOT NULL,
  `iSkinMeshVariant` int(11) NOT NULL,
  `iSkinTextureVariant` int(11) NOT NULL,
  `fAgeing` double DEFAULT NULL,
  `iNumber` int(11) DEFAULT NULL,
  `Characteristics_fMovementAllowance` double DEFAULT NULL,
  `Characteristics_fStrength` double DEFAULT NULL,
  `Characteristics_fAgility` double DEFAULT NULL,
  `Characteristics_fArmourValue` double DEFAULT NULL,
  `idPlayer_Levels` int(11) DEFAULT NULL,
  `iExperience` int(11) DEFAULT NULL,
  `idEquipment_Listing_Helmet` int(11) DEFAULT NULL,
  `idEquipment_Listing_Pauldron` int(11) DEFAULT NULL,
  `idEquipment_Listing_Gauntlet` int(11) DEFAULT NULL,
  `idEquipment_Listing_Boot` int(11) DEFAULT NULL,
  `Durability_iHelmet` int(11) DEFAULT NULL,
  `Durability_iPauldron` int(11) DEFAULT NULL,
  `Durability_iGauntlet` int(11) DEFAULT NULL,
  `Durability_iBoot` int(11) DEFAULT NULL,
  `iSalary` int(11) DEFAULT NULL,
  `Contract_iDuration` int(11) DEFAULT NULL,
  `Contract_iSeasonRemaining` int(11) DEFAULT NULL,
  `idNegotiation_Condition_Types` int(11) DEFAULT NULL,
  `Negotiation_iRemainingTries` int(11) DEFAULT NULL,
  `Negotiation_iConditionDemand` int(11) DEFAULT NULL,
  `iValue` int(11) DEFAULT NULL,
  `iMatchSuspended` int(11) DEFAULT NULL,
  `iNbLevelsUp` int(11) DEFAULT NULL,
  `LevelUp_iRollResult` int(11) DEFAULT NULL,
  `LevelUp_iRollResult2` int(11) DEFAULT NULL,
  `LevelUp_bDouble` int(11) DEFAULT NULL,
  `bGenerated` int(11) DEFAULT NULL,
  `bStar` int(11) DEFAULT NULL,
  `bEdited` int(11) DEFAULT NULL,
  `bDead` int(11) DEFAULT NULL,
  `strLevelUp` varchar(255) DEFAULT NULL,
  UNIQUE KEY `ix_uploadid_ID` (`upload_id`,`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `staging_Away_Player_Skills`
--

CREATE TABLE IF NOT EXISTS `staging_Away_Player_Skills` (
  `upload_id` int(11) NOT NULL,
  `ID` int(11) NOT NULL,
  `idPlayer_Listing` int(11) DEFAULT NULL,
  `idSkill_Listing` int(11) DEFAULT NULL,
  KEY `upload_id` (`upload_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `staging_Away_Player_Types`
--

CREATE TABLE IF NOT EXISTS `staging_Away_Player_Types` (
  `upload_id` int(11) NOT NULL,
  `ID` int(11) NOT NULL,
  `DATA_CONSTANT` varchar(255) DEFAULT NULL,
  `idRaces` int(11) DEFAULT NULL,
  `idPlayer_BaseTypes` int(11) DEFAULT NULL,
  `idPlayer_Name_Types` int(11) DEFAULT NULL,
  `idStrings_Localized` int(11) DEFAULT NULL,
  `strName` varchar(255) DEFAULT NULL,
  `Characteristics_fMovementAllowance` double DEFAULT NULL,
  `Characteristics_fStrength` double DEFAULT NULL,
  `Characteristics_fAgility` double DEFAULT NULL,
  `Characteristics_fArmourValue` double DEFAULT NULL,
  `iPrice` int(11) DEFAULT NULL,
  `iMaxQuantity` int(11) DEFAULT NULL,
  KEY `upload_id` (`upload_id`),
  KEY `ID` (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `staging_Away_Player_Type_Skills`
--

CREATE TABLE IF NOT EXISTS `staging_Away_Player_Type_Skills` (
  `upload_id` int(11) NOT NULL,
  `ID` int(11) NOT NULL,
  `idPlayer_Types` int(11) DEFAULT NULL,
  `idSkill_Listing` int(11) DEFAULT NULL,
  `DESCRIPTION` varchar(255) DEFAULT NULL,
  KEY `upload_id` (`upload_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `staging_Away_Player_Type_Skill_Categories_Double`
--

CREATE TABLE IF NOT EXISTS `staging_Away_Player_Type_Skill_Categories_Double` (
  `upload_id` int(11) NOT NULL,
  `ID` int(11) NOT NULL,
  `idPlayer_Types` int(11) DEFAULT NULL,
  `idSkill_Categories` int(11) DEFAULT NULL,
  `DESCRIPTION` varchar(255) DEFAULT NULL,
  KEY `upload_id` (`upload_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `staging_Away_Player_Type_Skill_Categories_Normal`
--

CREATE TABLE IF NOT EXISTS `staging_Away_Player_Type_Skill_Categories_Normal` (
  `upload_id` int(11) NOT NULL,
  `ID` int(11) NOT NULL,
  `idPlayer_Types` int(11) DEFAULT NULL,
  `idSkill_Categories` int(11) DEFAULT NULL,
  `DESCRIPTION` varchar(255) DEFAULT NULL,
  KEY `upload_id` (`upload_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `staging_Away_Races`
--

CREATE TABLE IF NOT EXISTS `staging_Away_Races` (
  `upload_id` int(11) NOT NULL,
  `ID` int(11) NOT NULL,
  `DATA_CONSTANT` varchar(255) DEFAULT NULL,
  `idStrings_Localized` int(11) DEFAULT NULL,
  `idStrings_Localized_Info` int(11) DEFAULT NULL,
  `strName` varchar(255) NOT NULL,
  `iRerollPrice` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `staging_Away_Statistics_Players`
--

CREATE TABLE IF NOT EXISTS `staging_Away_Statistics_Players` (
  `upload_id` int(11) NOT NULL,
  `ID` int(11) NOT NULL,
  `idPlayer_Listing` int(11) DEFAULT NULL,
  `iMatchPlayed` int(11) DEFAULT NULL,
  `iMVP` int(11) DEFAULT NULL,
  `Inflicted_iPasses` int(11) DEFAULT NULL,
  `Inflicted_iCatches` int(11) DEFAULT NULL,
  `Inflicted_iInterceptions` int(11) DEFAULT NULL,
  `Inflicted_iTouchdowns` int(11) DEFAULT NULL,
  `Inflicted_iCasualties` int(11) DEFAULT NULL,
  `Inflicted_iTackles` int(11) DEFAULT NULL,
  `Inflicted_iKO` int(11) DEFAULT NULL,
  `Inflicted_iStuns` int(11) DEFAULT NULL,
  `Inflicted_iInjuries` int(11) DEFAULT NULL,
  `Inflicted_iDead` int(11) DEFAULT NULL,
  `Inflicted_iMetersRunning` int(11) DEFAULT NULL,
  `Inflicted_iMetersPassing` int(11) DEFAULT NULL,
  `Sustained_iInterceptions` int(11) DEFAULT NULL,
  `Sustained_iCasualties` int(11) DEFAULT NULL,
  `Sustained_iTackles` int(11) DEFAULT NULL,
  `Sustained_iKO` int(11) DEFAULT NULL,
  `Sustained_iStuns` int(11) DEFAULT NULL,
  `Sustained_iInjuries` int(11) DEFAULT NULL,
  `Sustained_iDead` int(11) DEFAULT NULL,
  UNIQUE KEY `ix_upload_id_ID` (`upload_id`,`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `staging_Away_Statistics_Season_Players`
--

CREATE TABLE IF NOT EXISTS `staging_Away_Statistics_Season_Players` (
  `upload_id` int(11) NOT NULL,
  `ID` int(11) NOT NULL,
  `idPlayer_Listing` int(11) DEFAULT NULL,
  `iSeason` int(11) DEFAULT NULL,
  `iMatchPlayed` int(11) DEFAULT NULL,
  `iMVP` int(11) DEFAULT NULL,
  `Inflicted_iPasses` int(11) DEFAULT NULL,
  `Inflicted_iCatches` int(11) DEFAULT NULL,
  `Inflicted_iInterceptions` int(11) DEFAULT NULL,
  `Inflicted_iTouchdowns` int(11) DEFAULT NULL,
  `Inflicted_iCasualties` int(11) DEFAULT NULL,
  `Inflicted_iTackles` int(11) DEFAULT NULL,
  `Inflicted_iKO` int(11) DEFAULT NULL,
  `Inflicted_iStuns` int(11) DEFAULT NULL,
  `Inflicted_iInjuries` int(11) DEFAULT NULL,
  `Inflicted_iDead` int(11) DEFAULT NULL,
  `Inflicted_iMetersRunning` int(11) DEFAULT NULL,
  `Inflicted_iMetersPassing` int(11) DEFAULT NULL,
  `Sustained_iInterceptions` int(11) DEFAULT NULL,
  `Sustained_iCasualties` int(11) DEFAULT NULL,
  `Sustained_iTackles` int(11) DEFAULT NULL,
  `Sustained_iKO` int(11) DEFAULT NULL,
  `Sustained_iStuns` int(11) DEFAULT NULL,
  `Sustained_iInjuries` int(11) DEFAULT NULL,
  `Sustained_iDead` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `staging_Away_Statistics_Season_Teams`
--

CREATE TABLE IF NOT EXISTS `staging_Away_Statistics_Season_Teams` (
  `upload_id` int(11) NOT NULL,
  `ID` int(11) NOT NULL,
  `idTeam_Listing` int(11) DEFAULT NULL,
  `iSeason` int(11) DEFAULT NULL,
  `iMatchPlayed` int(11) DEFAULT NULL,
  `iMVP` int(11) DEFAULT NULL,
  `Inflicted_iPasses` int(11) DEFAULT NULL,
  `Inflicted_iCatches` int(11) DEFAULT NULL,
  `Inflicted_iInterceptions` int(11) DEFAULT NULL,
  `Inflicted_iTouchdowns` int(11) DEFAULT NULL,
  `Inflicted_iCasualties` int(11) DEFAULT NULL,
  `Inflicted_iTackles` int(11) DEFAULT NULL,
  `Inflicted_iKO` int(11) DEFAULT NULL,
  `Inflicted_iInjuries` int(11) DEFAULT NULL,
  `Inflicted_iDead` int(11) DEFAULT NULL,
  `Inflicted_iMetersRunning` int(11) DEFAULT NULL,
  `Inflicted_iMetersPassing` int(11) DEFAULT NULL,
  `Sustained_iPasses` int(11) DEFAULT NULL,
  `Sustained_iCatches` int(11) DEFAULT NULL,
  `Sustained_iInterceptions` int(11) DEFAULT NULL,
  `Sustained_iTouchdowns` int(11) DEFAULT NULL,
  `Sustained_iCasualties` int(11) DEFAULT NULL,
  `Sustained_iTackles` int(11) DEFAULT NULL,
  `Sustained_iKO` int(11) DEFAULT NULL,
  `Sustained_iInjuries` int(11) DEFAULT NULL,
  `Sustained_iDead` int(11) DEFAULT NULL,
  `Sustained_iMetersRunning` int(11) DEFAULT NULL,
  `Sustained_iMetersPassing` int(11) DEFAULT NULL,
  `iPoints` int(11) DEFAULT NULL,
  `iWins` int(11) DEFAULT NULL,
  `iDraws` int(11) DEFAULT NULL,
  `iLoss` int(11) DEFAULT NULL,
  `iBestMatchRating` int(11) DEFAULT NULL,
  `Average_iMatchRating` int(11) DEFAULT NULL,
  `Average_iSpectators` int(11) DEFAULT NULL,
  `Average_iCashEarned` int(11) DEFAULT NULL,
  `iSpectators` int(11) DEFAULT NULL,
  `iCashEarned` int(11) DEFAULT NULL,
  `iPossessionBall` int(11) DEFAULT NULL,
  `Occupation_iOwn` int(11) DEFAULT NULL,
  `Occupation_iTheir` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `staging_Away_Statistics_Teams`
--

CREATE TABLE IF NOT EXISTS `staging_Away_Statistics_Teams` (
  `upload_id` int(11) NOT NULL,
  `ID` int(11) NOT NULL,
  `idTeam_Listing` int(11) DEFAULT NULL,
  `iMatchPlayed` int(11) DEFAULT NULL,
  `iMVP` int(11) DEFAULT NULL,
  `Inflicted_iPasses` int(11) DEFAULT NULL,
  `Inflicted_iCatches` int(11) DEFAULT NULL,
  `Inflicted_iInterceptions` int(11) DEFAULT NULL,
  `Inflicted_iTouchdowns` int(11) DEFAULT NULL,
  `Inflicted_iCasualties` int(11) DEFAULT NULL,
  `Inflicted_iTackles` int(11) DEFAULT NULL,
  `Inflicted_iKO` int(11) DEFAULT NULL,
  `Inflicted_iInjuries` int(11) DEFAULT NULL,
  `Inflicted_iDead` int(11) DEFAULT NULL,
  `Inflicted_iMetersRunning` int(11) DEFAULT NULL,
  `Inflicted_iMetersPassing` int(11) DEFAULT NULL,
  `Sustained_iPasses` int(11) DEFAULT NULL,
  `Sustained_iCatches` int(11) DEFAULT NULL,
  `Sustained_iInterceptions` int(11) DEFAULT NULL,
  `Sustained_iTouchdowns` int(11) DEFAULT NULL,
  `Sustained_iCasualties` int(11) DEFAULT NULL,
  `Sustained_iTackles` int(11) DEFAULT NULL,
  `Sustained_iKO` int(11) DEFAULT NULL,
  `Sustained_iInjuries` int(11) DEFAULT NULL,
  `Sustained_iDead` int(11) DEFAULT NULL,
  `Sustained_iMetersRunning` int(11) DEFAULT NULL,
  `Sustained_iMetersPassing` int(11) DEFAULT NULL,
  `iPoints` int(11) DEFAULT NULL,
  `iWins` int(11) DEFAULT NULL,
  `iDraws` int(11) DEFAULT NULL,
  `iLoss` int(11) DEFAULT NULL,
  `iBestMatchRating` int(11) DEFAULT NULL,
  `Average_iMatchRating` int(11) DEFAULT NULL,
  `Average_iSpectators` int(11) DEFAULT NULL,
  `Average_iCashEarned` int(11) DEFAULT NULL,
  `iSpectators` int(11) DEFAULT NULL,
  `iCashEarned` int(11) DEFAULT NULL,
  `iPossessionBall` int(11) DEFAULT NULL,
  `Occupation_iOwn` int(11) DEFAULT NULL,
  `Occupation_iTheir` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `staging_Away_Team_Listing`
--

CREATE TABLE IF NOT EXISTS `staging_Away_Team_Listing` (
  `upload_id` int(11) NOT NULL,
  `ID` int(11) NOT NULL,
  `strName` varchar(255) NOT NULL,
  `idRaces` int(11) NOT NULL,
  `strLogo` varchar(255) NOT NULL,
  `iTeamColor` int(11) DEFAULT NULL,
  `strLeitmotiv` text,
  `strBackground` text,
  `iValue` int(11) DEFAULT NULL,
  `iPopularity` int(11) DEFAULT NULL,
  `iCash` int(11) DEFAULT NULL,
  `iCheerleaders` int(11) DEFAULT NULL,
  `iBalms` int(11) DEFAULT NULL,
  `bApothecary` int(11) DEFAULT NULL,
  `iRerolls` int(11) DEFAULT NULL,
  `bEdited` int(11) DEFAULT NULL,
  `idTeam_Listing_Filters` int(11) NOT NULL,
  `idStrings_Formatted_Background` int(11) DEFAULT NULL,
  `idStrings_Localized_Leitmotiv` int(11) DEFAULT NULL,
  `iNextPurchase` int(11) DEFAULT NULL,
  `iAssistantCoaches` int(11) DEFAULT NULL,
  UNIQUE KEY `upload_id` (`upload_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `staging_eventlog`
--

CREATE TABLE IF NOT EXISTS `staging_eventlog` (
  `upload_id` int(8) unsigned NOT NULL,
  `line_no` mediumint(6) unsigned NOT NULL,
  `raw_text` varchar(4000) NOT NULL,
  PRIMARY KEY (`upload_id`,`line_no`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `staging_Home_Player_Casualties`
--

CREATE TABLE IF NOT EXISTS `staging_Home_Player_Casualties` (
  `upload_id` int(11) DEFAULT NULL,
  `ID` int(11) DEFAULT NULL,
  `idPlayer_Listing` int(11) DEFAULT NULL,
  `idPlayer_Casualty_Types` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `staging_Home_Player_Listing`
--

CREATE TABLE IF NOT EXISTS `staging_Home_Player_Listing` (
  `upload_id` int(11) NOT NULL,
  `ID` int(11) NOT NULL,
  `idPlayer_Names` int(11) DEFAULT NULL,
  `strName` varchar(255) DEFAULT NULL,
  `idPlayer_Types` int(11) DEFAULT NULL,
  `idTeam_Listing` int(11) DEFAULT NULL,
  `idTeam_Listing_Previous` int(11) DEFAULT NULL,
  `idRaces` int(11) DEFAULT NULL,
  `iPlayerColor` int(11) DEFAULT NULL,
  `iSkinScalePercent` int(11) NOT NULL,
  `iSkinMeshVariant` int(11) NOT NULL,
  `iSkinTextureVariant` int(11) NOT NULL,
  `fAgeing` double DEFAULT NULL,
  `iNumber` int(11) DEFAULT NULL,
  `Characteristics_fMovementAllowance` double DEFAULT NULL,
  `Characteristics_fStrength` double DEFAULT NULL,
  `Characteristics_fAgility` double DEFAULT NULL,
  `Characteristics_fArmourValue` double DEFAULT NULL,
  `idPlayer_Levels` int(11) DEFAULT NULL,
  `iExperience` int(11) DEFAULT NULL,
  `idEquipment_Listing_Helmet` int(11) DEFAULT NULL,
  `idEquipment_Listing_Pauldron` int(11) DEFAULT NULL,
  `idEquipment_Listing_Gauntlet` int(11) DEFAULT NULL,
  `idEquipment_Listing_Boot` int(11) DEFAULT NULL,
  `Durability_iHelmet` int(11) DEFAULT NULL,
  `Durability_iPauldron` int(11) DEFAULT NULL,
  `Durability_iGauntlet` int(11) DEFAULT NULL,
  `Durability_iBoot` int(11) DEFAULT NULL,
  `iSalary` int(11) DEFAULT NULL,
  `Contract_iDuration` int(11) DEFAULT NULL,
  `Contract_iSeasonRemaining` int(11) DEFAULT NULL,
  `idNegotiation_Condition_Types` int(11) DEFAULT NULL,
  `Negotiation_iRemainingTries` int(11) DEFAULT NULL,
  `Negotiation_iConditionDemand` int(11) DEFAULT NULL,
  `iValue` int(11) DEFAULT NULL,
  `iMatchSuspended` int(11) DEFAULT NULL,
  `iNbLevelsUp` int(11) DEFAULT NULL,
  `LevelUp_iRollResult` int(11) DEFAULT NULL,
  `LevelUp_iRollResult2` int(11) DEFAULT NULL,
  `LevelUp_bDouble` int(11) DEFAULT NULL,
  `bGenerated` int(11) DEFAULT NULL,
  `bStar` int(11) DEFAULT NULL,
  `bEdited` int(11) DEFAULT NULL,
  `bDead` int(11) DEFAULT NULL,
  `strLevelUp` varchar(255) DEFAULT NULL,
  UNIQUE KEY `ix_upload_id_ID` (`upload_id`,`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `staging_Home_Player_Skills`
--

CREATE TABLE IF NOT EXISTS `staging_Home_Player_Skills` (
  `upload_id` int(11) NOT NULL,
  `ID` int(11) NOT NULL,
  `idPlayer_Listing` int(11) DEFAULT NULL,
  `idSkill_Listing` int(11) DEFAULT NULL,
  KEY `upload_id` (`upload_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `staging_Home_Player_Types`
--

CREATE TABLE IF NOT EXISTS `staging_Home_Player_Types` (
  `upload_id` int(11) NOT NULL,
  `ID` int(11) NOT NULL,
  `DATA_CONSTANT` varchar(255) DEFAULT NULL,
  `idRaces` int(11) DEFAULT NULL,
  `idPlayer_BaseTypes` int(11) DEFAULT NULL,
  `idPlayer_Name_Types` int(11) DEFAULT NULL,
  `idStrings_Localized` int(11) DEFAULT NULL,
  `strName` varchar(255) DEFAULT NULL,
  `Characteristics_fMovementAllowance` double DEFAULT NULL,
  `Characteristics_fStrength` double DEFAULT NULL,
  `Characteristics_fAgility` double DEFAULT NULL,
  `Characteristics_fArmourValue` double DEFAULT NULL,
  `iPrice` int(11) DEFAULT NULL,
  `iMaxQuantity` int(11) DEFAULT NULL,
  KEY `upload_id` (`upload_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `staging_Home_Player_Type_Skills`
--

CREATE TABLE IF NOT EXISTS `staging_Home_Player_Type_Skills` (
  `upload_id` int(11) NOT NULL,
  `ID` int(11) NOT NULL,
  `idPlayer_Types` int(11) DEFAULT NULL,
  `idSkill_Listing` int(11) DEFAULT NULL,
  `DESCRIPTION` varchar(255) DEFAULT NULL,
  KEY `upload_id` (`upload_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `staging_Home_Player_Type_Skill_Categories_Double`
--

CREATE TABLE IF NOT EXISTS `staging_Home_Player_Type_Skill_Categories_Double` (
  `upload_id` int(11) NOT NULL,
  `ID` int(11) NOT NULL,
  `idPlayer_Types` int(11) DEFAULT NULL,
  `idSkill_Categories` int(11) DEFAULT NULL,
  `DESCRIPTION` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `staging_Home_Player_Type_Skill_Categories_Normal`
--

CREATE TABLE IF NOT EXISTS `staging_Home_Player_Type_Skill_Categories_Normal` (
  `upload_id` int(11) NOT NULL,
  `ID` int(11) NOT NULL,
  `idPlayer_Types` int(11) DEFAULT NULL,
  `idSkill_Categories` int(11) DEFAULT NULL,
  `DESCRIPTION` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `staging_Home_Races`
--

CREATE TABLE IF NOT EXISTS `staging_Home_Races` (
  `upload_id` int(11) NOT NULL,
  `ID` int(11) NOT NULL,
  `DATA_CONSTANT` varchar(255) DEFAULT NULL,
  `idStrings_Localized` int(11) DEFAULT NULL,
  `idStrings_Localized_Info` int(11) DEFAULT NULL,
  `strName` varchar(255) NOT NULL,
  `iRerollPrice` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `staging_Home_Statistics_Players`
--

CREATE TABLE IF NOT EXISTS `staging_Home_Statistics_Players` (
  `upload_id` int(11) NOT NULL,
  `ID` int(11) NOT NULL,
  `idPlayer_Listing` int(11) DEFAULT NULL,
  `iMatchPlayed` int(11) DEFAULT NULL,
  `iMVP` int(11) DEFAULT NULL,
  `Inflicted_iPasses` int(11) DEFAULT NULL,
  `Inflicted_iCatches` int(11) DEFAULT NULL,
  `Inflicted_iInterceptions` int(11) DEFAULT NULL,
  `Inflicted_iTouchdowns` int(11) DEFAULT NULL,
  `Inflicted_iCasualties` int(11) DEFAULT NULL,
  `Inflicted_iTackles` int(11) DEFAULT NULL,
  `Inflicted_iKO` int(11) DEFAULT NULL,
  `Inflicted_iStuns` int(11) DEFAULT NULL,
  `Inflicted_iInjuries` int(11) DEFAULT NULL,
  `Inflicted_iDead` int(11) DEFAULT NULL,
  `Inflicted_iMetersRunning` int(11) DEFAULT NULL,
  `Inflicted_iMetersPassing` int(11) DEFAULT NULL,
  `Sustained_iInterceptions` int(11) DEFAULT NULL,
  `Sustained_iCasualties` int(11) DEFAULT NULL,
  `Sustained_iTackles` int(11) DEFAULT NULL,
  `Sustained_iKO` int(11) DEFAULT NULL,
  `Sustained_iStuns` int(11) DEFAULT NULL,
  `Sustained_iInjuries` int(11) DEFAULT NULL,
  `Sustained_iDead` int(11) DEFAULT NULL,
  UNIQUE KEY `ix_upload_id_ID` (`upload_id`,`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `staging_Home_Statistics_Season_Players`
--

CREATE TABLE IF NOT EXISTS `staging_Home_Statistics_Season_Players` (
  `upload_id` int(11) NOT NULL,
  `ID` int(11) NOT NULL,
  `idPlayer_Listing` int(11) DEFAULT NULL,
  `iSeason` int(11) DEFAULT NULL,
  `iMatchPlayed` int(11) DEFAULT NULL,
  `iMVP` int(11) DEFAULT NULL,
  `Inflicted_iPasses` int(11) DEFAULT NULL,
  `Inflicted_iCatches` int(11) DEFAULT NULL,
  `Inflicted_iInterceptions` int(11) DEFAULT NULL,
  `Inflicted_iTouchdowns` int(11) DEFAULT NULL,
  `Inflicted_iCasualties` int(11) DEFAULT NULL,
  `Inflicted_iTackles` int(11) DEFAULT NULL,
  `Inflicted_iKO` int(11) DEFAULT NULL,
  `Inflicted_iStuns` int(11) DEFAULT NULL,
  `Inflicted_iInjuries` int(11) DEFAULT NULL,
  `Inflicted_iDead` int(11) DEFAULT NULL,
  `Inflicted_iMetersRunning` int(11) DEFAULT NULL,
  `Inflicted_iMetersPassing` int(11) DEFAULT NULL,
  `Sustained_iInterceptions` int(11) DEFAULT NULL,
  `Sustained_iCasualties` int(11) DEFAULT NULL,
  `Sustained_iTackles` int(11) DEFAULT NULL,
  `Sustained_iKO` int(11) DEFAULT NULL,
  `Sustained_iStuns` int(11) DEFAULT NULL,
  `Sustained_iInjuries` int(11) DEFAULT NULL,
  `Sustained_iDead` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `staging_Home_Statistics_Season_Teams`
--

CREATE TABLE IF NOT EXISTS `staging_Home_Statistics_Season_Teams` (
  `upload_id` int(11) NOT NULL,
  `ID` int(11) NOT NULL,
  `idTeam_Listing` int(11) DEFAULT NULL,
  `iSeason` int(11) DEFAULT NULL,
  `iMatchPlayed` int(11) DEFAULT NULL,
  `iMVP` int(11) DEFAULT NULL,
  `Inflicted_iPasses` int(11) DEFAULT NULL,
  `Inflicted_iCatches` int(11) DEFAULT NULL,
  `Inflicted_iInterceptions` int(11) DEFAULT NULL,
  `Inflicted_iTouchdowns` int(11) DEFAULT NULL,
  `Inflicted_iCasualties` int(11) DEFAULT NULL,
  `Inflicted_iTackles` int(11) DEFAULT NULL,
  `Inflicted_iKO` int(11) DEFAULT NULL,
  `Inflicted_iInjuries` int(11) DEFAULT NULL,
  `Inflicted_iDead` int(11) DEFAULT NULL,
  `Inflicted_iMetersRunning` int(11) DEFAULT NULL,
  `Inflicted_iMetersPassing` int(11) DEFAULT NULL,
  `Sustained_iPasses` int(11) DEFAULT NULL,
  `Sustained_iCatches` int(11) DEFAULT NULL,
  `Sustained_iInterceptions` int(11) DEFAULT NULL,
  `Sustained_iTouchdowns` int(11) DEFAULT NULL,
  `Sustained_iCasualties` int(11) DEFAULT NULL,
  `Sustained_iTackles` int(11) DEFAULT NULL,
  `Sustained_iKO` int(11) DEFAULT NULL,
  `Sustained_iInjuries` int(11) DEFAULT NULL,
  `Sustained_iDead` int(11) DEFAULT NULL,
  `Sustained_iMetersRunning` int(11) DEFAULT NULL,
  `Sustained_iMetersPassing` int(11) DEFAULT NULL,
  `iPoints` int(11) DEFAULT NULL,
  `iWins` int(11) DEFAULT NULL,
  `iDraws` int(11) DEFAULT NULL,
  `iLoss` int(11) DEFAULT NULL,
  `iBestMatchRating` int(11) DEFAULT NULL,
  `Average_iMatchRating` int(11) DEFAULT NULL,
  `Average_iSpectators` int(11) DEFAULT NULL,
  `Average_iCashEarned` int(11) DEFAULT NULL,
  `iSpectators` int(11) DEFAULT NULL,
  `iCashEarned` int(11) DEFAULT NULL,
  `iPossessionBall` int(11) DEFAULT NULL,
  `Occupation_iOwn` int(11) DEFAULT NULL,
  `Occupation_iTheir` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `staging_Home_Statistics_Teams`
--

CREATE TABLE IF NOT EXISTS `staging_Home_Statistics_Teams` (
  `upload_id` int(11) NOT NULL,
  `ID` int(11) NOT NULL,
  `idTeam_Listing` int(11) DEFAULT NULL,
  `iMatchPlayed` int(11) DEFAULT NULL,
  `iMVP` int(11) DEFAULT NULL,
  `Inflicted_iPasses` int(11) DEFAULT NULL,
  `Inflicted_iCatches` int(11) DEFAULT NULL,
  `Inflicted_iInterceptions` int(11) DEFAULT NULL,
  `Inflicted_iTouchdowns` int(11) DEFAULT NULL,
  `Inflicted_iCasualties` int(11) DEFAULT NULL,
  `Inflicted_iTackles` int(11) DEFAULT NULL,
  `Inflicted_iKO` int(11) DEFAULT NULL,
  `Inflicted_iInjuries` int(11) DEFAULT NULL,
  `Inflicted_iDead` int(11) DEFAULT NULL,
  `Inflicted_iMetersRunning` int(11) DEFAULT NULL,
  `Inflicted_iMetersPassing` int(11) DEFAULT NULL,
  `Sustained_iPasses` int(11) DEFAULT NULL,
  `Sustained_iCatches` int(11) DEFAULT NULL,
  `Sustained_iInterceptions` int(11) DEFAULT NULL,
  `Sustained_iTouchdowns` int(11) DEFAULT NULL,
  `Sustained_iCasualties` int(11) DEFAULT NULL,
  `Sustained_iTackles` int(11) DEFAULT NULL,
  `Sustained_iKO` int(11) DEFAULT NULL,
  `Sustained_iInjuries` int(11) DEFAULT NULL,
  `Sustained_iDead` int(11) DEFAULT NULL,
  `Sustained_iMetersRunning` int(11) DEFAULT NULL,
  `Sustained_iMetersPassing` int(11) DEFAULT NULL,
  `iPoints` int(11) DEFAULT NULL,
  `iWins` int(11) DEFAULT NULL,
  `iDraws` int(11) DEFAULT NULL,
  `iLoss` int(11) DEFAULT NULL,
  `iBestMatchRating` int(11) DEFAULT NULL,
  `Average_iMatchRating` int(11) DEFAULT NULL,
  `Average_iSpectators` int(11) DEFAULT NULL,
  `Average_iCashEarned` int(11) DEFAULT NULL,
  `iSpectators` int(11) DEFAULT NULL,
  `iCashEarned` int(11) DEFAULT NULL,
  `iPossessionBall` int(11) DEFAULT NULL,
  `Occupation_iOwn` int(11) DEFAULT NULL,
  `Occupation_iTheir` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `staging_Home_Team_Listing`
--

CREATE TABLE IF NOT EXISTS `staging_Home_Team_Listing` (
  `upload_id` int(11) NOT NULL,
  `ID` int(11) NOT NULL,
  `strName` varchar(255) NOT NULL,
  `idRaces` int(11) NOT NULL,
  `strLogo` varchar(255) NOT NULL,
  `iTeamColor` int(11) DEFAULT NULL,
  `strLeitmotiv` text,
  `strBackground` text,
  `iValue` int(11) DEFAULT NULL,
  `iPopularity` int(11) DEFAULT NULL,
  `iCash` int(11) DEFAULT NULL,
  `iCheerleaders` int(11) DEFAULT NULL,
  `iBalms` int(11) DEFAULT NULL,
  `bApothecary` int(11) DEFAULT NULL,
  `iRerolls` int(11) DEFAULT NULL,
  `bEdited` int(11) DEFAULT NULL,
  `idTeam_Listing_Filters` int(11) NOT NULL,
  `idStrings_Formatted_Background` int(11) DEFAULT NULL,
  `idStrings_Localized_Leitmotiv` int(11) DEFAULT NULL,
  `iNextPurchase` int(11) DEFAULT NULL,
  `iAssistantCoaches` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `staging_mr_Away_Player_Casualties`
--

CREATE TABLE IF NOT EXISTS `staging_mr_Away_Player_Casualties` (
  `upload_id` int(11) NOT NULL,
  `ID` int(11) NOT NULL,
  `idPlayer_Listing` int(11) DEFAULT NULL,
  `idPlayer_Casualty_Types` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `staging_mr_Away_Statistics_Players`
--

CREATE TABLE IF NOT EXISTS `staging_mr_Away_Statistics_Players` (
  `upload_id` int(11) NOT NULL,
  `ID` int(11) NOT NULL,
  `idPlayer_Listing` int(11) DEFAULT NULL,
  `iMatchPlayed` int(11) DEFAULT NULL,
  `iMVP` int(11) DEFAULT NULL,
  `Inflicted_iPasses` int(11) DEFAULT NULL,
  `Inflicted_iCatches` int(11) DEFAULT NULL,
  `Inflicted_iInterceptions` int(11) DEFAULT NULL,
  `Inflicted_iTouchdowns` int(11) DEFAULT NULL,
  `Inflicted_iCasualties` int(11) DEFAULT NULL,
  `Inflicted_iTackles` int(11) DEFAULT NULL,
  `Inflicted_iKO` int(11) DEFAULT NULL,
  `Inflicted_iStuns` int(11) DEFAULT NULL,
  `Inflicted_iInjuries` int(11) DEFAULT NULL,
  `Inflicted_iDead` int(11) DEFAULT NULL,
  `Inflicted_iMetersRunning` int(11) DEFAULT NULL,
  `Inflicted_iMetersPassing` int(11) DEFAULT NULL,
  `Sustained_iInterceptions` int(11) DEFAULT NULL,
  `Sustained_iCasualties` int(11) DEFAULT NULL,
  `Sustained_iTackles` int(11) DEFAULT NULL,
  `Sustained_iKO` int(11) DEFAULT NULL,
  `Sustained_iStuns` int(11) DEFAULT NULL,
  `Sustained_iInjuries` int(11) DEFAULT NULL,
  `Sustained_iDead` int(11) DEFAULT NULL,
  KEY `upload_player_id` (`upload_id`,`idPlayer_Listing`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `staging_mr_Calendar`
--

CREATE TABLE IF NOT EXISTS `staging_mr_Calendar` (
  `upload_id` int(11) NOT NULL,
  `ID` int(10) DEFAULT NULL,
  `idTeam_Listing_Away` int(11) DEFAULT NULL,
  `idTeam_Listing_Home` int(11) DEFAULT NULL,
  `Championship_iSeason` int(11) DEFAULT NULL,
  `Championship_iDay` int(11) DEFAULT NULL,
  `Championship_iGroup` int(11) DEFAULT NULL,
  `Championship_idRule_Types` int(11) DEFAULT NULL,
  `Championship_iEliminitationLevel` int(11) DEFAULT NULL,
  `Playoff_iEliminationLevel` int(11) DEFAULT NULL,
  `Playoff_bAwayGame` int(11) DEFAULT NULL,
  `Away_iScore` int(11) DEFAULT NULL,
  `Away_iReward` int(11) DEFAULT NULL,
  `Away_iCashEarned` int(11) DEFAULT NULL,
  `Away_iPossessionBall` int(11) DEFAULT NULL,
  `Away_Occupation_iOwn` int(11) DEFAULT NULL,
  `Away_Occupation_iTheir` int(11) DEFAULT NULL,
  `Away_iMVP` int(11) DEFAULT NULL,
  `Away_Inflicted_iPasses` int(11) DEFAULT NULL,
  `Away_Inflicted_iCatches` int(11) DEFAULT NULL,
  `Away_Inflicted_iInterceptions` int(11) DEFAULT NULL,
  `Away_Inflicted_iTouchdowns` int(11) DEFAULT NULL,
  `Away_Inflicted_iCasualties` int(11) DEFAULT NULL,
  `Away_Inflicted_iTackles` int(11) DEFAULT NULL,
  `Away_Inflicted_iKO` int(11) DEFAULT NULL,
  `Away_Inflicted_iInjuries` int(11) DEFAULT NULL,
  `Away_Inflicted_iDead` int(11) DEFAULT NULL,
  `Away_Inflicted_iMetersRunning` int(11) DEFAULT NULL,
  `Away_Inflicted_iMetersPassing` int(11) DEFAULT NULL,
  `Away_Sustained_iPasses` int(11) DEFAULT NULL,
  `Away_Sustained_iCatches` int(11) DEFAULT NULL,
  `Away_Sustained_iInterceptions` int(11) DEFAULT NULL,
  `Away_Sustained_iTouchdowns` int(11) DEFAULT NULL,
  `Away_Sustained_iCasualties` int(11) DEFAULT NULL,
  `Away_Sustained_iTackles` int(11) DEFAULT NULL,
  `Away_Sustained_iKO` int(11) DEFAULT NULL,
  `Away_Sustained_iInjuries` int(11) DEFAULT NULL,
  `Away_Sustained_iDead` int(11) DEFAULT NULL,
  `Away_Sustained_iMetersRunning` int(11) DEFAULT NULL,
  `Away_Sustained_iMetersPassing` int(11) DEFAULT NULL,
  `Away_iMetersRunning` int(11) DEFAULT NULL,
  `Away_iMetersPassing` int(11) DEFAULT NULL,
  `Home_iScore` int(11) DEFAULT NULL,
  `Home_iReward` int(11) DEFAULT NULL,
  `Home_iCashEarned` int(11) DEFAULT NULL,
  `Home_iPossessionBall` int(11) DEFAULT NULL,
  `Home_Occupation_iOwn` int(11) DEFAULT NULL,
  `Home_Occupation_iTheir` int(11) DEFAULT NULL,
  `Home_iMVP` int(11) DEFAULT NULL,
  `Home_Inflicted_iPasses` int(11) DEFAULT NULL,
  `Home_Inflicted_iCatches` int(11) DEFAULT NULL,
  `Home_Inflicted_iInterceptions` int(11) DEFAULT NULL,
  `Home_Inflicted_iTouchdowns` int(11) DEFAULT NULL,
  `Home_Inflicted_iCasualties` int(11) DEFAULT NULL,
  `Home_Inflicted_iTackles` int(11) DEFAULT NULL,
  `Home_Inflicted_iKO` int(11) DEFAULT NULL,
  `Home_Inflicted_iInjuries` int(11) DEFAULT NULL,
  `Home_Inflicted_iDead` int(11) DEFAULT NULL,
  `Home_Inflicted_iMetersRunning` int(11) DEFAULT NULL,
  `Home_Inflicted_iMetersPassing` int(11) DEFAULT NULL,
  `Home_Sustained_iPasses` int(11) DEFAULT NULL,
  `Home_Sustained_iCatches` int(11) DEFAULT NULL,
  `Home_Sustained_iInterceptions` int(11) DEFAULT NULL,
  `Home_Sustained_iTouchdowns` int(11) DEFAULT NULL,
  `Home_Sustained_iCasualties` int(11) DEFAULT NULL,
  `Home_Sustained_iTackles` int(11) DEFAULT NULL,
  `Home_Sustained_iKO` int(11) DEFAULT NULL,
  `Home_Sustained_iInjuries` int(11) DEFAULT NULL,
  `Home_Sustained_iDead` int(11) DEFAULT NULL,
  `Home_Sustained_iMetersRunning` int(11) DEFAULT NULL,
  `Home_Sustained_iMetersPassing` int(11) DEFAULT NULL,
  `Home_iMetersRunning` int(11) DEFAULT NULL,
  `Home_iMetersPassing` int(11) DEFAULT NULL,
  `iSpectators` int(11) DEFAULT NULL,
  `iRating` int(11) DEFAULT NULL,
  `bPlayed` int(11) DEFAULT NULL,
  PRIMARY KEY (`upload_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `staging_mr_Home_Player_Casualties`
--

CREATE TABLE IF NOT EXISTS `staging_mr_Home_Player_Casualties` (
  `upload_id` int(11) NOT NULL,
  `ID` int(11) NOT NULL,
  `idPlayer_Listing` int(11) DEFAULT NULL,
  `idPlayer_Casualty_Types` int(11) DEFAULT NULL,
  KEY `upload_id` (`upload_id`),
  KEY `upload_id_2` (`upload_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `staging_mr_Home_Statistics_Players`
--

CREATE TABLE IF NOT EXISTS `staging_mr_Home_Statistics_Players` (
  `upload_id` int(11) NOT NULL,
  `ID` int(11) NOT NULL,
  `idPlayer_Listing` int(11) DEFAULT NULL,
  `iMatchPlayed` int(11) DEFAULT NULL,
  `iMVP` int(11) DEFAULT NULL,
  `Inflicted_iPasses` int(11) DEFAULT NULL,
  `Inflicted_iCatches` int(11) DEFAULT NULL,
  `Inflicted_iInterceptions` int(11) DEFAULT NULL,
  `Inflicted_iTouchdowns` int(11) DEFAULT NULL,
  `Inflicted_iCasualties` int(11) DEFAULT NULL,
  `Inflicted_iTackles` int(11) DEFAULT NULL,
  `Inflicted_iKO` int(11) DEFAULT NULL,
  `Inflicted_iStuns` int(11) DEFAULT NULL,
  `Inflicted_iInjuries` int(11) DEFAULT NULL,
  `Inflicted_iDead` int(11) DEFAULT NULL,
  `Inflicted_iMetersRunning` int(11) DEFAULT NULL,
  `Inflicted_iMetersPassing` int(11) DEFAULT NULL,
  `Sustained_iInterceptions` int(11) DEFAULT NULL,
  `Sustained_iCasualties` int(11) DEFAULT NULL,
  `Sustained_iTackles` int(11) DEFAULT NULL,
  `Sustained_iKO` int(11) DEFAULT NULL,
  `Sustained_iStuns` int(11) DEFAULT NULL,
  `Sustained_iInjuries` int(11) DEFAULT NULL,
  `Sustained_iDead` int(11) DEFAULT NULL,
  KEY `upload_id` (`upload_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `staging_Replay_NetCommands`
--

CREATE TABLE IF NOT EXISTS `staging_Replay_NetCommands` (
  `upload_id` int(11) NOT NULL,
  `ID` int(11) NOT NULL,
  `iTurn` int(11) DEFAULT NULL,
  `iPlayerIndex` int(11) DEFAULT NULL,
  `iType` int(11) DEFAULT NULL,
  `iPart1` int(11) DEFAULT NULL,
  `iPart2` int(11) DEFAULT NULL,
  `iPart3` int(11) DEFAULT NULL,
  `iPart4` int(11) DEFAULT NULL,
  `iPart5` int(11) DEFAULT NULL,
  `iPart6` int(11) DEFAULT NULL,
  `iPart7` int(11) DEFAULT NULL,
  `iPart8` int(11) DEFAULT NULL,
  `iPart9` int(11) DEFAULT NULL,
  `iPart10` int(11) DEFAULT NULL,
  `iPart11` int(11) DEFAULT NULL,
  `iPart12` int(11) DEFAULT NULL,
  KEY `upload_id` (`upload_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `staging_SavedGameInfo`
--

CREATE TABLE IF NOT EXISTS `staging_SavedGameInfo` (
  `upload_id` int(11) NOT NULL,
  `ID` int(11) NOT NULL,
  `strName` varchar(255) DEFAULT NULL,
  `strSaveDate` varchar(255) DEFAULT NULL,
  `strVersion` varchar(255) DEFAULT NULL,
  `Championship_idTeam_Listing` int(11) DEFAULT NULL,
  `Championship_strTeamName` varchar(255) NOT NULL,
  `Championship_idTeamLogo` int(11) DEFAULT NULL,
  `Championship_idRule_Types_Current` int(11) DEFAULT NULL,
  `Championship_iCurrentDay` int(11) DEFAULT NULL,
  `Championship_iCurrentSeason` int(11) DEFAULT NULL,
  `Championship_iTeamCash` int(11) DEFAULT NULL,
  `Championship_iTeamValue` int(11) DEFAULT NULL,
  `Championship_iTeamPopularity` int(11) DEFAULT NULL,
  `Campaign_idCampaign_Listing` int(11) DEFAULT NULL,
  `Campaign_bRealTime` int(11) DEFAULT NULL,
  `Campaign_iTeamPrestige` int(11) DEFAULT NULL,
  `Campaign_iCurrentPeriod` int(11) DEFAULT NULL,
  `Match_strSave` text,
  `Match_idDifficultyLevels` int(11) DEFAULT NULL,
  `Match_iStadium` int(11) DEFAULT NULL,
  `Match_bDeathZoneEnabled` int(11) DEFAULT NULL,
  `iSlot` int(11) DEFAULT NULL,
  `strTeamLogo` varchar(255) NOT NULL,
  `iLogoRace` int(11) DEFAULT NULL,
  `iLogoIndex` int(11) DEFAULT NULL,
  `strAvailableRaces` varchar(8) NOT NULL,
  `iInMatch` int(11) DEFAULT NULL,
  `iRace` int(11) DEFAULT NULL,
  `Story_strName` varchar(255) DEFAULT NULL,
  `Story_strContext` text,
  UNIQUE KEY `upload_id` (`upload_id`,`ID`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------


--
-- Constraints for dumped tables
--

--
-- Constraints for table `bb_api_call`
--
ALTER TABLE `bb_api_call`
  ADD CONSTRAINT `fk_api_type_id` FOREIGN KEY (`api_type_id`) REFERENCES `bb_api_type` (`api_type_id`),
  ADD CONSTRAINT `fk_api_user_id` FOREIGN KEY (`api_user_id`) REFERENCES `bb_api_user` (`api_user_id`);

--
-- Constraints for table `bb_competition`
--
ALTER TABLE `bb_competition`
  ADD CONSTRAINT `bb_competition_ibfk_1` FOREIGN KEY (`tiebreaker_id`) REFERENCES `bb_lkp_competition_tiebreaker` (`competition_tiebreaker_id`),
  ADD CONSTRAINT `bb_competition_ibfk_2` FOREIGN KEY (`competition_type_id`) REFERENCES `bb_lkp_competition_type` (`competition_type_id`);

--
-- Constraints for table `bb_role_permission`
--
ALTER TABLE `bb_role_permission`
  ADD CONSTRAINT `bb_role_permission_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `bb_role` (`role_id`),
  ADD CONSTRAINT `bb_role_permission_ibfk_2` FOREIGN KEY (`permission_id`) REFERENCES `bb_permission` (`permission_id`);

--
-- Constraints for table `bb_stat_comp_table`
--
ALTER TABLE `bb_stat_comp_table`
  ADD CONSTRAINT `fk_scoring_system` FOREIGN KEY (`scoring_system_id`) REFERENCES `bb_lkp_scoring_system` (`scoring_system_id`);

--
-- Constraints for table `bb_upload`
--
ALTER TABLE `bb_upload`
  ADD CONSTRAINT `bb_upload_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `bb_user` (`user_id`);

--
-- Constraints for table `bb_user`
--
ALTER TABLE `bb_user`
  ADD CONSTRAINT `bb_user_ibfk_1` FOREIGN KEY (`default_domain_id`) REFERENCES `bb_domain` (`domain_id`);

--
-- Constraints for table `bb_user_role`
--
ALTER TABLE `bb_user_role`
  ADD CONSTRAINT `bb_user_role_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `bb_role` (`role_id`),
  ADD CONSTRAINT `bb_user_role_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `bb_user` (`user_id`);


--
-- Dumping data for table `bb_lkp_ag`
--

INSERT INTO `bb_lkp_ag` (`bb1_id`, `human_val`) VALUES
('16.666', 1),
('33.333', 2),
('50.000', 3),
('66.666', 4),
('83.333', 5),
('100.000', 6);

--
-- Dumping data for table `bb_lkp_av`
--

INSERT INTO `bb_lkp_av` (`bb1_id`, `human_val`) VALUES
('0.000', 0),
('0.990', 1),
('2.768', 2),
('8.324', 3),
('16.657', 4),
('27.777', 5),
('41.666', 6),
('58.333', 7),
('72.222', 8),
('83.333', 9),
('91.666', 10),
('97.213', 11),
('99.990', 12);

--
-- Dumping data for table `bb_lkp_award`
--

INSERT INTO `bb_lkp_award` (`award_id`, `award_type_id`, `award_level_id`, `criteria`) VALUES
(1, 1, 30, NULL),
(2, 1, 20, NULL),
(3, 1, 10, NULL),
(4, 2, 30, NULL),
(5, 2, 20, NULL),
(6, 2, 10, NULL),
(7, 3, 10, NULL),
(8, 3, 20, NULL),
(9, 3, 30, NULL),
(10, 4, 10, NULL),
(11, 4, 20, NULL),
(12, 4, 30, NULL),
(13, 5, 10, NULL),
(14, 5, 20, NULL),
(15, 5, 30, NULL),
(16, 6, 10, NULL),
(17, 6, 20, NULL),
(18, 6, 30, NULL),
(19, 7, 10, NULL),
(20, 7, 20, NULL),
(21, 7, 30, NULL),
(22, 8, 10, NULL),
(23, 8, 20, NULL),
(24, 8, 30, NULL),
(25, 9, 10, NULL),
(26, 9, 20, NULL),
(27, 9, 30, NULL);

--
-- Dumping data for table `bb_lkp_award_category`
--

INSERT INTO `bb_lkp_award_category` (`award_category_id`, `entity_name`, `description`) VALUES
(1, 'Player', 'Competition'),
(2, 'Player', 'Longevity'),
(3, 'Team', 'Competition'),
(4, 'Team', 'Longevity'),
(5, 'Team', 'Honours');

--
-- Dumping data for table `bb_lkp_award_level`
--

INSERT INTO `bb_lkp_award_level` (`award_level_id`, `description`, `position`, `rank_no`) VALUES
(8, 'Losing Semi-finalist', 'Losing Semi-finalist', 0),
(10, 'Bronze', 'Third', 3),
(20, 'Silver', 'Runner-up', 2),
(30, 'Gold', '1st', 1);

--
-- Dumping data for table `bb_lkp_award_type`
--

INSERT INTO `bb_lkp_award_type` (`award_type_id`, `award_category_id`, `description`, `measured_value`, `table_id`) VALUES
(1, 1, 'Touchdown King', 'Most Touchdowns', 1),
(2, 1, 'Sir Pass-a-lot', 'Most Passes', 2),
(3, 1, 'Howzzat', 'Most Catches', 3),
(4, 1, 'Bravely Running Away', 'Most Meters Run', 5),
(5, 1, 'Go Long', 'Most Meters Passed', 6),
(6, 1, 'Hit ''em all', 'Most Knockdowns Inflicted', 7),
(7, 1, 'Oi Stop Hitting Me', 'Most Knockdowns Received ', 8),
(8, 1, 'Sending yer to St John''s', 'Most Injuries Inflicted', 9),
(9, 1, 'Hospital Frequent Visitor', 'Most Injuries Received', 10);

--
-- Dumping data for table `bb_lkp_block_dice`
--

INSERT INTO `bb_lkp_block_dice` (`block_dice_id`, `description`, `bb1_desc`, `img_src`) VALUES
(1, 'Red Skull', 'Attacker Down', NULL),
(2, 'Both Down', 'Both Down', NULL),
(3, 'Pushed', 'Pushed', NULL),
(4, 'Defender Stumbles', 'Defender Stumbles', NULL),
(5, 'Defender Down', 'Defender Down', NULL);

--
-- Dumping data for table `bb_lkp_block_dice_count`
--

INSERT INTO `bb_lkp_block_dice_count` (`block_dice_count_id`, `dice_count`, `description`, `against`) VALUES
(1, 3, '3 dice for', 0),
(2, 2, '2 dice for', 0),
(3, 1, '1 die block', 0),
(4, 2, '2 dice against', 1),
(5, 3, '3 dice against', 1);

--
-- Dumping data for table `bb_lkp_block_dice_perm`
--

INSERT INTO `bb_lkp_block_dice_perm` (`block_dice_perm_id`, `block_dice_count_id`, `block_dice_id_1`, `block_dice_id_2`, `block_dice_id_3`, `short_description`) VALUES
(1, 3, 1, NULL, NULL, 'RS'),
(2, 3, 2, NULL, NULL, 'BD'),
(3, 3, 3, NULL, NULL, 'PS'),
(4, 3, 4, NULL, NULL, 'DS'),
(5, 3, 5, NULL, NULL, 'DD'),
(6, 2, 1, 1, NULL, 'RS-RS'),
(7, 2, 1, 2, NULL, 'RS-BD'),
(8, 2, 1, 3, NULL, 'RS-PS'),
(9, 2, 1, 4, NULL, 'RS-DS'),
(10, 2, 1, 5, NULL, 'RS-DD'),
(11, 2, 2, 2, NULL, 'BD-BD'),
(12, 2, 2, 3, NULL, 'BD-PS'),
(13, 2, 2, 4, NULL, 'BD-DS'),
(14, 2, 2, 5, NULL, 'BD-DD'),
(15, 2, 3, 3, NULL, 'PS-PS'),
(16, 2, 3, 4, NULL, 'PS-DS'),
(17, 2, 3, 5, NULL, 'PS-DD'),
(18, 2, 4, 4, NULL, 'DS-DS'),
(19, 2, 4, 5, NULL, 'DS-DD'),
(20, 2, 5, 5, NULL, 'DD-DD'),
(21, 4, 1, 1, NULL, 'RS-RS'),
(22, 4, 1, 2, NULL, 'RS-BD'),
(23, 4, 1, 3, NULL, 'RS-PS'),
(24, 4, 1, 4, NULL, 'RS-DS'),
(25, 4, 1, 5, NULL, 'RS-DD'),
(26, 4, 2, 2, NULL, 'BD-BD'),
(27, 4, 2, 3, NULL, 'BD-PS'),
(28, 4, 2, 4, NULL, 'BD-DS'),
(29, 4, 2, 5, NULL, 'BD-DD'),
(30, 4, 3, 3, NULL, 'PS-PS'),
(31, 4, 3, 4, NULL, 'PS-DS'),
(32, 4, 3, 5, NULL, 'PS-DD'),
(33, 4, 4, 4, NULL, 'DS-DS'),
(34, 4, 4, 5, NULL, 'DS-DD'),
(35, 4, 5, 5, NULL, 'DD-DD'),
(36, 1, 1, 1, 1, 'RS-RS-RS'),
(37, 1, 1, 1, 2, 'RS-RS-BD'),
(38, 1, 1, 1, 3, 'RS-RS-PS'),
(39, 1, 1, 1, 4, 'RS-RS-DS'),
(40, 1, 1, 1, 5, 'RS-RS-DD'),
(41, 1, 1, 2, 2, 'RS-BD-BD'),
(42, 1, 1, 2, 3, 'RS-BD-PS'),
(43, 1, 1, 2, 4, 'RS-BD-DS'),
(44, 1, 1, 2, 5, 'RS-BD-DD'),
(45, 1, 1, 3, 3, 'RS-PS-PS'),
(46, 1, 1, 3, 4, 'RS-PS-DS'),
(47, 1, 1, 3, 5, 'RS-PS-DD'),
(48, 1, 1, 4, 4, 'RS-DS-DS'),
(49, 1, 1, 4, 5, 'RS-DS-DD'),
(50, 1, 1, 5, 5, 'RS-DD-DD'),
(51, 1, 2, 2, 2, 'BD-BD-BD'),
(52, 1, 2, 2, 3, 'BD-BD-PS'),
(53, 1, 2, 2, 4, 'BD-BD-DS'),
(54, 1, 2, 2, 5, 'BD-BD-DD'),
(55, 1, 2, 3, 3, 'BD-PS-PS'),
(56, 1, 2, 3, 4, 'BD-PS-DS'),
(57, 1, 2, 3, 5, 'BD-PS-DD'),
(58, 1, 2, 4, 4, 'BD-DS-DS'),
(59, 1, 2, 4, 5, 'BD-DS-DD'),
(60, 1, 2, 5, 5, 'BD-DD-DD'),
(61, 1, 3, 3, 3, 'PS-PS-PS'),
(62, 1, 3, 3, 4, 'PS-PS-DS'),
(63, 1, 3, 3, 5, 'PS-PS-DD'),
(64, 1, 3, 4, 4, 'PS-DS-DS'),
(65, 1, 3, 4, 5, 'PS-DS-DD'),
(66, 1, 3, 5, 5, 'PS-DD-DD'),
(67, 1, 4, 4, 4, 'DS-DS-DS'),
(68, 1, 4, 4, 5, 'DS-DS-DD'),
(69, 1, 4, 5, 5, 'DS-DD-DD'),
(70, 1, 5, 5, 5, 'DD-DD-DD'),
(99, 5, 1, 1, 1, 'RS-RS-RS'),
(100, 5, 1, 1, 2, 'RS-RS-BD'),
(101, 5, 1, 1, 3, 'RS-RS-PS'),
(102, 5, 1, 1, 4, 'RS-RS-DS'),
(103, 5, 1, 1, 5, 'RS-RS-DD'),
(104, 5, 1, 2, 2, 'RS-BD-BD'),
(105, 5, 1, 2, 3, 'RS-BD-PS'),
(106, 5, 1, 2, 4, 'RS-BD-DS'),
(107, 5, 1, 2, 5, 'RS-BD-DD'),
(108, 5, 1, 3, 3, 'RS-PS-PS'),
(109, 5, 1, 3, 4, 'RS-PS-DS'),
(110, 5, 1, 3, 5, 'RS-PS-DD'),
(111, 5, 1, 4, 4, 'RS-DS-DS'),
(112, 5, 1, 4, 5, 'RS-DS-DD'),
(113, 5, 1, 5, 5, 'RS-DD-DD'),
(114, 5, 2, 2, 2, 'BD-BD-BD'),
(115, 5, 2, 2, 3, 'BD-BD-PS'),
(116, 5, 2, 2, 4, 'BD-BD-DS'),
(117, 5, 2, 2, 5, 'BD-BD-DD'),
(118, 5, 2, 3, 3, 'BD-PS-PS'),
(119, 5, 2, 3, 4, 'BD-PS-DS'),
(120, 5, 2, 3, 5, 'BD-PS-DD'),
(121, 5, 2, 4, 4, 'BD-DS-DS'),
(122, 5, 2, 4, 5, 'BD-DS-DD'),
(123, 5, 2, 5, 5, 'BD-DD-DD'),
(124, 5, 3, 3, 3, 'PS-PS-PS'),
(125, 5, 3, 3, 4, 'PS-PS-DS'),
(126, 5, 3, 3, 5, 'PS-PS-DD'),
(127, 5, 3, 4, 4, 'PS-DS-DS'),
(128, 5, 3, 4, 5, 'PS-DS-DD'),
(129, 5, 3, 5, 5, 'PS-DD-DD'),
(130, 5, 4, 4, 4, 'DS-DS-DS'),
(131, 5, 4, 4, 5, 'DS-DS-DD'),
(132, 5, 4, 5, 5, 'DS-DD-DD'),
(133, 5, 5, 5, 5, 'DD-DD-DD');

--
-- Dumping data for table `bb_lkp_casualty`
--

INSERT INTO `bb_lkp_casualty` (`casualty_id`, `bb1_id`, `description`, `effect_english`) VALUES
(1, 1, 'Badly Hurt', 'No long term effect'),
(2, 2, 'Broken Ribs', 'Miss next game'),
(3, 3, 'Groin Strain', 'Miss next game'),
(4, 4, 'Gouged Eye', 'Miss next game'),
(5, 5, 'Broken Jaw', 'Miss next game'),
(6, 6, 'Fractured Arm', 'Miss next game'),
(7, 7, 'Fractured Leg', 'Miss next game'),
(8, 8, 'Smashed Hand', 'Miss next game'),
(9, 9, 'Pinched Nerve', 'Miss next game'),
(10, 10, 'Damaged Back', 'Niggling Injury'),
(11, 11, 'Smashed Knee', 'Niggling Injury'),
(12, 12, 'Smashed Hip', '-1 MA'),
(13, 13, 'Smashed Ankle', '-1 MA'),
(14, 14, 'Serious Concussion', '-1 AV'),
(15, 15, 'Fractured Skull', '-1 AV'),
(16, 16, 'Broken neck', '-1 AG'),
(17, 17, 'Smashed Collar Bone', '-1 ST'),
(18, 18, 'Dead', 'DEAD!'),
(19, NULL, 'Retired', 'Coach removed player from roster');

--
-- Dumping data for table `bb_lkp_casualty_status`
--

INSERT INTO `bb_lkp_casualty_status` (`casualty_status_id`, `description`) VALUES
(1, 'Valid'),
(2, 'Apocathery-ed away'),
(3, 'Regenerated'),
(4, 'Decay');

--
-- Dumping data for table `bb_lkp_competition_tiebreaker`
--

INSERT INTO `bb_lkp_competition_tiebreaker` (`competition_tiebreaker_id`, `description`) VALUES
(1, 'TD difference'),
(2, 'TD diff + (0.5 * CAS diff)');

--
-- Dumping data for table `bb_lkp_competition_type`
--

INSERT INTO `bb_lkp_competition_type` (`competition_type_id`, `description`, `start_league`, `knockout_end`, `start_group_count`) VALUES
(0, 'None', 0, 0, NULL),
(1, 'Standard 1-match league', 1, 0, 1),
(2, 'Standard 1-play knockout cup', 0, 1, NULL);

--
-- Dumping data for table `bb_lkp_dice_type`
--

INSERT INTO `bb_lkp_dice_type` (`dice_type_id`, `description`) VALUES
(1, 'D6'),
(2, '2D6'),
(4, 'Block Dice'),
(5, 'D68 Casualty'),
(7, 'Coin Toss'),
(8, 'Turnover type');

--
-- Dumping data for table `bb_lkp_event_outcome`
--

INSERT INTO `bb_lkp_event_outcome` (`event_outcome_id`, `description`) VALUES
(1, 'Success'),
(2, 'Turnover');

--
-- Dumping data for table `bb_lkp_fail_turnover`
--

INSERT INTO `bb_lkp_fail_turnover` (`fail_turnover_id`, `description`) VALUES
(0, 'No'),
(1, 'Yes'),
(2, 'Depends');

--
-- Dumping data for table `bb_lkp_match_status`
--

INSERT INTO `bb_lkp_match_status` (`match_status_id`, `description`) VALUES
(1, 'Result'),
(2, 'Fixture'),
(3, 'Fragment');

--
-- Dumping data for table `bb_lkp_mv`
--

INSERT INTO `bb_lkp_mv` (`bb1_id`, `human_val`) VALUES
('8.3330000000000000000000', 1),
('16.6670000000000000000000', 2),
('25.0000000000000000000000', 3),
('33.3330000000000000000000', 4),
('41.6670000000000000000000', 5),
('50.0000000000000000000000', 6),
('58.3330000000000000000000', 7),
('66.6670000000000000000000', 8),
('75.0000000000000000000000', 9),
('83.3330000000000000000000', 10);

--
-- Dumping data for table `bb_lkp_player_status`
--

INSERT INTO `bb_lkp_player_status` (`player_status_id`, `description`, `short_description`) VALUES
(1, 'Active', 'A'),
(2, 'Dead', 'D'),
(3, 'Retired', 'R'),
(4, 'Star Player', 'S'),
(5, 'Mercenary', 'M'),
(6, 'Journeyman', 'J'),
(7, 'Unknown', 'U');

--
-- Dumping data for table `bb_lkp_player_supertype`
--

INSERT INTO `bb_lkp_player_supertype` (`player_supertype_id`, `description`) VALUES
(1, 'Goblin'),
(2, 'Treeman'),
(3, 'Skeleton'),
(4, 'Ghoul'),
(5, 'Zombie'),
(6, 'Thrower'),
(7, 'Blitzer'),
(8, 'High ST'),
(9, 'Troll'),
(10, 'Stunty (excl. Goblins)'),
(11, 'Runner/Catcher'),
(12, 'Ogre'),
(13, 'Linemen'),
(14, 'Line-elves');

--
-- Dumping data for table `bb_lkp_player_type`
--

INSERT INTO `bb_lkp_player_type` (`player_type_id`, `player_supertype_id`, `bb1_id`, `long_description`, `description`, `short_description`, `race_id`, `mv`, `st`, `ag`, `av`, `price`, `max_quantity`) VALUES
(1, NULL, 53, 'AllStar_Orc_MorgNThorg', 'Morg''n''Thorg', NULL, 0, 6, 6, 3, 10, 430000, 1),
(2, 1, 123, 'Team_Underworld_Goblin', 'Goblin', NULL, 2, 6, 2, 3, 7, 40000, 12),
(3, 13, 124, 'Team_Underworld_SkavenLineman', 'Linerat', NULL, 2, 7, 3, 3, 7, 50000, 2),
(4, 6, 125, 'Team_Underworld_SkavenThrower', 'Skaven Thrower', NULL, 2, 7, 3, 3, 7, 70000, 2),
(5, 7, 126, 'Team_Underworld_SkavenBlitzer', 'Skaven Stormvermin', NULL, 2, 7, 3, 3, 8, 90000, 2),
(6, NULL, 133, 'AllStar_Goblin_BomberDribblesnot', 'Bomber Dribblesnot (LEGEND)', NULL, 0, 6, 2, 3, 7, 60000, 1),
(8, NULL, 100, 'AllStar_Goblin_ScrappaSorehead', 'Scrappa Sorehead', NULL, 0, 7, 2, 3, 7, 150000, 1),
(9, 10, 95, 'Team_Ogre_Snotling', 'Snotling', NULL, 1, 5, 1, 3, 5, 20000, 16),
(10, 12, 96, 'Team_Ogre_Ogre', 'Ogre', NULL, 1, 5, 5, 2, 9, 140000, 6),
(12, 13, 68, 'Team_Amazon_Linewoman', 'Linewoman', NULL, 4, 6, 3, 3, 7, 50000, 16),
(13, 6, 69, 'Team_Amazon_Thrower', 'Thrower', NULL, 4, 6, 3, 3, 7, 70000, 2),
(14, 11, 70, 'Team_Amazon_Catcher', 'Catcher', NULL, 4, 6, 3, 3, 7, 70000, 2),
(15, 7, 71, 'Team_Amazon_Blitzer', 'Blitzer', NULL, 4, 6, 3, 3, 7, 90000, 4),
(16, NULL, 99, 'AllStar_Human_ZaraTheSlayer', 'Zara The Slayer', NULL, 0, 6, 4, 3, 8, 270000, 1),
(19, NULL, 36, 'AllStar_Chaos_GrashnakBlackhoof', 'Grashnak Blackhoof', NULL, 0, 6, 6, 2, 8, 310000, 1),
(20, 13, 91, 'Team_Nurgle_Rotter', 'Rotter', NULL, 3, 5, 3, 3, 8, 40000, 16),
(21, NULL, 92, 'Team_Nurgle_Pestigor', 'Pestigor', NULL, 3, 6, 3, 3, 8, 80000, 4),
(22, 8, 93, 'Team_Nurgle_NurgleWarrior', 'Nurgle Warrior', NULL, 3, 4, 4, 2, 9, 110000, 4),
(23, NULL, 102, 'AllStar_Chaos_LordBorakTheDespoiler', 'Lord Borak The Despoiler', NULL, 0, 5, 5, 3, 9, 300000, 1),
(26, 13, 16, 'Team_Skaven_Lineman', 'Linerat', NULL, 5, 7, 3, 3, 7, 50000, 16),
(27, 6, 17, 'Team_Skaven_Thrower', 'Thrower', NULL, 5, 7, 3, 3, 7, 70000, 2),
(28, 7, 19, 'Team_Skaven_Blitzer', 'Stormvermin', NULL, 5, 7, 3, 3, 8, 90000, 2),
(29, NULL, 20, 'BigGuy_Skaven_RatOgre', 'Rat Ogre', NULL, 5, 6, 5, 2, 8, 150000, 1),
(30, NULL, 39, 'AllStar_Skaven_Headsplitter', NULL, NULL, 0, 6, 6, 3, 8, 340000, 1),
(33, 13, 129, 'Team_Khorne_PitFighter', 'Pit Fighter', NULL, 6, 6, 3, 3, 8, 60000, 16),
(34, 7, 130, 'Team_Khorne_Herald', 'Herald', NULL, 6, 6, 3, 3, 8, 90000, 2),
(35, NULL, 131, 'Team_Khorne_Bloodletter', 'Bloodletter', NULL, 6, 6, 3, 3, 7, 80000, 4),
(36, NULL, 59, 'AllStar_Undead_CountLuthorvonDrakenborg', NULL, NULL, 0, 6, 5, 4, 9, 390000, 1),
(37, 13, 97, 'Team_Vampire_Thrall', 'Thrall', NULL, 8, 6, 3, 3, 7, 40000, 16),
(39, 13, 108, 'Team_ChaosDwarf_Hobgoblin', 'Hobgoblin', NULL, 7, 6, 3, 3, 7, 40000, 16),
(40, NULL, 109, 'Team_ChaosDwarf_Blocker', 'Blocker', NULL, 7, 4, 3, 2, 9, 70000, 6),
(41, 8, 110, 'Team_ChaosDwarf_BullCentaur', 'Bull Centaur', NULL, 7, 6, 4, 2, 9, 130000, 2),
(42, NULL, 111, 'BigGuy_ChaosDwarf_Minotaur', 'Minotaur', NULL, 7, 5, 5, 2, 8, 150000, 1),
(43, NULL, 134, 'AllStar_ChaosDwarf_ZzhargMadeye', NULL, NULL, 0, 4, 4, 3, 9, 90000, 1),
(46, 10, 27, 'Team_Lizardman_Skink', 'Skink', NULL, 9, 8, 2, 3, 7, 60000, 16),
(47, 8, 28, 'Team_Lizardman_Saurus', 'Saurus', NULL, 9, 6, 4, 1, 9, 80000, 6),
(48, NULL, 29, 'BigGuy_Lizardman_Kroxigor', 'Kroxigor', NULL, 9, 6, 5, 1, 9, 140000, 1),
(49, NULL, 42, 'AllStar_Lizardman_Silibili', NULL, NULL, 0, 7, 4, 1, 9, 250000, 1),
(53, 1, 22, 'Team_Orc_Goblin', 'Goblin', NULL, 10, 6, 2, 3, 7, 40000, 4),
(54, 8, 24, 'Team_Orc_BlackBlocker', 'Black Orc', NULL, 10, 4, 4, 2, 9, 80000, 4),
(55, 7, 25, 'Team_Orc_Blitzer', 'Blitzer', NULL, 10, 6, 3, 3, 9, 80000, 4),
(56, 9, 26, 'BigGuy_Orc_Troll', 'Troll', NULL, 10, 4, 5, 1, 9, 110000, 1),
(57, NULL, 41, 'AllStar_Goblin_Ripper', NULL, NULL, 0, 4, 6, 1, 9, 270000, 1),
(58, NULL, 43, 'AllStar_Orc_VaragGhoulChewer', 'Varag The Ghoul Chewer', NULL, 0, 6, 4, 3, 9, 290000, 1),
(60, 14, 11, 'Team_WoodElf_Lineman', 'Lineman', NULL, 11, 7, 3, 4, 7, 70000, 16),
(61, 11, 12, 'Team_WoodElf_Catcher', 'Catcher', NULL, 11, 8, 2, 4, 7, 90000, 4),
(62, 6, 13, 'Team_WoodElf_Thrower', 'Thrower', NULL, 11, 7, 3, 4, 7, 90000, 2),
(63, 7, 14, 'Team_WoodElf_WarDancer', 'Wardancer', NULL, 11, 8, 3, 4, 7, 120000, 2),
(64, 2, 15, 'BigGuy_WoodElf_Treeman', 'Treeman', NULL, 11, 2, 6, 1, 10, 120000, 1),
(65, NULL, 40, 'AllStar_WoodElf_JordellFreshbreeze', 'Jordell Freshbreeze', NULL, 0, 8, 3, 5, 7, 260000, 1),
(67, 5, 55, 'Team_Undead_Zombie', 'Zombie', NULL, 13, 4, 3, 2, 8, 40000, 16),
(68, 4, 56, 'Team_Undead_Ghoul', 'Ghoul', NULL, 13, 7, 3, 3, 7, 70000, 4),
(69, 7, 57, 'Team_Undead_Wight', 'Wight', NULL, 13, 6, 3, 3, 8, 90000, 2),
(70, 8, 58, 'BigGuy_Undead_Mummy', 'Mummy', NULL, 13, 3, 5, 1, 9, 120000, 2),
(71, NULL, 104, 'AllStar_Khemri_Nekbrekerekh', NULL, NULL, 0, 6, 4, 2, 8, 220000, 1),
(74, 14, 47, 'Team_DarkElf_Lineman', 'Lineman', NULL, 14, 6, 3, 4, 8, 70000, 16),
(75, 7, 48, 'Team_DarkElf_Runner', 'Runner', NULL, 14, 7, 3, 4, 7, 80000, 2),
(76, NULL, 49, 'Team_DarkElf_Assassin', 'Assassin', NULL, 14, 6, 3, 4, 7, 90000, 2),
(77, 7, 50, 'Team_DarkElf_Blitzer', 'Blitzer', NULL, 14, 7, 3, 4, 8, 100000, 4),
(78, NULL, 51, 'Team_DarkElf_WitchElf', 'Witch Elf', NULL, 14, 7, 3, 4, 7, 110000, 2),
(79, NULL, 52, 'AllStar_DarkElf_HorkonHeartripper', 'Horkon Heartripper', NULL, 0, 7, 3, 4, 7, 210000, 1),
(81, 13, 1, 'Team_Human_Lineman', 'Lineman', NULL, 15, 6, 3, 3, 8, 50000, 16),
(82, 11, 2, 'Team_Human_Catcher', 'Catcher', NULL, 15, 8, 2, 3, 7, 70000, 4),
(83, 6, 3, 'Team_Human_Thrower', 'Thrower', NULL, 15, 6, 3, 3, 8, 70000, 2),
(84, 7, 4, 'Team_Human_Blitzer', 'Blitzer', NULL, 15, 7, 3, 3, 8, 90000, 4),
(85, NULL, 37, 'AllStar_Human_GriffOberwald', 'Griff Oberwald', NULL, 0, 7, 4, 4, 8, 320000, 1),
(88, 10, 60, 'Team_Halfling_Halfling', 'Halfling', NULL, 12, 5, 2, 3, 6, 30000, 16),
(89, 2, 61, 'BigGuy_Halfling_Treeman', 'Treeman', NULL, 12, 2, 6, 1, 10, 120000, 2),
(90, NULL, 103, 'AllStar_Halfling_DeeprootStrongbranch', 'Deeproot Strongbranch', NULL, 0, 2, 7, 1, 10, 300000, 1),
(91, NULL, 101, 'AllStar_Elf_EldrilSidewinder', 'Eldril Sidewinder', NULL, 0, 8, 3, 4, 7, 200000, 1),
(92, 3, 54, 'Team_Undead_Skeleton', 'Skeleton', NULL, 13, 5, 3, 2, 7, 40000, 16),
(93, NULL, 105, 'AllStar_Khemri_RamtutIII', 'Ramtut III', NULL, 0, 5, 6, 1, 9, 380000, 1),
(95, 5, 86, 'Team_Necromantic_Zombie', 'Zombie', NULL, 16, 4, 3, 2, 8, 40000, 16),
(96, 4, 87, 'Team_Necromantic_Ghoul', 'Ghoul', NULL, 16, 7, 3, 3, 7, 70000, 2),
(97, 7, 88, 'Team_Necromantic_Wight', 'Wight', NULL, 16, 6, 3, 3, 8, 90000, 2),
(98, 8, 89, 'BigGuy_Necromantic_FleshGolem', 'Flesh Golem', NULL, 16, 4, 4, 2, 9, 110000, 2),
(99, NULL, 90, 'Team_Necromantic_Werewolf', 'Werewolf', NULL, 16, 8, 3, 3, 8, 120000, 2),
(102, 13, 21, 'Team_Orc_Lineman', 'Lineman', NULL, 10, 5, 3, 3, 9, 50000, 16),
(103, 6, 23, 'Team_Orc_Thrower', 'Thrower', NULL, 10, 5, 3, 3, 8, 70000, 2),
(105, 9, 127, 'BigGuy_Underworld_WarpstoneTroll', 'Warpstone Troll', NULL, 2, 4, 5, 1, 9, 110000, 1),
(106, 11, 18, 'Team_Skaven_GutterRunner', 'Gutter Runner', NULL, 5, 9, 2, 4, 7, 80000, 4),
(107, NULL, 94, 'BigGuy_Nurgle_BeastOfNurgle', 'Beast Of Nurgle', NULL, 3, NULL, NULL, NULL, NULL, NULL, 1),
(108, NULL, 98, 'Team_Vampire_Vampire', 'Vampire', NULL, 8, NULL, NULL, NULL, NULL, NULL, 6),
(109, NULL, 132, 'BigGuy_Khorne_Bloodthirster', 'Bloodthirster', NULL, 6, NULL, NULL, NULL, NULL, NULL, 1),
(110, 12, 5, 'BigGuy_Human_Ogre', 'Ogre', NULL, 15, NULL, NULL, NULL, NULL, NULL, 1),
(111, 3, 81, 'Team_Khemri_Skeleton', 'Skeleton', NULL, 17, NULL, NULL, NULL, NULL, NULL, 16),
(112, 6, 82, 'Team_Khemri_ThroRa', 'Thro-Ra', NULL, 17, NULL, NULL, NULL, NULL, NULL, 2),
(113, 7, 83, 'Team_Khemri_BlitzRa', 'Blitz-Ra', NULL, 17, NULL, NULL, NULL, NULL, NULL, 2),
(114, 8, 84, 'Team_Khemri_TombGuardian', 'Tomb Guardian', NULL, 17, NULL, NULL, NULL, NULL, NULL, 4),
(118, 14, 72, 'Team_Elf_Lineman', 'Lineman', NULL, 18, NULL, NULL, NULL, NULL, NULL, 16),
(119, 6, 73, 'Team_Elf_Thrower', 'Thrower', NULL, 18, NULL, NULL, NULL, NULL, NULL, 2),
(120, 11, 74, 'Team_Elf_Catcher', 'Catcher', NULL, 18, NULL, NULL, NULL, NULL, NULL, 4),
(121, 7, 75, 'Team_Elf_Blitzer', 'Blitzer', NULL, 18, NULL, NULL, NULL, NULL, NULL, 2),
(122, 1, 30, 'Team_Goblin_Gob', 'Goblin', NULL, 20, NULL, NULL, NULL, NULL, NULL, 16),
(123, NULL, 31, 'Team_Goblin_Looney', 'Looney', NULL, 20, NULL, NULL, NULL, NULL, NULL, 1),
(124, 9, 44, 'BigGuy_Goblin_Troll', 'Troll', NULL, 20, NULL, NULL, NULL, NULL, NULL, 2),
(125, NULL, 45, 'Team_Goblin_Pogoer', 'Pogoer', NULL, 20, NULL, NULL, NULL, NULL, NULL, 1),
(126, NULL, 46, 'Team_Goblin_Fanatic', 'Fanatic', NULL, 20, NULL, NULL, NULL, NULL, NULL, 1),
(127, NULL, 107, 'Team_Goblin_Bombardier', 'Bombardier', NULL, 20, NULL, NULL, NULL, NULL, NULL, 1),
(129, 13, 62, 'Team_Norse_Lineman', 'Lineman', NULL, 19, NULL, NULL, NULL, NULL, NULL, 16),
(130, 6, 63, 'Team_Norse_Thrower', 'Thrower', NULL, 19, NULL, NULL, NULL, NULL, NULL, 2),
(131, 11, 64, 'Team_Norse_Runner', 'Runner', NULL, 19, NULL, NULL, NULL, NULL, NULL, 2),
(132, 7, 65, 'Team_Norse_Berserker', 'Berserker', NULL, 19, NULL, NULL, NULL, NULL, NULL, 2),
(133, 8, 66, 'Team_Norse_Ulfwerener', 'Ulfwerener ', NULL, 19, NULL, NULL, NULL, NULL, NULL, 2),
(134, 9, 67, 'BigGuy_Norse_Yhetee', 'Snow Troll', NULL, 19, NULL, NULL, NULL, NULL, NULL, 1),
(135, NULL, 106, 'AllStar_Norse_IcepeltHammerblow', NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, 1),
(136, 13, 6, 'Team_Dwarf_Blocker', 'Blocker', NULL, 21, NULL, NULL, NULL, NULL, NULL, 16),
(137, 11, 7, 'Team_Dwarf_Runner', 'Runner', NULL, 21, NULL, NULL, NULL, NULL, NULL, 2),
(138, 7, 8, 'Team_Dwarf_Blitzer', 'Blitzer', NULL, 21, NULL, NULL, NULL, NULL, NULL, 2),
(139, NULL, 9, 'Team_Dwarf_TrollSlayer', 'Troll Slayer', NULL, 21, NULL, NULL, NULL, NULL, NULL, 2),
(140, NULL, 10, 'Team_Dwarf_DeathRoller', 'Death Roller', NULL, 21, NULL, NULL, NULL, NULL, NULL, 1),
(141, NULL, 38, 'AllStar_Dwarf_GrimIronjaw', NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, 1),
(142, 13, 32, 'Team_Chaos_Beastman', 'Beastman', NULL, 22, NULL, NULL, NULL, NULL, NULL, 16),
(143, 8, 33, 'Team_Chaos_Warrior', 'Chaos Warrior', NULL, 22, NULL, NULL, NULL, NULL, NULL, 4),
(144, NULL, 34, 'BigGuy_Chaos_Minotaur', 'Minotaur', NULL, 22, NULL, NULL, NULL, NULL, NULL, 1),
(145, 14, 77, 'Team_HighElf_Lineman', 'Lineman', NULL, 23, NULL, NULL, NULL, NULL, NULL, 16),
(146, 6, 78, 'Team_HighElf_Thrower', 'Thrower', NULL, 23, NULL, NULL, NULL, NULL, NULL, 2),
(147, 11, 79, 'Team_HighElf_Catcher', 'Catcher', NULL, 23, NULL, NULL, NULL, NULL, NULL, 4),
(148, 7, 80, 'Team_HighElf_Blitzer', 'Blitzer', NULL, 23, NULL, NULL, NULL, NULL, NULL, 2);

--
-- Dumping data for table `bb_lkp_player_type_skill`
--

INSERT INTO `bb_lkp_player_type_skill` (`player_type_id`, `skill_id`) VALUES
(1, 13),
(1, 30),
(1, 44),
(1, 63),
(1, 64),
(2, 7),
(2, 52),
(2, 59),
(3, 86),
(4, 47),
(4, 61),
(4, 86),
(5, 30),
(5, 86),
(6, 7),
(6, 21),
(6, 44),
(6, 52),
(6, 54),
(6, 59),
(6, 80),
(8, 7),
(8, 8),
(8, 11),
(8, 27),
(8, 32),
(8, 44),
(8, 52),
(8, 59),
(8, 60),
(9, 7),
(9, 52),
(9, 56),
(9, 59),
(9, 83),
(10, 13),
(10, 31),
(10, 63),
(10, 64),
(12, 7),
(13, 7),
(13, 47),
(14, 6),
(14, 7),
(15, 7),
(15, 30),
(16, 7),
(16, 26),
(16, 30),
(16, 41),
(16, 44),
(16, 77),
(16, 79),
(19, 13),
(19, 15),
(19, 36),
(19, 44),
(19, 63),
(20, 81),
(20, 82),
(21, 15),
(21, 19),
(21, 82),
(22, 10),
(22, 19),
(22, 33),
(22, 82),
(23, 13),
(23, 27),
(23, 30),
(23, 44),
(27, 47),
(27, 61),
(28, 30),
(29, 13),
(29, 36),
(29, 44),
(29, 49),
(29, 67),
(30, 13),
(30, 36),
(30, 44),
(30, 49),
(33, 36),
(34, 15),
(34, 36),
(34, 40),
(35, 15),
(35, 19),
(35, 40),
(36, 19),
(36, 30),
(36, 44),
(36, 56),
(36, 78),
(40, 30),
(40, 57),
(40, 63),
(41, 8),
(41, 60),
(41, 63),
(42, 13),
(42, 15),
(42, 36),
(42, 44),
(42, 63),
(42, 67),
(43, 39),
(43, 44),
(43, 47),
(43, 54),
(43, 57),
(43, 58),
(43, 61),
(43, 63),
(46, 7),
(46, 59),
(48, 13),
(48, 31),
(48, 44),
(48, 49),
(48, 63),
(49, 17),
(49, 30),
(49, 37),
(49, 38),
(49, 44),
(53, 7),
(53, 52),
(53, 59),
(55, 30),
(56, 13),
(56, 18),
(56, 19),
(56, 44),
(56, 51),
(56, 64),
(57, 13),
(57, 19),
(57, 37),
(57, 44),
(57, 64),
(58, 13),
(58, 30),
(58, 41),
(58, 44),
(58, 63),
(61, 6),
(61, 7),
(61, 8),
(62, 47),
(63, 7),
(63, 11),
(63, 30),
(64, 13),
(64, 17),
(64, 20),
(64, 44),
(64, 58),
(64, 63),
(64, 64),
(65, 7),
(65, 11),
(65, 28),
(65, 30),
(65, 44),
(65, 56),
(67, 19),
(68, 7),
(69, 19),
(69, 30),
(70, 13),
(70, 19),
(71, 1),
(71, 19),
(71, 22),
(71, 30),
(71, 40),
(71, 44),
(75, 29),
(76, 55),
(76, 77),
(77, 30),
(78, 7),
(78, 36),
(78, 41),
(79, 7),
(79, 11),
(79, 44),
(79, 55),
(79, 70),
(79, 77),
(82, 6),
(82, 7),
(83, 47),
(83, 61),
(84, 30),
(85, 7),
(85, 8),
(85, 30),
(85, 35),
(85, 44),
(85, 60),
(88, 7),
(88, 52),
(88, 59),
(89, 13),
(89, 17),
(89, 20),
(89, 58),
(89, 63),
(89, 64),
(90, 13),
(90, 17),
(90, 30),
(90, 44),
(90, 58),
(90, 63),
(90, 64),
(91, 6),
(91, 7),
(91, 9),
(91, 44),
(91, 45),
(91, 78),
(92, 19),
(92, 63),
(93, 13),
(93, 19),
(93, 22),
(93, 44),
(93, 68),
(95, 19),
(96, 7),
(97, 19),
(97, 30),
(98, 17),
(98, 19),
(98, 63),
(99, 19),
(99, 36),
(99, 75),
(103, 47),
(103, 61),
(105, 13),
(105, 18),
(105, 19),
(105, 44),
(105, 51),
(105, 64),
(106, 7),
(107, 10),
(107, 13),
(107, 19),
(107, 33),
(107, 44),
(107, 51),
(107, 69),
(107, 82),
(108, 19),
(108, 78),
(108, 84),
(109, 15),
(109, 19),
(109, 36),
(109, 40),
(109, 44),
(109, 67),
(109, 75),
(110, 13),
(110, 31),
(110, 44),
(110, 63),
(110, 64),
(111, 19),
(111, 63),
(112, 19),
(112, 47),
(112, 61),
(113, 19),
(113, 30),
(114, 19),
(114, 81),
(119, 47),
(120, 6),
(120, 45),
(121, 30),
(121, 56),
(122, 7),
(122, 52),
(122, 59),
(123, 25),
(123, 54),
(123, 59),
(124, 13),
(124, 18),
(124, 19),
(124, 44),
(124, 51),
(124, 64),
(125, 7),
(125, 11),
(125, 32),
(125, 59),
(126, 46),
(126, 54),
(126, 59),
(126, 76),
(127, 7),
(127, 54),
(127, 59),
(127, 80),
(129, 30),
(130, 30),
(130, 47),
(131, 26),
(131, 30),
(132, 30),
(132, 36),
(132, 41),
(133, 36),
(134, 33),
(134, 36),
(134, 44),
(134, 67),
(134, 75),
(135, 19),
(135, 33),
(135, 36),
(135, 44),
(135, 63),
(135, 75),
(136, 30),
(136, 57),
(136, 63),
(137, 61),
(137, 63),
(138, 30),
(138, 63),
(139, 26),
(139, 30),
(139, 36),
(139, 63),
(140, 13),
(140, 17),
(140, 22),
(140, 27),
(140, 40),
(140, 44),
(140, 46),
(140, 54),
(141, 26),
(141, 30),
(141, 36),
(141, 44),
(141, 63),
(141, 70),
(142, 15),
(144, 13),
(144, 15),
(144, 36),
(144, 44),
(144, 63),
(144, 67),
(146, 47),
(146, 53),
(147, 6),
(148, 30);

--
-- Dumping data for table `bb_lkp_player_type_skill_access`
--

INSERT INTO `bb_lkp_player_type_skill_access` (`player_type_id`, `skill_category_id`, `access_roll`) VALUES
(2, 2, 'N'),
(2, 5, 'N'),
(3, 1, 'N'),
(3, 5, 'N'),
(4, 1, 'N'),
(4, 3, 'N'),
(4, 5, 'N'),
(5, 1, 'N'),
(5, 4, 'N'),
(5, 5, 'N'),
(2, 1, 'D'),
(2, 4, 'D'),
(2, 3, 'D'),
(3, 2, 'D'),
(3, 4, 'D'),
(3, 3, 'D'),
(4, 2, 'D'),
(4, 4, 'D'),
(5, 2, 'D'),
(5, 3, 'D'),
(9, 2, 'N'),
(10, 4, 'N'),
(9, 1, 'D'),
(9, 3, 'D'),
(9, 4, 'D'),
(10, 1, 'D'),
(10, 2, 'D'),
(10, 3, 'D'),
(12, 1, 'N'),
(13, 1, 'N'),
(13, 3, 'N'),
(14, 1, 'N'),
(14, 2, 'N'),
(15, 1, 'N'),
(15, 4, 'N'),
(12, 2, 'D'),
(12, 3, 'D'),
(12, 4, 'D'),
(13, 2, 'D'),
(13, 4, 'D'),
(14, 3, 'D'),
(14, 4, 'D'),
(15, 2, 'D'),
(15, 3, 'D'),
(20, 1, 'N'),
(20, 5, 'N'),
(21, 1, 'N'),
(21, 4, 'N'),
(21, 5, 'N'),
(22, 1, 'N'),
(22, 4, 'N'),
(22, 5, 'N'),
(20, 2, 'D'),
(20, 3, 'D'),
(20, 4, 'D'),
(21, 2, 'D'),
(21, 3, 'D'),
(22, 2, 'D'),
(22, 3, 'D'),
(26, 1, 'N'),
(27, 1, 'N'),
(27, 3, 'N'),
(28, 1, 'N'),
(28, 4, 'N'),
(29, 4, 'N'),
(26, 2, 'D'),
(26, 4, 'D'),
(26, 5, 'D'),
(26, 3, 'D'),
(27, 2, 'D'),
(27, 4, 'D'),
(27, 5, 'D'),
(28, 2, 'D'),
(28, 5, 'D'),
(28, 3, 'D'),
(29, 1, 'D'),
(29, 2, 'D'),
(29, 3, 'D'),
(29, 5, 'D'),
(33, 1, 'N'),
(33, 3, 'N'),
(34, 4, 'N'),
(34, 1, 'N'),
(35, 1, 'N'),
(35, 2, 'N'),
(35, 4, 'N'),
(33, 2, 'D'),
(33, 4, 'D'),
(34, 2, 'D'),
(34, 3, 'D'),
(35, 3, 'D'),
(37, 1, 'N'),
(37, 2, 'D'),
(37, 3, 'D'),
(37, 4, 'D'),
(39, 1, 'N'),
(40, 1, 'N'),
(40, 4, 'N'),
(41, 1, 'N'),
(41, 4, 'N'),
(42, 4, 'N'),
(39, 2, 'D'),
(39, 4, 'D'),
(39, 3, 'D'),
(40, 2, 'D'),
(40, 3, 'D'),
(40, 5, 'D'),
(41, 2, 'D'),
(41, 3, 'D'),
(42, 1, 'D'),
(42, 2, 'D'),
(42, 3, 'D'),
(42, 5, 'D'),
(46, 2, 'N'),
(47, 4, 'N'),
(47, 1, 'N'),
(48, 4, 'N'),
(46, 1, 'D'),
(46, 3, 'D'),
(46, 4, 'D'),
(47, 2, 'D'),
(47, 3, 'D'),
(48, 1, 'D'),
(48, 2, 'D'),
(48, 3, 'D'),
(53, 2, 'N'),
(54, 1, 'N'),
(54, 4, 'N'),
(55, 4, 'N'),
(55, 1, 'N'),
(56, 4, 'N'),
(53, 1, 'D'),
(53, 3, 'D'),
(53, 4, 'D'),
(54, 2, 'D'),
(54, 3, 'D'),
(55, 2, 'D'),
(55, 3, 'D'),
(56, 1, 'D'),
(56, 2, 'D'),
(56, 3, 'D'),
(60, 1, 'N'),
(60, 2, 'N'),
(61, 1, 'N'),
(61, 2, 'N'),
(62, 1, 'N'),
(62, 2, 'N'),
(62, 3, 'N'),
(63, 1, 'N'),
(63, 2, 'N'),
(64, 4, 'N'),
(60, 4, 'D'),
(60, 3, 'D'),
(61, 4, 'D'),
(61, 3, 'D'),
(62, 4, 'D'),
(63, 3, 'D'),
(63, 4, 'D'),
(64, 1, 'D'),
(64, 2, 'D'),
(64, 3, 'D'),
(67, 1, 'N'),
(68, 1, 'N'),
(68, 2, 'N'),
(69, 1, 'N'),
(69, 4, 'N'),
(70, 4, 'N'),
(67, 2, 'D'),
(67, 4, 'D'),
(67, 3, 'D'),
(68, 4, 'D'),
(68, 3, 'D'),
(69, 2, 'D'),
(69, 3, 'D'),
(70, 1, 'D'),
(70, 2, 'D'),
(70, 3, 'D'),
(74, 1, 'N'),
(74, 2, 'N'),
(75, 1, 'N'),
(75, 2, 'N'),
(75, 3, 'N'),
(76, 1, 'N'),
(76, 2, 'N'),
(77, 1, 'N'),
(77, 2, 'N'),
(78, 1, 'N'),
(78, 2, 'N'),
(74, 3, 'D'),
(74, 4, 'D'),
(75, 4, 'D'),
(76, 3, 'D'),
(76, 4, 'D'),
(77, 3, 'D'),
(77, 4, 'D'),
(78, 3, 'D'),
(78, 4, 'D'),
(81, 1, 'N'),
(82, 1, 'N'),
(82, 2, 'N'),
(83, 1, 'N'),
(83, 3, 'N'),
(84, 1, 'N'),
(84, 4, 'N'),
(81, 2, 'D'),
(81, 3, 'D'),
(81, 4, 'D'),
(82, 3, 'D'),
(82, 4, 'D'),
(83, 2, 'D'),
(83, 4, 'D'),
(84, 2, 'D'),
(84, 3, 'D'),
(88, 2, 'N'),
(89, 4, 'N'),
(88, 1, 'D'),
(88, 4, 'D'),
(88, 3, 'D'),
(89, 2, 'D'),
(89, 1, 'D'),
(89, 3, 'D'),
(92, 1, 'N'),
(92, 2, 'D'),
(92, 4, 'D'),
(92, 3, 'D'),
(95, 1, 'N'),
(96, 1, 'N'),
(96, 2, 'N'),
(97, 1, 'N'),
(97, 4, 'N'),
(98, 1, 'N'),
(98, 4, 'N'),
(99, 1, 'N'),
(99, 2, 'N'),
(95, 2, 'D'),
(95, 2, 'D'),
(95, 3, 'D'),
(95, 4, 'D'),
(96, 3, 'D'),
(96, 4, 'D'),
(97, 2, 'D'),
(97, 3, 'D'),
(98, 2, 'D'),
(98, 3, 'D'),
(99, 3, 'D'),
(99, 4, 'D'),
(102, 1, 'N'),
(103, 3, 'N'),
(103, 1, 'N'),
(102, 2, 'D'),
(102, 3, 'D'),
(102, 4, 'D'),
(103, 2, 'D'),
(103, 4, 'D'),
(105, 4, 'N'),
(105, 5, 'N'),
(105, 1, 'D'),
(105, 2, 'D'),
(105, 3, 'D'),
(106, 1, 'N'),
(106, 2, 'N'),
(106, 4, 'D'),
(106, 3, 'D'),
(106, 5, 'D'),
(107, 4, 'N'),
(107, 1, 'D'),
(107, 2, 'D'),
(107, 3, 'D'),
(107, 5, 'D'),
(108, 1, 'N'),
(108, 2, 'N'),
(108, 4, 'N'),
(108, 3, 'D'),
(109, 4, 'N'),
(109, 1, 'D'),
(109, 2, 'D'),
(109, 3, 'D'),
(110, 4, 'N'),
(110, 2, 'D'),
(110, 1, 'D'),
(110, 3, 'D'),
(111, 1, 'N'),
(112, 1, 'N'),
(112, 3, 'N'),
(113, 1, 'N'),
(113, 4, 'N'),
(114, 4, 'N'),
(111, 2, 'D'),
(111, 3, 'D'),
(111, 4, 'D'),
(112, 2, 'D'),
(112, 4, 'D'),
(113, 2, 'D'),
(113, 3, 'D'),
(114, 1, 'D'),
(114, 2, 'D'),
(114, 3, 'D'),
(118, 1, 'N'),
(118, 2, 'N'),
(119, 1, 'N'),
(119, 2, 'N'),
(119, 3, 'N'),
(120, 1, 'N'),
(120, 2, 'N'),
(121, 1, 'N'),
(121, 2, 'N'),
(118, 3, 'D'),
(118, 4, 'D'),
(119, 4, 'D'),
(120, 3, 'D'),
(120, 4, 'D'),
(121, 3, 'D'),
(121, 4, 'D'),
(122, 2, 'N'),
(123, 2, 'N'),
(124, 4, 'N'),
(125, 2, 'N'),
(126, 4, 'N'),
(127, 2, 'N'),
(122, 1, 'D'),
(122, 3, 'D'),
(122, 4, 'D'),
(123, 1, 'D'),
(123, 3, 'D'),
(123, 4, 'D'),
(124, 1, 'D'),
(124, 2, 'D'),
(124, 3, 'D'),
(125, 1, 'D'),
(125, 3, 'D'),
(125, 4, 'D'),
(126, 2, 'D'),
(126, 1, 'D'),
(126, 3, 'D'),
(127, 1, 'D'),
(127, 3, 'D'),
(127, 4, 'D'),
(129, 1, 'N'),
(130, 1, 'N'),
(130, 3, 'N'),
(131, 1, 'N'),
(131, 2, 'N'),
(132, 1, 'N'),
(132, 4, 'N'),
(133, 1, 'N'),
(133, 4, 'N'),
(134, 4, 'N'),
(129, 2, 'D'),
(129, 4, 'D'),
(129, 3, 'D'),
(130, 4, 'D'),
(130, 2, 'D'),
(131, 4, 'D'),
(131, 3, 'D'),
(132, 2, 'D'),
(132, 3, 'D'),
(133, 2, 'D'),
(133, 3, 'D'),
(134, 1, 'D'),
(134, 2, 'D'),
(134, 3, 'D'),
(136, 4, 'N'),
(136, 1, 'N'),
(137, 1, 'N'),
(137, 3, 'N'),
(138, 4, 'N'),
(138, 1, 'N'),
(139, 4, 'N'),
(139, 1, 'N'),
(140, 4, 'N'),
(136, 2, 'D'),
(136, 3, 'D'),
(137, 2, 'D'),
(137, 4, 'D'),
(138, 2, 'D'),
(138, 3, 'D'),
(139, 2, 'D'),
(139, 3, 'D'),
(140, 1, 'D'),
(140, 2, 'D'),
(140, 3, 'D'),
(142, 4, 'N'),
(143, 1, 'N'),
(143, 4, 'N'),
(143, 5, 'N'),
(144, 4, 'N'),
(144, 5, 'N'),
(142, 1, 'N'),
(142, 5, 'N'),
(142, 3, 'D'),
(142, 2, 'D'),
(143, 2, 'D'),
(143, 3, 'D'),
(144, 1, 'D'),
(144, 2, 'D'),
(144, 3, 'D'),
(145, 1, 'N'),
(145, 2, 'N'),
(146, 1, 'N'),
(146, 2, 'N'),
(146, 3, 'N'),
(147, 1, 'N'),
(147, 2, 'N'),
(148, 1, 'N'),
(148, 2, 'N'),
(148, 3, 'D'),
(145, 3, 'D'),
(145, 4, 'D'),
(146, 4, 'D'),
(147, 3, 'D'),
(147, 4, 'D'),
(148, 3, 'D'),
(148, 4, 'D');

--
-- Dumping data for table `bb_lkp_player_type_stats`
--

INSERT INTO `bb_lkp_player_type_stats` (`player_type_id`, `ruleset_id`, `mv`, `st`, `ag`, `av`, `price`) VALUES
(1, 1, 6, 6, 3, 10, 430000),
(2, 1, 6, 2, 3, 7, 40000),
(3, 1, 7, 3, 3, 7, 50000),
(4, 1, 7, 3, 3, 7, 70000),
(5, 1, 7, 3, 3, 8, 90000),
(6, 1, 6, 2, 3, 7, 60000),
(8, 1, 7, 2, 3, 7, 150000),
(9, 1, 5, 1, 3, 5, 20000),
(10, 1, 5, 5, 2, 9, 140000),
(12, 1, 6, 3, 3, 7, 50000),
(13, 1, 6, 3, 3, 7, 70000),
(14, 1, 6, 3, 3, 7, 70000),
(15, 1, 6, 3, 3, 7, 90000),
(16, 1, 6, 4, 3, 8, 270000),
(19, 1, 6, 6, 2, 8, 310000),
(20, 1, 5, 3, 3, 8, 40000),
(21, 1, 6, 3, 3, 8, 80000),
(22, 1, 4, 4, 2, 9, 110000),
(23, 1, 5, 5, 3, 9, 300000),
(26, 1, 7, 3, 3, 7, 50000),
(27, 1, 7, 3, 3, 7, 70000),
(28, 1, 7, 3, 3, 8, 90000),
(29, 1, 6, 5, 2, 8, 150000),
(30, 1, 6, 6, 3, 8, 340000),
(33, 1, 6, 3, 3, 8, 60000),
(34, 1, 6, 3, 3, 8, 90000),
(35, 1, 6, 3, 3, 7, 80000),
(36, 1, 6, 5, 4, 9, 390000),
(37, 1, 6, 3, 3, 7, 40000),
(39, 1, 6, 3, 3, 7, 40000),
(40, 1, 4, 3, 2, 9, 70000),
(41, 1, 6, 4, 2, 9, 130000),
(42, 1, 5, 5, 2, 8, 150000),
(43, 1, 3, 4, 3, 9, 90000),
(46, 1, 8, 2, 3, 7, 60000),
(47, 1, 6, 4, 1, 9, 80000),
(48, 1, 6, 5, 1, 9, 140000),
(49, 1, 7, 4, 1, 9, 250000),
(53, 1, 6, 2, 3, 7, 40000),
(54, 1, 4, 4, 2, 9, 80000),
(55, 1, 6, 3, 3, 9, 80000),
(56, 1, 4, 5, 1, 9, 110000),
(57, 1, 3, 6, 1, 9, 270000),
(58, 1, 6, 4, 3, 9, 290000),
(60, 1, 7, 3, 4, 7, 70000),
(61, 1, 8, 2, 4, 7, 90000),
(62, 1, 7, 3, 4, 7, 90000),
(63, 1, 8, 3, 4, 7, 120000),
(64, 1, 2, 6, 1, 10, 120000),
(65, 1, 8, 3, 5, 7, 260000),
(67, 1, 4, 3, 2, 8, 40000),
(68, 1, 7, 3, 3, 7, 70000),
(69, 1, 6, 3, 3, 8, 90000),
(70, 1, 3, 5, 1, 9, 120000),
(71, 1, 6, 4, 2, 8, 220000),
(74, 1, 6, 3, 4, 8, 70000),
(75, 1, 7, 3, 4, 7, 80000),
(76, 1, 6, 3, 4, 7, 90000),
(77, 1, 7, 3, 4, 8, 100000),
(78, 1, 7, 3, 4, 7, 110000),
(79, 1, 7, 3, 4, 7, 210000),
(81, 1, 6, 3, 3, 8, 50000),
(82, 1, 8, 2, 3, 7, 70000),
(83, 1, 6, 3, 3, 8, 70000),
(84, 1, 7, 3, 3, 8, 90000),
(85, 1, 7, 4, 4, 8, 320000),
(88, 1, 5, 2, 3, 6, 30000),
(89, 1, 2, 6, 1, 10, 120000),
(90, 1, 2, 7, 1, 10, 300000),
(91, 1, 8, 3, 4, 7, 200000),
(92, 1, 5, 3, 2, 7, 40000),
(93, 1, 5, 6, 1, 9, 380000),
(95, 1, 4, 3, 2, 8, 40000),
(96, 1, 7, 3, 3, 7, 70000),
(97, 1, 6, 3, 3, 8, 90000),
(98, 1, 4, 4, 2, 9, 110000),
(99, 1, 8, 3, 3, 8, 120000),
(102, 1, 5, 3, 3, 9, 50000),
(103, 1, 5, 3, 3, 8, 70000),
(105, 1, 4, 5, 1, 9, 110000),
(106, 1, 9, 2, 4, 7, 80000),
(107, 1, 4, 5, 1, 9, 140000),
(108, 1, 6, 4, 4, 8, 110000),
(109, 1, 6, 5, 1, 9, 180000),
(110, 1, 5, 5, 2, 9, 140000),
(111, 1, 5, 3, 2, 7, 40000),
(112, 1, 6, 3, 2, 7, 70000),
(113, 1, 6, 3, 2, 8, 90000),
(114, 1, 4, 5, 1, 9, 100000),
(118, 1, 6, 3, 4, 7, 60000),
(119, 1, 6, 3, 4, 7, 70000),
(120, 1, 8, 3, 4, 7, 100000),
(121, 1, 7, 3, 4, 8, 110000),
(122, 1, 6, 2, 3, 7, 40000),
(123, 1, 6, 2, 3, 7, 40000),
(124, 1, 4, 5, 1, 9, 110000),
(125, 1, 7, 2, 3, 7, 70000),
(126, 1, 3, 7, 3, 7, 70000),
(127, 1, 6, 2, 3, 7, 40000),
(129, 1, 6, 3, 3, 7, 50000),
(130, 1, 6, 3, 3, 7, 70000),
(131, 1, 7, 3, 3, 7, 90000),
(132, 1, 6, 3, 3, 7, 90000),
(133, 1, 6, 4, 2, 8, 110000),
(134, 1, 5, 5, 1, 8, 140000),
(135, 1, 5, 6, 1, 8, 330000),
(136, 1, 4, 3, 2, 9, 70000),
(137, 1, 6, 3, 3, 8, 80000),
(138, 1, 5, 3, 3, 9, 80000),
(139, 1, 5, 3, 2, 8, 90000),
(140, 1, 4, 7, 1, 10, 160000),
(141, 1, 5, 4, 3, 8, 220000),
(142, 1, 6, 3, 3, 8, 60000),
(143, 1, 5, 4, 3, 9, 100000),
(144, 1, 5, 5, 2, 8, 150000),
(145, 1, 6, 3, 4, 8, 70000),
(146, 1, 6, 3, 4, 8, 90000),
(147, 1, 8, 3, 4, 7, 90000),
(148, 1, 7, 3, 4, 8, 100000);

--
-- Dumping data for table `bb_lkp_race`
--

INSERT INTO `bb_lkp_race` (`race_id`, `bb1_id`, `description`, `single_description`, `reroll_price`, `short_description`) VALUES
(1, 19, 'Ogre', NULL, 70000, 'OGR'),
(2, 22, 'Underworld', 'Underworldling', 70000, 'UWR'),
(3, 18, 'Nurgle', NULL, 70000, 'NUR'),
(4, 13, 'Amazon', NULL, 50000, 'AMZ'),
(5, 3, 'Skaven', NULL, 60000, 'SKV'),
(6, 23, 'Khorne', NULL, 70000, 'KHO'),
(7, 21, 'Chaos Dwarf', NULL, 70000, 'CDW'),
(8, 20, 'Vampire', NULL, 70000, 'VAM'),
(9, 5, 'Lizardman', NULL, 60000, 'LIZ'),
(10, 4, 'Orc', NULL, 60000, 'ORC'),
(11, 7, 'Wood Elf', NULL, 50000, 'WEL'),
(12, 11, 'Halfling', NULL, 60000, 'HLF'),
(13, 10, 'Undead', NULL, 70000, 'UDE'),
(14, 9, 'Dark Elf', NULL, 50000, 'DEL'),
(15, 1, 'Human', NULL, 50000, 'HUM'),
(16, 17, 'Necromantic', NULL, 70000, 'NEC'),
(17, 16, 'Khemri', NULL, 70000, 'KHM'),
(18, 14, 'Elf', NULL, 50000, 'ELF'),
(19, 12, 'Norse', NULL, 60000, 'NRS'),
(20, 6, 'Goblin', NULL, 60000, 'GOB'),
(21, 2, 'Dwarf', NULL, 50000, 'DWF'),
(22, 8, 'Chaos', NULL, 60000, 'CHO'),
(23, 15, 'High Elf', NULL, 50000, 'HEL');

--
-- Dumping data for table `bb_lkp_reroll_type`
--

INSERT INTO `bb_lkp_reroll_type` (`reroll_type_id`, `description`) VALUES
(0, 'None'),
(1, 'Skill re-roll'),
(2, 'Team re-roll'),
(3, 'Pro re-roll'),
(4, 'Team re-roll used'),
(5, 'Skill re-roll used'),
(6, 'Pro re-roll used'),
(7, 'Team re-roll but Loner failed'),
(8, 'Team re-roll after passing Loner'),
(9, 'Skill reroll available but not used'),
(10, 'Re-roll lost via failed loner');

--
-- Dumping data for table `bb_lkp_roll_aim`
--

INSERT INTO `bb_lkp_roll_aim` (`roll_aim_id`, `description`) VALUES
(1, 'Agility'),
(2, 'Armour Value'),
(3, 'Fixed (2+)'),
(4, 'Fixed (4+)'),
(5, 'Special');

--
-- Dumping data for table `bb_lkp_roll_modifiers`
--

INSERT INTO `bb_lkp_roll_modifiers` (`modifier_id`, `description`, `bb1_log_desc`, `fixed_value`) VALUES
(1, 'Interception', 'Interception', -2),
(2, 'Tackle Zone', 'TZ', NULL),
(3, 'Short Pass', 'SP', 0),
(4, 'Long Pass', 'LP', -1),
(5, 'Long Bomb', 'LB', -2),
(6, 'Quick Pass', 'QP', 1),
(7, 'Accurate Pass', 'Accurate Pass', 1),
(8, 'Adjacent Team-Mates', 'Team-Mates', 2);

--
-- Dumping data for table `bb_lkp_roll_outcome`
--

INSERT INTO `bb_lkp_roll_outcome` (`outcome_id`, `description`, `turnover_flag`) VALUES
(1, 'Turnover', b'1'),
(2, 'Wrestled (success)', b'0'),
(3, 'Defender down (on the spot)', b'0'),
(4, 'Pushed', b'0'),
(5, 'Defender down (& pushed)', b'0'),
(6, 'Nothing', b'0'),
(7, 'Crowd-surfed', b'0'),
(8, 'Attacker Down', b'1'),
(9, 'Both down', b'1'),
(10, 'Wrestled (failure)', b'0'),
(11, 'Turnover (catch failed)', b'1'),
(12, 'Turnover (GFI failed)', b'1'),
(13, 'Failure', b'0'),
(14, 'Success', b'0'),
(15, 'Stunned', b'0'),
(16, 'KO''d', b'0'),
(17, 'Injured', b'0'),
(18, 'Inaccurate Pass!', b'0'),
(19, 'Fumble!', b'0'),
(20, 'Accurate pass!', b'0'),
(21, 'Defender down, pushed, unable to follow up', b'0'),
(22, 'Pushed, unable to follow up', b'0');

--
-- Dumping data for table `bb_lkp_roll_type`
--

INSERT INTO `bb_lkp_roll_type` (`roll_type_id`, `dice_type_id`, `description`, `bb1_desc`, `fail_turnover_id`, `roll_aim_id`, `roll_modifier`, `optional_modifier_flag`, `reroll_skill_id`, `modify_desc`) VALUES
(1, 1, 'Always Hungry', NULL, 0, 3, 0, b'0', NULL, NULL),
(2, 1, 'Blood Lust', 'Blood Lust', 0, 3, 0, b'0', NULL, NULL),
(3, 2, 'Tentacles', 'Tentacles', 0, 5, 0, b'0', NULL, NULL),
(4, 1, 'Bonehead', 'Bone-head', 0, 3, 0, b'0', NULL, NULL),
(5, 1, 'Catch', 'Catch', 2, 1, 0, b'1', 6, 'accurate pass +1, receiving hand-off +1,  enemy tackle zone -1'),
(6, 1, 'Dauntless', NULL, 0, 3, 0, b'1', NULL, 'difference in strength... so usually 2+'),
(7, 1, 'Dodge', 'Dodge', 1, 1, 1, b'1', 7, 'enemy tackle zone -1'),
(8, 1, 'Foul Appearance', 'Foul Appearance', 0, 3, 0, b'0', NULL, NULL),
(9, 1, 'Go For It', 'Going for it', 1, 3, 0, b'1', 60, 'weather (snowy?)'),
(10, 1, 'Jump Up', 'Jump Up', 0, 1, 2, b'0', NULL, NULL),
(11, 1, 'KO Recovery', 'KO', 0, 4, 0, b'1', NULL, 'Babes'),
(12, 1, 'Leap', 'Leap', 1, 1, 0, b'1', NULL, 'Very Long Legs +1'),
(13, 1, 'Pass', 'Launch', 2, 5, 0, b'1', 47, '-1 enemy tackle zones, also distance-related modifiers'),
(14, 1, 'Pick-up Ball', 'Pick-up', 1, 1, 1, b'1', 61, '-1 enemy tackle zones'),
(15, 1, 'Really Stupid', 'Really Stupid', 0, 4, 0, b'1', NULL, '-2 if no friendly player next to him'),
(16, 1, 'Regeneration', NULL, 0, 4, 0, b'0', NULL, NULL),
(17, 1, 'Wild Animal', NULL, 0, 3, 0, b'1', NULL, '+2 if taking a block/blitz action'),
(18, 1, 'Loner', 'Loner', 0, 4, 0, b'0', NULL, NULL),
(19, 1, 'Interception', 'Interception', 0, 1, -2, b'1', NULL, '-1 per enemy tackle zone'),
(20, 1, 'Pro', NULL, 0, 4, 0, b'0', NULL, NULL),
(21, 4, 'Vanilla block', NULL, 1, NULL, 0, b'0', NULL, NULL),
(22, 4, 'Blitz', 'Blitz', 1, NULL, 0, b'0', NULL, NULL),
(23, 4, 'Frenzy (2nd block)', 'Frenzy', 1, NULL, 0, b'0', NULL, NULL),
(24, 4, 'Multiple Block', NULL, 1, NULL, 0, b'0', NULL, NULL),
(25, 2, 'Shadowing', NULL, 0, 5, 0, b'0', NULL, NULL),
(26, 7, 'Kickoff coin toss', NULL, 0, NULL, 0, b'0', NULL, NULL),
(27, 5, 'Casualty Type', NULL, 0, NULL, 0, b'0', NULL, 'An apothecary can reroll this once per match. '),
(28, 2, 'Weather', NULL, 0, NULL, 0, b'0', NULL, NULL),
(29, 2, 'Armour (block)', NULL, 0, 2, 0, b'1', NULL, NULL),
(30, 2, 'Armour (piling on)', NULL, 0, 2, 0, b'1', NULL, NULL),
(31, 2, 'Armour (stab)', NULL, 0, 2, 0, b'1', NULL, 'Only modifier possible is +1 for Stakes'),
(32, 2, 'Armour (foul)', NULL, 0, 2, 0, b'1', NULL, 'Assists from both sides, and chainsaw'),
(33, 2, 'Armour (chainsaw)', 'Chainsaw', 0, 2, 3, b'0', NULL, NULL),
(34, 1, 'Chainsaw', NULL, 1, 3, 0, b'0', NULL, NULL),
(35, 2, 'Armour (block fail)', NULL, 0, 2, 0, b'0', NULL, NULL),
(36, 2, 'Armour (dodge fail)', NULL, 0, 2, 0, b'0', NULL, NULL),
(37, 2, 'Armour (GFI fail)', NULL, 0, 2, 0, b'0', NULL, NULL),
(38, 2, 'Kickoff', NULL, 0, 5, 0, b'0', NULL, NULL),
(39, 2, 'Injury', 'Injury', 0, 5, 0, b'1', NULL, '+1 Mighty Blow, +1 Stunty'),
(40, 2, 'Injury (Decay 2nd roll)', NULL, 0, 5, 0, b'1', NULL, '+1 Mighty Blow, +1 Stunty'),
(41, 1, 'Sweltering Heat', NULL, 0, 3, 0, b'0', NULL, NULL),
(42, 2, 'Bribe', NULL, 1, 3, 0, b'0', NULL, NULL),
(43, 1, 'Bomb Blast Proximity', 'Bomb', 0, 4, 0, b'0', NULL, NULL),
(44, 2, 'Armour (not a block)', NULL, 0, 2, 0, b'0', NULL, NULL),
(45, 1, 'Throw Team-Mate', 'Throw Team-Mate', 0, 3, -1, b'1', NULL, 'effectively 2+, all you are trying to avoid is a fumble'),
(46, 8, 'Turnover', NULL, 0, NULL, 0, b'0', NULL, NULL);

--
-- Dumping data for table `bb_lkp_ruleset`
--

INSERT INTO `bb_lkp_ruleset` (`ruleset_id`, `description`) VALUES
(1, 'Blood Bowl 1');

--
-- Dumping data for table `bb_lkp_scoring_system`
--

INSERT INTO `bb_lkp_scoring_system` (`scoring_system_id`, `description`) VALUES
(1, '3 points for a win, 1 point for a draw, tie-breaker is TD difference'),
(2, '3 points for a win, 1 point for a draw, tie-breaker is TD difference plus (0.5* casualty difference)');

--
-- Dumping data for table `bb_lkp_skill`
--

INSERT INTO `bb_lkp_skill` (`skill_id`, `bb1_desc`, `human_desc`, `skill_category_id`, `long_description`) VALUES
(1, 'StripBall', 'Strip Ball', 1, 'When a player with this skill blocks an opponent with the ball, applying a "Pushed" or "Defender Stumbles" result will cause the opposing player to drop the ball in the square that they are pushed to, even if the opposing player is not Knocked Down.'),
(2, 'STR+', '+ Strength', 0, NULL),
(3, 'AGI+', '+ Agility', 0, NULL),
(4, 'MA+', '+ Movement', 0, NULL),
(5, 'AV+', '+ Armour Value', 0, NULL),
(6, 'Catch', 'Catch', 2, 'A player who has the Catch skill is allowed to re-roll the D6 if he fails a catch roll. It also allows the player to re-roll the D6 if he drops a hand-off or fails to make an interception.'),
(7, 'Dodge', 'Dodge', 2, 'A player with the Dodge skill is adept at slipping away from opponents, and  is allowed to re-roll the D6 if he fails to dodge out of any of an \nopposing player''s tackle zones. However, the player may only re-roll one failed Dodge roll per turn. In addition, the Dodge skill, if used, affects the results rolled on the Block dice, as explained in the  Blocking rules.'),
(8, 'Sprint', 'Sprint', 2, 'The player may attempt to move up to three extra squares rather than the normal two when Going For It. His coach must still roll to see if the player is Knocked Down in each extra square he enters.'),
(9, 'PassBlock', 'Pass Block', 1, 'A player with this skill is allowed to move up to three squares when the opposing coach announces that one of his players is going to pass the ball (but not a bomb from a Hail Mary Pass). A player may not make the move unless able to reach a legal destination and may not follow a route that would not allow them to reach a legal destination. A legal destination puts the player in a position to attempt an interception, an empty square that is the target of the pass, or with his tackle zone on the thrower or catcher.'),
(10, 'FoulAppearance', 'Foul Appearance', 5, 'The player''s appearance is so horrible that any opposing player that wants to block the player (or use a special attack that takes the place of a block) must first roll a D6 and score 2 or more. If the opposing player rolls a 1 he is too revolted to make the block and it is wasted (though the opposing team does not suffer a turnover). '),
(11, 'Leap', 'Leap', 2, 'A player with the Leap skill is allowed to jump to any empty square within 2 squares even if it requires jumping over a player from either team. Making a leap costs the player two squares of movement. In order to make the leap, move the player to any empty square 1 to 2 squares from their current square and then make an Agility roll for the player. No modifiers apply to this D6 roll unless he has Very Long Legs. The player does not have to dodge to leave the square he starts in. If the player successfully makes the D6 roll then they make a perfect jump and may carry on moving. If the player fails the Agility roll then he is Knocked Down in the square that he was leaping to, and the opposing coach makes an Armour roll to see if he was injured. A failed leap causes a turnover, and the moving team''s turn ends immediately. A player may only use the Leap skill once per Action.'),
(12, 'ExtraArm', 'Extra Arms', 5, 'A player with one or more extra arms may add 1 to any attempt to pick up, catch or intercept.'),
(13, 'MightyBlow', 'Mighty Blow', 4, 'Add 1 to any Armour or Injury roll made by a player with this skill when an opponent is Knocked Down by this player during a block. Note that you only modify one of the dice rolls, so if you decide to use Mighty Blow to modify the Armour roll, you may not modify the Injury roll as well. Mighty Blow cannot be used with the Stab or Chainsaw skills.'),
(14, 'Leader', 'Leader', 3, 'The player is a natural leader and commands the rest of the team from the back-field as he prepares to throw the ball. A team with one or more players with the Leader skill may take a single Leader Re-roll counter and add it to their team re-rolls at the start of the game and at half time after any Master Chef rolls. The Leader re-roll is used exactly the same in every way as a normal Team re-roll with all the same restrictions. In addition, the Leader re-roll may only be used so long as at least one player with the Leader skill is on the pitch - even if they are Prone or Stunned! Re-rolls from Leader may be carried over into Overtime if not used, but the team does not receive a new Leader re-roll at the start of Overtime. '),
(15, 'Horns', 'Horns', 5, 'A player with horns may use them to butt an opponent. This adds 1 to the player''s Strength when he makes a block. However, the player may only use this ability as part of a Blitz, and only if he has moved at least one square before he makes the block (standing up at the start of your Action does not count!). If the player has the Frenzy skill, then the Horns bonus applies on the second block if it applied on the first.'),
(16, 'TwoHeads', 'Two Heads', 5, 'Having two heads enables this player to watch where he is going and the opponent  trying to make sure he does not get there at the same time. Add 1 to all Dodge rolls the player makes. '),
(17, 'StandFirm', 'Stand Firm', 4, 'A player with this skill may choose to not be pushed back as the result of a block. He may choose to ignore being pushed by "Pushed" results, and to have "Knock-down" results knock the player down in the square where he started. If a player is pushed back into a player using Stand Firm then neither player moves.'),
(18, 'AlwaysHungry', 'Always Hungry', 7, 'The player is always ravenously hungry - and what''s more, he''ll eat absolutely anything! Should the player ever use the Throw Team-Mate skill, roll a D6 after he has finished moving, but before he throws his team-mate. On a 2+ continue with the throw. On a roll of 1 he attempts to eat the unfortunate team-mate! Roll the D6 again, a second 1 means that he successfully scoffs the team-mate down, which kills the team-mate without opportunity for recovery (Apothecaries, Regeneration or anything else cannot be used). If the team-mate had the ball it will scatter once from the team-mate''s square. If the second roll is 2-6 the team-mate squirms free and the Pass Action is automatically treated as a fumbled pass. Fumble the player with the Right Stuff skill as normal.'),
(19, 'Regeneration', 'Regeneration', 6, 'If the player suffers a Casualty result on the Injury table, then roll a D6 for Regeneration after the roll on the Casualty table and after any Apothecary roll, if allowed. On a result of 1-3, the player suffers the result of this injury. On a 4-6, the player will heal the injury after a short period of time to ''re-organise'' himself, and is placed in the Reserves box instead. Regeneration rolls may not be re-rolled. Note that an opposing player still earns Star Player points as normal for inflicting a Casualty result on a player with this skill, even if the result doesn''t affect the player in the normal way.'),
(20, 'Takeroot', 'Take Root', 7, 'Immediately after declaring an Action with this player, roll a D6. On a 2 or more, the player may take his Action as normal. On a 1, the player “takes root”, and his MA is considered 0 until a drive ends, or he is Knocked Down or Placed Prone (and no, players from his own team may not try and block him in order to try to knock him down!). A player that has taken root may not Go For It, be pushed back for any reason, or use any skill that would allow him to move out of his current square or be Placed Prone. The player may block adjacent players without following-up as part of a Block Action however if a player fails his Take Root roll as part of a Blitz Action he may not block that turn (he can still roll to stand up if he is Prone). '),
(21, 'Accurate', 'Accurate', 3, 'The player may add 1 to the D6 roll when he passes.'),
(22, 'BreakTackle', 'Break Tackle', 4, 'The player may use his Strength instead of his Agility when making a Dodge roll. For example, a player with Strength 4 and Agility 2 would count as having an Agility of 4 when making a Dodge roll. This skill may only be used once per turn.'),
(23, 'SneakyGit', 'Sneaky Git', 2, 'This player has the quickness and finesse to stick the boot to a downed opponent without drawing a referee''s attention unless he hears the armour crack. During a Foul Action a player with this skill is not ejected for rolling doubles on the Armour roll unless the Armour roll was successful.'),
(25, 'Chainsaw', 'Chainsaw', 6, 'A player armed with a chainsaw must attack with it instead of making a block as part of a Block or Blitz  Action. When the chainsaw is used to make an attack, roll a D6 instead of the Block dice. On a roll of 2 or more the  chainsaw  hits the opposing player, but on a roll of 1 it ''kicks  back'' and hits the wielder instead! Make an Armour roll for the player hit by the chainsaw, adding 3 to the score. If the roll beats the victim’s Armour value then the victim is Knocked Down and injured – roll on the Injury table. If the roll fails to beat the victim’s Armour value then the attack has no effect. A player armed with a chainsaw may take a Foul Action, and adds 3 to the Armour roll, but must roll for kick back as described above. A running chainsaw is a dangerous thing to carry around, so if a player holding a chainsaw is Knocked Down for any reason, the opposing coach is allowed to add 3 to his Armour roll to see if the player was injured. However, blocking a player with a chainsaw is equally dangerous! If an opponent knocks himself over when blocking the chainsaw  player then add 3 to his Armour roll. This skill may only be used once per turn (e.g., cannot be used with Frenzy or Multiple Block) and if used as part of a Blitz  Action, the player cannot continue moving after using it. Casualties caused by a chainsaw player do not count for Star Player points. '),
(26, 'Dauntless', 'Dauntless', 1, 'When a player attempts to block an opponent who is stronger than himself, the coach of the player with the Dauntless skill rolls a D6 and adds it to his strength. If the total is equal to or lower than the opponent''s Strength, the player must block using his normal Strength. If the total is greater, then the player with the Dauntless skill counts as having a Strength equal to his opponent''s when he makes the block.'),
(27, 'DirtyPlayer', 'Dirty Player', 1, 'Add 1 to any Armour roll or Injury roll made by a player with this skill when they make a Foul as part of a Foul Action. Note that you may only modify one of the dice rolls, so if you decide to use Dirty Player to modify the Armour roll, you may not modify the Injury roll as well.'),
(28, 'DivingCatch', 'Diving Catch', 2, 'The player is superb at diving to catch balls others cannot reach and jumping to more easily catch perfect passes. The player may add 1 to any catch roll from an accurate pass targeted to his square. In addition, the player can attempt to catch any pass, kick off or crowd throw-in, but not bouncing ball, that would land in an empty square in one of his tackle zones as if it had landed in his own square without leaving his current square. A failed catch will bounce from the Diving Catch player''s square. If there are two or more players attempting to use this skill then they get in each other’s way and neither can use it. '),
(29, 'DumpOff', 'Dump Off', 3, 'This skill allows the player to make a Quick Pass when an opponent declares that he will throw a block at him, allowing the player to get rid of the ball before he is hit. Work out the Dump-Off pass before the opponent makes his block. The normal throwing rules apply, except that neither team''s turn ends as a result of the throw, whatever it may be. After the throw is worked out your opponent completes the block, and then carries on with his turn. Dump-Off may not be used on the second block from an opponent with the Frenzy skill or in conjunction  with  the Bombardier or Throw Team-Mate skills. '),
(30, 'Block', 'Block', 1, 'A player with the Block skill is proficient at knocking opponents down. The Block skill, if used, affects the results rolled with the Block dice, as explained in the Blocking rules.'),
(31, 'BoneHead', 'Bonehead', 7, 'The player is not noted for his intelligence. Because of this you must roll a D6 immediately after declaring an Action for the player, but before taking the Action. On a roll of 1 he stands around trying to remember what it is he''s meant to be doing. The player can''t do anything for the turn, and the player''s team loses the declared Action for the turn. (So if a Bone-head player declares a Blitz Action and rolls a 1, then the team cannot declare another Blitz Action that turn.) The player loses his tackle zones and may not catch, intercept or pass, assist another player on a block or foul, or voluntarily move until he manages to roll a 2 or better at the start of a future Action or the drive ends.'),
(32, 'LongLegs', 'Very Long Legs', 5, 'The player is allowed to add 1 to the D6 roll whenever he attempts to intercept or uses the Leap skill. In addition, the Safe Throw skill may not be used to affect any Interception rolls made by this player.'),
(33, 'DisturbingPresence', 'Disturbing Presence', 5, 'This player''s presence is very disturbing, whether it is caused by a massive cloud of flies, sprays of soporific musk, an aura of random chaos or intense cold, or a pheromone that causes fear and panic. Regardless of the nature of this mutation, any player must subtract 1 from the D6 when they pass, intercept or catch for each opposing player with Disturbing Presence that is within three squares of them, even if the Disturbing Presence player is Prone or Stunned. '),
(34, 'DivingTackle', 'Diving Tackle', 2, 'The player may use this skill after an opposing player attempts to dodge out of any of his tackle zones. The opposing player must subtract 2 from his Dodge roll for leaving the player''s tackle zone. If a player is attempting to leave the tackle zone of several players that have the Diving Tackle skill, then only one of the opposing players may use Diving Tackle. Diving Tackle may be used on a re-rolled dodge if not declared for use on the first Dodge roll. Once the dodge is resolved but before any armour roll for the opponent (if needed), the Diving Tackle Player is Placed Prone in the square vacated by the dodging player but do not make an Armour or Injury roll for the Diving Tackle player.  '),
(35, 'Fend', 'Fend', 1, 'This player is very skilled at holding off would-be attackers. Opposing players may not follow-up blocks made against this player even if the Fend player is Knocked Down. The opposing player may still continue moving after blocking if he had declared a Blitz Action.'),
(36, 'Frenzy', 'Frenzy', 1, 'A player with this skill is a slavering psychopath who attacks his opponents in an uncontrollable rage. Unless otherwise overridden, this skill must always be used. When making a block, a player with this skill must always follow up if he can. If a "Pushed" or "Defender Stumbles" result was chosen, the player must immediately throw a second block against the same opponent so long as they are both still standing and adjacent. If possible, the player must also follow up this second block. If the frenzied player is performing a Blitz Action then he must pay a square of Movement and must make the second block unless he has no further movement and can''t Go For It again.'),
(37, 'Grab', 'Grab', 4, 'A player with this skill uses his great strength and prowess to grab his opponent and throw him around. To represent this, only while making a Block Action, if his block results in a push back he may choose any empty square adjacent to his opponent to push back his opponent. When making a Block or Blitz Action, Grab and Side Step will cancel each other out and the standard pushback rules apply. Grab will not work if there are no empty adjacent squares. A player with the Grab skill can never learn or gain the Frenzy skill through any means. Likewise, a player with the Frenzy skill can never learn or gain the Grab skill through any means.'),
(38, 'Guard', 'Guard', 4, 'A player with this skill assists an offensive or defensive block even if he is in another player''s tackle zone. This skill may not be used to assist a foul.'),
(39, 'HailMaryPass', 'Hail Mary Pass', 3, 'The player may throw the ball to any square on the playing pitch, no matter what the range: the range ruler is not used. Roll a D6. On a roll of 1 the player fumbles the throw, and the ball will bounce once from the thrower’s square. On a roll of 2-6, the player may make the pass. The Hail Mary pass may not be intercepted, but it is never accurate – the ball automatically misses and scatters three squares. Note that if you are lucky, the ball will scatter back into the target square! This skill may not be used in a Blizzard or with the Throw Team-Mate skill.'),
(40, 'Juggernaut', 'Juggernaut', 4, 'A player with this skill is virtually impossible to stop once he is in motion. If this player takes a Blitz Action, then opposing players may not use their Fend, Stand Firm or Wrestle skills against blocks, and he may choose to treat a "Both Down" result as if a "Pushed" result has been rolled instead.'),
(41, 'JumpUp', 'Jump Up', 2, 'If a player declares any Action other than a Block Action while he is down, he may stand up for free without paying the three squares of movement. The player may also declare a Block Action while Prone which requires an Agility roll with a +2 modifier to see if he can complete the Action. A successful roll means the player can stand up for free and block an adjacent opponent. A failed roll means the Block Action is wasted and the player may not stand up.'),
(44, 'Loner', 'Loner', 7, 'Loners, through inexperience, arrogance, animal ferocity or just plain stupidity, do not work well with the rest of the team. As a result, a Loner may use team re-rolls but has to roll a D6 first. On a roll of 4+, he may \nuse the team re-roll as normal. On a roll of 1-3 the original result stands without being re-rolled but the team re-roll is lost (i.e., used). '),
(45, 'NervesOfSteel', 'Nerves Of Steel', 3, 'The player ignores modifiers for enemy tackle zones when he attempts to pass, catch or intercept.'),
(46, 'NoHands', 'No Hands', 7, 'The player is unable to pick up, intercept or carry the ball and will fail any catch  roll automatically, either because he literally has no hands or because his hands  are full. If he attempts to pick up the ball then it will bounce, and will cause a turnover if it is his team''s turn.'),
(47, 'Pass', 'Pass', 3, 'A player with the Pass skill is allowed to re-roll the D6 if he throws an inaccurate pass or fumbles. '),
(48, 'PilingOn', 'Piling On', 4, 'The player may use this skill after he has made a block as part of one of his Block or Blitz Actions, but only if the Piling On player is currently standing adjacent to the victim and the victim was Knocked Down. You may re-roll the Armour roll or Injury roll for the victim. The Piling On player is Placed Prone in his own square - it is assumed that he rolls back there after flattening his opponent (do not make an Armour roll for him as he has been cushioned by the other player!). Piling On does not cause a turnover unless the Piling On player is carrying the ball. Piling On cannot be used with the Stab or Chainsaw skills.'),
(49, 'PrehensiveTail', 'Prehensile Tail', 5, 'The player has a long, thick tail which he can use to trip up opposing players. To represent this, opposing players must subtract 1 from the D6 roll if they attempt to dodge out of any of the player?s tackle zones.'),
(50, 'Pro', 'Pro', 1, 'Once per turn, a Pro is allowed to re-roll any one dice roll he has made other than Armour, Injury or Casualty, even if he is Prone or Stunned. However, before the re-roll may be made, his coach must roll a D6. On a roll of 4, 5 or 6 the re-roll may be made. On a roll of 1, 2 or 3 the original result stands and may not be re-rolled with a skill or team re-roll; however you can re-roll the Pro roll with a Team re-roll.'),
(51, 'ReallyStupid', 'Really Stupid', 7, 'This player is without doubt one of the dimmest creatures to ever take to a Blood Bowl pitch (which, considering the IQ of most other players, is really saying something!). Because of this you must roll a D6 immediately after declaring an Action for the player, but before taking the Action. If there are one or more players from the same team standing adjacent to the Really Stupid player''s square, and who aren''t Really Stupid, then add 2 to the D6 roll. On a result of 1-3 he stands around trying to remember what it is he’s meant to be doing. The player can’t do anything for the turn, and the player''s team loses the declared Action for that turn (for example, if a Really Stupid player declares a Blitz Action and fails the Really Stupid roll, then the team cannot declare another Blitz Action that turn). The player loses his tackle zones and may not catch, intercept or pass the ball, assist another player on a block or foul, or voluntarily move until he manages to roll a successful result for a Really Stupid roll at the start of a future Action or the drive ends. '),
(52, 'RightStuff', 'Right Stuff', 6, 'A player with the Right Stuff skill can be thrown by another player from his team who has the Throw Team-Mate skill. See the Throw Team-Mate skill entry below for details of how the player is thrown. When a player with this skill is thrown or fumbled and ends up in an unoccupied square, he must make a landing roll unless he landed on another player during the throw. A landing roll is an Agility roll with a -1 modifier for each opposing player’s tackle zone on the square he lands in. If he passes the roll he lands on his feet. If the landing roll is failed or he landed on another player during the throw he is Placed Prone and must pass an Armour roll to avoid injury. If the player is not injured during his landing he may take an Action later this turn if he has not already done so. A failed landing roll or landing in the crowd does not cause a turnover, unless he was holding the ball.'),
(53, 'SafeThrow', 'Safe Throw', 3, 'This player is an expert at throwing the ball in a way that makes it even more difficult for any opponent to intercept it. If a pass made by this player is ever intercepted then the Safe Throw player may make an unmodified Agility roll. If successful, the interception is cancelled out and the passing sequence continues as normal. In addition, if this player fumbles a pass of a ball (not a bomb or team-mate) on any roll other than a natural 1, he manages to keep hold of the ball instead of suffering a fumble and the team does not suffer a turnover.'),
(54, 'SecretWeapon', 'Secret Weapon', 6, 'Some players are armed with special pieces of equipment that are called ''secret weapons.'' Although the Blood Bowl rules specifically ban the use of any weapons, the game has a long history of teams trying to get weapons of some sort onto the pitch. Nonetheless, the use of secret weapons is simply not legal, and referees have a nasty habit of sending off players that use them. Once a drive ends that this player has played in at any point, the referee orders the player to be sent off to the dungeon to join players that have been caught committing fouls during the match, regardless of whether the player is still on the pitch or not.'),
(55, 'Shadowing', 'Shadowing', 1, 'The player may use this skill when a player performing an Action on the opposing team moves out of any of his tackle zones for any reason. The opposing coach rolls 2D6 adding his own player’s movement allowance and subtracting the Shadowing player''s movement allowance from the score. If the final result is 7 or less, the player with Shadowing may move into the square vacated by the opposing player. He does not have to make any Dodge rolls when he makes this move, and it has no effect on his own movement in his own turn. If the final result is 8 or more, the opposing player successfully avoids the Shadowing player and the Shadowing player may not move into the vacated square. A player may make any number of shadowing moves per turn. If a player has left the tackle zone of several players that have the Shadowing skill, then only one of the opposing players may attempt to shadow him.'),
(56, 'SideStep', 'Side Step', 2, 'A player with this skill is an expert at stepping neatly out of the way of an attacker. To represent this ability, his coach may choose which square the player is moved to when he is pushed back, rather than the opposing coach. Furthermore, the coach may choose to move the player to any adjacent square, not just the three squares shown on the Push Back diagram. Note that the player may not use this skill if there are no open squares on the pitch adjacent to this player. Note that the coach may choose which square the player is moved to even if the player is Knocked Down after the push back.'),
(57, 'Tackle', 'Tackle', 1, 'Opposing players who are standing in any of this player''s tackle zones are not allowed to use their Dodge skill if they attempt to dodge out of any of the player''s tackle zones, nor may they use their Dodge skill if the player throws a block at them and uses the Tackle skill.'),
(58, 'StrongArm', 'Strong Arm', 4, 'The player may add 1 to the D6 when he passes to Short, Long or Long Bomb range. '),
(59, 'Stunty', 'Stunty', 6, 'The player is so small that they are very difficult to tackle because they can duck underneath opposing players'' outstretched arms and run between their legs. On the other hand, Stunty players are just a bit too small to throw the ball very well, and are easily injured. To represent these things a player with the Stunty skill may ignore any enemy tackle zones on the square he is moving to when he makes a Dodge roll (i.e., they always end up with a +1 Dodge roll modifier), but must subtract 1 from the roll when he passes. In addition, this player treats a roll of 7 and 9 on the Injury table after any modifiers have been applied as a KO''d and Badly Hurt result respectively, rather than the normal results. Stunties that are armed with a Secret Weapon are not allowed to ignore enemy tackle zones, but still suffer the other penalties.'),
(60, 'SureFeet', 'Sure Feet', 2, 'The Player may re-roll the D6 if he us Knocked Down when trying to Go For It. A Player may only use the Sure Feet skill once per turn.'),
(61, 'SureHands', 'Sure Hands', 1, 'A player with the Sure Hands skill is allowed to re-roll the D6 if he fails to pick up the ball. In addition, the Strip Ball skill will not work against a player with this skill.'),
(63, 'ThickSkull', 'Thick Skull', 4, 'This player treats a roll of 8 on the Injury table, after any modifiers have been applied, as a Stunned result rather than a KO''d result. This skill may be used even if the player is Prone or Stunned.'),
(64, 'ThrowTeamMate', 'Throw Team-Mate', 6, 'A player with this skill has the ability to throw a player from the same team instead of the ball! (This includes the ball if the player thrown already has it!) The throwing player must end the movement of his Pass Action standing next to the intended team-mate to be thrown, who must have the Right Stuff skill and be standing. The pass is worked out exactly the same as if the player with Throw Team-Mate was passing a ball, except the player must subtract 1 from the D6 roll when he passes the player, fumbles are not automatically turnovers, and Long Pass or Long Bomb range passes are not possible. In addition, accurate passes are treated instead as inaccurate passes thus scattering the thrown player three times as players are heavier and harder to pass than a ball. The thrown player cannot be intercepted. A fumbled team-mate will land in the square he originally occupied. If the thrown player scatters off the pitch, he is beaten up by the crowd in the same manner as a player who has been pushed off the pitch. If the final square he scatters into is occupied by another player, treat the player landed on as Knocked Down and roll for Armour (even if already Prone or Stunned), and then the player being thrown will scatter one more square. If the thrown player would land on another player, continue to scatter the thrown player until he ends up in an empty square or off the pitch (i.e., he cannot land on more than one player). See the Right Stuff entry to see if the player lands on his feet or head-down in a crumpled heap! '),
(67, 'WildAnimal', 'Wild Animal', 7, 'Wild Animals are uncontrollable creatures that rarely do exactly what a coach wants of them. In fact, just about all you can really rely on them to do is lash out at opposing players that move too close to them! To represent this, immediately after declaring an Action with a Wild Animal, roll a D6, adding 2 to the roll if taking a Block or Blitz Action. On a roll of 1-3, the Wild Animal does not move and roars in rage instead, and the Action is wasted.'),
(68, 'Wrestle', 'Wrestle', 1, 'The player is specially trained in grappling techniques. This player may use Wrestle when he blocks or is blocked and a "Both Down" result on the Block dice is chosen by either coach. Instead of applying the "Both Down" result, both players are wrestled to the ground. Both players are Placed Prone in their respective squares even if one or both have the Block skill. Do not make Armour rolls for either player. Use of this skill does not cause a turnover unless the active player was holding the ball.'),
(69, 'Tentacles', 'Tentacles', 5, 'The player may use this skill when an opposing player attempts to dodge or leap out of any of his tackle zones. The opposing coach rolls 2D6 adding his player''s ST and subtracting the Tentacles player''s ST from the score. If the final results is 5 or less, then the moving player is held firm, and his Action ends immediately. If a player attempts to leave the tackle zone of several players that have the Tentacles skill, only one may attempt to grab him with Tentacles.'),
(70, 'MultipleBlock', 'Multiple Block', 4, 'At the start of a Block Action a player who is adjacent to at least two opponents may choose to throw blocks against two of them. Make each block in turn as normal except that each defender''s strength is increased by 2. The player cannot follow up either block when using this skill, so Multiple Block can be used instead of Frenzy, but both skills cannot be used  together. To have the option to throw the second block the player must still be on his feet after the first block. '),
(71, 'Kick', 'Kick', 1, 'The player is an expert at kicking the ball and can place the kick with great precision. In order to use this skill the player must be set up on the pitch when his team kicks off. The player may not be set up in either wide zone or on the line of scrimmage. Only if all these conditions are met is the player then allowed to take the kick-off. Because his kick is so accurate, you may choose to halve the number of squares that the ball scatters on kick-off, rounding any fractions down (i.e., 1 = 0, 2-3 = 1, 4-5 = 2, 6 = 3).'),
(72, 'KickOffReturn', 'Kick-Off Return', 1, 'A player on the receiving team that is not on the Line of Scrimmage or in an opposing tackle zone may use this skill when the ball has been kicked. It allows the player to move up to 3 squares after the ball has been scattered but before rolling on the Kick-Off table. Only one player may use this skill each kick-off. This skill may not be used for a touchback kick-off and does not allow the player to cross into the opponent?s half of the pitch.'),
(74, 'BigHand', 'Big Hand', 5, 'One of the player''s hands has grown monstrously large, yet remained completely functional. The player ignores modifier(s) for enemy tackle zones or Pouring Rain weather when he attempts to pick up the ball.'),
(75, 'Claw', 'Claw', 5, 'A player with this skill is blessed with a huge crab-like claw or razor sharp talons that make armour useless. When an opponent is Knocked Down by this player during a block, any Armour roll of 8 or more after modifications automatically breaks armour.'),
(76, 'BallChain', 'Ball and Chain', 6, 'Players armed with a Ball & Chain can only take Move Actions. To move or Go For It, place the throw-in template over the player facing up or down the pitch or towards either sideline. Then roll a D6 and move the player one square in the indicated direction; no Dodge roll is required if you leave a tackle zone. If this movement takes the player off the pitch, he is beaten up by the crowd in the same manner as a player who has been pushed off the pitch.Repeat this process for each and every square of normal movement the player has. You may then GFI using the same process if you wish.If during his Move Action he would move into an occupied square then the player will throw a block following normal blocking rules against whoever is in that square, friend or foe (and it even ignores Foul Appearance!). A Prone or Stunned player in an occupied square is pushed back and an Armour roll is made to see if he is injured, instead of the block being thrown at him. The player must follow up if he will push back another player, and will then carry on with his move as described above. If the player is ever Knocked Down or Placed Prone, roll immediately for injury (no Armour roll is required). Stunned results for any Injury rolls for the Ball & Chain player are always treated as KO''d. A Ball & Chain player may use the Grab skill (as if a Block Action was being used) with his blocks (if he has learned it!). A Ball & Chain player may never use the Diving Tackle, Frenzy, Kick-Off Return, Leap, Pass Block or Shadowing skills.'),
(77, 'Stab', 'Stab', 6, 'A player with this skill is armed with something very good at stabbing, slashing or hacking up an opponent, like sharp fangs or a trusty dagger. This player may attack an opponent with his stabbing attack instead of throwing a block. Make an unmodified Armour roll (except for Stakes) for the victim. If the score is less than or equal to the victim’s Armour value then the attack has no effect. If the score beats the victim’s Armour value then he has been wounded and an Injury roll must be made. This Injury roll ignores all modifiers from any source - including Niggling injuries. If Stab is used as part of a Blitz Action, the player cannot continue moving after using it. Casualties caused by a stabbing attack do not count for Star Player points.'),
(78, 'HypnoticGaze', 'Hypnotic Gaze', 6, 'The player has a powerful telepathic ability that he can use to stun an opponent into immobility. The player may use hypnotic gaze at the end of his Move Action on one opposing player who is in an adjacent square. Make an Agility roll for the player with hypnotic gaze, with a -1 modifier for each opposing tackle zone on the player with hypnotic gaze other than the victim''s. If the Agility roll is successful, then the opposing player loses his tackle zones and may not catch, intercept or pass the ball, assist another player on a block or foul, or move voluntarily until the start of his next Action or the drive ends. If the roll fails, then the hypnotic gaze has no effect.'),
(79, 'Stakes', 'Stakes', 6, 'This player is armed with special stakes that are blessed to cause extra damage to the Undead and those that work with them. This player may add 1 to the Armour roll when they make a Stab attack against any player playing for a Khemri, Necromantic, Undead or Vampire team. '),
(80, 'Bombadier', 'Bombadier', 6, 'A coach may choose to have a Bombardier who is not Prone or Stunned throw a bomb instead of taking any other Action with the player. This does not use the team''s Pass Action for the turn. The bomb is thrown using the rules for throwing the ball (including weather effects and use of Hail Mary Pass), except that the player may not move or stand up before throwing it (he needs time to light the fuse!). Intercepted bomb passes are not turnovers. Fumbles or any bomb explosions that lead to a player on the active team being Knocked Down are turnovers. All skills that may be used when a ball is thrown may be used when a bomb is thrown also. A bomb may be intercepted or caught using the same rules for catching the ball, in which case the player catching it must throw it again immediately. This is a special bonus Action that takes place out of the normal sequence of play. A player holding the ball can catch or intercept and throw a bomb. The bomb explodes when it lands in an empty square or an opportunity to catch the bomb fails or is declined (i.e., bombs don''t bounce). If the bomb is fumbled it explodes in the bomb thrower''s square. If a bomb lands in the crowd, it explodes with no effect. When the bomb finally does explode any player in the same square is Knocked Down, and players in adjacent squares are Knocked Down on a roll of 4+. Players can be hit by a bomb and treated as Knocked Down even if they are already Prone or Stunned. Make Armour and Injury rolls for any players Knocked Down by the bomb. Casualties caused by a bomb do not count for Star Player points.'),
(81, 'Decay', 'Decay', 7, 'Staying on the pitch is difficult when your rotting body is barely held together. When this player suffers a Casualty result on the Injury table, roll twice on the Casualty table and apply both results. The player will only ever miss one future match as a result of his injuries, even if he suffers two results with this effect. A successful Regeneration roll will heal both results.'),
(82, 'NurglesRot', 'Nurgle''s Rot', 6, 'This player has a horrible infectious disease which spreads when he kills an opponent during a Block, Blitz or Foul Action. Instead of truly dying, the infected opponent becomes a new rookie Rotter. To do so, the opponent must have been removed from the roster during step 2.1 of the Post-game sequence, his Strength cannot exceed 4, and he cannot have the Decay, Regeneration or Stunty skills. The new Rotter can be added to the Nurgle team for free during step 5 of Updating Your Team Roster (see page 29) if the team has an open Roster slot. This new Rotter still counts at full value towards the total value of theNurgle team. '),
(83, 'Titchy', 'Titchy', 6, 'Titchy players tend to be even smaller and more nimble than other Stunty players. To represent this, the player may add 1 to any Dodge roll he attempts. On the other hand, while opponents do have to dodge to leave any of a Titchy player''s tackle zones, a Titchy player is so small that he does not exert a -1 modifier when opponents dodge into any of his tackle zones.'),
(84, 'BloodLust', 'Blood Lust', 7, 'Vampires must occasionally feed on the blood of the living. Immediately after declaring an Action with a Vampire, roll a d6: On a 2+ the Vampire can carry out the Action as normal. On a 1, however, the Vampire must feed on a Thrall team-mate or a spectator. The Vampire may continue with his declared Action or if he had declared a Block Action, he may take a Move Action instead. Either way, at the end of the declared Action, but before actually passing, handing off, or scoring, the vampire must feed. If he is standing adjacent to one or more Thrall team-mates (standing, prone or stunned), then choose one to bite and make an Injury roll on the Thrall treating any casualty roll as Badly Hurt. The injury will not cause a turnover unless the Thrall was holding the ball. Once the Vampire has bitten a Thrall he may complete his Action. Failure to bite a Thrall is a turnover and requires him to feed on a spectator ? move the Vampire to the reserves box if he was still on the pitch. If he was holding the ball, it bounces from the square he occupied when he was removed and he will not score a touchdown if he was in the opposing end zone.'),
(85, 'FanFavourite', 'Fan Favourite', 6, 'The fans love seeing this player on the pitch so much that even the opposing fans cheer for your team. For each player with Fan Favourite on the pitch your team receives an additional +1 FAME modifier (see page 18) for any Kick-Off table results, but not for the Winnings roll. '),
(86, 'Animosity', 'Animosity', 7, 'A player with this skill does not like players from his team that are a different race than he is and will often refuse to play with them despite the coach''s orders. If this player at the end of his Hand-off or Pass Actionattempts to hand-off or pass the ball to a team-mate that is not the same race as the Animosity player, roll a D6. On a 2+, the pass/hand-off is carried out as normal. On a 1, the player refuses to give the ball to any team-mate except one of his own race. The coach may choose to change the target of the pass/hand-off to another team-mate of the same race as the Animosity player, however no more movement is allowed for the Animosity player, so the current Action may be lost for the turn.'),
(87, 'Blitz', 'Blitz', NULL, NULL);

--
-- Dumping data for table `bb_lkp_skill_category`
--

INSERT INTO `bb_lkp_skill_category` (`skill_category_id`, `skill_category`) VALUES
(0, 'Stat level-up'),
(1, 'General'),
(2, 'Agility'),
(3, 'Passing'),
(4, 'Strength'),
(5, 'Mutation'),
(6, 'Extraordinary'),
(7, 'Extraordinary (nega-trait)');

--
-- Dumping data for table `bb_lkp_spp_levels`
--

INSERT INTO `bb_lkp_spp_levels` (`spp_level`, `ruleset_id`, `description`, `limit_spp`) VALUES
(1, 1, 'Rookie', 6),
(2, 1, 'Experienced', 16),
(3, 1, 'Veteran', 31),
(4, 1, 'Emerging Star', 51),
(5, 1, 'Star', 76),
(6, 1, 'Super Star', 176),
(7, 1, 'Legend', 65535);

--
-- Dumping data for table `bb_lkp_st`
--

INSERT INTO `bb_lkp_st` (`bb1_id`, `human_val`) VALUES
(30, 1),
(40, 2),
(50, 3),
(60, 4),
(70, 5),
(80, 6),
(90, 7),
(100, 8),
(110, 9);

--
-- Dumping data for table `bb_lkp_star_player_race`
--

INSERT INTO `bb_lkp_star_player_race` (`player_type_id`, `race_id`, `ruleset_id`) VALUES
(1, 1, 1),
(1, 2, 1),
(1, 3, 1),
(1, 4, 1),
(1, 5, 1),
(1, 6, 1),
(1, 7, 1),
(1, 8, 1),
(1, 9, 1),
(1, 10, 1),
(1, 11, 1),
(1, 12, 1),
(1, 14, 1),
(1, 15, 1),
(1, 18, 1),
(1, 19, 1),
(1, 20, 1),
(1, 21, 1),
(1, 22, 1),
(1, 23, 1),
(6, 1, 1),
(6, 2, 1),
(6, 10, 1),
(6, 20, 1),
(8, 1, 1),
(8, 10, 1),
(8, 20, 1),
(16, 4, 1),
(16, 12, 1),
(16, 15, 1),
(16, 19, 1),
(16, 21, 1),
(16, 23, 1),
(19, 3, 1),
(19, 6, 1),
(19, 7, 1),
(19, 22, 1),
(23, 3, 1),
(23, 22, 1),
(30, 5, 1),
(36, 8, 1),
(36, 13, 1),
(36, 16, 1),
(43, 7, 1),
(49, 9, 1),
(57, 10, 1),
(57, 20, 1),
(58, 10, 1),
(65, 11, 1),
(65, 18, 1),
(71, 13, 1),
(71, 16, 1),
(71, 17, 1),
(79, 14, 1),
(85, 15, 1),
(90, 12, 1),
(91, 11, 1),
(91, 14, 1),
(91, 18, 1),
(91, 23, 1),
(93, 13, 1),
(93, 16, 1),
(93, 17, 1),
(135, 19, 1),
(141, 21, 1);

--
-- Dumping data for table `bb_lkp_table_player`
--

INSERT INTO `bb_lkp_table_player` (`table_id`, `description`, `column_header`, `statistic_measured`, `default_chart_size`) VALUES
(1, 'Highest Scorers', 'TD''s', 'Touchdowns scored', 10),
(2, 'Prolific Passers', 'Passes', 'Successful passes made', 10),
(3, 'Top Catchers', 'Catches', 'Catches', 10),
(4, 'Most Valuable', 'MVP''s', 'MVP''s', 5),
(5, 'Top Runners', 'Meters Run', 'MRun', 10),
(6, 'Longest Passers', 'Meters Passed', 'MPass', 10),
(7, 'Biggest Brutes', 'KD''s', 'Inflicted Knockdowns', 10),
(8, 'Punching Bags', 'KD Rec.', 'Received Knockdowns', 10),
(9, 'Career-Wreckers', 'Injured', 'Inflicted Injuries', 10),
(10, 'Sicknotes', 'Injured', 'Received Injuries', 5);

--
-- Dumping data for table `bb_lkp_table_team`
--

INSERT INTO `bb_lkp_table_team` (`table_id`, `description`, `column_header`, `statistic_measured`, `default_chart_size`) VALUES
(1, 'Highest Scorers', 'TD''s', 'Touchdowns scored', NULL),
(2, 'Prolific Passers', 'Passes', 'Successful passes made', NULL),
(3, 'Top Catchers', 'Catches', 'Catches', NULL),
(4, 'Best Defence', 'TD''S', 'Touchdowns conceded', NULL),
(5, 'Top Runners', 'Meters Run', 'MRun', NULL),
(6, 'Longest Passers', 'Meters Passed', 'MPass', NULL),
(7, 'Biggest Brutes', 'KD''s', 'Inflicted Knockdowns', NULL),
(8, 'Punching Bags', 'KD Rec.', 'Received Knockdowns', NULL),
(9, 'Career Wreckers', 'Injuries', 'Inflicted Injuries', NULL),
(10, 'Favourites of the Hospital Staff', 'Injuries', 'Received Injuries', NULL);

--
-- Dumping data for table `bb_lkp_turnover_type`
--

INSERT INTO `bb_lkp_turnover_type` (`turnover_type_id`, `description`) VALUES
(1, 'Time limit exceeded'),
(2, 'Touchdown'),
(3, 'Half time / Full time'),
(4, 'Failed block'),
(5, 'Failed dodge'),
(6, 'Failed ball handling'),
(7, 'Knocked down (temporary category)'),
(8, 'Failed GFI');

--
-- Dumping data for table `bb_lkp_turn_end_reason`
--

INSERT INTO `bb_lkp_turn_end_reason` (`turn_end_reason`, `description`, `text_search`) VALUES
(1, 'User selected', ''),
(2, 'Sent off', 'Ejected by the referee!'),
(3, 'Ran out of time', 'Time limit exceeded!'),
(4, 'Pick-up failed', 'Pick-up failed!');

--
-- Dumping data for table `bb_lkp_user_activation_status`
--

INSERT INTO `bb_lkp_user_activation_status` (`user_activation_status_id`, `description`) VALUES
(1, 'Awaiting validation'),
(2, 'Validated'),
(3, 'Blocked'),
(4, 'Validation not required');

--
-- Dumping data for table `bb_lkp_weather`
--

INSERT INTO `bb_lkp_weather` (`weather_id`, `description`, `long_description`, `probability`) VALUES
(1, 'Sweltering Heat', 'It’s so hot and humid that some players collapse from heat exhaustion. Roll a D6 for each player on the pitch at the end of a drive. On a roll of 1 the player collapses and may not be set up for the next kick-off.', '0.02777777777777777778'),
(2, 'Very Sunny', 'A glorious day, but the blinding sunshine causes a -1 modifier on all passing rolls.', '0.05555555555555555555'),
(3, 'Nice', 'Perfect Blood Bowl weather.', '0.83333333333333333334'),
(4, 'Pouring Rain', 'It’s raining, making the ball slippery and difficult to hold.  A -1 modifier applies to all catch, intercept, or pick-up rolls.', '0.05555555555555555555'),
(5, 'Blizzard', 'It’s cold and snowing! The ice on the pitch means that any player attempting  to move an extra square (GFI) will slip and be Knocked Down on a roll of 1-2, while the snow means that only quick or short passes can be attempted.', '0.02777777777777777778');  

--
-- Dumping data for table `bb_api_type`
--

INSERT INTO `bb_api_type` (`api_type_id`, `entity_name`, `entity_version`, `depracated`) VALUES
(1, 'matchdetail', 1, 0),
(2, 'matchdetail', 2, 0);

--
-- Dumping data for table `bb_role`
--

INSERT INTO `bb_role` (`role_id`, `description`) VALUES
(1, 'Superadmin'),
(2, 'Domain-level guest'),
(3, 'Guest account'),
(4, 'Normal user');

--
-- Dumping data for table `bb_permission`
--

INSERT INTO `bb_permission` (`permission_id`, `description`) VALUES
(1, 'Upload matches'),
(2, 'Claim team'),
(3, 'Change domains'),
(4, 'Standard read privileges'),
(5, 'Manually create users'),
(6, 'Refresh competition tables and stats'),
(7, 'Close a competition'),
(8, 'Create a new competition'),
(9, 'Assign coaches to unassigned teams'),
(10, 'Create new coaches');

--
-- Dumping data for table `bb_role_permission`
--

INSERT INTO `bb_role_permission` (`role_id`, `permission_id`) VALUES
(1, 1),
(1, 2),
(1, 3),
(1, 4),
(1, 5),
(1, 6),
(1, 7),
(1, 8),
(1, 9),
(1, 10),
(2, 1),
(2, 4),
(3, 4),
(4, 1),
(4, 4);
  
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
