-- Test displaying tokens

UPDATE `card` set card_location='market' WHERE card_id in (1,2,3,19 ,20);

DELETE FROM token;

INSERT INTO `token` (`token_player_id`, `token_card_id`, `token_state`) VALUES (:player1, 19, '1'); 
INSERT INTO `token` (`token_player_id`, `token_card_id`, `token_state`) VALUES (:player2, 19, '1'); 
INSERT INTO `token` (`token_player_id`, `token_card_id`, `token_state`) VALUES (:player3, 19, '0'); 
INSERT INTO `token` (`token_player_id`, `token_card_id`, `token_state`) VALUES (:player4, 19, '0'); 
                     
INSERT INTO `token` (`token_player_id`, `token_card_id`, `token_state`) VALUES (:player1, 20, '0'); 
INSERT INTO `token` (`token_player_id`, `token_card_id`, `token_state`) VALUES (:player2, 20, '0'); 
INSERT INTO `token` (`token_player_id`, `token_card_id`, `token_state`) VALUES (:player3, 20, '1'); 
INSERT INTO `token` (`token_player_id`, `token_card_id`, `token_state`) VALUES (:player4, 20, '1'); 

INSERT INTO `token` (`token_player_id`, `token_card_id`, `token_state`) VALUES (:player3, 1, '0'); 

INSERT INTO `token` (`token_player_id`, `token_card_id`, `token_state`) VALUES (:player2, 2, '1'); 

INSERT INTO `token` (`token_player_id`, `token_card_id`, `token_state`) VALUES (:player1, 3, '1'); 
