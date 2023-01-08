-- TEST SCORING 2 : all jokers on discard pile


-- REMEMBER 2 copies of same small cards !  use LIMIT 1 to update the right one 
-- EXAMPLE :
-- SELECT * FROM `card` WHERE card_type = 1 AND card_type_arg =2  ; -- 2 lines
-- UPDATE `card` set card_location='hand', card_location_arg = 2373995 WHERE card_type = 1 AND card_type_arg =2 order by card_id asc LIMIT 1 ;
-- UPDATE `card` set card_location='hand', card_location_arg = 2373994 WHERE card_type = 1 AND card_type_arg =2 order by card_id DESC LIMIT 1 ;

-- RESET hands
UPDATE `card` set card_location='deck' WHERE card_location='hand';

-- PLAYER 1 IS 2373992,  PLAYER 2 IS 2373993, PLAYER 3 IS 2373994, PLAYER 4 IS 2373995,
UPDATE `card` set card_location='hand', card_location_arg = :player1 WHERE card_type = 1 AND card_type_arg =2 order by card_id asc LIMIT 1 ;
UPDATE `card` set card_location='hand', card_location_arg = :player1 WHERE card_type = 1 AND card_type_arg =3 order by card_id asc LIMIT 1 ;
UPDATE `card` set card_location='hand', card_location_arg = :player1 WHERE card_type = 1 AND card_type_arg =4 order by card_id asc LIMIT 1 ;
UPDATE `card` set card_location='hand', card_location_arg = :player1 WHERE card_type = 1 AND card_type_arg =5 order by card_id asc LIMIT 1 ;
UPDATE `card` set card_location='hand', card_location_arg = :player1 WHERE card_type = 1 AND card_type_arg =6 order by card_id asc LIMIT 1 ;
UPDATE `card` set card_location='hand', card_location_arg = :player1 WHERE card_type = 1 AND card_type_arg =7 order by card_id asc LIMIT 1 ;
UPDATE `card` set card_location='hand', card_location_arg = :player1 WHERE card_type = 1 AND card_type_arg =8 order by card_id asc LIMIT 1 ;
UPDATE `card` set card_location='hand', card_location_arg = :player1 WHERE card_type = 2 AND card_type_arg =1 order by card_id asc LIMIT 1 ;
UPDATE `card` set card_location='hand', card_location_arg = :player1 WHERE card_type = 2 AND card_type_arg =1 order by card_id DESC LIMIT 1 ;
UPDATE `card` set card_location='hand', card_location_arg = :player1 WHERE card_type = 2 AND card_type_arg =2 order by card_id asc LIMIT 1 ;
UPDATE `card` set card_location='hand', card_location_arg = :player1 WHERE card_type = 2 AND card_type_arg =2 order by card_id DESC LIMIT 1 ;
UPDATE `card` set card_location='hand', card_location_arg = :player1 WHERE card_type = 2 AND card_type_arg =3 order by card_id DESC LIMIT 1 ;
UPDATE `card` set card_location='hand', card_location_arg = :player1 WHERE card_type = 2 AND card_type_arg =4 order by card_id DESC LIMIT 1 ;
UPDATE `card` set card_location='hand', card_location_arg = :player1 WHERE card_type = 2 AND card_type_arg =6 order by card_id DESC LIMIT 1 ;
UPDATE `card` set card_location='hand', card_location_arg = :player1 WHERE card_type = 2 AND card_type_arg =7 order by card_id DESC LIMIT 1 ;
UPDATE `card` set card_location='hand', card_location_arg = :player1 WHERE card_type = 2 AND card_type_arg =8 order by card_id DESC LIMIT 1 ;
UPDATE `card` set card_location='hand', card_location_arg = :player1 WHERE card_type = 2 AND card_type_arg =9 order by card_id DESC LIMIT 1 ;
UPDATE `card` set card_location='hand', card_location_arg = :player1 WHERE card_type = 3 AND card_type_arg =1 order by card_id asc LIMIT 1 ;
UPDATE `card` set card_location='hand', card_location_arg = :player1 WHERE card_type = 3 AND card_type_arg =1 order by card_id DESC LIMIT 1 ;
UPDATE `card` set card_location='hand', card_location_arg = :player1 WHERE card_type = 3 AND card_type_arg =8 order by card_id DESC LIMIT 1 ;

UPDATE `card` set card_location='hand', card_location_arg = :player2 WHERE card_type = 1 AND card_type_arg =3 order by card_id DESC LIMIT 1 ;
UPDATE `card` set card_location='hand', card_location_arg = :player2 WHERE card_type = 1 AND card_type_arg =4 order by card_id DESC LIMIT 1 ;
UPDATE `card` set card_location='hand', card_location_arg = :player2 WHERE card_type = 1 AND card_type_arg =5 order by card_id DESC LIMIT 1 ;
UPDATE `card` set card_location='hand', card_location_arg = :player2 WHERE card_type = 1 AND card_type_arg =7 order by card_id DESC LIMIT 1 ;
UPDATE `card` set card_location='hand', card_location_arg = :player2 WHERE card_type = 1 AND card_type_arg =8 order by card_id DESC LIMIT 1 ;
UPDATE `card` set card_location='hand', card_location_arg = :player2 WHERE card_type = 1 AND card_type_arg =9 order by card_id DESC LIMIT 1 ;
UPDATE `card` set card_location='hand', card_location_arg = :player2 WHERE card_type = 2 AND card_type_arg =5 order by card_id asc LIMIT 1 ;
UPDATE `card` set card_location='hand', card_location_arg = :player2 WHERE card_type = 2 AND card_type_arg =5 order by card_id DESC LIMIT 1 ;
UPDATE `card` set card_location='hand', card_location_arg = :player2 WHERE card_type = 3 AND card_type_arg =5 order by card_id DESC LIMIT 1 ;

