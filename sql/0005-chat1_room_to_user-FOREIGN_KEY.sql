ALTER TABLE chat1_room_to_user ADD FOREIGN KEY (roomID) REFERENCES chat1_room (roomID) ON DELETE CASCADE;
ALTER TABLE chat1_room_to_user ADD FOREIGN KEY (userID) REFERENCES wcf1_user (userID) ON DELETE CASCADE;
