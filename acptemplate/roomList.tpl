{include file='header' pageTitle='chat.acp.room.list'}
	
<script type="text/javascript">
	//<![CDATA[
	$(function() {
		new WCF.Action.Delete('\\chat\\data\\room\\RoomAction', $('.chatRoomRow'));
		new WCF.Sortable.List('roomList', '\\chat\\data\\room\\RoomAction', {@$startIndex-1});
	});
	//]]>
</script>
	
<header class="boxHeadline">
	<hgroup>
		<h1>{lang}chat.acp.room.list{/lang}</h1>
	</hgroup>
</header>

<div class="contentNavigation">
	{if $__wcf->session->getPermission('admin.chat.canAddRoom')}
		<nav>
			<ul>
				<li><a href="{link application='chat' controller='roomAdd'}{/link}" title="{lang}chat.acp.room.add{/lang}" class="button"><span class="icon icon16 icon-plus"></span> <span>{lang}chat.acp.room.add{/lang}</span></a></li>
			</ul>
		</nav>
	{/if}
</div>
{hascontent}
	<section id="roomList" class="container containerPadding sortableListContainer marginTop shadow">
		<ol class="sortableList" data-object-id="0" start="{$startIndex}">
			{content}
				{foreach from=$objects item=chatRoom}
					<li class="sortableNode sortableNoNesting chatRoomRow" data-object-id="{@$chatRoom->roomID}">
						<span class="sortableNodeLabel">
							{if $__wcf->session->getPermission('admin.chat.canEditRoom')}
								<a href="{link  application='chat' controller='roomEdit' id=$chatRoom->roomID}{/link}">{$chatRoom->title|language}</a>
							{else}
								{$chatRoom->title|language}
							{/if}
							
							<span class="statusDisplay sortableButtonContainer">
								{if $__wcf->session->getPermission('admin.chat.canEditRoom')}
									<a href="{link application='chat' controller='roomEdit' id=$chatRoom->roomID}{/link}"><span title="{lang}wcf.global.button.edit{/lang}" class="jsTooltip icon icon16 icon-edit" /></a>
								{/if}
								{if $__wcf->session->getPermission('admin.chat.canDeleteRoom')}
									<span title="{lang}wcf.global.button.delete{/lang}" class="jsDeleteButton jsTooltip icon icon16 icon-remove" data-object-id="{@$chatRoom->roomID}" data-confirm-message="{lang}chat.acp.room.delete.sure{/lang}" />
								{/if}
							</span>
						</span>
						<ol class="sortableList" data-object-id="{@$chatRoom->roomID}"></ol></li>
					</li>
				{/foreach}
			{/content}
		</ol>
		<div class="formSubmit">
			<button class="button" data-type="submit">{lang}wcf.global.button.submit{/lang}</button>
		</div>
	</section>
{hascontentelse}
	<p class="warning">{lang}chat.acp.room.noneAvailable{/lang}</p>
{/hascontent}

{include file='footer'}
