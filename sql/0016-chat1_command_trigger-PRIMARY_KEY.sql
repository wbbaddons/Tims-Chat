ALTER TABLE chat1_command_trigger DROP PRIMARY KEY;
ALTER TABLE chat1_command_trigger ADD COLUMN triggerID INT(10) NOT NULL AUTO_INCREMENT PRIMARY KEY;
ALTER TABLE chat1_command_trigger ADD UNIQUE KEY (commandTrigger);
