{include file='header'}
	
<script type="text/javascript">
	//<![CDATA[
	$(function() {
		new WCF.Action.Delete('\\wcf\\data\\chat\\room\\ChatRoomAction', $('.chatRoomRow'));
		new WCF.Sortable.List('chatRoomList', '\\wcf\\data\\chat\\room\\ChatRoomAction');
	});
	//]]>
</script>
	
<header class="wcf-mainHeading wcf-container">
	<img src="{@$__wcf->getPath('wcf')}icon/chat1.svg" alt="" class="wcf-containerIcon" />
	<hgroup class="wcf-containerContent">
		<h1>{lang}wcf.acp.chat.room.list{/lang}</h1>
	</hgroup>
</header>

<div class="wcf-contentHeader">
	{pages print=true assign=pagesLinks controller="ChatRoomList" link="pageNo=%d"}
	
	{if $__wcf->session->getPermission('admin.content.chat.canAddRoom')}
		<nav>
			<ul class="wcf-largeButtons">
				<li><a href="{link controller='ChatRoomAdd'}{/link}" title="{lang}wcf.acp.chat.room.add{/lang}" class="wcf-button"><img src="{@$__wcf->getPath('wcf')}icon/add1.svg" alt="" /> <span>{lang}wcf.acp.chat.room.add{/lang}</span></a></li>
			</ul>
		</nav>
	{/if}
</div>

<section id="chatRoomList" class="wcf-border wcf-sortableListContainer">
	{hascontent}
	<ol class="wcf-sortableList" data-object-id="0">
		{content}
			{foreach from=$objects item=chatRoom}
				<li class="wcf-sortableNode chatRoomRow" data-object-id="{@$chatRoom->roomID}">
					<span class="wcf-sortableNodeLabel">
						<a href="{link controller='ChatRoomEdit' id=$chatRoom->roomID}{/link}">{$chatRoom->title|language}</a>
						
						<span class="wcf-sortableButtonContainer">
							{if $__wcf->session->getPermission('admin.content.chat.canEditRoom')}
								<a href="{link controller='ChatRoomEdit' id=$chatRoom->roomID}{/link}"><img src="{@$__wcf->getPath('wcf')}icon/edit1.svg" alt="" title="{lang}wcf.global.button.edit{/lang}" class="balloonTooltip" /></a>
							{/if}
							{if $__wcf->session->getPermission('admin.content.chat.canDeleteRoom')}
								<img src="{@$__wcf->getPath('wcf')}icon/delete1.svg" alt="" title="{lang}wcf.global.button.delete{/lang}" class="jsDeleteButton jsTooltip" data-object-id="{@$chatRoom->roomID}" data-confirm-message="{lang}wcf.acp.bbcode.delete.sure{/lang}" />
							{/if}
						</span>
					</span>
				</li>
			{/foreach}
		{/content}
	</ol>
	{hascontentelse}
		<p class="wcf-warning">{lang}wcf.acp.chat.room.noneAvailable{/lang}</p>
	{/hascontent}
</section>

{include file='footer'}