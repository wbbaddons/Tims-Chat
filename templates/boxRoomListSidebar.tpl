<div
data-active-room-id="{if $activeRoomID|isset}{$activeRoomID}{else}0{/if}"
{if $boxID|isset}data-box-id="{$boxID}"
{else}
data-skip-empty-rooms="{if $skipEmptyRooms|isset && $skipEmptyRooms}1{else}0{/if}"
data-is-sidebar="1"
{/if}
>
	{capture assign='chatBoxRoomList'}
		{foreach from=$boxRoomList item='room'}
			{if $room->canSee() && (!$skipEmptyRooms|isset || !$skipEmptyRooms || !$room->getUsers()|empty)}
				<li{if $activeRoomID|isset && $room->roomID === $activeRoomID} class="active"{/if}>
					{if $room->canJoin()}
					<a href="{link controller='Room' application='chat' object=$room}{/link}" class="boxMenuLink">
					{else}
					<span class="boxMenuLink">
					{/if}
						<span class="boxMenuLinkTitle">{$room->getTitle()}</span>
						<span class="badge">{$room->getUsers()|count}</span>
					{if $room->canJoin()}
					</a>
					{else}
					</span>
					{/if}
				</li>
			{/if}
		{/foreach}
	{/capture}
	{if $chatBoxRoomList|trim}
		<ol class="chatBoxRoomList boxMenu" data-hash="{$chatBoxRoomList|trim|sha1}">
			{@$chatBoxRoomList}
		</ol>
	{else}
		<div class="chatBoxRoomList"></div>
	{/if}

	<script>
		;(function (container) {
			document.addEventListener("DOMContentLoaded", function(event) {
				require([ 'Bastelstu.be/Chat/BoxRoomList' ], BoxRoomList => new BoxRoomList(container))
			})
		})(document.currentScript.parentNode);
	</script>
</div>
