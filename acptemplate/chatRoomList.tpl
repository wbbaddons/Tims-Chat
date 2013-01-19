{include file='header' pageTitle='chat.acp.room.list'}
	
<script type="text/javascript">
	//<![CDATA[
	$(function() {
		new WCF.Action.Delete('\\chat\\data\\room\\RoomAction', $('.chatRoomRow'));
		new WCF.Sortable.List('chatRoomList', '\\chat\\data\\room\\RoomAction', {@$startIndex-1});
	});
	//]]>
</script>
	
<header class="boxHeadline">
	<hgroup>
		<h1>{lang}wcf.acp.chat.room.list{/lang}</h1>
	</hgroup>
</header>

<div class="contentNavigation">
	{if $__wcf->session->getPermission('admin.chat.canAddRoom')}
		<nav>
			<ul>
				<li><a href="{link application='chat' controller='ChatRoomAdd'}{/link}" title="{lang}chat.acp.room.add{/lang}" class="button"><img src="{@$__wcf->getPath('wcf')}icon/add.svg" alt="" class="icon24" /> <span>{lang}chat.acp.room.add{/lang}</span></a></li>
			</ul>
		</nav>
	{/if}
</div>
{hascontent}
	<section id="chatRoomList" class="container containerPadding sortableListContainer marginTop shadow">
		<ol class="sortableList" data-object-id="0" start="{$startIndex}">
			{content}
				{foreach from=$objects item=chatRoom}
					<li class="sortableNode sortableNoNesting chatRoomRow" data-object-id="{@$chatRoom->roomID}">
						<span class="sortableNodeLabel">
							{if $__wcf->session->getPermission('admin.chat.canEditRoom')}
								<a href="{link  application='chat'controller='ChatRoomEdit' id=$chatRoom->roomID}{/link}">{$chatRoom->title|language}</a>
							{else}
								{$chatRoom->title|language}
							{/if}
							
							<span class="statusDisplay sortableButtonContainer">
								{if $__wcf->session->getPermission('admin.content.chat.canEditRoom')}
									<a href="{link application='chat' controller='ChatRoomEdit' id=$chatRoom->roomID}{/link}"><img src="{@$__wcf->getPath('wcf')}icon/edit.svg" alt="" title="{lang}wcf.global.button.edit{/lang}" class="jsTooltip icon16" /></a>
								{/if}
								{if $__wcf->session->getPermission('admin.content.chat.canDeleteRoom')}
									<img src="{@$__wcf->getPath('wcf')}icon/delete.svg" alt="" title="{lang}wcf.global.button.delete{/lang}" class="jsDeleteButton jsTooltip icon16" data-object-id="{@$chatRoom->roomID}" data-confirm-message="{lang}chat.acp.room.delete.sure{/lang}" />
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
