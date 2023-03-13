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
  * sticktocolours.game.php
  *
  * This is the main file for your game logic.
  *
  * In this PHP file, you are going to defines the rules of the game.
  *
  */


require_once( APP_GAMEMODULE_PATH.'module/table/table.game.php' );


class StickToColours extends Table
{
	function __construct( )
	{
        // Your global variables labels:
        //  Here, you can assign labels to global variables you are using for this game.
        //  You can use any number of global variables with IDs between 10 and 99.
        //  If your game has options (variants), you also have to associate here a label to
        //  the corresponding ID in gameoptions.inc.php.
        // Note: afterwards, you can get/set the global variables with getGameStateValue/setGameStateInitialValue/setGameStateValue
        parent::__construct();
        
        self::initGameStateLabels( array( 
            //    "my_first_global_variable" => 10,
            //    "my_second_global_variable" => 11,
            //      ...
            //    "my_first_game_variant" => 100,
            //    "my_second_game_variant" => 101,
            //      ...
            
                //ID of the dealer player
                "dealer" => 10,
                "market_size" => 11,
                //Min Number of tokens for current trading (ie. number of tokens of the last player action)
                "current_trading_tokens" => 20,
                "current_trading_player_id" => 21,
                
        ) );        
        
        $this->cards = self::getNew( "module.common.deck" );
        $this->cards->init( "card" ); // card is the name of the  DB table
	}
	
    protected function getGameName( )
    {
		// Used for translations and stuff. Please do not modify.
        return "sticktocolours";
    }	

    /*
        setupNewGame:
        
        This method is called only once, when a new game is launched.
        In this method, you must setup the game according to the game rules, so that
        the game is ready to be played.
    */
    protected function setupNewGame( $players, $options = array() )
    {    
        // Set the colors of the players with HTML color code
        // The default below is red/green/blue/orange/brown
        // The number of colors defined here must correspond to the maximum number of players allowed for the gams
        $gameinfos = self::getGameinfos();
        $default_colors = $gameinfos['player_colors'];
 
        // Create players
        // Note: if you added some extra field on "player" table in the database (dbmodel.sql), you can initialize it there.
        $sql = "INSERT INTO player (player_id, player_color, player_canal, player_name, player_avatar) VALUES ";
        $values = array();
        $dealer = null;
        foreach( $players as $player_id => $player )
        {
            $color = array_shift( $default_colors );
            $values[] = "('".$player_id."','$color','".$player['player_canal']."','".addslashes( $player['player_name'] )."','".addslashes( $player['player_avatar'] )."')";
            if(is_null($dealer)){
                $dealer = $player_id;
            }
        }
        $sql .= implode( $values, ',' );
        self::DbQuery( $sql );
        self::reattributeColorsBasedOnPreferences( $players, $gameinfos['player_colors'] );
        self::reloadPlayersBasicInfos();
        
        /************ Start the game initialization *****/

        // Init global values with their initial values
        self::setGameStateInitialValue( 'dealer', $dealer );
        //initial number of cards depends on number of players
        if(count($players) ==4){
            self::setGameStateInitialValue( 'market_size', 6 );
        } else {
            self::setGameStateInitialValue( 'market_size', 5 );
        }
        
        
        // Init game statistics
        // (note: statistics used in this file must be defined in your stats.inc.php file)
        self::initStat( 'table', 'bids_number', 0 );
        self::initStat( 'table', 'discarded_number', 0 );
        self::initStat( 'player', 'dealer_number', 0 );
        self::initStat( 'player', 'passChoice_number', 0 );
        self::initStat( 'player', 'bids_number', 0 );
        self::initStat( 'player', 'passBid_number', 0 );
        self::initStat( 'player', 'won_number', 0 );
        self::initStat( 'player', 'wonJoker_number', 0 );
        self::initStat( 'player', 'discarded_number', 0 );
        self::initStat( 'player', 'hand_size', 0 );
        self::initStat( 'player', 'scoreCombinationLow_number', 0 );
        self::initStat( 'player', 'scoreCombinationHigh_number', 0 );
        self::initStat( 'player', 'scoreRed_points', 0 );
        self::initStat( 'player', 'scoreGreen_points', 0 );
        self::initStat( 'player', 'scoreBlue_points', 0 );


        //setup the initial game situation here
        $this->initTables();
       

        // Activate first player (which is in general a good idea :) )
        $this->activeNextPlayer();

        /************ End of the game initialization *****/
    }

    /*
        getAllDatas: 
        
        Gather all informations about current game situation (visible by the current player).
        
        The method is called each time the game interface is displayed to a player, ie:
        _ when the game starts
        _ when a player refreshes the game page (F5)
    */
    protected function getAllDatas()
    {
        $result = array();
    
        $current_player_id = self::getCurrentPlayerId();    // !! We must only return informations visible by this player !!
    
        // Get information about players
        // Note: you can retrieve some extra field you added for "player" table in "dbmodel.sql" if you need it.
        $sql = "SELECT player_id id, player_score score FROM player ";
        $result['players'] = self::getCollectionFromDb( $sql );
        $result['dealer'] = self::getGameStateValue( 'dealer');
  
        //Gather all information about current game situation (visible by player $current_player_id).
        $market = $this->cards->getCardsInLocation( "market");
        $result['market'] = $market;
        $result['marketTokens'] = self::dbGetAllTokens("market");
        
        $result['biddingCard'] = self::getBiddingCard();
        $result['biddingCardTokens'] = array();
        if(!is_null($result['biddingCard'])){
            $result['biddingCardTokens'] = self::dbGetRefuseTokens($result['biddingCard']['id']);
        }
        
        $result['hand'] = $this->cards->getPlayerHand($current_player_id);
        
        $result['deckSize'] = $this->cards->countCardInLocation("deck");
        //Size of each player hand in an array :
        $result['handsSize'] = $this->cards->countCardsByLocationArgs( 'hand' );
        
        $result['bestOffer'] = self::getCurrentBestOffer();
        
        $result['currentOffer'] = self::getCurrentOffer($current_player_id);
        
        $result['handRefusedCards'] = self::dbCountHandRefusedCardsByPlayer();
  
        $result['notBiddingPlayers'] =  self::getCurrentNotBiddingPlayers();
        
        $currentState = $this->gamestate->state();
        if( $currentState['name'] == "gameEnd"){//ONLY IF USER REFRESH AT THE END
            $hands = array();
            $players = self::loadPlayersBasicInfos();
            foreach($players as $player_id => $player){
                $hands[$player_id] = $this->cards->getPlayerHand($player_id);
            }
            $result['hands'] = $hands;
        }
        $result['jokers'] = self::dbGetAllJokerValues();
        
        return $result;
    }

    /*
        getGameProgression:
        
        Compute and return the current game progression.
        The number returned must be an integer beween 0 (=the game just started) and
        100 (= the game is finished or almost finished).
    
        This method is called each time we are in a game state with the "updateGameProgression" property set to true 
        (see states.inc.php)
    */
    function getGameProgression()
    {
        $players = $this->loadPlayersBasicInfos();
        $nbPlayers = count($players);
        $deckSize = $this->cards->countCardInLocation("deck");
        $marketSize = $this->cards->countCardInLocation("market");
        $initialMarketSize = self::getGameStateValue("market_size");
        
        $startNb = 48 - 2*$nbPlayers;// - $initialMarketSize
        $endNb = 0;
        
        $currentNb = $deckSize + $marketSize;//Will be between $startNb AND 0
        $progression = ($startNb - $currentNb ) * (100/$startNb  );

        self::trace("getGameProgression() :  $progression %");
        return round($progression);
    }


//////////////////////////////////////////////////////////////////////////////
//////////// Utility functions
////////////    

