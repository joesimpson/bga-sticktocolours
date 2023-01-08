
-- ------
-- BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
-- StickToColours implementation : © joesimpson <Your email address here>
-- 
-- This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
-- See http://en.boardgamearena.com/#!doc/Studio for more information.
-- -----

-- dbmodel.sql

-- This is the file where you are describing the database schema of your game
-- Basically, you just have to export from PhpMyAdmin your table structure and copy/paste
-- this export here.
-- Note that the database itself and the standard tables ("global", "stats", "gamelog" and "player") are
-- already created and must not be created here

-- Note: The database schema is created from this file when the game starts. If you modify this file,
--       you have to restart a game to see your changes in database.

-- create a standard "card" table to be used with the "Deck" tools (see example game "hearts"):
CREATE TABLE IF NOT EXISTS `card` (
  `card_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `card_type` varchar(16) NOT NULL COMMENT 'Color RED(1), GREEN(2), BLUE(3)',
  `card_type_arg` int(11) NOT NULL COMMENT 'Value from 1 to 10 (1 to 9 + Joker )',
  `card_location` varchar(16) NOT NULL COMMENT 'Location on the board',
  `card_location_arg` int(11) NOT NULL,
  
  `card_joker_value` int(2) NULL DEFAULT NULL COMMENT 'Joker value if assigned',
  PRIMARY KEY (`card_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- possible card_type for this game = Color RGB : RED(1), GREEN(2), BLUE(3)
-- possible card_type_arg for this game = value from 1 to 10 (1 to 9 + Joker )
-- possible card_location for this game : deck,hand, hand_refused, market, bidding, discard
-- possible card_location_arg for this game : If HAND => id of player 

-- possible card_joker_value for this game :  1 to 9 for an assigned joker, Null otherwise 


-- Example 2: add a custom field to the standard "player" table
ALTER TABLE `player` ADD `player_pass_bid` INT UNSIGNED NOT NULL DEFAULT '0';
-- 0 : player didn't pass the current bid
-- 1 : player did pass the current bid
 
 
 
 
 CREATE TABLE IF NOT EXISTS `token` (
 `token_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
 `token_player_id` int(10) NOT NULL COMMENT 'Id of player who owns the token',
 `token_card_id` varchar(32) NOT NULL COMMENT 'Id of card on which the token is',
 `token_state` int(1) NOT NULL DEFAULT 0 COMMENT 'State (0=front Question mark/1=back Refuse)',
 PRIMARY KEY (`token_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
