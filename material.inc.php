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
 * material.inc.php
 *
 * StickToColours game material description
 *
 * Here, you can describe the material of your game with PHP variables.
 *   
 * This file is loaded in your game logic class constructor, ie these variables
 * are available everywhere in your game logic code.
 *
 */

$this->card_types  = array(
    1 => array( 'name' => clienttranslate('red'),
                'nametr' => self::_('red') ),    
    2 => array( 'name' => clienttranslate('green'),
                'nametr' => self::_('green') ),
    3 => array( 'name' => clienttranslate('blue'),
                'nametr' => self::_('blue') ),
);

$this->values_labels = array(
    1 => '1',
    2 => '2',
    3 => '3',
    4 => '4',
    5 => '5',
    6 => '6',
    7 => '7',
    8 => '8',
    9 => '9',
    10 => clienttranslate('Joker'),
);

/* USELESS
$this->token_states  = array(
    0 => array( 'name' => clienttranslate('question'),
                'nametr' => self::_('question') ),    
    1 => array( 'name' => clienttranslate('refuse'),
                'nametr' => self::_('refuse') ),
);
*/