    /*
        In this space, you can put any utility methods useful for your game logic
    */


/*
Scoring Helper functions
These functions should have been API but they are not, just add them to your php game and use for every game.
*/
    // get score
    function dbGetScore($player_id) {
       return $this->getUniqueValueFromDB("SELECT player_score FROM player WHERE player_id='$player_id'");
    }
    // set score
    function dbSetScore($player_id, $count) {
       $this->DbQuery("UPDATE player SET player_score='$count' WHERE player_id='$player_id'");
    }
    // increment score (can be negative too)
    function dbIncScore($player_id, $inc) {
       $count = $this->dbGetScore($player_id);
       if ($inc != 0) {
           $count += $inc;
           $this->dbSetScore($player_id, $count);
       }
       return $count;
    }
    
    function dbGetAllScores() {
       return $this->getCollectionFromDb("SELECT player_id, player_score FROM player ",true);
    }
   
    /**
    Add a token in default state
    */
    function dbAddToken($player_id, $card_id) {
       return $this->DbQuery(" INSERT INTO `token` (`token_player_id`, `token_card_id`) VALUES ( '$player_id', '$card_id') ");
    }  
    function dbTurnOverTokens($player_id, $fromState, $toState){
       return $this->DbQuery(" UPDATE `token` SET token_state='$toState' WHERE token_state='$fromState' AND token_player_id= '$player_id' ");
    }    
    /**
    Read a specific token
    */
    function dbGetToken($player_id, $card_id) {
       return $this->getObjectFromDB("SELECT token_id id ,token_player_id player_id, token_card_id card_id, token_state state,player_color player_color FROM player, token WHERE player_id=token_player_id AND player_id='$player_id' AND token_card_id='$card_id'");
    }
    /**
    Read all tokens on one location (useful for market, and not reading discard if they still exists!)
    */
    function dbGetAllTokens($card_location) {
       return $this->getCollectionFromDb("SELECT token_id id ,token_player_id player_id, token_card_id card_id, token_state state,player_color player_color FROM player, token, card  WHERE player_id=token_player_id AND token_card_id =card_id AND card_location='$card_location'",false);
    }
    /*
    function dbGetTokensByCard($card_id) {
       return $this->getCollectionFromDb("SELECT token_id id ,token_player_id player_id, token_card_id card_id, token_state state,player_color player_color FROM player, token WHERE player_id=token_player_id  AND token_card_id=$card_id",false);
    }*/
    function dbGetRefuseTokens($card_id) {
       return $this->getCollectionFromDb("SELECT token_id id ,token_player_id player_id, token_card_id card_id, token_state state,player_color player_color FROM player, token WHERE player_id=token_player_id  AND token_card_id=$card_id AND token_state=1",false);
    }
 
    function dbGetCardsWithMaxRefusedTokens(){
        $nbMaxTokens = self::getPlayersNumber() -1;
        /*
        SELECT token_card_id, count( token_player_id) nbPlayers, group_concat(token_player_id order by token_player_id separator ', ' ) refusedPlayers
        FROM `token` join card on card.card_id=token_card_id 
        WHERE card_location = 'market' AND token_state=1 
        GROUP BY token_card_id 
        HAVING count( token_player_id) >=2
        */
        return $this->getCollectionFromDb("SELECT token_card_id, group_concat(token_player_id order by token_player_id separator ', ' ) refusedPlayers FROM `token` join card on card.card_id=token_card_id WHERE card_location = 'market' AND token_state=1 GROUP BY token_card_id HAVING count( token_player_id)  >=$nbMaxTokens",true);
    }
 
    function dbCountHandRefusedCardsByPlayer() {
        /*
        SELECT player_id, player_color, count(*) nbCards
        FROM `card` join player ON card_location_arg=player_id  WHERE `card_location`='hand_refused' 
        group by 1,2 ;
        */
       return $this->getCollectionFromDb("SELECT player_id, count(*) nbCards FROM `card` join player ON card_location_arg=player_id  WHERE `card_location`='hand_refused' group by 1  ",true);
    }
    
    /**
    return counts of all tokens grouped by player, then by state (0/1).

    Example for 4 players :
    array(4) {
        [2373992]=>
            array(1) {
            [0]=>
            string(1) "4"
        }
        [2373993]=>
            array(1) {
            [0]=>
            string(1) "3"
        }
        [2373994]=>
            array(1) {
            [0]=>
            string(1) "5"
        }
        [2373995]=>
            array(2) {
            [0]=>
            string(1) "1"
            [1]=>
            string(1) "3"
            }
    }
    */
    function dbCountAllTokensByPlayerAndState() {
       return $this->getDoubleKeyCollectionFromDB("SELECT token_player_id player_id, token_state state, count(1) nb FROM token group by token_player_id,token_state ",true);
    }
    /**
    Delete every token left of this current player at this current state
    */
    function dbDeleteTokens($player_id, $state){
       self::trace("dbDeleteTokens($player_id, $state)");
       return $this->DbQuery("DELETE FROM token WHERE token_player_id='$player_id' AND token_state = '$state'");
    }
    function dbDeleteTokensOnCard($card_id){
       self::trace("dbDeleteTokensOnCard($card_id)");
       return $this->DbQuery("DELETE FROM token WHERE token_card_id='$card_id'");
    }
   
   
    function dbGetJokerValue($card_id){
        return $this->getUniqueValueFromDB("SELECT card_joker_value FROM card WHERE card_id='$card_id'");
    }
    function dbSetJokerValue($card_id, $value){
        $this->DbQuery("UPDATE card SET card_joker_value ='$value' WHERE card_id='$card_id'");
    }
       
    function dbGetAllJokerValues(){
        self::trace("dbGetAllJokerValues()...");
        $query = "SELECT card_id id, card_joker_value joker_value FROM card where card_joker_value IS not  null ";
        return $this->getCollectionFromDb($query,true);
    }
    
    /**
    Copy the user chosen value for joker into the card value.
    
    
    (Sounds like a hack in the card model, but it is a "simple" way of reusing the Stock of cards and DISPLAY the right value + reusing the scoring computation)
    */
    function dbRefreshJokersValues(){
        self::trace("dbRefreshJokersValues()...");
        $this->DbQuery("UPDATE card SET card_type_arg =card_joker_value WHERE card_joker_value is not NULL");
    }
    /**
    return list of Joker cards to be assigned (ie. not in discard pile but in hand, and of the identified player if not null)
    */
    function dbGetUnassignedJokers($player_id = null){
        $joker_value=10;
        $query = "SELECT card_id id, card_type type, card_joker_value joker_value, card_location_arg player_id FROM card where card_type_arg = '$joker_value' AND card_joker_value IS null AND card_location='hand' ";
        if(!is_null($player_id) ){
             $query = "SELECT card_id id, card_type type, card_joker_value joker_value, card_location_arg player_id FROM card where card_type_arg = '$joker_value' AND card_joker_value IS null AND card_location='hand' AND card_location_arg='$player_id' ";
        }
        return $this->getCollectionFromDb($query);
    }
    /**
    DELETE ALL EXISTING CARDS (for test purpose)
    */
    function deleteCards(){
        self::DbQuery("DELETE FROM card");
    }
    function resetPlayersBids(){
        self::DbQuery("UPDATE player SET player_pass_bid=0 ");
    }
    function passPlayerBid($player_id){
        //remove All temporary Tokens (state Question) for this player
        self::dbDeleteTokens($player_id,0);
        self::DbQuery("UPDATE player SET player_pass_bid=1 WHERE player_id = '$player_id' ");
        
        // Notify all players 
        self::notifyAllPlayers( "passBid", clienttranslate( '${player_name} passes the bid' ), array(
            'player_id' => $player_id,
            'player_name' => self::getActivePlayerName()
        ) );
    }
    
