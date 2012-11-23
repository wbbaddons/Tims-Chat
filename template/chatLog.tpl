{include file='documentHeader'}

<head>
	<title>{$room} - {lang}wcf.chat.log.title{/lang} - {PAGE_TITLE|language}</title>
	
	{include file='headInclude' sandbox=false}
	<style type="text/css">
		#timsChatCopyrightDialog {
			background-image: url("{link controller='Chat' action='Copyright' sheep=1}{/link}");
		}
		
		{assign var='type' value='\wcf\data\chat\message\ChatMessage::TYPE_'}
		.timsChatMessage{$type|concat:'JOIN'|constant}, .timsChatMessage{$type|concat:'LEAVE'|constant},
		.timsChatMessage{$type|concat:'INFORMATION'|constant}, .timsChatMessage{$type|concat:'ERROR'|constant} {
			background-position: left top;
			background-repeat: no-repeat;
			background-size: 16px 16px;
		}
		
		.timsChatMessage{$type|concat:'JOIN'|constant} {
			background-image: url({icon size='S'}circleArrowRight{/icon});
		}
		
		.timsChatMessage{$type|concat:'LEAVE'|constant} {
			background-image: url({icon size='S'}circleArrowLeft{/icon});
		}
		
		.timsChatMessage{$type|concat:'INFORMATION'|constant} {
			background-image: url({icon size='S'}systemInfo{/icon});
		}
		
		.timsChatMessage{$type|concat:'ERROR'|constant} {
			background-image: url({icon size='S'}systemError{/icon});
		}
	</style>
</head>

<body id="tpl{$templateName|ucfirst}">
{capture assign='sidebar'}{include file='chatLogSidebar'}{/capture}
{capture assign='headerNavigation'}{include file='chatNavigationInclude'}{/capture}
{include file='header' sandbox=false sidebarOrientation='right'}

<div id="timsChatRoomContent">
	<fieldset>
		<div class="timsChatMessageContainer container box shadow1">
			<ul>
				<li class="error">{lang}wcf.chat.noJs{/lang}</li>
			</ul>
		</div>
	</fieldset>
	
	<div id="timsChatControls" class="marginTop">
		<nav id="timsChatOptions">
			<ul class="smallButtons">
				<li>
					<a id="timsChatFullscreen" accesskey="f" class="timsChatToggle jsTooltip button" title="{lang}wcf.global.button.disable{/lang}" data-disable-message="{lang}wcf.global.button.disable{/lang}" data-enable-message="{lang}wcf.global.button.enable{/lang}" data-status="0">
						<img alt="" src="{icon size='S'}disabled{/icon}" /> <span>{lang}wcf.chat.fullscreen{/lang}</span>
					</a>
				</li>
				<li>
					<a id="timsChatMark" class="jsTooltip button" title="{lang}wcf.chat.mark.description{/lang}">
						<img alt="" src="{icon size='S'}check{/icon}" /> <span>{lang}wcf.chat.mark{/lang}</span>
					</a>
				</li>
			</ul>
		</nav>
	</div>
	{include file='chatCopyright'}
</div>
{include file='chatJavascriptInclude'}
<script type="text/javascript">
	//<![CDATA[
		(function ($, window) {
			// remove noscript message
			$('.timsChatMessageContainer .error').remove();
			
			// populate templates
			{capture assign='chatMessageTemplate'}{include file='chatMessage'}{/capture}
			be.bastelstu.WCF.Chat.Log.messageTemplate = (new WCF.Template('{@$chatMessageTemplate|encodeJS}')).compile();
			
			{event name='shouldInit'}
			// Boot the chat
			be.bastelstu.WCF.Chat.Log.init();
			{event name='didInit'}
			
			// show the last X messages
			be.bastelstu.WCF.Chat.Log.handleMessages([
				{implode from=$messages item='message'}
					{@$message->jsonify()}
				{/implode}
			]);
			
			$('#timsChatCopyright').click(function (event) {
				event.preventDefault();
				if ($.wcfIsset('timsChatCopyrightDialog')) return WCF.showDialog('timsChatCopyrightDialog', { title: 'Tims Chat{if CHAT_SHOW_VERSION && $chatVersion|isset} {$chatVersion}{/if}' });
				var container = $('<fieldset id="timsChatCopyrightDialog"></fieldset>');
				container.load('{link controller='Chat' action='Copyright'}{/link}', function() {
					$('body').append(container);
					WCF.showDialog('timsChatCopyrightDialog', { title: 'Tims Chat{if CHAT_SHOW_VERSION && $chatVersion|isset} {$chatVersion}{/if}' });
				});
			});
		})(jQuery, this)
	//]]>
</script>

{include file='footer' sandbox=false}
</body>
</html>