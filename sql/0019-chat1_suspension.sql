CREATE TABLE chat1_suspension ( suspensionID    INT(10)      NOT NULL AUTO_INCREMENT PRIMARY KEY
                              , time            INT(10)      NOT NULL
                              , expires         INT(10)      NULL
                              , roomID          INT(10)      NULL
                              , userID          INT(10)      NOT NULL
                              , objectTypeID    INT(10)      NOT NULL
                              , reason          VARCHAR(255) NOT NULL
                              , judgeID         INT(10)      NULL
                              , judge           VARCHAR(100) NOT NULL
                              , revoked         TINYINT(1)   NOT NULL DEFAULT 0
                              , revokerID       INT(10)      DEFAULT NULL
                              , revoker         VARCHAR(100) DEFAULT NULL

                              , KEY (roomID, userID, objectTypeID)
                              , KEY (userID)
                              , KEY (objectTypeID, roomID)
                              , KEY (time)
                              , KEY (judgeID)
                              );

ALTER TABLE chat1_suspension ADD FOREIGN KEY (roomID) REFERENCES chat1_room (roomID) ON DELETE CASCADE;
ALTER TABLE chat1_suspension ADD FOREIGN KEY (userID) REFERENCES wcf1_user (userID) ON DELETE CASCADE;
ALTER TABLE chat1_suspension ADD FOREIGN KEY (judgeID) REFERENCES wcf1_user (userID) ON DELETE SET NULL;
ALTER TABLE chat1_suspension ADD FOREIGN KEY (revokerID) REFERENCES wcf1_user (userID) ON DELETE SET NULL;
ALTER TABLE chat1_suspension ADD FOREIGN KEY (objectTypeID) REFERENCES wcf1_object_type (objectTypeID) ON DELETE CASCADE;
