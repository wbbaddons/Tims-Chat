CREATE TABLE chat1_session ( roomID INT(10) NOT NULL
                           , userID INT(10) NOT NULL
                           , sessionID BINARY(16) NOT NULL
                           , lastRequest INT(10) NOT NULL

                           , PRIMARY KEY (roomID, userID, sessionID)
                           , KEY (userID, sessionID)
                           , KEY (sessionID)
                           );

ALTER TABLE chat1_session ADD FOREIGN KEY (roomID) REFERENCES chat1_room (roomID) ON DELETE CASCADE;
ALTER TABLE chat1_session ADD FOREIGN KEY (userID) REFERENCES wcf1_user (userID) ON DELETE CASCADE;
