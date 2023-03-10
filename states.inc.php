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
 * states.inc.php
 *
 * StickToColours game states description
 *
 */

/*
   Game state machine is a tool used to facilitate game developpement by doing common stuff that can be set up
   in a very easy way from this configuration file.

   Please check the BGA Studio presentation about game state to understand this, and associated documentation.

   Summary:

   States types:
   _ activeplayer: in this type of state, we expect some action from the active player.
   _ multipleactiveplayer: in this type of state, we expect some action from multiple players (the active players)
   _ game: this is an intermediary state where we don't expect any actions from players. Your game logic must decide what is the next game state.
   _ manager: special type for initial and final state

   Arguments of game states:
   _ name: the name of the GameState, in order you can recognize it on your own code.
   _ description: the description of the current game state is always displayed in the action status bar on
                  the top of the game. Most of the time this is useless for game state with "game" type.
   _ descriptionmyturn: the description of the current game state when it's your turn.
   _ type: defines the type of game states (activeplayer / multipleactiveplayer / game / manager)
   _ action: name of the method to call when this game state become the current game state. Usually, the
             action method is prefixed by "st" (ex: "stMyGameStateName").
   _ possibleactions: array that specify possible player actions on this step. It allows you to use "checkAction"
                      method on both client side (Javacript: this.checkAction) and server side (PHP: self::checkAction).
   _ transitions: the transitions are the possible paths to go from a game state to another. You must name
                  transitions in order to use transition names in "nextState" PHP method, and use IDs to
                  specify the next game state for each transition.
   _ args: name of the method to call to retrieve arguments for this gamestate. Arguments are sent to the
           client side to be used on "onEnteringState" or to set arguments in the gamestate description.
   _ updateGameProgression: when specified, the game progression is updated (=> call to your getGameProgression
                            method).
*/

//    !! It is not a good idea to modify this file when a game is running !!

 
/*
  states  DIAGRAM :


 SETUP -> newRound -> choosingCard  -> playerTurn  ->  nextPlayer -> endRound -> assignJokers -> scoring -> END
         ^ ^    |          |              ^              |            |
         | |    |   (PASS) |              | (BID or PASS)|            |
         | \---nextRound---/              \--------------/            |
         |                                                            |
         |                                                            |
         \------------------------------------------------------------/

newRound : new phase (change dealer)
choosingCard : dealer must choose a card and start bidding OR "pass" and we have a new round (new dealer)
nextRound: go to next player for the new round,
playerTurn : players bid or not
NEXTPLAYER : go to next player for bidding, if no player left end the round
endRound : give the card to the winner.
    IF there are cards to win, we start a new Round
    ELSE end the game
assignJokers :  players assign a nominal value to each of their jokers  (other players wait)
scoring : compute and display scoring according to jokers

*/ 
 
 
 
$machinestates = array(

    // The initial state. Please do not modify.
    1 => array(
        "name" => "gameSetup",
        "description" => "",
        "type" => "manager",
        "action" => "stGameSetup",
        "transitions" => array( "" => 2 )
    ),
    
    
    2 => array(
        "name" => "newRound",
        "description" => clienttranslate('New round'),
        "type" => "game",
        "action" => "stNewRound",
        "updateGameProgression" => true,
        "transitions" => array( "next" => 10 , "autoPass" => 11 )
    ),

    10 => array(
        "name" => "choosingCard",
        "description" => clienttranslate('${actplayer} must choose a card in the market or pass'),
        "descriptionmyturn" => clienttranslate('${you} must choose a card in the market or pass'),
        "type" => "activeplayer",
        "args" => "argChoosingCard",
        "possibleactions" => array( "chooseMarketCard", "passChoice" ),
        "transitions" => array( "chooseMarketCard" => 20, "pass" => 11, "zombiePass"=>11)
    ),
    
    11 => array(
        "name" => "nextRound",
        "description" => '',
        "type" => "game",
        "action" => "stNextRound",
        "transitions" => array( "next" => 2 )
    ),
    
    
    20 => array(
        "name" => "playerTurn",
        "description" => clienttranslate('${actplayer} must bid or pass'),
        "descriptionmyturn" => clienttranslate('${you} must bid or pass'),
        "type" => "activeplayer",
        "args" => "argPlayerTurn",
        "possibleactions" => array( "bid", "passBid", "cancelChoice" ),
        "transitions" => array( "bid" => 21, "pass" => 21, "zombiePass"=>21, "cancelChoice"=>10 )
    ),
    
    21 => array(
        "name" => "nextPlayer",
        "description" => '',
        "type" => "game",
        "action" => "stNextPlayer",
        "transitions" => array( "end" => 30, "nextPlayer" => 20 )
    ),
    
    30 => array(
        "name" => "endRound",
        "description" => clienttranslate('End round'),
        "type" => "game",
        "action" => "stEndRound",
        "updateGameProgression" => true,   
        "transitions" => array( "endGame" => 50, "newRound" => 2 )
    ),
    
    
    50 => array(
        "name" => "assignJokers",
        "description" => clienttranslate('Some players must choose a value for their jokers'),
        "descriptionmyturn" => clienttranslate('${you} must choose a value for your jokers'),
        "type" => "multipleactiveplayer",
        "action" => "stAssignJokers",
        "args" => "argAssignJokers",
        "possibleactions" => array( "chooseJoker" ),
        "transitions" => array( "end" => 51 )
    ),
    
    51 => array(
        "name" => "scoring",
        "description" => clienttranslate('Computing scores'),
        "type" => "game",
        "action" => "stScoring",
        "args" => "argScoring",
        "transitions" => array( "endGame" => 99 )
    ),
       
    // Final state.
    // Please do not modify (and do not overload action/args methods).
    99 => array(
        "name" => "gameEnd",
        "description" => clienttranslate("End of game"),
        "type" => "manager",
        "action" => "stGameEnd",
        "args" => "argGameEnd"
    )

);



