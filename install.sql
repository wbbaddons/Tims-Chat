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
	additionalData	TEXT		DEFAULT NULL,
	
	KEY (roomID),
	KEY (sender),
	KEY (receiver)
);

DROP TABLE IF EXISTS chat1_room;
CREATE TABLE chat1_room (
	roomID		INT(10)		NOT NULL AUTO_INCREMENT PRIMARY KEY,
	title		VARCHAR(255)	NOT NULL,
	topic		VARCHAR(255)	NOT NULL DEFAULT '',
	showOrder	INT(10)		NOT NULL DEFAULT 0,
	permanent	TINYINT(1)	NOT NULL DEFAULT 1,
	owner		INT(10)		DEFAULT NULL,
	
	KEY (showOrder),
	KEY (owner)
);

DROP TABLE IF EXISTS chat1_suspension;
CREATE TABLE chat1_suspension (
	suspensionID	INT(10)		NOT NULL AUTO_INCREMENT PRIMARY KEY,
	userID		INT(10)		NOT NULL,
	roomID		INT(10)		DEFAULT NULL,
	type		TINYINT(3)	NOT NULL,
	expires		INT(10)		NOT NULL,
	time		INT(10)		NOT NULL,
	issuer		INT(10)		DEFAULT NULL,
	reason		VARCHAR(255)	NOT NULL DEFAULT '',
	revoked		TINYINT(1)	NOT NULL DEFAULT 0,
	revoker		INT(10)		DEFAULT NULL,
	
	KEY suspension (userID, roomID, type),
	KEY (roomID),
	KEY (type),
	KEY (expires)
);

ALTER TABLE wcf1_user ADD COLUMN chatRoomID INT(10) DEFAULT NULL;
ALTER TABLE wcf1_user ADD COLUMN chatColor1 INT(10) NOT NULL DEFAULT 0;
ALTER TABLE wcf1_user ADD COLUMN chatColor2 INT(10) NOT NULL DEFAULT 0;
ALTER TABLE wcf1_user ADD COLUMN chatLastActivity INT(10) NOT NULL DEFAULT 0;
ALTER TABLE wcf1_user ADD COLUMN chatAway TEXT DEFAULT NULL;
ALTER TABLE wcf1_user ADD COLUMN chatLastSeen INT(10) NOT NULL DEFAULT 0;

ALTER TABLE chat1_message ADD FOREIGN KEY (receiver) REFERENCES wcf1_user (userID) ON DELETE CASCADE;
ALTER TABLE chat1_message ADD FOREIGN KEY (roomID) REFERENCES chat1_room (roomID) ON DELETE CASCADE;
ALTER TABLE chat1_message ADD FOREIGN KEY (sender) REFERENCES wcf1_user (userID) ON DELETE SET NULL;

ALTER TABLE chat1_room ADD FOREIGN KEY (owner) REFERENCES wcf1_user (userID) ON DELETE SET NULL;

ALTER TABLE chat1_suspension ADD FOREIGN KEY (userID) REFERENCES wcf1_user (userID) ON DELETE CASCADE;
ALTER TABLE chat1_suspension ADD FOREIGN KEY (roomID) REFERENCES chat1_room (roomID) ON DELETE CASCADE;
ALTER TABLE chat1_suspension ADD FOREIGN KEY (issuer) REFERENCES wcf1_user (userID) ON DELETE SET NULL;
ALTER TABLE chat1_suspension ADD FOREIGN KEY (revoker) REFERENCES wcf1_user (userID) ON DELETE SET NULL;

ALTER TABLE wcf1_user ADD FOREIGN KEY (chatRoomID) REFERENCES chat1_room (roomID) ON DELETE SET NULL;

INSERT INTO chat1_room (title, topic, showOrder) VALUES ('chat.room.title1', 'chat.room.topic1', 1);
INSERT INTO chat1_room (title, topic, showOrder) VALUES ('Testroom 2', 'Topic of Testroom 2', 2);
INSERT INTO chat1_room (title, topic, showOrder) VALUES ('Testroom with a very long name', 'The topic of this room is rather loing as well!', 3);
INSERT INTO chat1_room (title, topic, showOrder) VALUES ('Room w/o topic', '', 4);
