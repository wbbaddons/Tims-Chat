{include file='header' pageTitle='chat.acp.room.list'}
	
<script type="text/javascript">
	//<![CDATA[
	$(function() {
		new WCF.Action.Delete('\\chat\\data\\room\\RoomAction', $('.chatRoomRow'));
		new WCF.Sortable.List('roomList', '\\chat\\data\\room\\RoomAction');
	});
	//]]>
</script>
	
<header class="boxHeadline">
	<h1>{lang}chat.acp.room.list{/lang}</h1>
</header>

<div class="contentNavigation">
	{hascontent}
		<nav>
			<ul>
				{content}
					{if $__wcf->session->getPermission('admin.chat.canAddRoom')}
						<li><a href="{link application='chat' controller='RoomAdd'}{/link}" class="button"><span class="icon icon16 icon-plus"></span> <span>{lang}chat.acp.room.add{/lang}</span></a></li>
					{/if}
					{event name='contentNavigationButtonsTop'}
				{/content}
			</ul>
		</nav>
	{/hascontent}
</div>
{if $objects|count}
	<section id="roomList" class="container containerPadding sortableListContainer marginTop">
		<ol class="sortableList" data-object-id="0">
			{foreach from=$objects item=chatRoom}
				<li class="sortableNode sortableNoNesting chatRoomRow" data-object-id="{@$chatRoom->roomID}">
					<span class="sortableNodeLabel">
						{if $__wcf->session->getPermission('admin.chat.canEditRoom')}
							<a href="{link  application='chat' controller='RoomEdit' id=$chatRoom->roomID}{/link}">{$chatRoom->title|language}</a>
						{else}
							{$chatRoom->title|language}
						{/if}
						
						<span class="statusDisplay sortableButtonContainer">
							{if $__wcf->session->getPermission('admin.chat.canEditRoom')}
								<a href="{link application='chat' controller='RoomEdit' id=$chatRoom->roomID}{/link}"><span title="{lang}wcf.global.button.edit{/lang}" class="jsTooltip icon icon16 icon-pencil" /></a>
							{/if}
							{if $__wcf->session->getPermission('admin.chat.canDeleteRoom')}
								<span title="{lang}wcf.global.button.delete{/lang}" class="jsDeleteButton jsTooltip icon icon16 icon-remove" data-object-id="{@$chatRoom->roomID}" data-confirm-message="{lang}chat.acp.room.delete.sure{/lang}" />
							{/if}
							
							{event name='itemButtons'}
						</span>
					</span>
					<ol class="sortableList" data-object-id="{@$chatRoom->roomID}"></ol></li>
				</li>
			{/foreach}
		</ol>
		<div class="formSubmit">
			<button class="button buttonPrimary" data-type="submit">{lang}wcf.global.button.saveSorting{/lang}</button>
		</div>
	</section>
{else}
	<p class="warning">{lang}chat.acp.room.noneAvailable{/lang}</p>
{/if}

{include file='footer'}