    function getBiddingCard(){
        $result = self::getObjectFromDb( "SELECT card_id id, card_type type, card_type_arg type_arg FROM card where card_location='bidding' LIMIT 1" );
        
        return $result;
    }
    
    function getCurrentBiddingPlayers(){
        //check players with X tokens => they cannot bid !
        $notPossiblePlayers = array();
        $biddingCard = self::getBiddingCard();
        if(!is_null($biddingCard)){
            $tokensOnCard = self::dbGetRefuseTokens($biddingCard['id']);
            foreach($tokensOnCard as $tokenOnCard){
                $notPossiblePlayers [] = $tokenOnCard['player_id'];
            }
        }
        return self::getObjectListFromDB( "SELECT player_id FROM player where player_pass_bid='0' AND player_id NOT IN ( '" . implode( "', '" , $notPossiblePlayers ) . "' )" , true);
    }
    function getCurrentNotBiddingPlayers(){
        //check players with X tokens => they cannot bid !
        $notPossiblePlayers = array();
        $biddingCard = self::getBiddingCard();
        if(!is_null($biddingCard)){
            $tokensOnCard = self::dbGetRefuseTokens($biddingCard['id']);
            foreach($tokensOnCard as $tokenOnCard){
                $notPossiblePlayers [] = $tokenOnCard['player_id'];
            }
        }
        return self::getObjectListFromDB( "SELECT player_id FROM player where player_pass_bid='1' OR player_id IN ( '" . implode( "', '" , $notPossiblePlayers ) . "' ) " , true);
    }
    
    function getCurrentBestOffer(){
        return array('count' =>self::getGameStateValue( 'current_trading_tokens') ,'player_id' =>self::getGameStateValue( 'current_trading_player_id') );
    }
    /**
        Count already placed "?" tokens
    */
    function getCurrentOffer($player_id){
        
        $stateQuestion = 0;
        $allAlreadyPlacedTokensAll = self::dbCountAllTokensByPlayerAndState();
        //self::dump("bid()... allAlreadyPlacedTokensAll", $allAlreadyPlacedTokensAll);
        $nbTokensAlreadyPlaced = array_key_exists($player_id,$allAlreadyPlacedTokensAll) && array_key_exists($stateQuestion,$allAlreadyPlacedTokensAll[$player_id])  ? $allAlreadyPlacedTokensAll[$player_id][$stateQuestion] : 0;
        return $nbTokensAlreadyPlaced;
    }
    
    function getNextBiddingPlayer(){
        self::trace("getNextBiddingPlayer() ...");
        $player_id = self::getActivePlayerId();
        
        //check players with X tokens => they cannot bid !
        $notPossiblePlayers = array();
        $biddingCard = self::getBiddingCard();
        if(!is_null($biddingCard)){
            $tokensOnCard = self::dbGetRefuseTokens($biddingCard['id']);
            foreach($tokensOnCard as $tokenOnCard){
                $notPossiblePlayers [] = $tokenOnCard['player_id'];
            }
        }
        
        $possiblePlayers = self::getObjectListFromDB( "SELECT player_id FROM player where player_pass_bid='0' AND player_id<>'".$player_id."' AND player_id NOT IN ( '" . implode( "', '" , $notPossiblePlayers ) . "' )" , true);
        
        /**getNextPlayerTable format :
        array( 
            1000 => 2000, 
            2000 => 3000, 
            3000 => 4000, 
            4000 => 1000, 
            0 => 1000 
           );
        */
        $nextPlayerTable = self::getNextPlayerTable();
        //self::dump("possiblePlayers",$possiblePlayers);
        //self::dump("nextPlayerTable",$nextPlayerTable);
        
        $k=0;
        $current_id = $player_id;
        while($k<count($nextPlayerTable )){
            $next_id = $nextPlayerTable[$current_id];
            
            if (in_array($next_id, $possiblePlayers)) {
                //IF player can play return 
                return $next_id;
            }
            //ELSE continue loop
            self::trace("getNextBiddingPlayer() ... $k / $current_id / $next_id KO");
            $current_id = $next_id;
            $k++;
        }
        
        return null;
    }
    
    /**
    Possible cards to choose in the market
    */
    function getPossibleCardsInMarket($player_id){
        //RETURN NEW POSSIBLE CARDS : array of card_id 
        $possibleCards = array();
        
        $market = $this->cards->getCardsInLocation("market");
        
        if(count($market)> 0){
            
            //Filter on placed tokens
            foreach($market as $card_id => $card){
                $existingToken = self::dbGetToken($player_id, $card_id);
                if(is_null($existingToken)){
                    $possibleCards[] = $card_id ;
                } 
            }
        }
        return $possibleCards;
    }  
    
    function getPossibleCardsInHand($player_id, $keep_value = null){
        self::trace("getPossibleCardsInHand($player_id): ...");
        //RETURN NEW POSSIBLE CARDS : array of card_id 
        $possibleCards = array();
        
        $hand = $this->cards->getPlayerHand( $player_id );
        
        if(count($hand)> 0){
            if(is_null($keep_value)){
                //THEN every card is possible 
                $possibleCards = array_keys($hand);
            }
            else {//IF we want to filter on ONE value
                foreach($hand as $card_id => $card){
                    $value = $card['type_arg'];
                    if($value == $keep_value){
                       $possibleCards[] = $card_id;
                    }
                }
            }
        }
        return $possibleCards;
    }  
    
    /**
    Init DataBase
    */
    function initTables(){
        try {
            $players = $this->loadPlayersBasicInfos();
            //TODO ? REMOVE DELETE AFTER DEV PHASE
            $this->deleteCards();
            $this->prepareDeck();
            $this->prepareGame($players);
        
        } catch ( Exception $e ) {
            // logging does not actually work in game init :(
            // but if you calling from php chat it will work
            $this->error("Fatal error while creating game");
            $this->dump('err', $e);
        }
        
    }
    
    function fillMarketWithCards(){
        $currentCount = $this->cards->countCardInLocation("market");
        $nbr = self::getGameStateValue("market_size") - $currentCount;
        //self::trace("fillMarketWithCards()... $currentCount / $nbr");// REMOVE for setupNewGame
        if($nbr>0){
           return $this->cards->pickCardsForLocation( $nbr, "deck", "market", 0, false );
        }
    }
    
