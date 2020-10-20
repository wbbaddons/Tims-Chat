<?php
/*
 * Copyright (c) 2010-2018 Tim Düsterhus.
 *
 * Use of this software is governed by the Business Source License
 * included in the LICENSE file.
 *
 * Change Date: 2024-10-20
 *
 * On the date above, in accordance with the Business Source
 * License, use of this software will be governed by version 2
 * or later of the General Public License.
 */

namespace chat\system\command;

use \chat\data\message\MessageAction;
use \chat\data\room\Room;
use \wcf\data\user\UserProfile;
use \wcf\system\exception\PermissionDeniedException;
use \wcf\system\exception\UserInputException;
use \wcf\system\WCF;
use \wcf\util\StringUtil;

/**
 * The color command allows a user to set a color for their username
 */
class ColorCommand extends AbstractCommand implements ICommand {
	/**
	 * Regular expression matching RGB values in hexadecimal notation
	 * @var \wcf\system\Regex
	 */
	protected $colorRegex = null;

	public function __construct(\wcf\data\DatabaseObject $object) {
		parent::__construct($object);

		$this->colorRegex = new \wcf\system\Regex('^#?([a-f0-9]{6})$', \wcf\system\Regex::CASE_INSENSITIVE);
	}

	/**
	 * @inheritDoc
	 */
	public function getJavaScriptModuleName() {
		return 'Bastelstu.be/Chat/Command/Color';
	}

