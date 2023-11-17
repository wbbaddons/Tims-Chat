FILES = $(shell find files -type f)
WCF_FILES = $(shell find files_wcf -type f)
TS_FILES = $(shell find ts/Bastelstu.be/ -type f |sed 's/ts$$/js/g;s!^ts/!files_wcf/js/!')
JS_MODULE_FILES = $(shell find ts/Bastelstu.be -type f)

all: be.bastelstu.chat.tar be.bastelstu.chat.tar.gz

be.bastelstu.chat.tar.gz: be.bastelstu.chat.tar
	gzip -9 < $< > $@

be.bastelstu.chat.tar: files.tar files_wcf.tar acptemplates.tar templates.tar *.xml LICENSE sql/*.sql language/*.xml
	tar cvf $@ --mtime="@$(shell git log -1 --format=%ct)" --owner=0 --group=0 --numeric-owner --exclude-vcs -- $^

files.tar: $(FILES)
files_wcf.tar: $(WCF_FILES) $(TS_FILES) files_wcf/js/Bastelstu.be.Chat.min.js
acptemplates.tar: acptemplates/*.tpl
templates.tar: templates/*.tpl

%.tar:
	tar cvf $@ --mtime="@$(shell git log -1 --format=%ct)" --owner=0 --group=0 --numeric-owner --exclude-vcs -C $* -- $(^:$*/%=%)

files_wcf/js/Bastelstu.be.Chat.min.js: Bastelstu.be.Chat.babel.js
	npx terser --comments '/Copyright|stackoverflow/' -m -c pure_funcs=[console.debug] --verbose --timings -o $@ $^

Bastelstu.be.Chat.babel.js: Bastelstu.be.Chat.js .babelrc
	npx babel $< --out-file $@

