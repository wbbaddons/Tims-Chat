CREATE TABLE chat1_room_temporary_invite ( roomID INT(10) NOT NULL
                                         , userID INT(10) NOT NULL
                                         , PRIMARY KEY (roomID, userID)
                                         , KEY (userID)
                                         );

ALTER TABLE chat1_room_temporary_invite ADD FOREIGN KEY (roomID) REFERENCES chat1_room (roomID) ON DELETE CASCADE;
ALTER TABLE chat1_room_temporary_invite ADD FOREIGN KEY (userID) REFERENCES wcf1_user (userID) ON DELETE CASCADE;
