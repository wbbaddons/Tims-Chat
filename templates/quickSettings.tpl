<nav id="chatQuickSettingsNavigation">
	<ul class="buttonGroup jsOnly">
		{if MODULE_SMILEY && !$smileyCategories|empty}<li><a class="button" href="#" data-module="Bastelstu.be/Chat/Ui/Settings/SmiliesButton"><span class="icon icon16 fa-smile-o"></span> <span>{lang}wcf.message.smilies{/lang}</span></a></li>{/if}
		<li><a class="button" href="#" data-module="Bastelstu.be/Chat/Ui/Settings/FullscreenButton"><span class="icon icon16 fa-arrows-alt"></span> <span>{lang}chat.room.button.fullscreen{/lang}</span></a></li>
		<li><a class="button" href="#" data-module="Bastelstu.be/Chat/Ui/Settings/NotificationsButton"><span class="icon icon16 fa-bell-o"></span> <span>{lang}chat.room.button.notifications{/lang}</span></a></li>
		<li><a class="button" href="#" data-module="Bastelstu.be/Chat/Ui/Settings/AutoscrollButton"><span class="icon icon16 fa-arrow-down"></span> <span>{lang}chat.room.button.autoscroll{/lang}</span></a></li>
		{if $__wcf->getSession()->getPermission('user.chat.canAttach')}
			<li><a id="chatAttachmentUploadButton" class="button" href="#"><span class="icon icon16 fa-paperclip"></span> <span>{lang}wcf.attachment.attachments{/lang}</span></a>
		{/if}
		{event name='buttons'}
	</ul>
</nav>