    function prepareDeck(){
    
        //Deck of 48 cards  : for each color 2*[1 to 6] +  1*[7 to 9] + 1 wildcard

        $cards = array(
            array( 'type' => 1, 'type_arg' => 1, 'nbr' => 2 ),
            array( 'type' => 1, 'type_arg' => 2, 'nbr' => 2 ),
            array( 'type' => 1, 'type_arg' => 3, 'nbr' => 2 ),
            array( 'type' => 1, 'type_arg' => 4, 'nbr' => 2 ),
            array( 'type' => 1, 'type_arg' => 5, 'nbr' => 2 ),
            array( 'type' => 1, 'type_arg' => 6, 'nbr' => 2 ),
            array( 'type' => 1, 'type_arg' => 7, 'nbr' => 1 ),
            array( 'type' => 1, 'type_arg' => 8, 'nbr' => 1 ),
            array( 'type' => 1, 'type_arg' => 9, 'nbr' => 1 ),
            array( 'type' => 1, 'type_arg' => 10, 'nbr' => 1 ),
            
            array( 'type' => 2, 'type_arg' => 1, 'nbr' => 2 ),
            array( 'type' => 2, 'type_arg' => 2, 'nbr' => 2 ),
            array( 'type' => 2, 'type_arg' => 3, 'nbr' => 2 ),
            array( 'type' => 2, 'type_arg' => 4, 'nbr' => 2 ),
            array( 'type' => 2, 'type_arg' => 5, 'nbr' => 2 ),
            array( 'type' => 2, 'type_arg' => 6, 'nbr' => 2 ),
            array( 'type' => 2, 'type_arg' => 7, 'nbr' => 1 ),
            array( 'type' => 2, 'type_arg' => 8, 'nbr' => 1 ),
            array( 'type' => 2, 'type_arg' => 9, 'nbr' => 1 ),
            array( 'type' => 2, 'type_arg' => 10, 'nbr' => 1 ),
            
            array( 'type' => 3, 'type_arg' => 1, 'nbr' => 2 ),
            array( 'type' => 3, 'type_arg' => 2, 'nbr' => 2 ),
            array( 'type' => 3, 'type_arg' => 3, 'nbr' => 2 ),
            array( 'type' => 3, 'type_arg' => 4, 'nbr' => 2 ),
            array( 'type' => 3, 'type_arg' => 5, 'nbr' => 2 ),
            array( 'type' => 3, 'type_arg' => 6, 'nbr' => 2 ),
            array( 'type' => 3, 'type_arg' => 7, 'nbr' => 1 ),
            array( 'type' => 3, 'type_arg' => 8, 'nbr' => 1 ),
            array( 'type' => 3, 'type_arg' => 9, 'nbr' => 1 ),
            array( 'type' => 3, 'type_arg' => 10, 'nbr' => 1 ),
        );
        
        $this->cards->createCards($cards);
        //No need to shuffle after creation ?
        $this->cards->shuffle("deck");
    }
    
    /**
    Preparation to be made before the first turn
    */
    function prepareGame($players){
        
        //Each player gets 2 cards from the deck
        foreach($players as $player_id => $player){
            $this->cards->pickCards(2, "deck", $player_id);
        }
        
        //Init Market of cards
        self::fillMarketWithCards();
    
        //According to the rules, tokens are not limited : we don't have to create and keep a track for 24 tokens in 'hand' , we will create a token when needed (ie  on the market)
        
    }

    function giveCardToWinner($card_id,$winner_id, $isAutomatic){
        self::trace("giveCardToWinner($card_id,$winner_id,$isAutomatic)");
        
        $card = $this->cards->getCard($card_id);
        $card_color = $card['type'];
        $card_value = $card['type_arg'];
        
        self::dbDeleteTokensOnCard($card_id);
        //Winner receives the card in hand :
        $this->cards->moveCard($card_id, "hand", $winner_id);
        
        self::incStat(1,'won_number',$winner_id);
        if($card_value == 10) {
            self::incStat(1,'wonJoker_number',$winner_id);
        }
        
        if(!$isAutomatic){
            $bestOffer = self::getCurrentBestOffer();
            $bestOfferCount = $bestOffer['count'];
            self::notifyAllPlayers( "finalOffer", clienttranslate( 'The final offer is ${nb} !' ), array(
                'nb' => $bestOfferCount,
            ) );
        }
        
        // Notify all players about the card played
        self::notifyAllPlayers( "bidWin", clienttranslate( '${player_name} wins the card ${value_displayed} ${color_displayed}' ), array(
            'i18n' => array ('color_displayed','value_displayed' ),
            'player_id' => $winner_id,
            'player_name' => self::getPlayerNameById($winner_id),
            'value' => $card_value,
            'color' => $card_color,
            'value_displayed' => $this->values_labels[$card_value],
            'color_displayed' => $this->card_types [$card_color] ['name'],
            'card_id' => "$card_id",
            'isAutomatic' => $isAutomatic
        ) );
    }

    function giveCardsToAutomaticWinners(){
        //if (other) cards now contains (nbPlayers-1) "Refuse" tokens, the last players win these cards and tokens are removed
        $cardsWithMaxRefusedTokens = self::dbGetCardsWithMaxRefusedTokens();
        //self::dump("stEndRound() ...cardsWithMaxRefusedTokens",$cardsWithMaxRefusedTokens);
        $players = self::loadPlayersBasicInfos();
        if(count($cardsWithMaxRefusedTokens)>0){
            self::notifyAllPlayers( "cardsWithMaxRefusedTokens", clienttranslate( '${nb} cards are automatically won' ), array( 'nb' => count($cardsWithMaxRefusedTokens) ) );
            
            foreach($cardsWithMaxRefusedTokens as $cardWithMaxRefusedTokens_id => $refusedPlayers){
                //FIND WINNER
                $winner = null;
                $refusedPlayersId = explode(', ',$refusedPlayers);
                foreach( $players  as $player_id=> $pp){
                    if( !in_array($player_id,$refusedPlayersId) ){
                        $winner = $player_id;
                        break;
                    }
                }
                if(!is_null( $winner)) self::giveCardToWinner($cardWithMaxRefusedTokens_id, $winner,true);
                
            }
        }
    }
    /**
    Notify all players about all hands => this should be done at the end because this info is private !
    */
    function notifyPlayersHand(){
        self::trace("notifyPlayersHand()...");
     
        $hands = array();
        $players = self::loadPlayersBasicInfos();
        foreach($players as $player_id => $player){
            $hands[$player_id] = $this->cards->getPlayerHand($player_id);
        }
        
        self::notifyAllPlayers( "playersHands", '', array(
            'hands' => $hands,
            'jokers' => self::dbGetAllJokerValues()
        ) );
    }
    /**
    Notify players about current scores
    */
    function notifyScoring(){
        self::trace("notifyScoring()...");
     
        $newScores = $this->dbGetAllScores();
        
        self::notifyAllPlayers( "newScores", '', array(
            'newScores' => $newScores
        ) );
    }
    

    /**
    RESET Score of each player based upon his hand cards
    */    
    function computeScoring(){
        self::trace("computeScoring()...");
        
        $players = self::loadPlayersBasicInfos();
        
        foreach($players as $player_id => $player){
            $player_name = $player['player_name'];
            self::trace("computeScoring()... Compute score for player $player_name ($player_id)...");
            //RESET  (useful for TESTING by calling this method)
            self::dbSetScore($player_id,0);
            
            $hand = $this->cards->getPlayerHand($player_id);
            
            self::computeNominalCombinations($hand,$player_id,$player_name, false);
            self::computeColorCombinations($hand,$player_id,$player_name, false);
        }
    }
    
