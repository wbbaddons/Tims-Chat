<div id="sidebarContent" class="sidebarContent">
	<nav class="timsChatSidebarTabs">
		<ul>
			<li id="toggleUsers" class="active"><a title="{lang}chat.general.users{/lang}">{lang}chat.general.users{/lang} <span class="badge">0</span></a></li>
			<li id="toggleRooms"><a title="{lang}chat.general.rooms{/lang}" data-refresh-url="{link application='chat' controller='RoomList'}{/link}">{lang}chat.general.rooms{/lang} <span class="badge">{#$rooms|count}</span><span class="ajaxLoad icon icon32 icon-spinner"></span></a></li>
		</ul>
	</nav>
	
	<div id="sidebarContainer">
		<ul id="timsChatUserList">
		</ul>
		<nav id="timsChatRoomList" class="sidebarMenu" style="display: none;">
			<div>
				<ul>
				{foreach from=$rooms item='roomListRoom'}
					{if $roomListRoom->canEnter()}
						<li{if $roomListRoom->roomID == $room->roomID} class="activeMenuItem"{/if}>
							<a href="{link application='chat' controller='Chat' object=$roomListRoom}{/link}" class="timsChatRoom">{$roomListRoom}</a>
						</li>
					{/if}
				{/foreach}
				</ul>
				<div><button type="button">{lang}chat.general.forceRefresh{/lang}</button></div>
			</div>
		</nav>
	</div>
</div>