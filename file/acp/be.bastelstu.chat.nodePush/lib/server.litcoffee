nodePush Pushserver for Tims Chat
=================================

Copyright Information
---------------------

	"@author	Tim Düsterhus"
	"@copyright	2010-2013 Tim Düsterhus"
	"@license	Creative Commons Attribution-NonCommercial-ShareAlike <http://creativecommons.org/licenses/by-nc-sa/3.0/legalcode>"
	"@package	be.bastelstu.chat"
	"@subpackage	nodePush"

Setup
-----

Load required namespaces.

	io = require 'socket.io'
	net = require 'net'
	fs = require 'fs'

Load config

	config = require '../config.js'

Prepare environment

	log = (message) ->
		console.log "[be.bastelstu.chat.nodePush] #{message}"

be.bastelstu.chat.nodePush
==========================

	class be.bastelstu.chat.nodePush

Methods
-------
**constructor()**

		constructor: ->
			log 'Starting Pushserver for Tims Chat'
			log "PID is #{process.pid}"
			log "Using port: #{config.port}"
			
			@initUnixSocket()
			@initSocketIO()

Bind shutdown function to needed events.

			process.on 'exit', @shutdown.bind @
			process.on 'uncaughtException', @shutdown.bind @
			process.on 'SIGINT', @shutdown.bind @
			process.on 'SIGTERM', @shutdown.bind @

Set nice title for PS.

			process.title = 'nodePush - Tims Chat'

Set newMessage event once a minute to allow for easier timeout detection in chat.

			setInterval =>
				@socket.sockets.emit 'newMessage'
			, 60e3

**initSocketIO()**  
Initialize socket server.

		initSocketIO: ->
			log 'Initializing socket.io'
			@socket = io.listen config.port
			
			@socket.set 'log level', 1
			@socket.set 'browser client etag', true
			@socket.set 'browser client minification', true
			@socket.set 'browser client gzip', true
			
			@socket.configure 'development', =>
				@socket.set 'log level', 3
				@socket.set 'browser client etag', false
				@socket.set 'browser client minification', false

**initUnixSocket()**  
Initialize PHP side unix socket.

		initUnixSocket: ->
			log 'Initializing Unix-Socket'
			socket = net.createServer (c) =>
				setTimeout =>
					@socket.sockets.emit 'newMessage'
				, 20
				
				c.end()
			
			socket.listen "#{__dirname}/../data.sock"
			fs.chmod "#{__dirname}/../data.sock", '777'

**shutdown()**  
Perform clean shutdown of nodePush.

		shutdown: ->
			return unless fs.existsSync "#{__dirname}/../data.sock"
			
			log 'Shutting down'
			fs.unlinkSync "#{__dirname}/../data.sock"
			process.exit()

And finally start the service.

	new be.bastelstu.chat.nodePush()