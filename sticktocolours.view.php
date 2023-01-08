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
 * sticktocolours.view.php
 *
 * This is your "view" file.
 *
 * The method "build_page" below is called each time the game interface is displayed to a player, ie:
 * _ when the game starts
 * _ when a player refreshes the game page (F5)
 *
 * "build_page" method allows you to dynamically modify the HTML generated for the game interface. In
 * particular, you can set here the values of variables elements defined in sticktocolours_sticktocolours.tpl (elements
 * like {MY_VARIABLE_ELEMENT}), and insert HTML block elements (also defined in your HTML template file)
 *
 * Note: if the HTML of your game interface is always the same, you don't have to place anything here.
 *
 */
  
require_once( APP_BASE_PATH."view/common/game.view.php" );
  
class view_sticktocolours_sticktocolours extends game_view
{
    protected function getGameName()
    {
        // Used for translations and stuff. Please do not modify.
        return "sticktocolours";
    }
    
  	function build_page( $viewArgs )
  	{		
  	    // Get players & players number
        $players = $this->game->loadPlayersBasicInfos();
        $players_nbr = count( $players );

        /*********** Place your code below:  ************/


        /*
        
        // Examples: set the value of some element defined in your tpl file like this: {MY_VARIABLE_ELEMENT}

        // Display a specific number / string
        $this->tpl['MY_VARIABLE_ELEMENT'] = $number_to_display;

        // Display a string to be translated in all languages: 
        $this->tpl['MY_VARIABLE_ELEMENT'] = self::_("A string to be translated");

        // Display some HTML content of your own:
        $this->tpl['MY_VARIABLE_ELEMENT'] = self::raw( $some_html_code );
        
        */
        $this->tpl['DECK_LABEL'] = self::_("Remaining cards");
        $this->tpl['MARKET'] = self::_("Market");
        $this->tpl['HAND_REFUSED'] = self::_("Hand refused cards");
        $this->tpl['BIDDING'] = self::_("Bidding target");
        $this->tpl['BEST_OFFER'] = self::_("Best offer :");
        $this->tpl['MY_HAND'] = self::_("My hand of cards");
        $this->tpl['JOKER_CHOICE_LABEL'] = self::_("Select a value for this Joker");
        
        /*
        
        // Example: display a specific HTML block for each player in this game.
        // (note: the block is defined in your .tpl file like this:
        //      <!-- BEGIN myblock --> 
        //          ... my HTML code ...
        //      <!-- END myblock --> 
        

        $this->page->begin_block( "sticktocolours_sticktocolours", "myblock" );
        foreach( $players as $player )
        {
            $this->page->insert_block( "myblock", array( 
                                                    "PLAYER_NAME" => $player['player_name'],
                                                    "SOME_VARIABLE" => $some_value
                                                    ...
                                                     ) );
        }
        
        */
        
        $this->page->begin_block( "sticktocolours_sticktocolours", "playerHand" );
        foreach( $players as $player )
        {
            $this->page->insert_block( "playerHand", array( 
                                                    "PLAYER_ID" => $player['player_id'],
                                                    "PLAYER_NAME" => $player['player_name'],
                                                    "PLAYER_COLOR" => $player['player_color'],
                                                     ) );
        }


        $this->page->begin_block( "sticktocolours_sticktocolours", "choiceJoker" );
        foreach( $this->game->card_types as $color_id => $type )
        {
            $this->page->insert_block( "choiceJoker", array( 
                                                    "JOKER_ID" => $color_id,
                                                    "JOKER_COLOR" => $type['name'],
                                                     ) );
        }

        /*********** Do not change anything below this line  ************/
  	}
}
