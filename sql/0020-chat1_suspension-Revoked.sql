ALTER TABLE chat1_suspension CHANGE revoked revoked INT(10) DEFAULT NULL;
UPDATE chat1_suspension SET revoked = NULL WHERE revoked = 0;
