ALTER TABLE chat1_room ADD isTemporary TINYINT(1) NOT NULL DEFAULT 0;
ALTER TABLE chat1_room ADD ownerID INT(10) DEFAULT NULL;

ALTER TABLE chat1_room ADD FOREIGN KEY (ownerID) REFERENCES wcf1_user (userID) ON DELETE SET NULL;