	/**
	 * Map CSS color names to hexcodes.
	 * See: https://www.w3.org/TR/css3-color/#svg-color
	 *
	 * @var	int[]
	 */
	public static $colors = [
		'aliceblue'		=> 0xF0F8FF,
		'antiquewhite'		=> 0xFAEBD7,
		'aqua'			=> 0x00FFFF,
		'aquamarine'		=> 0x7FFFD4,
		'azure'			=> 0xF0FFFF,
		'beige'			=> 0xF5F5DC,
		'bisque'		=> 0xFFE4C4,
		'black'			=> 0x000000,
		'blanchedalmond'	=> 0xFFEBCD,
		'blue'			=> 0x0000FF,
		'bluescreenblue'	=> 0x0000AA,
		'blueviolet'		=> 0x8A2BE2,
		'brown'			=> 0xA52A2A,
		'burlywood'		=> 0xDEB887,
		'cadetblue'		=> 0x5F9EA0,
		'chartreuse'		=> 0x7FFF00,
		'chocolate'		=> 0xD2691E,
		'coral'			=> 0xFF7F50,
		'cornflowerblue'	=> 0x6495ED,
		'cornsilk'		=> 0xFFF8DC,
		'crimson'		=> 0xDC143C,
		'cyan'			=> 0x00FFFF,
		'darkblue'		=> 0x00008B,
		'darkcyan'		=> 0x008B8B,
		'darkgoldenrod'		=> 0xB8860B,
		'darkgray'		=> 0xA9A9A9,
		'darkgrey'		=> 0xA9A9A9,
		'darkgreen'		=> 0x006400,
		'darkkhaki'		=> 0xBDB76B,
		'darkmagenta'		=> 0x8B008B,
		'darkolivegreen'	=> 0x556B2F,
		'darkorange'		=> 0xFF8C00,
		'darkorchid'		=> 0x9932CC,
		'darkred'		=> 0x8B0000,
		'darksalmon'		=> 0xE9967A,
		'darkseagreen'		=> 0x8FBC8F,
		'darkslateblue'		=> 0x483D8B,
		'darkslategray'		=> 0x2F4F4F,
		'darkslategrey'		=> 0x2F4F4F,
		'darkturquoise'		=> 0x00CED1,
		'darkviolet'		=> 0x9400D3,
		'deeppink'		=> 0xFF1493,
		'deepskyblue'		=> 0x00BFFF,
		'dimgray'		=> 0x696969,
		'dimgrey'		=> 0x696969,
		'dodgerblue'		=> 0x1E90FF,
		'firebrick'		=> 0xB22222,
		'floralwhite'		=> 0xFFFAF0,
		'forestgreen'		=> 0x228B22,
		'fuchsia'		=> 0xFF00FF,
		'gainsboro'		=> 0xDCDCDC,
		'ghostwhite'		=> 0xF8F8FF,
		'gold'			=> 0xFFD700,
		'goldenrod'		=> 0xDAA520,
		'gray'			=> 0x808080,
		'grey'			=> 0x808080,
		'green'			=> 0x008000,
		'greenyellow'		=> 0xADFF2F,
		'honeydew'		=> 0xF0FFF0,
		'hotpink'		=> 0xFF69B4,
		'indianred'		=> 0xCD5C5C,
		'indigo'		=> 0x4B0082,
		'ivory'			=> 0xFFFFF0,
		'khaki'			=> 0xF0E68C,
		'lavender'		=> 0xE6E6FA,
		'lavenderblush'		=> 0xFFF0F5,
		'lawngreen'		=> 0x7CFC00,
		'lemonchiffon'		=> 0xFFFACD,
		'lightblue'		=> 0xADD8E6,
		'lightcoral'		=> 0xF08080,
		'lightcyan'		=> 0xE0FFFF,
		'lightgoldenrodyellow'	=> 0xFAFAD2,
		'lightgray'		=> 0xD3D3D3,
		'lightgrey'		=> 0xD3D3D3,
		'lightgreen'		=> 0x90EE90,
		'lightpink'		=> 0xFFB6C1,
		'lightsalmon'		=> 0xFFA07A,
		'lightseagreen'		=> 0x20B2AA,
		'lightskyblue'		=> 0x87CEFA,
		'lightslategray'	=> 0x778899,
		'lightslategrey'	=> 0x778899,
		'lightsteelblue'	=> 0xB0C4DE,
		'lightyellow'		=> 0xFFFFE0,
		'lime'			=> 0x00FF00,
		'limegreen'		=> 0x32CD32,
		'linen'			=> 0xFAF0E6,
		'magenta'		=> 0xFF00FF,
		'maroon'		=> 0x800000,
		'mediumaquamarine'	=> 0x66CDAA,
		'mediumblue'		=> 0x0000CD,
		'mediumorchid'		=> 0xBA55D3,
		'mediumpurple'		=> 0x9370D8,
		'mediumseagreen'	=> 0x3CB371,
		'mediumslateblue'	=> 0x7B68EE,
		'mediumspringgreen'	=> 0x00FA9A,
		'mediumturquoise'	=> 0x48D1CC,
		'mediumvioletred'	=> 0xC71585,
		'midnightblue'		=> 0x191970,
		'mintcream'		=> 0xF5FFFA,
		'mistyrose'		=> 0xFFE4E1,
		'moccasin'		=> 0xFFE4B5,
		'navajowhite'		=> 0xFFDEAD,
		'navy'			=> 0x000080,
		'oldlace'		=> 0xFDF5E6,
		'olive'			=> 0x808000,
		'olivedrab'		=> 0x6B8E23,
		'orange'		=> 0xFFA500,
		'orangered'		=> 0xFF4500,
		'orchid'		=> 0xDA70D6,
		'oxford'		=> 0xF02D,  // looks like green
		'palegoldenrod'		=> 0xEEE8AA,
		'palegreen'		=> 0x98FB98,
		'paleturquoise'		=> 0xAFEEEE,
		'palevioletred'		=> 0xD87093,
		'papayawhip'		=> 0xFFEFD5,
		'peachpuff'		=> 0xFFDAB9,
		'peru'			=> 0xCD853F,
		'pink'			=> 0xFFC0CB,
		'plum'			=> 0xDDA0DD,
		'powderblue'		=> 0xB0E0E6,
		'purple'		=> 0x800080,
		'red'			=> 0xFF0000,
		'rosybrown'		=> 0xBC8F8F,
		'royalblue'		=> 0x4169E1,
		'saddlebrown'		=> 0x8B4513,
		'sadwin'		=> 0x2067B2,
		'salmon'		=> 0xFA8072,
		'sandybrown'		=> 0xF4A460,
		'seagreen'		=> 0x2E8B57,
		'seashell'		=> 0xFFF5EE,
		'sienna'		=> 0xA0522D,
		'silver'		=> 0xC0C0C0,
		'skyblue'		=> 0x87CEEB,
		'slateblue'		=> 0x6A5ACD,
		'slategray'		=> 0x708090,
		'slategrey'		=> 0x708090,
		'snow'			=> 0xFFFAFA,
		'springgreen'		=> 0x00FF7F,
		'steelblue'		=> 0x4682B4,
		'tan'			=> 0xD2B48C,
		'teal'			=> 0x008080,
		'thistle'		=> 0xD8BFD8,
		'tomato'		=> 0xFF6347,
		'turquoise'		=> 0x40E0D0,
		'violet'		=> 0xEE82EE,
		'wheat'			=> 0xF5DEB3,
		'white'			=> 0xFFFFFF,
		'whitesmoke'		=> 0xF5F5F5,
		'yellow'		=> 0xFFFF00,
		'yellowgreen'		=> 0x9ACD32
	];

