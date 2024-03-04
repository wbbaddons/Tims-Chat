<nav id="chatQuickSettingsNavigation">
	<ul class="buttonGroup jsOnly">
		{if MODULE_SMILEY && !$smileyCategories|empty}<li><a class="button" href="#" data-module="Bastelstu.be/Chat/Ui/Settings/SmiliesButton">{icon name='face-smile'} <span>{lang}wcf.message.smilies{/lang}</span></a></li>{/if}
		<li><a class="button" href="#" data-module="Bastelstu.be/Chat/Ui/Settings/FullscreenButton">{icon name='maximize'} <span>{lang}chat.room.button.fullscreen{/lang}</span></a></li>
		<li><a class="button" href="#" data-module="Bastelstu.be/Chat/Ui/Settings/NotificationsButton">{icon name='bell'} <span>{lang}chat.room.button.notifications{/lang}</span></a></li>
		<li><a class="button" href="#" data-module="Bastelstu.be/Chat/Ui/Settings/AutoscrollButton">{icon name='arrow-down'} <span>{lang}chat.room.button.autoscroll{/lang}</span></a></li>
		<li hidden><a class="button" href="#" data-module="Bastelstu.be/Chat/Ui/Settings/UserListButton">{icon name='users'} <span>{lang}chat.room.button.userList{/lang}</span></a></li>
		<li hidden><a class="button" href="#" data-module="Bastelstu.be/Chat/Ui/Settings/RoomListButton">{icon name='comments' type='solid'} <span>{lang}chat.room.button.roomList{/lang}</span></a></li>
		{event name='buttons'}
	</ul>
</nav>