    /**
    IF $isSilent => Compute score silently (no notif, no stat, no DB value set)
    return : the total score for this player nominal combinations
    */
    function computeNominalCombinations($hand,$player_id,$player_name, $isSilent = false){
        $scoreCounter = 0;
        //compute score for each  card combinations at nominal value
        for($k = 9; $k>= 1 ; $k--){//Starts with better combination to use Joker
            $nbCombinations = self::countNominalCombinations($hand,$k ,$isSilent) ;
            if( $nbCombinations >0 ){
                //! look for a 2nd same combination ? (Ex : 1,1,1 and 1,1,1)
                $score = 3 * $nbCombinations;
                if($k>6){
                    $score = 6 * $nbCombinations;
                }
                self::trace("computeNominalCombinations($isSilent) : $score points for nominal combination of $k for player $player_id");
                
                $scoreCounter+= $score;
                if( $isSilent) {
                    continue;
                }
                
                if($k>6){
                    self::incStat($nbCombinations,'scoreCombinationHigh_number',$player_id);
                }
                else {
                    self::incStat($nbCombinations,'scoreCombinationLow_number',$player_id);
                }
                self::notifyAllPlayers( "scoreThreeOfAKind", clienttranslate( '${player_name} scores ${points} points for completing combination of ${value_displayed}' ), array(
                    'i18n' => array("value_displayed"),
                    'player_id' => $player_id,
                    'player_name' => $player_name,
                    'points' => $score,
                    'value' => $k,
                    'value_displayed' => $this->values_labels [$k],
                    
                ) );
                self::dbIncScore($player_id,$score );
            }
        }
        return $scoreCounter;
    }
    /*
    return true if hand contains a combination of 3 different colors of value $k,
           false otherwise
    */
    function countNominalCombinations($hand,$k, $isSilent = false){
        //owned colors with this "k" value
        $colors = array();
        
        foreach($hand as $card ){
            $card_id = $card['id'];
            $color = $card['type'];
            $value = $card['type_arg'];
           
                if($value ==$k ){ // same number
                    if(!array_key_exists($color,$colors)){
                        $colors[$color] = 1;
                    }
                    else {
                        $colors[$color] ++;
                    }
                }
                else if($value == 10 ){// WILDCARD
                   //TODO GET JOKER VALUE Chosen by player => SHOULD NOT HAPPEN by using dbRefreshJokersValues()
                   
                }
        }
        $nbColors = count($colors);
        if($nbColors >= 3){//PLAYER has all colors of value K
            $result = min($colors);// 1 OR 2 if player has ALL CARDS of value K
            //self::dump("countNominalCombinations($k)...  result = $result for colors:",$colors);
            return $result;
        } 
        self::trace("countNominalCombinations($k, $isSilent)...  result = 0");
        return 0;
    }

    /**
    IF $isSilent => Compute score silently (no notif, no stat, no DB value set)
    return : the total score for this player color combinations
    */
    function computeColorCombinations($hand,$player_id,$player_name, $isSilent = false){
        $scoreCounter = 0;
        //compute score for each  card combinations at color (SUITS)
        foreach($this->card_types as $color_id => $type){
            //MAX 3 different suits of the same color ( with 1,2,3,-  ,5,6,7,-, 9,1,2,3,4,5)
            // but we keep 2 different arrays for each group of cards from 1 to 9 and 1 to 6
            $suit1 = array();//1 to 9
            $suit2 = array();//1 to 6
            // $suit3 = array();
            
            foreach($hand as $card ){// LOOP EACH CARD TO save it in corresponding suit
                $color = $card['type'];
                if($color != $color_id ) continue;
                $card_id = $card['id'];
                $value = $card['type_arg'];
                
                if(!array_key_exists($value,$suit1)){
                    $suit1[$value] = $card_id;
                } else if(!array_key_exists($value,$suit2)){
                    $suit2[$value] = $card_id;
                } else {
                    //SHOULD NOT HAPPEN except with a useless joker
                }
                
            }
            
            $score = 0;
            $score += 1 * self::countCardsInSuit($suit1); // 1 Point for each card in suit
            $score += 1 * self::countCardsInSuit($suit2); // 1 Point for each card in suit
            self::trace("computeColorCombinations($player_name, $isSilent) : $score points for color $color_id combination for player $player_id");
            $scoreCounter+= $score;
            if( $isSilent) {
                continue;
            }
            
            if($score>0){
                self::notifyAllPlayers( "scoreSuit", clienttranslate( '${player_name} scores ${points} points for ${color_displayed} combinations' ), array(
                    'i18n' => array("color_displayed"),
                    'player_id' => $player_id,
                    'player_name' => $player_name,
                    'points' => $score,
                    'color' => $color_id,
                    'color_displayed' => $this->card_types [$color_id] ['name'],
                    
                ) );
            }
            if($color_id == 1){
                self::setStat($score,'scoreRed_points',$player_id);
            } else if($color_id == 2){
                self::setStat($score,'scoreGreen_points',$player_id);
            } else if($color_id == 3){
                self::setStat($score,'scoreBlue_points',$player_id);
            } 
            
            self::dbIncScore($player_id,$score );  
        }
        return $scoreCounter;
    }
    
    /** 
    Compute score silently (no notif, no stat, no DB value set)
    */
    function getCurrentScore($player_id){
        
        $hand = $this->cards->getPlayerHand($player_id);
        $score = 0;
        $score += self::computeNominalCombinations($hand,$player_id,'', true);
        $score += self::computeColorCombinations($hand,$player_id,'',true);
        return $score;
    }
    
    /**
    count how many cards in the array $suit are really part of a suit of 3 cards min
    */
    function countCardsInSuit($suit){
        if(count($suit)<3) return 0;
        $size = 0;
        //SORT ARRAY by key  for debug
        ksort($suit);
        foreach($suit as $value => $id) {
            if( array_key_exists($value -2,$suit) && array_key_exists($value - 1,$suit)
              ||array_key_exists($value -1,$suit) && array_key_exists($value + 1,$suit)
              ||array_key_exists($value +1,$suit) && array_key_exists($value + 2,$suit)
            ){
                //In these 3 cases, this card is part of a suit of 3 Cards minimum, and counts the same amount of (1) point whatever the case
                $size += 1;
            }
        }
        //self::dump("countCardsInSuit() ... return size $size for suit  ",$suit);
        return $size;
    }            
    
    
//////////////////////////////////////////////////////////////////////////////
//////////// Player actions
//////////// 

    /*
        Each time a player is doing some game action, one of the methods below is called.
        (note: each method below must match an input method in sticktocolours.action.php)
    */
    function chooseMarketCard( $card_id )
    {
        // Check that this is the player's turn and that it is a "possible action" at this game state (see states.inc.php)
        self::checkAction( 'chooseMarketCard' ); 
        
        $player_id = self::getActivePlayerId();
        
        $card = $this->cards->getCard($card_id);
        if(is_null($card) || $card['location']  != "market") throw new BgaVisibleSystemException ( ("This card is not available in the market"));
    
        $biddingCard = self::getBiddingCard();
        if(!is_null($biddingCard ) ) throw new BgaVisibleSystemException ( ("A card has already been chosen"));
        
        $existingToken = self::dbGetToken($player_id, $card_id);
        if(!is_null($existingToken) && $existingToken['state'] ==1 ) throw new BgaVisibleSystemException ( ("You cannot choose to trade a card with a 'Refuse' token"));
            
        $card_color = $card['type'];
        $card_value = $card['type_arg'];
        
        //Move the card on bidding area
        $this->cards->moveCard($card_id,"bidding");
        
        // Notify all players about the card played
        self::notifyAllPlayers( "marketCardChosen", clienttranslate( '${player_name} chooses ${value_displayed} ${color_displayed} for the next trading' ), array(
            'i18n' => array ('color_displayed','value_displayed' ),
            'player_id' => $player_id,
            'player_name' => self::getActivePlayerName(),
            'value' => $card_value,
            'color' => $card_color,
            'value_displayed' => $this->values_labels[$card_value],
            'color_displayed' => $this->card_types [$card_color] ['name'],
            'card_id' => $card_id,
            'biddingCardTokens' => self::dbGetRefuseTokens($card_id)
        ) );
          
        $this->gamestate->nextState("chooseMarketCard");
    }
    
