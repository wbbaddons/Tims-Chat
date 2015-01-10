ALTER TABLE wcf1_user CHANGE chatColor1 chatColor1 INT(10) DEFAULT NULL;
ALTER TABLE wcf1_user CHANGE chatColor2 chatColor2 INT(10) DEFAULT NULL;

ALTER TABLE chat1_room ADD COLUMN maxUsers INT(10) NOT NULL DEFAULT 0;

ALTER TABLE chat1_message CHANGE color1 color1 INT(10) DEFAULT NULL;
ALTER TABLE chat1_message CHANGE color2 color2 INT(10) DEFAULT NULL;

UPDATE wcf1_user SET chatColor1 = NULL, chatColor2 = NULL WHERE chatColor1 = 0 AND chatColor2 = 0;
