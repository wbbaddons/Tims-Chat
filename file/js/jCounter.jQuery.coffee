###
# jCounter - a simple character counter
#
# @author	Maximilian Mader
# @copyright	2011 Maximilian Mader
# @license	Creative Commons Attribution-NonCommercial-ShareAlike <http://creativecommons.org/licenses/by-nc-sa/3.0/legalcode>
# @package	jQuery.jCounter
###
(($) ->
	$.fn.jCounter = (container, options) ->
		options = $.extend
			max: 0
			counterClass: 'jCounter'
			countUp: false
			width: '100%'
		, options
		
		max = if @.attr('maxlength')? then @.attr 'maxlength' else options.max

		if not container?
			@.addClass 'jCounterInput'
			
			@.wrap("""<div class="jCounterContainer" style="width: #{options.width}"><div></div></div>""").parent().append """<div class="#{options.counterClass} color-1">#{max}</div>"""
			jCounterContainer = $(@).parent().children ".#{options.counterClass}"
		else
			jCounterContainer = if typeof container is 'object' then container else $ container

		@.on 'keypress keyup', $.proxy () ->
			length = if options.countUp then @.val().length else max - @.val().length
			
			if options.countUp && max > 0
				if length < max / 2
					color = 1
				else if max / 2 < length <= max / 1.2
					color = 2
				else
					color = 3
			else if options.countUp
				color = 1
			else
				if max / 2 < length
					color = 1
				else if max / 6 <= length <= max / 2
					color = 2
				else
					color = 3
			
			jCounterContainer.text(length).removeClass().addClass "#{options.counterClass} color-#{color}"
		, @
)(jQuery)