UPDATE `card` set card_location='hand', card_location_arg = :player3 WHERE card_type = 1 AND card_type_arg =9 order by card_id ASC LIMIT 1 ;
UPDATE `card` set card_location='hand', card_location_arg = :player3 WHERE card_type = 2 AND card_type_arg =9 order by card_id asc LIMIT 1 ;
UPDATE `card` set card_location='hand', card_location_arg = :player3 WHERE card_type = 3 AND card_type_arg =2 order by card_id asc LIMIT 1 ;
UPDATE `card` set card_location='hand', card_location_arg = :player3 WHERE card_type = 3 AND card_type_arg =3 order by card_id asc LIMIT 1 ;
UPDATE `card` set card_location='hand', card_location_arg = :player3 WHERE card_type = 3 AND card_type_arg =4 order by card_id asc LIMIT 1 ;
UPDATE `card` set card_location='hand', card_location_arg = :player3 WHERE card_type = 3 AND card_type_arg =5 order by card_id asc LIMIT 1 ;
UPDATE `card` set card_location='hand', card_location_arg = :player3 WHERE card_type = 3 AND card_type_arg =6 order by card_id asc LIMIT 1 ;
UPDATE `card` set card_location='hand', card_location_arg = :player3 WHERE card_type = 3 AND card_type_arg =7 order by card_id asc LIMIT 1 ;
UPDATE `card` set card_location='hand', card_location_arg = :player3 WHERE card_type = 3 AND card_type_arg =8 order by card_id asc LIMIT 1 ;
UPDATE `card` set card_location='hand', card_location_arg = :player3 WHERE card_type = 3 AND card_type_arg =9 order by card_id asc LIMIT 1 ;

UPDATE `card` set card_location='hand', card_location_arg = :player4 WHERE card_type = 1 AND card_type_arg =1 order by card_id DESC LIMIT 1 ;
UPDATE `card` set card_location='hand', card_location_arg = :player4 WHERE card_type = 1 AND card_type_arg =2 order by card_id DESC LIMIT 1 ;
UPDATE `card` set card_location='hand', card_location_arg = :player4 WHERE card_type = 1 AND card_type_arg =6 order by card_id DESC LIMIT 1 ;
UPDATE `card` set card_location='hand', card_location_arg = :player4 WHERE card_type = 2 AND card_type_arg =2 order by card_id DESC LIMIT 1 ;
UPDATE `card` set card_location='hand', card_location_arg = :player4 WHERE card_type = 2 AND card_type_arg =3 order by card_id asc LIMIT 1 ;
UPDATE `card` set card_location='hand', card_location_arg = :player4 WHERE card_type = 2 AND card_type_arg =4 order by card_id asc LIMIT 1 ;
UPDATE `card` set card_location='hand', card_location_arg = :player4 WHERE card_type = 2 AND card_type_arg =6 order by card_id asc LIMIT 1 ;
UPDATE `card` set card_location='hand', card_location_arg = :player4 WHERE card_type = 2 AND card_type_arg =7 order by card_id asc LIMIT 1 ;
UPDATE `card` set card_location='hand', card_location_arg = :player4 WHERE card_type = 2 AND card_type_arg =8 order by card_id asc LIMIT 1 ;
UPDATE `card` set card_location='hand', card_location_arg = :player4 WHERE card_type = 3 AND card_type_arg =2 order by card_id DESC LIMIT 1 ;
UPDATE `card` set card_location='hand', card_location_arg = :player4 WHERE card_type = 3 AND card_type_arg =3 order by card_id DESC LIMIT 1 ;
UPDATE `card` set card_location='hand', card_location_arg = :player4 WHERE card_type = 3 AND card_type_arg =4 order by card_id DESC LIMIT 1 ;
UPDATE `card` set card_location='hand', card_location_arg = :player4 WHERE card_type = 3 AND card_type_arg =6 order by card_id DESC LIMIT 1 ;

-- remaining cards are on the market
UPDATE `card` set card_location='market' WHERE card_location='deck';

SELECT * FROM `card`;


-- give JOKERs to test scoring jokers :
UPDATE `card` set card_location='discard' WHERE card_type = 1 AND card_type_arg =10 ;
UPDATE `card` set card_location='discard' WHERE card_type = 2 AND card_type_arg =10 ;
UPDATE `card` set card_location='discard' WHERE card_type = 3 AND card_type_arg =10 ;

