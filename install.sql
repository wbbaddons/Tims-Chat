-- --
-- Database Structure for Tims Chat
-- 
-- @author 	Tim Düsterhus
-- @copyright	2010-2013 Tim Düsterhus
-- @license	Creative Commons Attribution-NonCommercial-ShareAlike <http://creativecommons.org/licenses/by-nc-sa/3.0/legalcode>
-- @package	be.bastelstu.chat
-- --

DROP TABLE IF EXISTS chat1_message;
CREATE TABLE chat1_message (
	messageID	INT(10)		NOT NULL AUTO_INCREMENT PRIMARY KEY,
	roomID		INT(10)		NOT NULL,
	sender		INT(10)		DEFAULT NULL,
	username	VARCHAR(255)	DEFAULT NULL,
	receiver	INT(10)		DEFAULT NULL,
	time		INT(10)		NOT NULL,
	type		TINYINT(3)	NOT NULL DEFAULT 1,
	message		MEDIUMTEXT	NOT NULL,
	enableSmilies	TINYINT(1)	NOT NULL DEFAULT 1,
	enableHTML	TINYINT(1)	NOT NULL DEFAULT 0,
	color1		INT(10)		NOT NULL DEFAULT 0,
	color2		INT(10)		NOT NULL DEFAULT 0,
	
	KEY roomID (roomID),
	KEY sender (sender),
	KEY receiver (receiver)
);

DROP TABLE IF EXISTS chat1_room;
CREATE TABLE chat1_room (
	roomID		INT(10)		NOT NULL AUTO_INCREMENT PRIMARY KEY,
	title		VARCHAR(255)	NOT NULL,
	topic		VARCHAR(255)	NOT NULL,
	position	INT(10)		NOT NULL DEFAULT 0,
	permanent	TINYINT(1)	NOT NULL DEFAULT 1,
	owner		INT(10)		DEFAULT NULL,
	
	KEY positionKey (position),
	KEY owner (owner)
);

DROP TABLE IF EXISTS chat1_suspension;
CREATE TABLE chat1_suspension (
	suspensionID	INT(10)		NOT NULL AUTO_INCREMENT PRIMARY KEY,
	userID		INT(10)		NOT NULL,
	roomID		INT(10)		DEFAULT NULL,
	type		TINYINT(3)	NOT NULL,
	time		INT(10)		NOT NULL,
	
	UNIQUE KEY suspension (userID, roomID, type),
	KEY roomID (roomID),
	KEY type (type),
	KEY time (time)
);

ALTER TABLE chat1_message ADD FOREIGN KEY (receiver) REFERENCES wcf1_user (userID) ON DELETE CASCADE;
ALTER TABLE chat1_message ADD FOREIGN KEY (roomID) REFERENCES chat1_room (roomID) ON DELETE CASCADE;
ALTER TABLE chat1_message ADD FOREIGN KEY (sender) REFERENCES wcf1_user (userID) ON DELETE SET NULL;

ALTER TABLE chat1_room ADD FOREIGN KEY (owner) REFERENCES wcf1_user (userID) ON DELETE SET NULL;

ALTER TABLE chat1_suspension ADD FOREIGN KEY (userID) REFERENCES wcf1_user (userID) ON DELETE CASCADE;
ALTER TABLE chat1_suspension ADD FOREIGN KEY (roomID) REFERENCES chat1_room (roomID) ON DELETE CASCADE;

INSERT INTO chat1_room (title, topic, position) VALUES ('chat.room.title1', 'chat.room.topic1', 1);
INSERT INTO chat1_room (title, topic, position) VALUES ('Testroom 2', 'Topic of Testroom 2', 2);
INSERT INTO chat1_room (title, topic, position) VALUES ('Testroom with a very long', 'The topic of this room is rather loing as well!', 3);
INSERT INTO chat1_room (title, topic, position) VALUES ('Room w/o topic', '', 4);
