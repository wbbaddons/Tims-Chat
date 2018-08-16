CREATE TABLE chat1_room_to_user ( roomID INT(10) NOT NULL
                                , userID INT(10) NOT NULL

                                , PRIMARY KEY (roomID, userID)
                                , KEY (userID)
                                );
