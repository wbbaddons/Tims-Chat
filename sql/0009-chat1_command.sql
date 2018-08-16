CREATE TABLE chat1_command ( commandID   INT(10)      NOT NULL AUTO_INCREMENT PRIMARY KEY
                           , packageID   INT(10)      NOT NULL
                           , identifier  VARCHAR(191) NOT NULL
                           , className   VARCHAR(191) NOT NULL

                           , UNIQUE KEY command (packageID, identifier)
                           );

ALTER TABLE chat1_command ADD FOREIGN KEY (packageID) REFERENCES wcf1_package (packageID) ON DELETE CASCADE;
