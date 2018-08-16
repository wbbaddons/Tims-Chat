CREATE TABLE chat1_message ( messageID    INT(10)      NOT NULL AUTO_INCREMENT PRIMARY KEY
                           , time         INT(10)      NOT NULL
                           , roomID       INT(10)      NOT NULL
                           , userID       INT(10)      DEFAULT NULL
                           , username     VARCHAR(255) NOT NULL
                           , objectTypeID INT(10)      NOT NULL
                           , payload      MEDIUMBLOB   NOT NULL

                           , KEY (roomID)
                           , KEY (userID)
                           , KEY (time)
                           );

ALTER TABLE chat1_message ADD FOREIGN KEY (userID) REFERENCES wcf1_user (userID) ON DELETE SET NULL;
ALTER TABLE chat1_message ADD FOREIGN KEY (roomID) REFERENCES chat1_room (roomID) ON DELETE CASCADE;
ALTER TABLE chat1_message ADD FOREIGN KEY (objectTypeID) REFERENCES wcf1_object_type (objectTypeID) ON DELETE CASCADE;
