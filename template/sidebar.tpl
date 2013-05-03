<div class="tabMenuContainer chatTabMenuContainer containerPadding">
	<nav class="menu chatSidebarMenu">
		<ul>
			<li id="toggleUsers" class="ui-state-active"><a href="{@$__wcf->getAnchor('timsChatUserList')}" title="{lang}chat.general.users{/lang}">{lang}chat.general.users{/lang} <span class="badge">0</span></a></li>
			<li id="toggleRooms"><a href="{@$__wcf->getAnchor('timsChatRoomList')}" title="{lang}chat.general.rooms{/lang}">{lang}chat.general.rooms{/lang} <span class="badge">{#$rooms|count}</span><span class="ajaxLoad icon icon32 icon-spinner"></span></a></li>
		</ul>
	</nav>
	
	<section id="sidebarContent">
		<fieldset>
			<nav id="timsChatUserList">
				<ul>
				</ul>
			</nav>
		</fieldset>
		
		<fieldset>
			<nav id="timsChatRoomList" style="display: none;">
				<div>
					<ul>
					{foreach from=$rooms item='roomListRoom'}
						{if $roomListRoom->canEnter()}
							<li{if $roomListRoom->roomID == $room->roomID} class="active"{/if}>
								<a href="{link application='chat' controller='Chat' object=$roomListRoom}{/link}" class="timsChatRoom">{$roomListRoom}</a>
							</li>
						{/if}
					{/foreach}
					</ul>
					<div><button type="button">{lang}chat.general.forceRefresh{/lang}</button></div>
				</div>
			</nav>
		</fieldset>
	</section>
</div>