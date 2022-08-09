<nav id="chatQuickSettingsNavigation">
	<ul class="buttonGroup jsOnly">
		{if MODULE_SMILEY && !$smileyCategories|empty}<li><a class="button" href="#" data-module="Bastelstu.be/Chat/Ui/Settings/SmiliesButton"><span class="icon icon16 fa-smile-o"></span> <span>{lang}wcf.message.smilies{/lang}</span></a></li>{/if}
		<li><a class="button" href="#" data-module="Bastelstu.be/Chat/Ui/Settings/FullscreenButton"><span class="icon icon16 fa-arrows-alt"></span> <span>{lang}chat.room.button.fullscreen{/lang}</span></a></li>
		<li><a class="button" href="#" data-module="Bastelstu.be/Chat/Ui/Settings/NotificationsButton"><span class="icon icon16 fa-bell-o"></span> <span>{lang}chat.room.button.notifications{/lang}</span></a></li>
		<li><a class="button" href="#" data-module="Bastelstu.be/Chat/Ui/Settings/AutoscrollButton"><span class="icon icon16 fa-arrow-down"></span> <span>{lang}chat.room.button.autoscroll{/lang}</span></a></li>
		<li hidden><a class="button" href="#" data-module="Bastelstu.be/Chat/Ui/Settings/UserListButton"><span class="icon icon16 fa-users"></span> <span>{lang}chat.room.button.userList{/lang}</span></a></li>
		<li hidden><a class="button" href="#" data-module="Bastelstu.be/Chat/Ui/Settings/RoomListButton"><span class="icon icon16 fa-comments"></span> <span>{lang}chat.room.button.roomList{/lang}</span></a></li>
		{event name='buttons'}
	</ul>
</nav>
