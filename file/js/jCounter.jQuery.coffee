###
# jCounter - a simple character counter
#
# @author	Maximilian Mader
# @copyright	2011 Maximilian Mader
# @license	Creative Commons Attribution-NonCommercial-ShareAlike <http://creativecommons.org/licenses/by-nc-sa/3.0/legalcode>
# @package	jQuery.jCounter
###
(($) ->
	$.fn.jCounter = (max, options) ->
		max ?= 140
		options = $.extend
			container: '<span></span>'
			counterClass: 'counter'
			countUp: false
		, options
		
		jCounterContainer = $ options.container
		
		this.on 'keypress keydown keyup', $.proxy () ->
			if options.countUp
				length = this.val().length
			else
				length = maxChars - this.val().length
			
			if options.countUp
				color = 1
			else
				if length > maxChars / 2
					color = 1
				else if length <= maxChars / 2 and length >= maxChars / 6
					color = 2
				else
					color = 3
			
			jCounterContainer.text(length).attr('class', '').addClass(options.counterClass + ' color-'+color)
		, this
)(jQuery)