	/**
	 * @inheritDoc
	 */
	public function validate($parameters, Room $room, UserProfile $user = null) {
		if ($user === null) $user = new UserProfile(WCF::getUser());

		if (!$user->getPermission('user.chat.canSetColor')) throw new PermissionDeniedException();

		foreach ($parameters as $parameter) {
			$value = StringUtil::trim($this->assertParameter($parameter, 'value'));
			$valid = true;

			switch ($this->assertParameter($parameter, 'type')) {
				case 'hex':
					if (!$this->colorRegex->match($value)) {
						throw new UserInputException('message', WCF::getLanguage()->getDynamicVariable('chat.error.invalidColor', [ 'color' => $value ]));
					}
					break;
				case 'word':
					if (!isset(self::$colors[$value])) {
						throw new UserInputException('message', WCF::getLanguage()->getDynamicVariable('chat.error.invalidColor', [ 'color' => $value ]));
					}
					break;

				default:
					throw new UserInputException('message');
			}
		}
	}

	/**
	 * @inheritDoc
	 */
	public function execute($parameters, Room $room, UserProfile $user = null) {
		if ($user === null) $user = new UserProfile(WCF::getUser());

		$objectTypeID = $this->getMessageObjectTypeID('be.bastelstu.chat.messageType.color');
		$colors = [ ];

		if (!isset($parameters[1])) $parameters[1] = $parameters[0];

		foreach ($parameters as $key => $parameter) {
			$value = StringUtil::trim($this->assertParameter($parameter, 'value'));

			switch ($this->assertParameter($parameter, 'type')) {
				case 'hex':
					$colors[$key] = hexdec($value);
					break;
				case 'word':
					if (!isset(self::$colors[$value])) throw new UserInputException('message');
					$colors[$key] = self::$colors[$value];
					break;
				default:
					throw new UserInputException('message');
			}
		}

		WCF::getDB()->beginTransaction();
		$editor = new \wcf\data\user\UserEditor($user->getDecoratedObject());
		$editor->update([ 'chatColor1' => $colors[0]
		                , 'chatColor2' => $colors[1]
		                ]);

		(new MessageAction([ ], 'create', [ 'data' => [ 'roomID'       => $room->roomID
		                                              , 'userID'       => $user->userID
		                                              , 'username'     => $user->username
		                                              , 'time'         => TIME_NOW
		                                              , 'objectTypeID' => $objectTypeID
		                                              , 'payload'      => serialize([ 'color1' => $colors[0]
		                                                                            , 'color2' => $colors[1]
		                                                                            ])
		                                              ]
		                                  , 'updateTimestamp' => true
		                                  ]
		                  )
		)->executeAction();
		WCF::getDB()->commitTransaction();
	}
}
