const infoCommandSuspensionDecorator = new Promise((resolve, reject) => {
	require([ 'Bastelstu.be/Chat/Helper' ], Helper => {
		chat.bottle.decorator('MessageType.be-bastelstu-chat-messageType-info', messageType => {
			messageType.addDecorator(payload => {
				if (payload.suspensions) {
					payload.suspensions = payload.suspensions.map(suspension => {
						suspension = Object.assign({ }, suspension)

						suspension.timeElement = Helper.getTimeElementHTML(new Date(suspension.time * 1000))

						if (suspension.expires) {
							suspension.expiresElement = Helper.getTimeElementHTML(new Date(suspension.expires * 1000))
						}

						return suspension
					})
				}

				return payload
			})

			return messageType
		})

		resolve()
	}, reject)
})

promises.add(infoCommandSuspensionDecorator)