files_wcf/js/%.js: tsconfig.json
files_wcf/js/Bastelstu.be/Chat/LocalStorage.js: ts/Bastelstu.be/Chat/LocalStorage.ts
files_wcf/js/Bastelstu.be/Chat/Parser.js: ts/Bastelstu.be/Chat/Parser.ts
files_wcf/js/Bastelstu.be/Chat/Helper.js: ts/Bastelstu.be/Chat/Helper.ts
files_wcf/js/Bastelstu.be/Chat/User.js: ts/Bastelstu.be/Chat/User.ts
files_wcf/js/Bastelstu.be/Chat/Ui.js: ts/Bastelstu.be/Chat/Ui.ts
files_wcf/js/Bastelstu.be/Chat/DataStructure/Throttle.js: ts/Bastelstu.be/Chat/DataStructure/Throttle.js
files_wcf/js/Bastelstu.be/Chat/DataStructure/LRU.js: ts/Bastelstu.be/Chat/DataStructure/LRU.ts
files_wcf/js/Bastelstu.be/Chat/DataStructure/EventEmitter.js: ts/Bastelstu.be/Chat/DataStructure/EventEmitter.js
files_wcf/js/Bastelstu.be/Chat/DataStructure/RedBlackTree/Node.js: ts/Bastelstu.be/Chat/DataStructure/RedBlackTree/Node.ts
files_wcf/js/Bastelstu.be/Chat/DataStructure/RedBlackTree/Tree.js: ts/Bastelstu.be/Chat/DataStructure/RedBlackTree/Tree.ts
files_wcf/js/Bastelstu.be/Chat/DataStructure/RedBlackTree/Helper.js: ts/Bastelstu.be/Chat/DataStructure/RedBlackTree/Helper.ts
files_wcf/js/Bastelstu.be/Chat/ProfileStore.js: ts/Bastelstu.be/Chat/ProfileStore.ts
files_wcf/js/Bastelstu.be/Chat/Autocompleter.js: ts/Bastelstu.be/Chat/Autocompleter.js
files_wcf/js/Bastelstu.be/Chat/ParseError.js: ts/Bastelstu.be/Chat/ParseError.ts
files_wcf/js/Bastelstu.be/Chat/Messenger.js: ts/Bastelstu.be/Chat/Messenger.js
files_wcf/js/Bastelstu.be/Chat/LocalStorageEmulator.js: ts/Bastelstu.be/Chat/LocalStorageEmulator.ts
files_wcf/js/Bastelstu.be/Chat/Command.js: ts/Bastelstu.be/Chat/Command.js
files_wcf/js/Bastelstu.be/Chat/Message.js: ts/Bastelstu.be/Chat/Message.ts
files_wcf/js/Bastelstu.be/Chat/Log.js: ts/Bastelstu.be/Chat/Log.js
files_wcf/js/Bastelstu.be/Chat/Command/Unmute.js: ts/Bastelstu.be/Chat/Command/Unmute.js
files_wcf/js/Bastelstu.be/Chat/Command/_Unsuspension.js: ts/Bastelstu.be/Chat/Command/_Unsuspension.js
files_wcf/js/Bastelstu.be/Chat/Command/Plain.js: ts/Bastelstu.be/Chat/Command/Plain.js
files_wcf/js/Bastelstu.be/Chat/Command/Whisper.js: ts/Bastelstu.be/Chat/Command/Whisper.js
files_wcf/js/Bastelstu.be/Chat/Command/Info.js: ts/Bastelstu.be/Chat/Command/Info.js
files_wcf/js/Bastelstu.be/Chat/Command/Team.js: ts/Bastelstu.be/Chat/Command/Team.js
files_wcf/js/Bastelstu.be/Chat/Command/Unban.js: ts/Bastelstu.be/Chat/Command/Unban.js
files_wcf/js/Bastelstu.be/Chat/Command/_Suspension.js: ts/Bastelstu.be/Chat/Command/_Suspension.js
files_wcf/js/Bastelstu.be/Chat/Command/Broadcast.js: ts/Bastelstu.be/Chat/Command/Broadcast.js
files_wcf/js/Bastelstu.be/Chat/Command/Where.js: ts/Bastelstu.be/Chat/Command/Where.js
files_wcf/js/Bastelstu.be/Chat/Command/Temproom.js: ts/Bastelstu.be/Chat/Command/Temproom.js
files_wcf/js/Bastelstu.be/Chat/Command/Color.js: ts/Bastelstu.be/Chat/Command/Color.js
files_wcf/js/Bastelstu.be/Chat/Command/Back.js: ts/Bastelstu.be/Chat/Command/Back.js
files_wcf/js/Bastelstu.be/Chat/Command/Me.js: ts/Bastelstu.be/Chat/Command/Me.js
files_wcf/js/Bastelstu.be/Chat/Command/Ban.js: ts/Bastelstu.be/Chat/Command/Ban.js
files_wcf/js/Bastelstu.be/Chat/Command/Mute.js: ts/Bastelstu.be/Chat/Command/Mute.js
files_wcf/js/Bastelstu.be/Chat/Command/Away.js: ts/Bastelstu.be/Chat/Command/Away.js
files_wcf/js/Bastelstu.be/Chat/BoxRoomList.js: ts/Bastelstu.be/Chat/BoxRoomList.ts
files_wcf/js/Bastelstu.be/Chat/Ui/UserActions/MuteAction.js: ts/Bastelstu.be/Chat/Ui/UserActions/MuteAction.js
files_wcf/js/Bastelstu.be/Chat/Ui/UserActions/WhisperAction.js: ts/Bastelstu.be/Chat/Ui/UserActions/WhisperAction.js
files_wcf/js/Bastelstu.be/Chat/Ui/UserActions/BanAction.js: ts/Bastelstu.be/Chat/Ui/UserActions/BanAction.js
files_wcf/js/Bastelstu.be/Chat/Ui/UserActions/Action.js: ts/Bastelstu.be/Chat/Ui/UserActions/Action.js
files_wcf/js/Bastelstu.be/Chat/Ui/MessageStream.js: ts/Bastelstu.be/Chat/Ui/MessageStream.js
files_wcf/js/Bastelstu.be/Chat/Ui/Input.js: ts/Bastelstu.be/Chat/Ui/Input.js
files_wcf/js/Bastelstu.be/Chat/Ui/ConnectionWarning.js: ts/Bastelstu.be/Chat/Ui/ConnectionWarning.js
files_wcf/js/Bastelstu.be/Chat/Ui/Settings.js: ts/Bastelstu.be/Chat/Ui/Settings.js
files_wcf/js/Bastelstu.be/Chat/Ui/ReadMarker.js: ts/Bastelstu.be/Chat/Ui/ReadMarker.js
files_wcf/js/Bastelstu.be/Chat/Ui/MessageActions/Delete.js: ts/Bastelstu.be/Chat/Ui/MessageActions/Delete.js
files_wcf/js/Bastelstu.be/Chat/Ui/Chat.js: ts/Bastelstu.be/Chat/Ui/Chat.js
files_wcf/js/Bastelstu.be/Chat/Ui/UserList.js: ts/Bastelstu.be/Chat/Ui/UserList.ts
files_wcf/js/Bastelstu.be/Chat/Ui/Input/Autocompleter.js: ts/Bastelstu.be/Chat/Ui/Input/Autocompleter.js
files_wcf/js/Bastelstu.be/Chat/Ui/ErrorDialog.js: ts/Bastelstu.be/Chat/Ui/ErrorDialog.ts
files_wcf/js/Bastelstu.be/Chat/Ui/AutoAway.js: ts/Bastelstu.be/Chat/Ui/AutoAway.js
files_wcf/js/Bastelstu.be/Chat/Ui/Log.js: ts/Bastelstu.be/Chat/Ui/Log.js
files_wcf/js/Bastelstu.be/Chat/Ui/Topic.js: ts/Bastelstu.be/Chat/Ui/Topic.ts
files_wcf/js/Bastelstu.be/Chat/Ui/Settings/ToggleButton.js: ts/Bastelstu.be/Chat/Ui/Settings/ToggleButton.js
files_wcf/js/Bastelstu.be/Chat/Ui/Settings/Button.js: ts/Bastelstu.be/Chat/Ui/Settings/Button.ts
files_wcf/js/Bastelstu.be/Chat/Ui/Settings/NotificationsButton.js: ts/Bastelstu.be/Chat/Ui/Settings/NotificationsButton.js
files_wcf/js/Bastelstu.be/Chat/Ui/Settings/FullscreenButton.js: ts/Bastelstu.be/Chat/Ui/Settings/FullscreenButton.js
files_wcf/js/Bastelstu.be/Chat/Ui/Settings/UserListButton.js: ts/Bastelstu.be/Chat/Ui/Settings/UserListButton.js
files_wcf/js/Bastelstu.be/Chat/Ui/Settings/RoomListButton.js: ts/Bastelstu.be/Chat/Ui/Settings/RoomListButton.js
files_wcf/js/Bastelstu.be/Chat/Ui/Settings/SmiliesButton.js: ts/Bastelstu.be/Chat/Ui/Settings/SmiliesButton.js
files_wcf/js/Bastelstu.be/Chat/Ui/Settings/AutoscrollButton.js: ts/Bastelstu.be/Chat/Ui/Settings/AutoscrollButton.js
files_wcf/js/Bastelstu.be/Chat/Ui/UserActionDropdownHandler.js: ts/Bastelstu.be/Chat/Ui/UserActionDropdownHandler.js
files_wcf/js/Bastelstu.be/Chat/Ui/Attachment/Upload.js: ts/Bastelstu.be/Chat/Ui/Attachment/Upload.js
files_wcf/js/Bastelstu.be/Chat/Ui/Notification.js: ts/Bastelstu.be/Chat/Ui/Notification.js
files_wcf/js/Bastelstu.be/Chat/Ui/Mobile.js: ts/Bastelstu.be/Chat/Ui/Mobile.ts
files_wcf/js/Bastelstu.be/Chat/console.js: ts/Bastelstu.be/Chat/console.ts
files_wcf/js/Bastelstu.be/Chat/Template.js: ts/Bastelstu.be/Chat/Template.ts
files_wcf/js/Bastelstu.be/Chat/Room.js: ts/Bastelstu.be/Chat/Room.ts
files_wcf/js/Bastelstu.be/Chat/MessageType.js: ts/Bastelstu.be/Chat/MessageType.ts
files_wcf/js/Bastelstu.be/Chat/CommandHandler.js: ts/Bastelstu.be/Chat/CommandHandler.js
files_wcf/js/Bastelstu.be/Chat/MessageType/Color.js: ts/Bastelstu.be/Chat/MessageType/Color.ts
files_wcf/js/Bastelstu.be/Chat/MessageType/Leave.js: ts/Bastelstu.be/Chat/MessageType/Leave.js
files_wcf/js/Bastelstu.be/Chat/MessageType/TemproomCreated.js: ts/Bastelstu.be/Chat/MessageType/TemproomCreated.js
files_wcf/js/Bastelstu.be/Chat/MessageType/Suspend.js: ts/Bastelstu.be/Chat/MessageType/Suspend.js
files_wcf/js/Bastelstu.be/Chat/MessageType/Where.js: ts/Bastelstu.be/Chat/MessageType/Where.js
files_wcf/js/Bastelstu.be/Chat/MessageType/Join.js: ts/Bastelstu.be/Chat/MessageType/Join.js
files_wcf/js/Bastelstu.be/Chat/MessageType/Me.js: ts/Bastelstu.be/Chat/MessageType/Me.js
files_wcf/js/Bastelstu.be/Chat/MessageType/ChatUpdate.js: ts/Bastelstu.be/Chat/MessageType/ChatUpdate.ts
files_wcf/js/Bastelstu.be/Chat/MessageType/Whisper.js: ts/Bastelstu.be/Chat/MessageType/Whisper.js
files_wcf/js/Bastelstu.be/Chat/MessageType/TemproomInvited.js: ts/Bastelstu.be/Chat/MessageType/TemproomInvited.js
files_wcf/js/Bastelstu.be/Chat/MessageType/Info.js: ts/Bastelstu.be/Chat/MessageType/Info.js
files_wcf/js/Bastelstu.be/Chat/MessageType/Team.js: ts/Bastelstu.be/Chat/MessageType/Team.js
files_wcf/js/Bastelstu.be/Chat/MessageType/Tombstone.js: ts/Bastelstu.be/Chat/MessageType/Tombstone.js
files_wcf/js/Bastelstu.be/Chat/MessageType/Unsuspend.js: ts/Bastelstu.be/Chat/MessageType/Unsuspend.js
files_wcf/js/Bastelstu.be/Chat/MessageType/Broadcast.js: ts/Bastelstu.be/Chat/MessageType/Broadcast.ts
files_wcf/js/Bastelstu.be/Chat/MessageType/Back.js: ts/Bastelstu.be/Chat/MessageType/Back.ts
files_wcf/js/Bastelstu.be/Chat/MessageType/Plain.js: ts/Bastelstu.be/Chat/MessageType/Plain.ts
files_wcf/js/Bastelstu.be/Chat/MessageType/Away.js: ts/Bastelstu.be/Chat/MessageType/Away.ts
files_wcf/js/Bastelstu.be/Chat.js: ts/Bastelstu.be/Chat.js
files_wcf/js/%.js:
	npx tsc

Bastelstu.be.Chat.js: $(TS_FILES) require.build.js package.json package-lock.json
	npx r.js -o require.build.js

clean:
	-rm -f files.tar
	-rm -f files_wcf.tar
	-rm -f templates.tar
	-rm -f acptemplates.tar
	-rm -f Bastelstu.be.Chat.js
	-rm -f Bastelstu.be.Chat.babel.js
	-rm -rf files_wcf/js/

distclean: clean
	-rm -f be.bastelstu.chat.tar
	-rm -f be.bastelstu.chat.tar.gz

constants.php: option.xml
	(echo "<?php" ; xq -r '.data.import.options.option[] | "define(\"" + (.["@name"] | ascii_upcase) + "\", " + .defaultvalue + ");"' < option.xml) > constants.php

.PHONY: distclean clean