    /** REVERSE action of chooseMarketCard()
    */
    function cancelChoice()
    {
        self::checkAction( 'cancelChoice' ); 
        
        $player_id = self::getActivePlayerId();
        
        //Controls : active player must be dealer AND no token must have been placed AND a card must have been chosen
        $dealer = self::getGameStateValue( 'dealer');
        $bestOffer = self::getCurrentBestOffer();
        $biddingCard = self::getBiddingCard(); //Should not be null at that time because of possibleActions...
        if($bestOffer['count']>0 || $dealer != $player_id || is_null($biddingCard ) ){
            throw new BgaVisibleSystemException(("You cannot cancel at this moment of the game"));
        }
        
        $card_id = $biddingCard['id'];
        $card_color = $biddingCard['type'];
        $card_value = $biddingCard['type_arg'];
        $tokens = self::dbGetAllTokens("bidding");
        
        //Move the card back on market area
        $this->cards->moveCard($card_id,"market");
        
        self::notifyAllPlayers( "cancelChoice", clienttranslate( '${player_name} cancels the choice for the next trading' ), array(
            'player_name' => self::getActivePlayerName(),
            'card_id' => $card_id,
            'value' => $card_value,
            'color' => $card_color,
            'newMarketTokens' => $tokens,
        ) );
        
        $this->gamestate->nextState("cancelChoice");
    }
    
    function passChoice()
    {
        // Check that this is the player's turn and that it is a "possible action" at this game state (see states.inc.php)
        self::checkAction( 'passChoice' ); 
        
        $player_id = self::getActivePlayerId();
        // Notify all players 
        self::notifyAllPlayers( "passChoice", clienttranslate( '${player_name} passes' ), array(
            'player_id' => $player_id,
            'player_name' => self::getActivePlayerName()
        ) );
          
        self::incStat(1,'passChoice_number',$player_id);
        
        $this->gamestate->nextState("pass");
    }
    
    function passBid()
    {
        self::trace("passBid");
        // Check that this is the player's turn and that it is a "possible action" at this game state (see states.inc.php)
        self::checkAction( 'passBid' ); 
        
        $player_id = self::getActivePlayerId();
        
        //if player is dealer, he must bid at least 1 token before passing
        $current_trading_tokens = self::getGameStateValue( 'current_trading_tokens' );
        if($current_trading_tokens == 0) throw new BgaVisibleSystemException(("You cannot pass your first bid as dealer"));
        
        //save   "passBid" for this player
        $this->passPlayerBid($player_id);
        
        self::incStat(1,'passBid_number',$player_id);
          
        $this->gamestate->nextState("pass");
    }
    
    function bid($card_ids)
    {
        // Check that this is the player's turn and that it is a "possible action" at this game state (see states.inc.php)
        self::checkAction( 'bid' ); 
        
        $player_id = self::getActivePlayerId();
        
        $nbTokens = count($card_ids);
        $nbTokensAlreadyPlaced = self::getCurrentOffer($player_id);;
        $playerNbTokens = $nbTokens + $nbTokensAlreadyPlaced;
        if($playerNbTokens == 0) throw new BgaUserException(self::_("You must bid with at least one card"));
        
        $lastNbTokens = self::getGameStateValue( 'current_trading_tokens' );
        self::trace("bid()...  $nbTokens new tokens is not enough for  $player_id with already $nbTokensAlreadyPlaced tokens because previous player bids with $lastNbTokens tokens.");
        if($lastNbTokens >= $playerNbTokens) throw new BgaUserException(self::_("You must bid with more cards than the previous player"));
        
        $numberHand = 0;
        
        foreach($card_ids as $card_id){
            //ALL CARD CONTROLS AT SAME TIME
            //TODO JSA reuse getPossibleCardsInMarket
            
            $existingToken = self::dbGetToken($player_id, $card_id);
            self::trace("bid() : This card  $card_id is not available to $player_id for trading");
            if(!is_null($existingToken)) throw new BgaUserException(self::_("This card is not available to you for trading"));
            
            $card = $this->cards->getCard($card_id);
            if(is_null($card) 
                || !($card['location']  == "market"  //CARD IS OK on the market if the player has 0 token on it
                    || $card['location']  == "hand" && $card['location_arg'] == $player_id)  //CARD IS OK on the hand if the player has 0 token on it
            ) {
                throw new BgaUserException(self::_("This card is not available to you for trading"));
            }
        }
        
        foreach($card_ids as $card_id){
                
            $card = $this->cards->getCard($card_id);
            $card_color = $card['type'];
            $card_value = $card['type_arg'];
            $card_location = $card['location'];
            
            self::dbAddToken($player_id, $card_id);   
            $token = self::dbGetToken($player_id, $card_id);

            self::trace("bid(): $player_id bids on $card_id");

            if($card_location == "hand"){
                $numberHand++;
                //Move to board zone "hand_refused"
                $this->cards->moveCard($card_id, "hand_refused", $player_id);
                        
                self::notifyPlayer($player_id, "bidCardFromHand", clienttranslate( 'You bid with ${value_displayed} ${color_displayed} from your hand' ), array(
                    'i18n' => array ('color_displayed','value_displayed' ),
                    'value' => $card_value,
                    'color' => $card_color,
                    'value_displayed' => $this->values_labels[$card_value],
                    'color_displayed' => $this->card_types [$card_color] ['name'],
                    'card_id' => $card_id
                ) );        
            }
            else { 
                // Notify all players about the token played  IF NOT IN HAND (keep it hidden)
                self::notifyAllPlayers( "bidCardsChosen", clienttranslate( '${player_name} bids with ${value_displayed} ${color_displayed}' ), array(
                    'i18n' => array ('color_displayed','value_displayed' ),
                    'player_id' => $player_id,
                    'player_name' => self::getActivePlayerName(),
                    'value' => $card_value,
                    'color' => $card_color,
                    'value_displayed' => $this->values_labels[$card_value],
                    'color_displayed' => $this->card_types [$card_color] ['name'],
                    'card_id' => $card_id
                ) );        
                self::notifyAllPlayers( "newTokens", '', array(
                    'token' => $token,
                ) );   
            }

        }

        if($numberHand>0){
            self::notifyAllPlayers( "handRefusedCards", clienttranslate( '${player_name} bids with ${number} cards from his hand' ), array(
                'player_id' => $player_id,
                'player_name' => self::getActivePlayerName(),
                'number' => $numberHand,
                'handRefusedCards' => self::dbCountHandRefusedCardsByPlayer()
            ) );
        }
        
        self::incStat(1,'bids_number');
        self::incStat(1,'bids_number',$player_id);
        self::incStat( $nbTokens,'tokens_number',$player_id);
        
        //Save number of tokens for next bid
        self::setGameStateValue( 'current_trading_tokens', $playerNbTokens );
        self::setGameStateValue( 'current_trading_player_id', $player_id );
        self::notifyAllPlayers( "bestOffer", '', array(
            'bestOffer' => self::getCurrentBestOffer() 
            //TODO JSA replace with state arg ?
        ) );
          
        $this->gamestate->nextState("bid");
    }
    
