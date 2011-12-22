DROP TABLE IF EXISTS wcf1_chat_message;
CREATE TABLE wcf1_chat_message (
  messageID int(10) NOT NULL AUTO_INCREMENT,
  roomID int(10) NOT NULL,
  sender int(10) DEFAULT NULL,
  username varchar(255) DEFAULT NULL,
  receiver int(10) DEFAULT NULL,
  time int(10) NOT NULL,
  type tinyint(3) NOT NULL DEFAULT 1,
  message mediumtext NOT NULL,
  enableSmilies tinyint(1) NOT NULL DEFAULT 1,
  enableHTML tinyint(1) NOT NULL DEFAULT 0,
  color1 int(10) NOT NULL DEFAULT 0,
  color2 int(10) NOT NULL DEFAULT 0,
  PRIMARY KEY (messageID),
  KEY roomID (roomID),
  KEY sender (sender),
  KEY receiver (receiver)
);

DROP TABLE IF EXISTS wcf1_chat_room;
CREATE TABLE wcf1_chat_room (
  roomID int(10) NOT NULL AUTO_INCREMENT,
  title varchar(25) NOT NULL,
  topic varchar(255) NOT NULL,
  position int(10) NOT NULL DEFAULT 0,
  permanent tinyint(1) NOT NULL DEFAULT 1,
  owner int(10) DEFAULT NULL,
  PRIMARY KEY (roomID),
  KEY positionKey (position),
  KEY owner (owner)
);

DROP TABLE IF EXISTS wcf1_chat_room_suspension;
CREATE TABLE wcf1_chat_room_suspension (
  roomID int(10) NOT NULL,
  userID int(10) NOT NULL,
  type tinyint(3) NOT NULL,
  time int(10) NOT NULL,
  PRIMARY KEY (roomID, userID),
  KEY userID (userID),
  KEY type (type, time),
  KEY time (time)
);

ALTER TABLE wcf1_chat_message ADD FOREIGN KEY (receiver) REFERENCES wcf1_user (userID) ON DELETE CASCADE;
ALTER TABLE wcf1_chat_message ADD FOREIGN KEY (roomID) REFERENCES wcf1_chat_room (roomID) ON DELETE CASCADE;
ALTER TABLE wcf1_chat_message ADD FOREIGN KEY (sender) REFERENCES wcf1_user (userID) ON DELETE SET NULL;

ALTER TABLE wcf1_chat_room ADD FOREIGN KEY (owner) REFERENCES wcf1_user (userID) ON DELETE SET NULL;

ALTER TABLE wcf1_chat_room_suspension ADD FOREIGN KEY (userID) REFERENCES wcf1_user (userID) ON DELETE CASCADE;
ALTER TABLE wcf1_chat_room_suspension ADD FOREIGN KEY (roomID) REFERENCES wcf1_chat_room (roomID) ON DELETE CASCADE;