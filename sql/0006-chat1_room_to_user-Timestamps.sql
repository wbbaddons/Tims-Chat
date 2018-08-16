ALTER TABLE chat1_room_to_user ADD lastFetch INT(10) NOT NULL DEFAULT 0;
ALTER TABLE chat1_room_to_user ADD lastPush INT(10) NOT NULL DEFAULT 0;
ALTER TABLE chat1_room_to_user ADD active TINYINT(1) NOT NULL DEFAULT 0;
ALTER TABLE chat1_room_to_user ADD KEY (roomID, active);
ALTER TABLE chat1_room_to_user ADD KEY (active);