    function chooseJoker($card_id, $newValue){
        self::checkAction( 'chooseJoker' ); 
        
        $player_id = self::getCurrentPlayerId();//(in multi players state) 
        $player_name = self::getCurrentPlayerName();
        
        $card = $this->cards->getCard($card_id);
        if(is_null($card) || $card['location']  != "hand" || $card['location_arg'] != $player_id) throw new BgaVisibleSystemException( ("This card is not in your hand"));
    
        $card_value = $card['type_arg'];
        if($card_value  != 10) throw new BgaVisibleSystemException(("This card is not a joker"));
        
        if($newValue <1 || $newValue > 9) throw new BgaVisibleSystemException(("The value of the card must be between 1 and 9"));
        
        $jokerValue = self::dbGetJokerValue($card_id);
        if(!is_null($jokerValue ) ) throw new BgaVisibleSystemException(("A value has already been chosen for this joker"));
        
        $card_color = $card['type'];
        $color_displayed = $this->card_types [$card_color] ['name'];
        
        self::trace("chooseJoker($card_id, $newValue): player $player_name (id=$player_id) chooses the value $newValue for the $color_displayed joker ");
        self::dbSetJokerValue($card_id,$newValue);
        
        // Notify all players about the card played
        self::notifyAllPlayers( "jokerChosen", clienttranslate( '${player_name} chooses to turn the ${color_displayed} joker into a ${value_displayed}' ), array(
            'i18n' => array ('color_displayed','value_displayed' ),
            'player_id' => $player_id,
            'player_name' => $player_name,
            'value' => $newValue,
            'color' => $card_color,
            'value_displayed' => $this->values_labels[$newValue],
            'color_displayed' => $this->card_types [$card_color] ['name'],
            'card_id' => $card_id
        ) );
    
        //-------------- IF WE WANT TO DISPLAY CURRENT SCORE AFTER EACH JOKER ------------------------
        self::dbRefreshJokersValues();
        self::notifyPlayer($player_id, "updatedScore", '', array(
            'currentScore' => self::getCurrentScore( $player_id)
        ) );   
        //-------------------------------------------------------------------------------------------  
        
        $unassignedJokers = self::dbGetUnassignedJokers($player_id);
        $stillJokers = count($unassignedJokers)>0;
        if($stillJokers)  {
            
            self::trace("chooseJoker($card_id, $newValue): player $player_name (id=$player_id) has other jokers to assign... don't deactivate this player.");
            self::giveExtraTime($player_id);
        }
        else {
            
            //When ending the player action, instead of a state transition, deactivate player.
            $this->gamestate->setPlayerNonMultiactive($player_id, 'end'); // deactivate player; if none left, transition to 'end' state
        }
        
    }
//////////////////////////////////////////////////////////////////////////////
//////////// Game state arguments
////////////

    /*
        Here, you can create methods defined as "game state arguments" (see "args" property in states.inc.php).
        These methods function is to return some additional information that is specific to the current
        game state.
    */

    function argChoosingCard(){
        $activePlayer = self::getActivePlayerId();
        $players = self::loadPlayersBasicInfos();
        
        $privateDatas = array ();
        foreach($players as $player_id => $player){
            $privateDatas[$player_id] = array(
                'currentScore' => self::getCurrentScore( $player_id)
            );
        }
        
        // return values:
        return array(
            'possibleCardInMarket' => self::getPossibleCardsInMarket( $activePlayer),
            '_private' => $privateDatas
        );
    }
    
    function argPlayerTurn(){
        $activePlayer = self::getActivePlayerId();
        $players = self::loadPlayersBasicInfos();
        
        $privateDatas = array ();
        /*
        $privateDatas['active'] =  array(
                    'possibleCardInHand' => self::getPossibleCardsInHand( $activePlayer)
                );
        */
        foreach($players as $player_id => $player){
            $privateDatas[$player_id] = array(
                'possibleCardInHand' => ($player_id == $activePlayer) ? self::getPossibleCardsInHand( $player_id) : null,
                'currentScore' => self::getCurrentScore( $player_id)
            );
        }
        // return values:
        return array(
            'currentBestOffer' => self::getCurrentBestOffer(),
            'notBiddingPlayers' => self::getCurrentNotBiddingPlayers(),
            'possibleCardInMarket' => self::getPossibleCardsInMarket( $activePlayer),
            '_private' => $privateDatas,
        );
    }
    
    function argAssignJokers(){
        $activePlayer = self::getActivePlayerId();
        self::trace("argAssignJokers($activePlayer)...");
        
        $jokersToAssign = array();
        $players = self::loadPlayersBasicInfos();
        foreach($players as $player_id => $player){
            $jokersToAssign[$player_id] = array( 
                'possibleCardInHand' => self::getPossibleCardsInHand( $player_id, 10),
                'currentScore' => self::getCurrentScore( $player_id),
                );
        }
        
        return array(
            //'_private' => array(   // all data inside this array will be private
            //        $specific_player_id => array(   // will be sent only to that player   
            //            'somePrivateData' => self::getSomePrivateData()   
            //        )
            //    ),
            '_private' => $jokersToAssign
        );
    }
    
    
    function argScoring()
    {
        $hands = array();
        $players = self::loadPlayersBasicInfos();
        foreach($players as $player_id => $player){
            $hands[$player_id] = $this->cards->getPlayerHand($player_id);
            self::setStat(count($hands[$player_id] ) ,'hand_size',$player_id);
        }
        $jokers = self::dbGetAllJokerValues();
        /*
        WORKS but is called BEFORE the execution needed to get the last values of jokers :(
        */ 
        return array(
            'hands' => $hands,
            'jokers' => $jokers,
        );
    }

//////////////////////////////////////////////////////////////////////////////
//////////// Game state actions
////////////

    /*
        Here, you can create methods defined as "game state actions" (see "action" property in states.inc.php).
        The action method of state X is called everytime the current game state is set to X.
    */
    
    function stNewRound()
    { 
        //$player_id = self::getActivePlayerId();
        //!! next dealer is not always the next player !
        $dealer = self::getGameStateValue( 'dealer');
        $nextPlayerTable = self::getNextPlayerTable();
        $newDealer = $nextPlayerTable[ $dealer];
        $this->gamestate->changeActivePlayer($newDealer);
        self::giveExtraTime($newDealer);
        self::setGameStateValue( 'dealer', $newDealer );
        self::setGameStateValue( 'current_trading_tokens', 0 );
        self::setGameStateValue( 'current_trading_player_id', $newDealer );
        
        self::incStat(1,'dealer_number',$newDealer);
        
        $this->resetPlayersBids();
        
        //Notify all about new dealer
        self::notifyAllPlayers( "newDeal", clienttranslate( '${player_name} is the dealer' ), array(
            'player_id' => $newDealer,
            'player_name' => self::getActivePlayerName()
        ) );
        
        //TEST IF DEALER has no option no play => the game may pass automatically to avoid time waste : (it could happen at the last turns of the game if 1 player puts their X token on all market cards )
        $possibleCardInMarket = self::getPossibleCardsInMarket( $newDealer);
        $autoPass = (count($possibleCardInMarket) == 0) ? true : false;
        if($autoPass){
                
            self::notifyAllPlayers( "passChoiceAuto", clienttranslate( '${player_name} automatically passes' ), array(
                'player_id' => $newDealer,
                'player_name' => self::getActivePlayerName()
            ) );
            self::incStat(1,'passChoice_number',$newDealer);
            
            $this->gamestate->nextState( 'autoPass' );
            return;
        }
        
        $this->gamestate->nextState( 'next' );
    }    
    function stNextRound()
    { 
    
        $player_id = $this->activeNextPlayer();
        self::giveExtraTime($player_id);
        
        $this->gamestate->nextState( 'next' );
    }
    function stNextPlayer()
    {
        $player_id = $this->getNextBiddingPlayer();
        if(is_null($player_id)){
            $this->gamestate->nextState( 'end' );
            return;
        }
        $possiblePlayers = self::getCurrentBiddingPlayers();
        if( count($possiblePlayers) <= 1){
            //END ALSO if only one player didn't pass => he wins
            $this->gamestate->changeActivePlayer($player_id);
            $this->gamestate->nextState( 'end' );
            return;
        }
        
        //! active only players who didn't pass !
        //$player_id = $this->activeNextPlayer();
        $this->gamestate->changeActivePlayer($player_id);
        
        self::giveExtraTime($player_id);
        $this->gamestate->nextState( 'nextPlayer' );
    } 
    
