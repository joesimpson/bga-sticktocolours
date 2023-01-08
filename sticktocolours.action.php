<?php
/**
 *------
 * BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
 * StickToColours implementation : © joesimpson <Your email address here>
 *
 * This code has been produced on the BGA studio platform for use on https://boardgamearena.com.
 * See http://en.doc.boardgamearena.com/Studio for more information.
 * -----
 * 
 * sticktocolours.action.php
 *
 * StickToColours main action entry point
 *
 *
 * In this file, you are describing all the methods that can be called from your
 * user interface logic (javascript).
 *       
 * If you define a method "myAction" here, then you can call it from your javascript code with:
 * this.ajaxcall( "/sticktocolours/sticktocolours/myAction.html", ...)
 *
 */
  
  
  class action_sticktocolours extends APP_GameAction
  { 
    // Constructor: please do not modify
   	public function __default()
  	{
  	    if( self::isArg( 'notifwindow') )
  	    {
            $this->view = "common_notifwindow";
  	        $this->viewArgs['table'] = self::getArg( "table", AT_posint, true );
  	    }
  	    else
  	    {
            $this->view = "sticktocolours_sticktocolours";
            self::trace( "Complete reinitialization of board game" );
      }
  	} 
  	
  	// TODO: defines your action entry points there

    public function chooseMarketCard()
    {
        self::setAjaxMode();     

        // Retrieve arguments
        // Note: these arguments correspond to what has been sent through the javascript "ajaxcall" method
        $cardId = self::getArg( "cardId", AT_posint, true );

        // Then, call the appropriate method in your game logic, like "playCard" or "myAction"
        $this->game->chooseMarketCard( $cardId );

        self::ajaxResponse( );
    }
    
    public function cancelChoice()
    {
        self::setAjaxMode();

        $this->game->cancelChoice();

        self::ajaxResponse();
    }
    
    public function passChoice()
    {
        self::setAjaxMode();     

        $this->game->passChoice();

        self::ajaxResponse( );
    }
    
    public function bid()
    {
        self::setAjaxMode();     
        
        //If your Javascript sends a list of integers separated by ";" (example: "1;2;3;4") as an argument
        $card_ids_raw = self::getArg( "card_ids", AT_numberlist, true );
        
        // Removing last ';' if exists
        if( substr( $card_ids_raw, -1 ) == ';' )
            $card_ids_raw = substr( $card_ids_raw, 0, -1 );
        if( $card_ids_raw == '' )
            $card_ids = array();
        else
            $card_ids = explode( ';', $card_ids_raw );
        
        $this->game->bid($card_ids);

        self::ajaxResponse( );
    }
    
    public function passBid()
    {
        self::setAjaxMode();     

        $this->game->passBid();

        self::ajaxResponse( );
    }
    
    public function chooseJoker()
    {
        self::setAjaxMode();     

        $cardId = self::getArg( "cardId", AT_posint, true );
        $value = self::getArg( "value", AT_posint, true );
        
        $this->game->chooseJoker($cardId, $value);

        self::ajaxResponse( );
    }
  }
  

