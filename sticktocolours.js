/**
 *------
 * BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
 * StickToColours implementation : © joesimpson <Your email address here>
 *
 * This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
 * See http://en.boardgamearena.com/#!doc/Studio for more information.
 * -----
 *
 * sticktocolours.js
 *
 * StickToColours user interface script
 * 
 * In this file, you are describing the logic of your user interface, in Javascript language.
 *
 */

define([
    "dojo","dojo/_base/declare",
    "ebg/core/gamegui",
    "ebg/counter",
    "ebg/stock",
    "ebg/zone",
    "ebg/popindialog"
],
function (dojo, declare) {
    return declare("bgagame.sticktocolours", ebg.core.gamegui, {
        constructor: function(){
            //console.log('sticktocolours constructor');
              
            // Here, you can init the global variables of your user interface
            
            //TODO UPDATE cards size with sprite infos :
            this.cardwidth = 75;
            this.cardheight = 103;
            this.image_items_per_row = 7;
            this.image_items_per_color = 10;// 10 is the number of cards of 1 color 
            this.cardsImage = 'img/cards.jpg';
            this.back_type_id = 35-1;//BACK of cards (the last image in current sprite)
            
            this.tokenZone_width = 32;
            
            this.joker_card_id = null;
            
            //tokens Zones on market
            this.marketZones = {};
            this.handRefusedZones = {};
            this.counterBestOffer={};
            this.counterCurrentOffer={};
            
            //this.biddingZone = new ebg.zone(); 
            
            //For each possible player color, we associate an icon (thanks to fontawesome icons)
            this.colorblind_icon_map = new Map([
                ["ff0000", 'fa-heart'],
                ["008000", 'fa-circle'],
                ["0000ff", 'fa-square'],
                ["ffa500", 'fa-sun-o'],
            ]);
        },
        
        /*
            setup:
            
            This method must set up the game user interface according to current game situation specified
            in parameters.
            
            The method is called each time the game interface is displayed to a player, ie:
            _ when the game starts
            _ when a player refreshes the game page (F5)
            
            "gamedatas" argument contains all datas retrieved by your "getAllDatas" PHP method.
        */
        
        setup: function( gamedatas )
        {
            //console.log( "Starting game setup" );
            
            this.counterHandsSize={};
            // Setting up player boards
            for( var player_id in gamedatas.players )
            {
                var player = gamedatas.players[player_id];
                var player_color ='';         
                var player_color_icon ='';
                var player_board_div = $('player_board_'+player_id);
                if(player != undefined){
                    player_color = player['color'];
                    player_color_icon = this.colorblind_icon_map.get(player_color);
                }
                dojo.place(  
                    this.format_block(   
                        'jstpl_player_board_details',
                        {
                            player_id : player_id,
                            player_color : player_color,
                            player_color_token_shape : player_color_icon,
                            TOKEN_SHAPE_TITLE: _("Token shape"),
                        }
                    ),
                    player_board_div
                );
                this.counterHandsSize[player_id] = new ebg.counter();
                this.counterHandsSize[player_id].create("hand_size_"+player_id);
            }
            // Set up game interface here, according to "gamedatas"
            this.addTooltipToClass( "hand_size_wrapper", _("Number of cards in hand"), '' );
            this.updateHandsSize(gamedatas.handsSize);
            
            this.updateDealer(gamedatas.dealer);
            this.addTooltipToClass( "dealer_wrapper", _("Dealer token"), '' );
            
            this.initMarket(gamedatas.market);
            
            this.displayTokensOnCard( gamedatas.marketTokens);
            
            this.initHandRefusedCards();
            this.updateHandRefusedCards(gamedatas.handRefusedCards);
            
            var biddingCard = gamedatas.biddingCard;
            if(biddingCard != null){
                this.playCardOnBidding(biddingCard.type,biddingCard.type_arg,biddingCard.id);
            }
            this.initTokensOnBiddingCard();
            this.displayTokensOnBiddingCard(gamedatas.biddingCardTokens);
            
            //Update current player offer
            this.counterCurrentOffer = new ebg.counter();
            this.counterCurrentOffer.create("counterCurrentOffer");
            this.initCurrentOffer(gamedatas.currentOffer);
            this.updateCurrentOffer(gamedatas.currentOffer);
            
            //Update best offer counter
            this.counterBestOffer = new ebg.counter();
            this.counterBestOffer.create("counterBestOffer");
            this.initBestOffer(gamedatas.bestOffer);
            this.updateBestOffer(gamedatas.bestOffer);
            
            this.initPlayerHand(gamedatas.hand,gamedatas.jokers);
            
            //TODO JSA display all players Hand during the whole game and not only the end ?
            
            this.initDeck(gamedatas.deckSize);
            
            this.updateActivePlayers(gamedatas.notBiddingPlayers);
            
            // Setup game notifications to handle (see "setupNotifications" method below)
            this.setupNotifications();
            
            if(gamedatas.gamestate.id == 99){//IF USER REFRESH AT THE END
                this.hideGameBoard();
                this.displayPlayerHands(gamedatas.hands,gamedatas.jokers);
                 dojo.query(".player_score_value").style("visibility", "visible");
            }

            this.addActionButton( 'button_scoreHelp', _('Score helper'), 'onScoreHelp', 'player_boards', false, 'blue'); 
            //dojo.query("#button_scoreHelp").removeClass("blinking");//Remove framework class "blinking" -> useless now with false on declaration
            
            //console.log( "Ending game setup" );
        },
       

        ///////////////////////////////////////////////////
        //// Game & client states
        
        // onEnteringState: this method is called each time we are entering into a new game state.
        //                  You can use this method to perform some user interface changes at this moment.
        //
        onEnteringState: function( stateName, args )
        {
            //console.log( 'Entering state: '+stateName );
            
            switch( stateName )
            {
            case 'newRound':
                this.enableAllPlayerPanels();
                dojo.style( 'biddingBestOffer', 'display', 'none' );
                this.updatePossibleCards([],"market");
                this.updatePossibleCards([],"hand");
                break;
            case 'choosingCard':
                this.enableAllPlayerPanels();
                dojo.style( 'biddingBestOffer', 'display', 'none' );
                this.updateCurrentOffer(0);
                this.updatePossibleCards(args.args.possibleCardInMarket,"market");
                
                if(args.args._private !=undefined){
                    //Score for THIS player ONLY
                    this.updatePlayerScore(this.player_id,args.args._private.currentScore);
                }
                break;
                
            case 'playerTurn':
                this.updateActivePlayers(args.args.notBiddingPlayers);
                //Multiple selection on player turn for bids
                this.playerHand.setSelectionMode(2);
                this.updatePossibleCards(args.args.possibleCardInMarket,"market");
                if(args.args._private !=undefined){
                    //Score for THIS player ONLY
                    this.updatePlayerScore(this.player_id,args.args._private.currentScore);
                    if(args.args._private.possibleCardInHand!=undefined){
                        this.updatePossibleCards(args.args._private.possibleCardInHand,"hand");
                    }
                    else {
                        this.updatePossibleCards([],"hand");
                    }
                } 
                
                break;
                
            case 'assignJokers':
                this.enableAllPlayerPanels();
                //simple selection on joker assignment
                this.playerHand.setSelectionMode(1);
                if(args.args._private !=undefined){
                    //Score for THIS player ONLY
                    this.updatePlayerScore(this.player_id,args.args._private.currentScore);
                    if(args.args._private.possibleCardInHand!=undefined){
                        this.updatePossibleCards(args.args._private.possibleCardInHand,"hand");
                    }
                    else {
                        this.updatePossibleCards([],"hand");
                    }
                } 
                this.hideGameBoardActionZone();
                break;
           
            case 'scoring':
                /*KO : at that time, the cards in "hands" are not hacked with new joker value => let's wait the notif after 
                this.hideGameBoard();
                this.displayPlayerHands(args.args.hands,args.args.jokers);
                */
                break;
                
            case 'gameEnd':
                break;
            }
        },

        // onLeavingState: this method is called each time we are leaving a game state.
        //                 You can use this method to perform some user interface changes at this moment.
        //
        onLeavingState: function( stateName )
        {
            //console.log( 'Leaving state: '+stateName );
            
            switch( stateName )
            {
            case 'dummmy':
                break;
            }               
        }, 

        // onUpdateActionButtons: in this method you can manage "action buttons" that are displayed in the
        //                        action status bar (ie: the HTML links in the status bar).
        //        
        onUpdateActionButtons: function( stateName, args )
        {
            //console.log( 'onUpdateActionButtons: '+stateName );
                      
            if( this.isCurrentPlayerActive() )
            {            
                switch( stateName )
                {
 
                 case 'choosingCard':
                    // Add 1 action button in the action status bar:
                    this.addActionButton( 'button_pass_choice', _('Pass'), 'onPassChoice' ); 
                    break;
                 case 'playerTurn':
                    // Add 2 action buttons in the action status bar:
                    this.addActionButton( 'button_bid', _('Bid'), 'onBid' ); 
                    
                    let bestOffer = args.currentBestOffer;
                    if(bestOffer.count == 0 && bestOffer.player_id == this.player_id){
                        //Dealer cannot pass when he just chose a card in the market
                        
                        /*
                        //disable button Pass (if it is visible):
                        dojo.addClass('button_pass_bid', 'disabled');
                        this.addTooltip( 'button_pass_bid', _("You cannot pass your first bid as dealer"), '' );
                        */
                        //+ ADD a cancel button in this case
                        this.addActionButton( 'button_cancel_choice', _('Cancel'), 'onCancelChoice',null,false,'red' ); 
                    }
                    else {
                        this.addActionButton( 'button_pass_bid', _('Pass'), 'onPassBid' ); 
                    }
                    break;
                }
            }
        },        

        ///////////////////////////////////////////////////
        //// Utility methods
        
        /*
        
            Here, you can defines some utility methods that you can use everywhere in your javascript
            script.
        
        */
        initDeck: function(deckSize ) 
        {
            this.counterDeckSize = new ebg.counter();
            this.counterDeckSize.create("deck_size");
            this.counterDeckSize.setValue(deckSize);
            
            this.deckView = new ebg.stock();            
            this.deckView.create( this, $('deckView'), this.cardwidth, this.cardheight );
            this.deckView.image_items_per_row  = this.image_items_per_row;
            this.deckView.extraClasses="cardOnDeck";
            this.deckView.setSelectionMode(0);// NO SELECTION
            this.deckView.addItemType(this.back_type_id, this.back_type_id, g_gamethemeurl + this.cardsImage, this.back_type_id);
            this.deckView.addToStockWithId( this.back_type_id, 0 );
        },
        
        initMarket: function(market ) 
        {
            // new stock object for market
            this.market = new ebg.stock();            
            this.market.create( this, $('market'), this.cardwidth, this.cardheight );
            this.market.image_items_per_row  = this.image_items_per_row;
            this.market.extraClasses="cardOnTable";
            this.market.setSelectionMode(2);
            this.market.setSelectionAppearance("class");//--> .stockitem_selected
            //Define new types of item and add it to the stock :
            for(var color=1; color <=3; color++){
               for(var value=1; value <=10; value++){// 1 -> Joker Wildcard
                    var card_type_id = this.getCardUniqueIdType(color,value);
                    this.market.addItemType(card_type_id, card_type_id, g_gamethemeurl + this.cardsImage, card_type_id);
                } 
            }
            //Add items to the stock :
            for ( var i in market) {
                var card = market[i];
                var color = card.type;
                var value = card.type_arg;                
                this.market.addToStockWithId( this.getCardUniqueIdType( color, value ), card.id );
                this.addToStockShapeColor(this.market,color,card.id);
                
                this.initTokensOnCard(card.id,"market");
                
            }

            dojo.connect(this.market,"onChangeSelection",  this,'onMarketSelectionChanged' );
            
        },
        
        initHandRefusedCards: function( ) 
        {
            this.handRefused = new ebg.stock();            
            this.handRefused.create( this, $('hand_refused'), this.cardwidth, this.cardheight );
            this.handRefused.image_items_per_row  = this.image_items_per_row;
            this.handRefused.extraClasses="cardOnHandRefused";
            this.handRefused.setSelectionMode(0);// NO SELECTION
            this.handRefused.addItemType(this.back_type_id, this.back_type_id, g_gamethemeurl + this.cardsImage, this.back_type_id);
        },
        
        initPlayerHand: function(hand, jokers ) 
        {
             //Player hand
            // new stock object for hand
            this.playerHand = new ebg.stock();            
            this.playerHand.create( this, $('myhand'), this.cardwidth, this.cardheight );
            this.playerHand.image_items_per_row  = this.image_items_per_row;
            this.playerHand.extraClasses="cardOnHand";
            this.playerHand.setSelectionAppearance("class");//--> .stockitem_selected
            this.playerHand.setSelectionMode(1);
            //Define new types of item and add it to the stock :
            for(var color=1; color <=3; color++){
               for(var value=1; value <=10; value++){// 1 -> Joker Wildcard
                    var card_type_id = this.getCardUniqueIdType(color,value);
                    var weight = card_type_id;
                    this.playerHand.addItemType(card_type_id, weight, g_gamethemeurl + this.cardsImage, card_type_id);
                } 
            }
            //Add items to the stock :
            for ( var i in hand) {
                var card = hand[i];
                var color = card.type;
                var value = card.type_arg;                
                if(jokers[card.id] != undefined ){
                    value = jokers[card.id];
                }
                this.playerHand.addToStockWithId( this.getCardUniqueIdType( color, value ), card.id );
                this.addToStockShapeColor(this.playerHand,color,card.id);
            }
            
            dojo.connect(this.playerHand,"onChangeSelection",  this,'onPlayerHandSelectionChanged' );
            
        },
        
        addToStockShapeColor: function(stock,color,id) {
            var divId = stock.container_div.id+"_item_"+id;
            dojo.addClass( divId, 'cardColor-'+color );
        },
        addToBiddingCardShapeColor: function(color) {
            var divId = "biddingCardTpl";
            dojo.addClass( divId, 'cardColor-'+color );
        },
        
        updateActivePlayers: function(notBiddingPlayers){
            this.enableAllPlayerPanels();
            for( var i in notBiddingPlayers){
                var player_id = notBiddingPlayers[i];
                this.disablePlayerPanel(player_id);
            }
        },
        
        updatePossibleCards: function(cards, source){
            var stock = null;
            if(source == "market"){
                stock = this.market;
            } else if(source == "hand"){
                stock = this.playerHand;
            }
            stock.unselectAll();//Unselect in case the player did click before receiving a new state
            var container_div = stock.container_div.id;
            dojo.query( "#"+container_div+' .possibleCard' ).removeClass( 'possibleCard' );
            
            var items = stock.getAllItems();
            
             //By default, no card is possible
            for ( var i in items) {
                var item = items[i];
                var divId = container_div+"_item_"+item.id;
                dojo.addClass( divId, 'notPossibleCard' );
                //this.addTooltip( divId, _("You cannot play this card for now"), '' );
            } 
            
            for ( var i in cards) {
                var cardId = cards[i];
                var divId = container_div+"_item_"+cardId;
                if($(divId) != null) {
                    dojo.addClass( divId, 'possibleCard' );
                    dojo.removeClass( divId, 'notPossibleCard' );
                }
            }
        },
        
        hideGameBoard: function( ) 
        {
            dojo.style( 'board', 'display', 'none' );
        },
        hideGameBoardActionZone: function( ) 
        {
            dojo.style( 'deck_market_wrap', 'display', 'none' );
            dojo.style( 'bidding_wrap', 'display', 'none' );
        },
        
        initTokensOnCard: function(card_id, stock_name){
            // Zone control for this card       	
            this.marketZones[card_id] = this.initTokenZone(stock_name+'_item_'+card_id);
        },
        
        initTokensOnRefusedCard: function(card_id){
            // Zone control for this card       	
            this.handRefusedZones[card_id] = this.initTokenZone('hand_refused'+'_item_'+card_id);
        },
        
        initTokenZone:function( divId){
            if(dojo.query("#"+divId).length==0) return null;
            var zone = new ebg.zone();    
            zone.create( this, divId, this.tokenZone_width, this.tokenZone_width );
            //zone.setPattern( 'ellipticalfit' );
            zone.setPattern( 'custom' );
            zone.autowidth = false;//Don't resize stock card after adding component in zone 
            zone.autoheight = false;//Don't resize stock card after adding component in zone 
            zone.itemIdToCoords = function( i, control_width ) {
                if( i%4==0 )
                {   return {  x:1,y:19, w:this.item_width, h:this.item_height }; }
                else if( i%4==1 )
                {   return {  x:30,y:38, w:this.item_width, h:this.item_height }; }
                else if( i%4==2 )
                {   return {  x:42,y:8, w:this.item_width, h:this.item_height }; }
                else if( i%4==3 )
                {   return {  x:5,y:58, w:this.item_width, h:this.item_height }; }
            };
            
            return zone;
        },
        
        initTokensOnBiddingCard: function(){
            // Zone control for this card       	
            this.biddingZone = this.initTokenZone('biddingCardTpl');
        },
        displayTokensOnBiddingCard: function(tokens){
            for(var i in tokens){
                var token = tokens[i];
                this.displayTokenOnBiddingCard(token);
            }
        },
        displayTokenOnBiddingCard: function(token){
            //console.log( 'displayTokenOnBiddingCard' , token);
            if(this.biddingZone == undefined ) {
                //console.log("displayTokenOnBiddingCard() no zone for biddingZone ");
                return;
            }
            var tokenDivId = this.formatToken(token);
            this.biddingZone.placeInZone(tokenDivId);
            
            this.displayTooltipOnToken(tokenDivId,token);
        },
        
        displayTokenOnHandRefusedCard: function(fake_card_id,player_id){
            var token = { id: fake_card_id, card_id: fake_card_id, player_id: player_id, state: 0 };
            if(this.handRefusedZones[fake_card_id]  == undefined ) {
                return;
            }
            var tokenDivId = this.formatToken(token);
            this.handRefusedZones[fake_card_id].placeInZone(tokenDivId);
            
            this.displayTooltipOnToken(tokenDivId,token);
        },
        displayTokensOnCard: function(tokens){
            //console.log("displayTokensOnCard", tokens);
            for(var i in tokens){
                var token = tokens[i];
                this.displayTokenOnCard(token);
            }
        },
        
        displayTokenOnCard: function(token){
            var card_id = token.card_id;
            if(this.marketZones[card_id] == undefined ) {
                //console.log("displayTokenOnCard() no zone for card_id = "+card_id);
                return;
            }
            
            var tokenDivId = this.formatToken(token, card_id);
            this.marketZones[card_id].placeInZone(tokenDivId);
            
            this.displayTooltipOnToken(tokenDivId,token);
        },
        
        formatToken: function(token, card_id = ''){
            var tokenDivId = "player_token_" + token.id;
            if(token.state == 0 ) {
                //In order to have a different div id in each state
                tokenDivId +="_question";
            }
            else if(token.state == 1 ) {
                //In order to have a different div id in each state
                tokenDivId +="_refuse";
            }
            var tokenTpl = (token.state == 1) ?  'jstpl_player_token_refuse' : 'jstpl_player_token_question';
            var player_color = '';
            var colorblind_icon = '';
            if(this.gamedatas.players[token.player_id] != undefined){
                player_color = this.gamedatas.players[token.player_id]['color'];
                token.player_color = player_color;
                token.player_name = this.gamedatas.players[token.player_id]['name'];
                colorblind_icon = this.colorblind_icon_map.get(player_color);
            }
            var divPlace = 'player_panel_details_'+token.player_id;
            if($(divPlace) == null) return null;
            
            dojo.place(  
                this.format_block(   
                tokenTpl,
                {
                    token_div_id : tokenDivId,
                    player_id : token.player_id,
                    player_color : player_color,//token.player_color,
                    card_id : card_id,
                    colorblind_icon: colorblind_icon,
                }
              ),
              'player_panel_details_'+token.player_id
            );
            return tokenDivId;
        },
        
        displayTooltipOnToken: function(tokenDivId,token){
            var translatedTooltip = dojo.string.substitute( _("*${player_name}* bids with this card"), {
                player_name: token.player_name,
            } );
            if(token.state==1){
                translatedTooltip = dojo.string.substitute( _("*${player_name}* refuses this card, and is not able to choose it nor trade it nor refuse it again until the end of the game"), {
                player_name: token.player_name,
            } );
            }
            this.addTooltip(tokenDivId,bga_format(translatedTooltip, {
                    '*': (t) => '<b style="color:#'+token.player_color+'">' + t + '</b>'
                }
            ),'')
        },
        
        updateHandRefusedCards: function(handRefusedCards){
            this.handRefused.removeAllTo("deckView");
            for (var i in this.handRefusedZones){
                //DELETE OLD ZONES (from old cards)
                //this.handRefusedZones[i].removeAll();
                this.handRefusedZones[i] = null;
            }
            this.handRefusedZones = {};
            
            for( var player_id in handRefusedCards ){
                var nb = handRefusedCards[player_id];
                for(var k=0;k<nb;k++){
                    var fake_card_id = player_id+"_"+ k;
                    this.handRefused.addToStockWithId( this.back_type_id, fake_card_id);
                    this.initTokensOnRefusedCard(fake_card_id);
                    this.displayTokenOnHandRefusedCard(fake_card_id,player_id);
                }
            }
        },
        
        initBestOffer: function(offer){
            var token = { id: "0", player_id: offer.player_id,  state:0};
            var tokenDivId = this.formatToken(token);
            if(tokenDivId!=null){
                this.attachToNewParent(tokenDivId,"bestOfferTokenIcon");
            }
            //Move token from formatted div to targetted span in order to align with text :
            var tokenSpanToKeep = dojo.query("#player_token_0_question>span")[0];
            tokenSpanToKeep.id = "bestOfferTokenIconMoved";
            dojo.place("bestOfferTokenIconMoved","bestOfferTokenIcon","replace"); 
        },
        updateBestOffer: function(offer){
            this.counterBestOffer.toValue(offer.count);
            var player_color = (this.gamedatas.players[offer.player_id] !=undefined) ? this.gamedatas.players[offer.player_id]['color'] : '000000';
            dojo.query("#bestOfferTokenIconMoved").style('color',"#"+player_color);
            
            //update COLORBLIND SHAPE :
            var colorblind_icon = this.colorblind_icon_map.get(player_color);
            var shape = dojo.query("#bestOfferTokenIconMoved>.colorblind_shape_token_background");
            //reset shape by removing all possible icons
            this.colorblind_icon_map.forEach((icon) => {
                shape.removeClass( icon ); // "fa-sun-o" , etc
            });
            shape.addClass(colorblind_icon);
            
            //Update player current offer
            if(this.player_id == offer.player_id) {
                this.updateCurrentOffer(offer.count);
            }
            
            dojo.style( 'biddingBestOffer', 'display', 'block' );
        },
        
        initCurrentOffer: function(offer){
            if (this.isSpectator) { 
                //Spectator has no offer ;) and above all has no player_id in gamedatas
                return;
            }
            var token = { id: "0", player_id: this.player_id,  state:0};
            var tokenDivId = this.formatToken(token);
            if(tokenDivId!=null){
                this.attachToNewParent(tokenDivId,"currentOfferTokenIcon");
            }
            //Move token from formatted div to targetted span in order to align with text :
            var tokenSpanToKeep = dojo.query("#player_token_0_question>span")[0];
            tokenSpanToKeep.id = "currentOfferTokenIconMoved";
            dojo.place("currentOfferTokenIconMoved","currentOfferTokenIcon","replace"); 
        },
        updateCurrentOffer: function(offer){
            this.counterCurrentOffer.toValue(offer);
        },
        //Remove "?" tokens of given player on all market cards
        removeQuestionTokens: function(player_id){
            var token_questions = dojo.query("#market .token_question_"+player_id);
            if(token_questions.length ==0 ) return;
            for (var i in token_questions){
                var token_question = token_questions[i];
                if(token_question.id != undefined){
                    //Delete Element only :
                    //this.fadeOutAndDestroy( token_question.id );
                    
                    //Delete element and update zone :
                    var card_id = dojo.attr( token_question,"card_id");
                    this.marketZones[card_id].removeFromZone( token_question.id, true, null );
                }
            }
        },
        
        blinkToken: function(tokenID){
            let myAnimation = dojo.fx.chain([
                        dojo.fadeOut({ node: tokenID }),
                        dojo.fadeIn({ node: tokenID }),
                        dojo.fadeOut({ node: tokenID,
                            onEnd: function( node ) {
                                dojo.removeClass( node, [ 'stc_visible_node', 'stc_hidden_node' ] );
                                dojo.addClass( node, 'stc_visible_node' );
                            } 
                        }),
                        dojo.fadeIn({ node: tokenID })
                    ]);
                    myAnimation.play();
        },
        
        turnOverQuestionTokens: function(player_id){
            var token_questions = dojo.query("#market .token_question_"+player_id);
            if(token_questions.length ==0 ) return;
            for (var i in token_questions){
                var token_question = token_questions[i];
                if(token_question.id != undefined){
                    var token_id = token_question.id.match("player_token_(\\d*)_question")[1];
                    //Delete Question and update zone :
                    var card_id = dojo.attr( token_question,"card_id");
                    //BLINK token before removing :
                    this.blinkToken("player_token_"+token_id+"_question");
                    
                    this.marketZones[card_id].removeFromZone( token_question.id, true, null );
                    
                    var refuseToken = {id: token_id , card_id: card_id, player_id: player_id, state: 1};
                    this.displayTokenOnCard(refuseToken);
                }
            }
        },
        
        displayPlayerHands: function(hands,jokers ) 
        {
            dojo.style( 'all_players_hand_wrap', 'display', 'block' );
            for(var player_id  in hands){
                var hand = hands[player_id];
                this.displayPlayerHand(player_id,hand,jokers);
            }
        },
        
        displayPlayerHand: function(player_id, hand,jokers ) 
        {
             //Player hand
            // new stock object for hand
            this.otherPlayerHand = new ebg.stock();                 
            this.otherPlayerHand.create( this, $('playerhand_'+player_id), this.cardwidth, this.cardheight );
            this.otherPlayerHand.image_items_per_row  = this.image_items_per_row;
            this.otherPlayerHand.extraClasses="cardOnHand";
            this.otherPlayerHand.setSelectionMode(0);// NO SELECTION
            //Define new types of item and add it to the stock :
            for(var color=1; color <=3; color++){
               for(var value=1; value <=10; value++){// 1 -> Joker Wildcard
                    var card_type_id = this.getCardUniqueIdType(color,value);
                    var weight = card_type_id;
                    this.otherPlayerHand.addItemType(card_type_id, weight, g_gamethemeurl + this.cardsImage, card_type_id);
                } 
            }
            this.otherPlayerHand.onItemCreate = dojo.hitch( this, 'setupPlayerHandNewCard',jokers );
            //Add items to the stock :
            for ( var i in hand) {
                var card = hand[i];
                var color = card.type;
                var value = card.type_arg;
                this.otherPlayerHand.addToStockWithId( this.getCardUniqueIdType( color, value ), card.id );
                
                this.addToStockShapeColor(this.otherPlayerHand,color,card.id);
            }
            
        },
            
        setupPlayerHandNewCard: function(jokers, card_div, card_type_id, card_id )
        {
            var tokens = card_id.split("_");
            var bdId = tokens[tokens.length-1];

            if(this.isJoker(card_type_id) || jokers[bdId] != undefined ){
                this.addTooltip( card_div.id, _("This is the joker card replacement"), '' );
                dojo.addClass( card_div.id, 'stockitem_joker' );
            }
        },
        
        displayAllCardsChoices: function(color, joker_card_id) 
        {
            this.joker_card_id = joker_card_id;
            this.cardsChoices = new ebg.stock();            
            this.cardsChoices.create( this, $('choiceJoker_'+color+'_cards'), this.cardwidth, this.cardheight );
            this.cardsChoices.image_items_per_row  = this.image_items_per_row;
            this.cardsChoices.extraClasses="cardOnChoices";
            this.cardsChoices.setSelectionMode(1);
            //Define new types of item and add it to the stock :
            for(var value=1; value <=9; value++){// 1 -> 9
                var card_type_id = this.getCardUniqueIdType(color,value);
                this.cardsChoices.addItemType(card_type_id, card_type_id, g_gamethemeurl + this.cardsImage, card_type_id);
                this.cardsChoices.addToStock( this.getCardUniqueIdType( color, value ) );
                this.addToStockShapeColor(this.cardsChoices,color,value);
            }
            dojo.connect(this.cardsChoices,"onChangeSelection",  this,'onJokerChoicesSelectionChanged' );
        },
        /* Get card unique identifier based on its color and value
        */
        getCardUniqueIdType: function( color, value )
        {
            //IF we have alls cards of 1 color filling a row:
            //return (color-1) * (this.image_items_per_row) + (value-1);
            return (color-1) * (this.image_items_per_color) + (value-1);
        },
        /**
        opposite operation of previous method getCardUniqueIdType
        */
        getCardColorValueFromIdType: function( type )
        {
            var color = Math.floor(type / (this.image_items_per_color)) + 1;
            var value = type % (this.image_items_per_color) + 1;
            return { "color": color, "value": value };
        },
        
        getCardXYinSprite: function( color, value )
        {
            var type = this.getCardUniqueIdType( color, value );
            var line = Math.floor(type / (this.image_items_per_row));
            var col = type % (this.image_items_per_row);
            return { "x": col, "y": line };
        },

        giveCardToWinner: function(color,value,card_id, winner_id,isAutomatic){
            //console.log("giveCardToWinner",color,value,card_id, winner_id);
            
            var target_placement = "myhand";
            var origin_placement = "biddingCardTpl";
            if(isAutomatic){//IF card is automatically won from market stock
                origin_placement = "market_item_"+card_id;
            }
        
            if(this.player_id == winner_id){
                //IF THIS IS THE WINNER, add the card in hand
                target_placement = "myhand";
                if(isAutomatic){
                    this.playerHand.addToStockWithId( this.getCardUniqueIdType( color, value ), card_id,origin_placement );
                    this.addToStockShapeColor(this.playerHand,color,card_id);
                    
                    this.market.removeFromStockById(card_id);
                    return;//STOP now to avoid ghost two animations
                }
                else {//IF card is THE BIDDING TARGET
                    this.playerHand.addToStockWithId( this.getCardUniqueIdType( color, value ), card_id );
                    this.addToStockShapeColor(this.playerHand,color,card_id);
                }
            }
            else {
                //ELSE MOVE THE CARD To winner panel
                target_placement = 'overall_player_board_'+winner_id;
            }
            
            var anim = this.slideToObject(origin_placement, target_placement,1000);
            dojo.connect(anim, 'onEnd', function(node){
                dojo.destroy(node);
            });
            anim.play(); 
            
            if(isAutomatic){//IF card is automatically won from market stock
                this.market.removeFromStockById(card_id);
            }
        },
        
        playCardOnBidding: function(color, value, card_id){
            var divId = "market_item_"+card_id;
            var marketItem = $(divId);
            var target_placement = "biddingCard"; 
       
            //REMOVE OLD IF EXISTS : (if window is not well refreshed ?)
            dojo.query("#biddingCardTpl").remove();
            dojo.place(  
                this.format_block(   
                    'jstpl_biddingcard',
                    {
                        x : this.getCardXYinSprite(color,value).x,
                        y : this.getCardXYinSprite(color,value).y 
                    }
                ),
                target_placement
            );
            this.placeOnObject("biddingCardTpl", target_placement);
            this.slideToObject("biddingCardTpl", target_placement).play();
            if(marketItem!= undefined){
                //IF the card is displayed in the market, remove it !
                //move items from marketZone to biddingCard :
                this.initTokensOnBiddingCard();
                var items = (this.marketZones[card_id]!=undefined) ? this.marketZones[card_id].getAllItems() : {};
                for(var i in items){
                    this.biddingZone.placeInZone(items[i]);//TODODONE JSA FIX SOME BUG on this line
                }
                //this.slideToObject(divId, target_placement).play();
                this.market.removeFromStockById(card_id);
            }
            
            this.addToBiddingCardShapeColor(color);
        },
        
        /**
        REVERSE ACTION of playCardOnBidding()
        */
        cancelCardOnBidding: function(color, value, card_id,newMarketTokens){
            dojo.query("#biddingCardTpl").remove();
            
            this.market.addToStockWithId( this.getCardUniqueIdType( color, value ), card_id );
            this.addToStockShapeColor(this.market,color,card_id);
            this.initTokensOnCard(card_id,"market");
            this.displayTokensOnCard( newMarketTokens);
        },

        updatePlayerScore: function(player_id, score){
            //console.log("updatePlayerScore",player_id, score);
            //Counter instance for score
            this.scoreCtrl[player_id].setValue(  score  );
            dojo.query("#player_score_"+player_id).style("visibility", "visible");
        },
        
        updateDealer: function(dealerId){
            dojo.query(".dealer_wrapper").style( 'display', 'none' );
            dojo.query("#dealer_wrapper_"+dealerId).style( 'display', 'block' );
            
        },
        
        updateHandsSize: function(handsSize)
        {
            //RESET to 0 in case some player is not in the array
            for ( var i in this.counterHandsSize) {
                this.counterHandsSize[i].toValue(0);
            }
            for ( var player_id in handsSize) {
                var size = handsSize[player_id]; 
                this.counterHandsSize[player_id].toValue(size);
            }
        },
        
        chooseJoker: function(card_id, newValue)
        {
            if( ! this.checkAction( 'chooseJoker' ) )
            {   
                return; 
            }
            
            this.ajaxcallwrapper( "chooseJoker",{ 
                    cardId: card_id, 
                    value: newValue} );
            
            dojo.style( 'all_choiceJoker_wrap', 'display', 'none' );    
            dojo.query(".choiceJoker").style( 'display', 'none' );
            this.playerHand.unselectAll();
            
            //IF response OK (wait notif) display choice above joker in hand (see below)
        },
        replaceJoker: function(player_id,card_id,color,value)
        {
            if(this.player_id == player_id && this.playerHand.getItemById(card_id) !=null){
                this.playerHand.removeFromStockById(card_id);
                this.playerHand.addToStockWithId( this.getCardUniqueIdType( color, value ), card_id );
            }
        },
        
        isJoker: function(type_id)
        {
            var colorAndValue = this.getCardColorValueFromIdType(type_id);
            return colorAndValue.value == 10;
        },            
        ///////////////////////////////////////////////////
        //// Player's action
        
        /*
        
            Here, you are defining methods to handle player's action (ex: results of mouse click on 
            game objects).
            
            Most of the time, these methods:
            _ check the action is possible at this game state.
            _ make a call to the game server
        
        */
        ajaxcallwrapper: function(action, args, handler) {
            if (!args) {
                args = {};
            }
            args.lock = true;

            if (this.checkAction(action)) {
                this.ajaxcall("/" + this.game_name + "/" + this.game_name + "/" + action + ".html", args, this, (result) => { }, handler);
            }
        },

        onMarketSelectionChanged: function( control_name, item_id )
        {
            if(item_id == undefined) return;
            if(control_name != "market") return;
            var selectedCards = this.market.getSelectedItems();
            if(selectedCards.length ==0) return;
            
            // Check that this action is possible (see "possibleactions" in states.inc.php)
            if( this.checkAction( 'chooseMarketCard' , true) )
            {   
                var card = selectedCards[0];
                var divId = control_name+"_item_"+card.id;
                if(dojo.hasClass(divId,'possibleCard' )  ){
                    this.ajaxcallwrapper( "chooseMarketCard",{cardId: card.id} );
                }
                
                this.market.unselectAll();
            }
            else if( this.checkAction( 'bid' , true ) )
            {   
                //DON't do anything special => the "bid" button will get these cards
                
                //Unselect not possible cards :
                var divId = control_name+"_item_"+item_id;
                if(!dojo.hasClass(divId,'possibleCard' )  ){
                    this.market.unselectItem(item_id);
                }
            }
            
        },   
        
        onPassChoice: function()
        {
            // Check that this action is possible (see "possibleactions" in states.inc.php)
            if( ! this.checkAction( 'passChoice' ) )
            {   
                return; 
            }
            
            this.ajaxcallwrapper( "passChoice",{} );
        },
        onBid: function()
        {
            if( ! this.checkAction( 'bid' ) )
            {   
                return; 
            }
            var selectedCards = this.market.getSelectedItems();
            //if(selectedCards.length ==0) return;
            var card_ids = "";
            for(var i in selectedCards){
                card_ids += selectedCards[i].id+";";
            }
            //LOOK for Hand too :
            selectedCards = this.playerHand.getSelectedItems();
            for(var i in selectedCards){
                card_ids += selectedCards[i].id+";";
            }
            
            this.ajaxcallwrapper( "bid",{card_ids: card_ids} );
                
            this.market.unselectAll();
            this.playerHand.unselectAll();
        },
        onPassBid: function()
        {
            // Check that this action is possible (see "possibleactions" in states.inc.php)
            if( ! this.checkAction( 'passBid' ) )
            {   
                return; 
            }
            
            this.ajaxcallwrapper( "passBid",{} );
        },
        
        onCancelChoice: function()
        {
            // Check that this action is possible (see "possibleactions" in states.inc.php)
            if( ! this.checkAction( 'cancelChoice' ) )
            {   
                return; 
            }
            
            this.ajaxcallwrapper( "cancelChoice",{} );
        },
        
        initScoreHelper: function()
        {
            // Create the new dialog over the play zone.
            this.scoreHelperDialog = new ebg.popindialog();
            this.scoreHelperDialog.create( 'scoreHelperDialogId' );
            this.scoreHelperDialog.setTitle( _("Score helper") );
            this.scoreHelperDialog.setMaxWidth( 200 ); 

            // Create the HTML of my dialog. 
            var html = this.format_block( 'jstpl_scoreHelperTemplate', { } );  

            this.scoreHelperDialog.setContent( html ); // Must be set before calling show() so that the size of the content is defined before positioning the dialog
        },
         
        onScoreHelp: function()
        {
            this.initScoreHelper();
            this.scoreHelperDialog.show();
        },
        
        onPlayerHandSelectionChanged: function( control_name, item_id )
        {
                
            if(item_id == undefined) return;
            if(control_name != "myhand") return;
            var selectedCards = this.playerHand.getSelectedItems();
            
            if(selectedCards.length ==0)
            {
                //RESET JOKER CHOICE UI
                dojo.style( 'all_choiceJoker_wrap', 'display', 'none' );
                dojo.query(".choiceJoker").style( 'display', 'none' );
                return;
            }
            
            
            if( this.checkAction( 'bid' , true ) )
            {   
                //console.log( 'onPlayerHandSelectionChanged ' );
                //DON't do anything special => the "bid" button will get these cards
                
            }
            else if(this.checkPossibleActions( 'chooseJoker' )){
                //RESET JOKER CHOICE UI
                dojo.style( 'all_choiceJoker_wrap', 'display', 'none' );
                dojo.query(".choiceJoker").style( 'display', 'none' );
                var card = selectedCards[0];
                var cardId = card.id;
                var typeId = card.type;
                var colorAndValue = this.getCardColorValueFromIdType(typeId);
                var color = colorAndValue.color;
                var value = colorAndValue.value;
                if(value == 10 ){
                    //JOKER
                    dojo.style( 'all_choiceJoker_wrap', 'display', 'block' );
                    dojo.style( 'choiceJoker_'+color, 'display', 'block' );
                    this.displayAllCardsChoices(color,cardId);
                }
                else {
                    this.playerHand.unselectAll();
                }
            }
            else {
                this.playerHand.unselectAll();
            }
        }, 
        
        onJokerChoicesSelectionChanged: function( control_name, item_id )
        {
            if(item_id == undefined) return;
            var selectedCards = this.cardsChoices.getSelectedItems();
            if(selectedCards.length ==0) return;
            
            var card = selectedCards[0];
            var typeId = card.type;
            var colorAndValue = this.getCardColorValueFromIdType(typeId);
            var color = colorAndValue.color;
            var value = colorAndValue.value;
            //console.log( 'onJokerChoicesSelectionChanged',colorAndValue );
            
            this.chooseJoker(this.joker_card_id,value);
            
            this.cardsChoices.unselectAll();
        },
        ///////////////////////////////////////////////////
        //// Reaction to cometD notifications

        /*
            setupNotifications:
            
            In this method, you associate each of your game notifications with your local method to handle it.
            
            Note: game notification names correspond to "notifyAllPlayers" and "notifyPlayer" calls in
                  your sticktocolours.game.php file.
        
        */
        setupNotifications: function()
        {
            //console.log( 'notifications subscriptions setup' );
            
            dojo.subscribe( 'marketCardChosen', this, "notif_marketCardChosen" );
            dojo.subscribe( 'newDeal', this, "notif_newDeal" );
            dojo.subscribe( 'cancelChoice', this, "notif_cancelChoice" );
            dojo.subscribe( 'passChoice', this, "notif_passChoice" );
            dojo.subscribe( 'newTokens', this, "notif_newTokens" );
            dojo.subscribe( 'bidCardFromHand', this, "notif_bidCardFromHand" );
            dojo.subscribe( 'handRefusedCards', this, "notif_handRefusedCards" );
            dojo.subscribe( 'bestOffer', this, "notif_bestOffer" );
            dojo.subscribe( 'passBid', this, "notif_passBid" );
            dojo.subscribe( 'handsSize', this, "notif_handsSize" );
            dojo.subscribe( 'bidWin', this, "notif_bidWin" );
            this.notifqueue.setSynchronous( 'bidWin', 1000 );
            dojo.subscribe( 'drawCard', this, "notif_drawCard" );
            dojo.subscribe( 'jokerChosen', this, "notif_jokerChosen" );
            dojo.subscribe( 'playersHands', this, "notif_playersHands" );
            dojo.subscribe( 'newScores', this, "notif_newScores" );
        },  
        
        // TODO: from this point and below, you can write your game notifications handling methods
        
        notif_bidWin: function( notif )
        {
            this.giveCardToWinner(notif.args.color, notif.args.value, notif.args.card_id, notif.args.player_id, notif.args.isAutomatic);
            this.turnOverQuestionTokens(notif.args.player_id);//Logically, all other players must have received their tokens before...
            this.updateHandRefusedCards([]);
        },  
        notif_handsSize: function( notif )
        {
            this.updateHandsSize(notif.args.handsSize);
        },  
        notif_newTokens: function( notif )
        {
            //console.log( 'notif_newTokens' , notif);
            this.displayTokenOnCard(notif.args.token);
            
        },          
        notif_handRefusedCards: function( notif )
        {
            this.updateHandRefusedCards(notif.args.handRefusedCards);
            //DECREASE hand size :
            this.counterHandsSize[notif.args.player_id].incValue(- notif.args.number );
        },  
        notif_bidCardFromHand: function( notif )
        {
            // Remove card from hand (only the active player is notified)
            this.playerHand.removeFromStockById(notif.args.card_id);
            
            // DON't DO because current player already receives the info from previous notif
            //  //DECREASE hand size :
            //  this.counterHandsSize[this.player_id].incValue(-1);
        },  
        notif_bestOffer: function( notif )
        {
            // display min offer to bid :
            this.updateBestOffer(notif.args.bestOffer);
            
        },  
        notif_cancelChoice: function( notif )
        {
            this.cancelCardOnBidding(notif.args.color,notif.args.value,notif.args.card_id, notif.args.newMarketTokens);
        },   
        notif_passChoice: function( notif )
        {
            // useless because we jump to newRound
            //this.disablePlayerPanel(notif.args.player_id);
        },
        notif_passBid: function( notif )
        {
            this.removeQuestionTokens(notif.args.player_id);
            //deactivate player panel !
            this.disablePlayerPanel(notif.args.player_id);
        },  
        
        notif_drawCard: function( notif )
        {
            this.market.addToStockWithId( this.getCardUniqueIdType( notif.args.color, notif.args.value ), notif.args.card_id,"deckView" );
            this.addToStockShapeColor(this.market,notif.args.color,notif.args.card_id);
            this.initTokensOnCard(notif.args.card_id,"market");
            this.counterDeckSize.setValue(notif.args.deckSize);
        },  
        notif_marketCardChosen: function( notif )
        {
            //console.log( 'notif_marketCardChosen' , notif);
            // this.initTokensOnBiddingCard(); //TODO JSA FIXME Unknown BUG   with zone.removeAllItems() 
            this.playCardOnBidding(notif.args.color, notif.args.value, notif.args.card_id);
            /* 
            this.displayTokensOnBiddingCard(notif.args.biddingCardTokens);*/
        },   
        
        notif_newDeal: function( notif )
        {
            this.updateDealer(notif.args.player_id);
        },
        
        notif_jokerChosen: function( notif )
        {
            this.replaceJoker(notif.args.player_id,notif.args.card_id,notif.args.color,notif.args.value);
        },

        // Update players' scores
        notif_newScores: function( notif )
        {
            //console.log( 'notif_newScores',notif );     
                    
            for(var player_id  in notif.args.newScores){
                this.updatePlayerScore(player_id,notif.args.newScores[player_id] );
            }
        },  
        
        // Display players' hands
        notif_playersHands: function( notif )
        {
            //console.log( 'notif_playersHands',notif );   
            this.hideGameBoard();
            this.displayPlayerHands(notif.args.hands,notif.args.jokers);
        },  
   });             
});
//# sourceURL=sticktocolours.js