    /*
     give the card to the winner.
    IF there still are cards to win, we start a new Round
    ELSE end the game
    */
    function stEndRound()
    { 
        //$player_id = self::getActivePlayerId();
        $winner = self::getGameStateValue( 'current_trading_player_id');
        $biddingCard = self::getBiddingCard();
        $card_id = $biddingCard['id'];
        
        //TURN OVERS tokens Question -> REFUSE
        self::dbTurnOverTokens($winner, 0, 1);
                
        //REMOVE ALL CARDs from "hand_refused" pile, their token doesn't exist anymore (for now they doesn't exist in DB before neither after)
        $hand_refused_counts = $this->cards->countCardsByLocationArgs( 'hand_refused' );
        foreach($hand_refused_counts  as $player_id => $hand_refused_count){
            self::incStat($hand_refused_count,'discarded_number');
            self::incStat($hand_refused_count,'discarded_number',$player_id);
        }

        $this->cards->moveAllCardsInLocation( "hand_refused", "discard" );
        
        self::giveCardToWinner($card_id, $winner,false);
        
        self::giveCardsToAutomaticWinners();
        
        // Notify all players about the hands size
        $handsSize = $this->cards->countCardsByLocationArgs( 'hand' );
        self::notifyAllPlayers( "handsSize", '', array(
            'handsSize' => $handsSize,
        ) );
        
        
        //Replenish market
        $marketUpdated = self::fillMarketWithCards();
        //self::dump("stEndRound()...   marketUpdated =", $marketUpdated);
        $deckSize = $this->cards->countCardInLocation("deck");
        if(count($marketUpdated)>0){ // IF there is enough cards to draw
            // Notify all players about the card drawn
            
            foreach($marketUpdated as $newCard ){// loop when multiple draws (possible >1 after automatic cards won)
                $card_id = $newCard['id'];
                $card_color = $newCard['type'];
                $card_value = $newCard['type_arg'];
                
                self::notifyAllPlayers( "drawCard", clienttranslate( 'The game draws the card ${value_displayed} ${color_displayed}' ), array(
                    'i18n' => array ('color_displayed','value_displayed' ),
                    'value' => $card_value,
                    'color' => $card_color,
                    'value_displayed' => $this->values_labels[$card_value],
                    'color_displayed' => $this->card_types [$card_color] ['name'],
                    'card_id' => $card_id,
                    'deckSize' => $deckSize
                ) );
            }
        }
        else {//ELSE there is no more cards in deck
            $market_size = $this->cards->countCardInLocation("market");
            if($market_size == 0){
                self::notifyAllPlayers( "nearEnd", clienttranslate( 'No card left, the game ends !' ), array(  ) );
                $this->gamestate->nextState( 'endGame' );
                return;
            }
        }
        
        $this->gamestate->nextState( 'newRound' );
    }         

    function stAssignJokers(){
        // this will make all players multiactive just before entering the state
        //$this->gamestate->setAllPlayersMultiactive();
        
        //active players with 1 joker minimum :
        $player_ids = array();
        $unassignedJokers = self::dbGetUnassignedJokers();
        foreach($unassignedJokers as $unassignedJoker){
            $player_ids[] = $unassignedJoker["player_id"];
        }
        // if 0 joker left (ie. in discard pile) => we skip this state to the next "end" state
        $this->gamestate->setPlayersMultiactive( $player_ids, "end", true);
        
    }
    
    function stScoring(){
        self::notifyAllPlayers( "scoringPhase", clienttranslate( 'No joker left, the game computes the scores' ), array(  ) );
        self::dbRefreshJokersValues();
        self::notifyPlayersHand();
        self::computeScoring();
        self::notifyScoring();
        $this->gamestate->nextState( 'endGame' );
    }
//////////////////////////////////////////////////////////////////////////////
//////////// Zombie
////////////

    /*
        zombieTurn:
        
        This method is called each time it is the turn of a player who has quit the game (= "zombie" player).
        You can do whatever you want in order to make sure the turn of this player ends appropriately
        (ex: pass).
        
        Important: your zombie code will be called when the player leaves the game. This action is triggered
        from the main site and propagated to the gameserver from a server, not from a browser.
        As a consequence, there is no current player associated to this action. In your zombieTurn function,
        you must _never_ use getCurrentPlayerId() or getCurrentPlayerName(), otherwise it will fail with a "Not logged" error message. 
    */

    function zombieTurn( $state, $active_player )
    {
    	$statename = $state['name'];
    	
        if ($state['type'] === "activeplayer") {
            switch ($statename) {
                case "playerTurn":
                    //send same the notification to players in order to refresh the UI
                    $this->passPlayerBid($active_player);
                    $this->gamestate->nextState( "zombiePass" );
                	break;
                default:
                    $this->gamestate->nextState( "zombiePass" );
                	break;
            }

            return;
        }

        if ($state['type'] === "multipleactiveplayer") {
            // Make sure player is in a non blocking status for role turn
            $this->gamestate->setPlayerNonMultiactive( $active_player, '' );
            
            return;
        }

        throw new feException( "Zombie mode not supported at this game state: ".$statename );
    }
    
///////////////////////////////////////////////////////////////////////////////////:
////////// DB upgrade
//////////

    /*
        upgradeTableDb:
        
        You don't have to care about this until your game has been published on BGA.
        Once your game is on BGA, this method is called everytime the system detects a game running with your old
        Database scheme.
        In this case, if you change your Database scheme, you just have to apply the needed changes in order to
        update the game database and allow the game to continue to run with your new version.
    
    */
    
    function upgradeTableDb( $from_version )
    {
        // $from_version is the current version of this game database, in numerical form.
        // For example, if the game was running with a release of your game named "140430-1345",
        // $from_version is equal to 1404301345
        
        // Example:
//        if( $from_version <= 1404301345 )
//        {
//            // ! important ! Use DBPREFIX_<table_name> for all tables
//
//            $sql = "ALTER TABLE DBPREFIX_xxxxxxx ....";
//            self::applyDbUpgradeToAllDB( $sql );
//        }
//        if( $from_version <= 1405061421 )
//        {
//            // ! important ! Use DBPREFIX_<table_name> for all tables
//
//            $sql = "CREATE TABLE DBPREFIX_xxxxxxx ....";
//            self::applyDbUpgradeToAllDB( $sql );
//        }
//        // Please add your future database scheme changes here
//
//


    }    
}
