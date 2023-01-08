<?php

/**
 *------
 * BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
 * StickToColours implementation : © joesimpson <Your email address here>
 *
 * This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
 * See http://en.boardgamearena.com/#!doc/Studio for more information.
 * -----
 *
 * stats.inc.php
 *
 * StickToColours game statistics description
 *
 */

/*
    In this file, you are describing game statistics, that will be displayed at the end of the
    game.
    
    !! After modifying this file, you must use "Reload  statistics configuration" in BGA Studio backoffice
    ("Control Panel" / "Manage Game" / "Your Game")
    
    There are 2 types of statistics:
    _ table statistics, that are not associated to a specific player (ie: 1 value for each game).
    _ player statistics, that are associated to each players (ie: 1 value for each player in the game).

    Statistics types can be "int" for integer, "float" for floating point values, and "bool" for boolean
    
    Once you defined your statistics there, you can start using "initStat", "setStat" and "incStat" method
    in your game logic, using statistics names defined below.
    
    !! It is not a good idea to modify this file when a game is running !!

    If your game is already public on BGA, please read the following before any change:
    http://en.doc.boardgamearena.com/Post-release_phase#Changes_that_breaks_the_games_in_progress
    
    Notes:
    * Statistic index is the reference used in setStat/incStat/initStat PHP method
    * Statistic index must contains alphanumerical characters and no space. Example: 'turn_played'
    * Statistics IDs must be >=10
    * Two table statistics can't share the same ID, two player statistics can't share the same ID
    * A table statistic can have the same ID than a player statistics
    * Statistics ID is the reference used by BGA website. If you change the ID, you lost all historical statistic data. Do NOT re-use an ID of a deleted statistic
    * Statistic name is the English description of the statistic as shown to players
    
*/

$stats_type = array(

    // Statistics global to table
    "table" => array(


        "bids_number" => array("id"=> 12,
                    "name" => totranslate("Placed bids"),
                    "type" => "int" ),

        "discarded_number" => array("id"=> 30,
                    "name" => totranslate("Discarded cards"),
                    "type" => "int" ),
    ),
    
    // Statistics existing for each player
    "player" => array(

        "dealer_number" => array("id"=> 10,
                    "name" => totranslate("Turns as dealer"),
                    "type" => "int" ),
        "passChoice_number" => array("id"=> 11,
                    "name" => totranslate("Passed turns as dealer"),
                    "type" => "int" ),
        "bids_number" => array("id"=> 12,
                    "name" => totranslate("Placed bids"),
                    "type" => "int" ),
        "passBid_number" => array("id"=> 13,
                    "name" => totranslate("Passed turns"),
                    "type" => "int" ),
        "tokens_number" => array("id"=> 14,
                    "name" => totranslate("Tokens placed on the market"),
                    "type" => "int" ),
                    
        "won_number" => array("id"=> 20,
                    "name" => totranslate("Won cards"),
                    "type" => "int" ),
        "wonJoker_number" => array("id"=> 21,
                    "name" => totranslate("Won Joker cards"),
                    "type" => "int" ),
                    
        "discarded_number" => array("id"=> 30,
                    "name" => totranslate("Discarded cards"),
                    "type" => "int" ),
        "hand_size" => array("id"=> 31,
                    "name" => totranslate("Cards in hand at the end"),
                    "type" => "int" ),

        "scoreCombinationLow_number" => array("id"=> 90,
                    "name" => totranslate("Completed combinations of 1,2,3,4,5 OR 6"),
                    "type" => "int" ),
        "scoreCombinationHigh_number" => array("id"=> 91,
                    "name" => totranslate("Completed combinations of 7,8 OR 9"),
                    "type" => "int" ),
                    
        "scoreRed_points" => array("id"=> 92,
                    "name" => totranslate("Points for red combinations"),
                    "type" => "int" ),
        "scoreGreen_points" => array("id"=> 93,
                    "name" => totranslate("Points for green combinations"),
                    "type" => "int" ),
        "scoreBlue_points" => array("id"=> 94,
                    "name" => totranslate("Points for blue combinations"),
                    "type" => "int" ),

    )

);
