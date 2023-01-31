{OVERALL_GAME_HEADER}

<!-- 
--------
-- BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
-- StickToColours implementation : © joesimpson <Your email address here>
-- 
-- This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
-- See http://en.boardgamearena.com/#!doc/Studio for more information.
-------
-->
<div id="sticktocolours-container">
    
<div id="board">

    <div id="deck_market_wrap">
        <div id="deck_wrap" class="whiteblock">
            <h3>{DECK_LABEL}</h3>
             <span id="deck_size"></span>
            <div id="deckView">
            </div>
        </div>
    
        <div id="market_wrap" class="whiteblock">
            <h3>{MARKET}</h3>
            <div id="market">
            </div>
        </div>
        
        <div id="hand_refused_wrap" class="whiteblock">
            <h3>{HAND_REFUSED}</h3>
            <div id="hand_refused">
            </div>
        </div>
    </div>

    <div id="bidding_wrap" class="whiteblock">
        <h3>{BIDDING}</h3>
        <div id="biddingBestOffer">
            <h5>{BEST_OFFER}<span id="counterBestOffer"></span><span id="bestOfferTokenIcon"></span></h5>
            <h5 id="currentOfferText">{CURRENT_OFFER}<span id="counterCurrentOffer"></span><span id="currentOfferTokenIcon"></span></h5>
            
        </div>
        <div id="biddingCard">
        </div>
    </div>
    
    <div id="myhand_wrap" class="whiteblock">
        <h3>{MY_HAND}</h3>
        <div id="myhand">
        </div>
    </div>
    
</div>

<!-- Jokers assignment : -->
<div id="all_choiceJoker_wrap" class="" style="display:none;">
    <!-- BEGIN choiceJoker -->
    <div id="choiceJoker_{JOKER_ID}" class="choiceJoker whiteblock" style="display:none;">
        <h3 style='color:{JOKER_COLOR}'>
            {JOKER_CHOICE_LABEL}
        </h3>
        <div id="choiceJoker_{JOKER_ID}_cards">
        </div>
    </div>
    <!-- END choiceJoker -->
</div>

<!-- Final Situation : -->
<div id="all_players_hand_wrap" class="" style="display:none;">
    <!-- BEGIN playerHand -->
    <div class="playerhand whiteblock">
        <h3 style='color:#{PLAYER_COLOR}'>
            {PLAYER_NAME}
        </h3>
        <div id="playerhand_{PLAYER_ID}">
        </div>
    </div>
    <!-- END playerHand -->
</div>




<script type="text/javascript">

// Javascript HTML templates

/*
// Example:
var jstpl_some_game_item='<div class="my_game_item" id="my_game_item_${MY_ITEM_ID}"></div>';

*/
var jstpl_player_board_details = '\<div class="player_panel_details" id="player_panel_details_${player_id}">\
    <div id="hand_size_wrapper_${player_id}" class="hand_size_wrapper"><i class="fa fa-hand-paper-o icon_handsize" id="icon_handsize_${player_id}"></i><span id="hand_size_${player_id}" class="hand_size_count">0</span> </div>\
    <div id="dealer_wrapper_${player_id}" class="dealer_wrapper"><i class="fa fa-bullseye icon_dealer" id="icon_dealer_${player_id}"></i> </div>\
    <div id="token_shape_wrapper_${player_id}" class="token_shape_wrapper"><span class="tokenShapeIcon" style="color:#${player_color}" title="${TOKEN_SHAPE_TITLE}"><i class="fa ${player_color_token_shape} fa-stack-1x colorblind_shape_token_background"></i>  </span></div>\
</div>';


//Template of card to put in the div "biddingCard"
var jstpl_biddingcard = '<div class="cardOnBidding" id="biddingCardTpl" style="background-position: -${x}00% -${y}00%"></div>';

//Template of 1 player token Question mark "?"
var jstpl_player_token_question = '\<div class="player_token_zone token_question_${player_id} token_question" id="${token_div_id}" card_id=${card_id}>\
        <span class="fa-stack icon_bid bid_wrapper_${player_id}" style="color:#${player_color}">\
            <i class="fa ${colorblind_icon} fa-stack-1x colorblind_shape_token_background" ></i>\
          <i class="fa fa-circle fa-stack-1x fa-inverse"></i>\
          <i class="fa fa-question-circle fa-stack-1x "></i>\
        </span>\
    </div>';

var jstpl_player_token_refuse = '\<div class="player_token_zone token_refuse_${player_id} token_refuse" id="${token_div_id}" card_id=${card_id}>\
        <span class="fa-stack icon_bid bid_wrapper_${player_id}" style="color:#${player_color}">\
            <i class="fa ${colorblind_icon} fa-stack-1x colorblind_shape_token_background" ></i>\
          <i class="fa fa-circle fa-stack-1x fa-inverse"></i>\
          <i class="fa fa-times-circle fa-stack-1x "></i>\
        </span>\
    </div>';



var jstpl_scoreHelperTemplate = '<div id="scoreHelper"></div>';

</script>  

</div>

{OVERALL_GAME_FOOTER}
