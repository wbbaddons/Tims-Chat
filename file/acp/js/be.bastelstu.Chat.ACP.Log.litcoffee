Tims Chat 3
===========

This is the javascript file providing functions related to the message log for [##Tims Chat##](https://github.com/wbbaddons/Tims-Chat).

	### Copyright Information
	# @author	Maximilian Mader
	# @copyright	2010-2013 Tim Düsterhus
	# @license	Creative Commons Attribution-NonCommercial-ShareAlike <http://creativecommons.org/licenses/by-nc-sa/3.0/legalcode>
	# @package	be.bastelstu.chat
	###

## Code

	(($, window) ->
		"use strict";
		
		###
		# message log content
		# @var jQuery
		###
		_messageLogContent = null
		
		###
		# list of containers
		# @var object
		###
		_hasContent = {}
		
		###
		# action proxy
		# @var WCF.Action.Proxy
		###
		_proxy = null
		
		init = ->
			_messageLogContent = $('#messageLogContent')
			
			activeMenuItem = _messageLogContent.data 'active'
			enableProxy = false
			
			_messageLogContent.find('div.tabMenuContent > .subTabMenuContent').each (index, container) ->
				containerID = $(container).wcfIdentify()
				
				if (! $("##{containerID}").hasClass 'empty')
					_hasContent[containerID] = true
				else
					_hasContent[containerID] = false
					enableProxy = true
					
			if (enableProxy)
				_proxy = new WCF.Action.Proxy
					success: _success
					
					_messageLogContent.bind 'wcftabsbeforeactivate',
					_loadContent
					
		_loadContent = (event, ui) ->
			containerID = $(ui.newPanel).attr 'id'
			
			if ($("##{$(ui.newPanel).attr('id')}").hasClass 'tabMenuContainer')
				containerID = $("##{containerID} > .subTabMenuContent").first().attr 'id'
				tab = $("##{containerID}").parent().find(".menu > ul > li").first()
			else
				tab = $(ui.newTab)
				
			unless _hasContent[containerID]
				start = _messageLogContent.data('baseTime') + (tab.data('hour') * 3600) + (tab.data('minutes') * 60)
				
				_proxy.setOption 'data',
					actionName: 'getMessages'
					className: 'chat\\data\\message\\MessageAction'
					parameters:
						containerID: containerID
						start: start
						end: start + 1799
						roomID: _messageLogContent.data('roomID')
				_proxy.sendRequest()
				
		_success = (data, textStatus, jqWHR) ->
			containerID = data.returnValues.containerID
			_hasContent[containerID] = true
			
			content = _messageLogContent.find "##{containerID}"
			if(data.returnValues.template != '')
				$("<div>#{data.returnValues.template}</div>").hide().appendTo content
				if(!data.returnValues.noMessages)
					content.addClass 'tabularBox'
				
			content.children().first().show()
		
		Log =
			TabMenu:
				init: init
				
		window.be ?= {}
		be.bastelstu ?= {}
		be.bastelstu.Chat ?= {}
		be.bastelstu.Chat.ACP ?= {}
		be.bastelstu.Chat.ACP.Log ?= {}
		window.be.bastelstu.Chat.ACP.Log = Log
	)(jQuery, @)
