/*
 * Copyright (c) 2010-2020 Tim Düsterhus.
 *
 * Use of this software is governed by the Business Source License
 * included in the LICENSE file.
 *
 * Change Date: 2024-11-01
 *
 * On the date above, in accordance with the Business Source
 * License, use of this software will be governed by version 2
 * or later of the General Public License.
 */

define([ 'WoltLabSuite/Core/Language'
       , 'WoltLabSuite/Core/Upload'
       , 'WoltLabSuite/Core/Dom/Change/Listener'
       , 'WoltLabSuite/Core/Dom/Util'
       , 'WoltLabSuite/Core/Ui/Dialog'
       , '../../DataStructure/EventEmitter'
       ], function(Language, Upload, DomChangeListener, DomUtil, Dialog, EventEmitter) {
	"use strict";

	const DIALOG_BUTTON_ID    = 'chatAttachmentUploadButton'
	const DIALOG_CONTAINER_ID = 'chatAttachmentUploadDialog'

	const DEPENDENCIES = [ 'UiInput', 'Room' ];
	class UiAttachmentUpload extends Upload {
		constructor(input, room) {
			const buttonContainer = document.querySelector(`#${DIALOG_CONTAINER_ID} > .upload`)
			const buttonContainerId = DomUtil.identify(buttonContainer)

			const previewContainer = document.querySelector(`#${DIALOG_CONTAINER_ID} > .attachmentPreview`)
			const previewContainerId = DomUtil.identify(previewContainer)

			super(buttonContainerId, previewContainerId, {
				className: 'wcf\\data\\attachment\\AttachmentAction',
				acceptableFiles: [ '.png', '.gif', '.jpg', '.jpeg' ]
			})

			this.input = input
			this.room = room
			this.previewContainer = previewContainer
			this.tmpHash = undefined
		}

		bootstrap() {
			this.uploadDescription = document.querySelector(`#${DIALOG_CONTAINER_ID} > small`)

			const button = document.getElementById(DIALOG_BUTTON_ID)
			const container = document.getElementById(DIALOG_CONTAINER_ID)

			elHide(container)
			container.classList.remove('jsStaticDialogContent')
			container.dataset.isStaticDialog = 'true'

			if (button) {
				button.addEventListener('click', (event) => {
					event.preventDefault()

					Dialog.openStatic(container.id, null, {
						title: elData(container, 'title'),
						onShow: () => this.showDialog()
					})
				})

				const deleteAction = new WCF.Action.Delete('wcf\\data\\attachment\\AttachmentAction', `#${this.previewContainer.id} > p`)
				deleteAction.setCallback(() => this.closeDialog())

				this.input.on('input', (event) => {
					if (event.target.input.value.length == 0) {
						button.classList.remove('disabled')
					}
					else {
						button.classList.add('disabled')
					}
				})
			}
		}

		closeDialog() {
			if (Dialog.getDialog(DIALOG_CONTAINER_ID)) {
				Dialog.close(DIALOG_CONTAINER_ID)
			}
		}

		showDialog() {
			if (this._button.parentNode) {
				this._removeButton()
			}

			this._target.innerHTML = ''
			this._createButton()
			elShow(this.uploadDescription)
		}

		async send(tmpHash, event) {
			event.preventDefault()
			const parameters = { promise: Promise.resolve()
			                   , tmpHash
			                   }
			this.emit('send', parameters)

			try {
				await parameters.promise
				this.closeDialog()
			}
			catch (error) {
				// TODO: Error handling
				console.error(error)
			}
		}

		createButtonGroup(uploadId, objectId, tmpHash) {
			const buttonGroup = document.createElement('ul')
			buttonGroup.classList.add('buttonGroup')

			let li = document.createElement('li')
			const cancelButton = document.createElement('span')
			cancelButton.classList.add('button', 'jsDeleteButton')
			cancelButton.dataset.objectId = objectId
			cancelButton.dataset.eventName = 'attachment'
			cancelButton.innerText = Language.get('wcf.global.button.cancel')
			li.appendChild(cancelButton)
			buttonGroup.appendChild(li)

			li = document.createElement('li')
			const sendButton = document.createElement('span')
			sendButton.classList.add('button')
			sendButton.innerText = Language.get('wcf.global.button.submit')
			sendButton.addEventListener('click', (e) => this.send(tmpHash, e))
			li.appendChild(sendButton)
			buttonGroup.appendChild(li)

			const target = this._fileElements[uploadId][0]
			target.appendChild(buttonGroup)

			DomChangeListener.trigger()
		}

		/**
		 * @see	WoltLabSuite/Core/Upload#_getParameters
		 */
		_getParameters() {
			this.tmpHash = [ ...crypto.getRandomValues(new Uint8Array(20)) ]
				.map(m => ('0' + m.toString(16)).slice(-2))
				.join('')

			return { objectType: "be.bastelstu.chat.message"
			       , parentObjectID: this.room.roomID
			       , tmpHash: this.tmpHash
			       }
		}

		/**
		 * @see	WoltLabSuite/Core/Upload#_success
		 */
		_success(uploadId, data, responseText, xhr, requestOptions) {
			if (data.returnValues.errors && data.returnValues.errors[0]) {
				const error = data.returnValues.errors[0]

				elInnerError(this._button, Language.get(`wcf.attachment.upload.error.${error.errorType}`, {
					filename: error.filename
				}))

				return
			}
			else {
				elInnerError(this._button, '')
			}

			if (data.returnValues.attachments && data.returnValues.attachments[uploadId]) {
				this._removeButton()
				elHide(this.uploadDescription)

				const attachment = data.returnValues.attachments[uploadId]
				const url = attachment.thumbnailURL || attachment.tinyURL || attachment.url

				if (!url) {
					throw new Error('Missing image URL')
				}

				const target = this._fileElements[uploadId][0]
				const progress = target.querySelector(':scope > progress')

				const img = document.createElement('img')
				img.setAttribute('src', url)
				img.setAttribute('alt', '')

				if (url === attachment.thumbnailURL) {
					img.classList.add('attachmentThumbnail')
				}
				else if (url === attachment.tinyURL) {
					img.classList.add('attachmentTinyThumbnail')
				}

				img.dataset.width = attachment.width
				img.dataset.height = attachment.height

				DomUtil.replaceElement(progress, img)

				this.createButtonGroup(uploadId, attachment.attachmentID, this.tmpHash)
			}
			else {
				console.error("Received neither an error nor an attachment response")
				console.error(data.returnValues)
			}
		}
	}
	UiAttachmentUpload.DEPENDENCIES = DEPENDENCIES
	EventEmitter(UiAttachmentUpload.prototype)

	return UiAttachmentUpload
})
