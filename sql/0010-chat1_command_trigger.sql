CREATE TABLE chat1_command_trigger ( commandTrigger   VARCHAR(191) NOT NULL PRIMARY KEY
                                   , commandID        INT(10)      NOT NULL

                                   , KEY commandID (commandID)
                                   );

ALTER TABLE chat1_command_trigger ADD FOREIGN KEY (commandID) REFERENCES chat1_command (commandID) ON DELETE CASCADE;
