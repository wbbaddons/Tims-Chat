{include file='header'}

<header class="wcf-mainHeading">
	<img src="{@RELATIVE_WCF_DIR}icon/chat1.svg" alt="" />
	<hgroup>
		<h1>{lang}wcf.acp.chat.room.list{/lang}</h1>
	</hgroup>
	
	<script type="text/javascript">
		//<![CDATA[
		$(function() {
			new WCF.Action.Delete('wcf\\data\\chat\\room\\ChatRoomAction', $('.chatRoomRow'), $('.wcf-content .wcf-badge'));
			new WCF.Action.Toggle('wcf\\data\\chat\\room\\ChatRoomAction', $('.chatRoomRow'));
		});
		//]]>
	</script>
</header>

<div class="wcf-contentHeader">
	{pages print=true assign=pagesLinks controller="ChatRoomList" link="pageNo=%d"}
	
	{if $__wcf->session->getPermission('admin.content.chat.canAddRoom')}
		<nav>
			<ul class="wcf-largeButtons">
				<li><a href="{link controller='ChatRoomAdd'}{/link}" title="{lang}wcf.acp.chat.room.add{/lang}" class="wcf-button"><img src="{@RELATIVE_WCF_DIR}icon/add1.svg" alt="" /> <span>{lang}wcf.acp.chat.room.add{/lang}</span></a></li>
			</ul>
		</nav>
	{/if}
</div>

{hascontent}
	<div class="wcf-border wcf-boxTitle">
		<hgroup>
			<h1>{lang}wcf.acp.chat.room.list{/lang} <span class="wcf-badge" title="{lang}wcf.acp.chat.room.list.count{/lang}">{#$items}</span></h1>
		</hgroup>
		
		<table>
			<thead>
				<tr>
					<th class="columnID columnChatRoomID" colspan="2">{lang}wcf.global.objectID{/lang}</th>
					<th class="columnTitle columnChatRoomTitle">{lang}wcf.acp.chat.room.title{/lang}</th>
					
					{event name='headColumns'}
				</tr>
			</thead>
			
			<tbody>
				{content}
					{foreach from=$objects item=chatRoom}
						<tr class="chatRoomRow">
							<td class="columnIcon">
								{if $__wcf->session->getPermission('admin.content.chat.canEditRoom')}
									<a href="{link controller='ChatRoomEdit' id=$chatRoom->roomID}{/link}"><img src="{@RELATIVE_WCF_DIR}icon/edit1.svg" alt="" title="{lang}wcf.global.button.edit{/lang}" class="balloonTooltip" /></a>
								{else}
									<img src="{@RELATIVE_WCF_DIR}icon/edit1D.svg" alt="" title="{lang}wcf.global.button.edit{/lang}" />
								{/if}
								{if $__wcf->session->getPermission('admin.content.chat.canDeleteRoom')}
									<img src="{@RELATIVE_WCF_DIR}icon/delete1.svg" alt="" title="{lang}wcf.global.button.delete{/lang}" class="deleteButton balloonTooltip" data-object-id="{@$chatRoom->roomID}" data-confirm-message="{lang}wcf.acp.bbcode.delete.sure{/lang}" />
								{else}
									<img src="{@RELATIVE_WCF_DIR}icon/delete1D.svg" alt="" title="{lang}wcf.global.button.delete{/lang}" />
								{/if}
								
								{event name='buttons'}
							</td>
							<td class="columnID"><p>{@$chatRoom->roomID}</p></td>
							<td class="columnTitle columnChatRoomTitle"><p>{if $__wcf->session->getPermission('admin.content.chat.canEditRoom')}<a href="{link controller='ChatRoomEdit' id=$chatRoom->roomID}{/link}">{$chatRoom->title|language}</a>{else}{$chatRoom->title|language}{/if}</p></td>
							
							{event name='columns'}
						</tr>
					{/foreach}
				{/content}
			</tbody>
		</table>
		
	</div>
	
	<div class="wcf-contentFooter">
		{@$pagesLinks}
		
		{if $__wcf->session->getPermission('admin.content.chat.canAddRoom')}
			<nav>
				<ul class="wcf-largeButtons">
					<li><a href="{link controller='ChatRoomAdd'}{/link}" title="{lang}wcf.acp.chat.room.add{/lang}" class="wcf-button"><img src="{@RELATIVE_WCF_DIR}icon/add1.svg" alt="" /> <span>{lang}wcf.acp.chat.room.add{/lang}</span></a></li>
				</ul>
			</nav>
		{/if}
	</div>
{hascontentelse}
	<div class="wcf-border wcf-content">
		<div>
			<p class="wcf-warning">{lang}wcf.acp.chat.room.noneAvailable{/lang}</p>
		</div>
	</div>
{/hascontent}

{include file='footer'}