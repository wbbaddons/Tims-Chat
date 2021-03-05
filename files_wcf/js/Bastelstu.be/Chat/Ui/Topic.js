/*
 * Copyright (c) 2010-2021 Tim Düsterhus.
 *
 * Use of this software is governed by the Business Source License
 * included in the LICENSE file.
 *
 * Change Date: 2025-03-05
 *
 * On the date above, in accordance with the Business Source
 * License, use of this software will be governed by version 2
 * or later of the General Public License.
 */

define(['WoltLabSuite/Core/Dom/Traverse'], function (Traverse) {
	'use strict'

	class Topic {
		bootstrap() {
			elBySelAll('.chatRoomTopic', document, function (element) {
				elBySel('.jsDismissRoomTopicButton', element).addEventListener(
					'click',
					function (event) {
						elRemove(element)
					}
				)
			})
		}
	}

	return Topic
})
