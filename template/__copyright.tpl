{if $__chat->isActiveApplication()}
	{if $templateName != '__copyright'}<address id="timsChatCopyright" class="copyright marginTop">{lang}chat.global.copyright{/lang}</address>
	{elseif $templateName == '__copyright'}
		<div style="background-image: url('data:image/png;base64,{$background}');">
			<dl>
				<dt>{lang}chat.global.copyright.leader{/lang}</dt>
				<dd>
					<ul>
						<li><a href="http://tims.bastelstu.be/" class="externalURL"{if EXTERNAL_LINK_TARGET_BLANK} target="_blank"{/if}>Tim D&uuml;sterhus</a></li>
					</ul>
				</dd>
			</dl>
			<dl>
				<dt>{lang}chat.global.copyright.developer{/lang}</dt>
				<dd>
					<ul>
						<li><a href="http://tims.bastelstu.be/" class="externalURL"{if EXTERNAL_LINK_TARGET_BLANK} target="_blank"{/if}>Tim D&uuml;sterhus</a></li>
						<li><a href="https://github.com/max-m" class="externalURL"{if EXTERNAL_LINK_TARGET_BLANK} target="_blank"{/if}>Maximilian Mader</a></li>
					</ul>
				</dd>
			</dl>
			{*<dl>
				<dt>{lang}chat.global.copyright.graphics{/lang}</dt>
				<dd>
					
				</dd>
			</dl>
			<dl>
				<dt>{lang}chat.global.copyright.translation{/lang}</dt>
				<dd>
					
				</dd>
			</dl>*}
			<dl>
				<dt>{lang}chat.global.copyright.thanks{/lang}</dt>
				<dd>
					<ul>
						<li><a href="http://www.wbbaddons.de/user/2020-noone/" class="externalURL"{if EXTERNAL_LINK_TARGET_BLANK} target="_blank"{/if}>-noone-</a></li>
						<li>Gabi</li>
						<li>Alexandra Glass</li>
						<li><a href="https://github.com/Leon-" class="externalURL"{if EXTERNAL_LINK_TARGET_BLANK} target="_blank"{/if}>Stefan Hahn</a></li>
						<li><a href="http://kittmedia.com/" class="externalURL"{if EXTERNAL_LINK_TARGET_BLANK} target="_blank"{/if}>Matthias Kittsteiner</a></li>
						<li><a href="http://www.wbbaddons.de" class="externalURL"{if EXTERNAL_LINK_TARGET_BLANK} target="_blank"{/if}>Martin Schwendowius</a></li>
						<li><a href="http://www.cls-design.com/" class="externalURL"{if EXTERNAL_LINK_TARGET_BLANK} target="_blank"{/if}>Tom</a></li>
					</ul>
				</dd>
			</dl>
		</div>
	{/if}
{/if}
