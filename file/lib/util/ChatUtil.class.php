<?php
namespace wcf\util;

/**
 * Chat utilities
 * 
 * @author	Tim Düsterhus
 * @copyright	2010-2011 Tim Düsterhus
 * @license	Creative Commons Attribution-NonCommercial-ShareAlike <http://creativecommons.org/licenses/by-nc-sa/3.0/legalcode>
 * @package	timwolla.wcf.chat
 * @subpackage	util
 */
class ChatUtil {
	/**
	 * Creates a gradient out of two colors represented by an integer.
	 * The first byte is red, the second byte is green, the third one is blue.
	 * The numbers can be easily expressed in hexadecimal notation: 0xFF0000 being red.
	 *
	 * @param	string	$string
	 * @param	integer	$start
	 * @param	integer	$end
	 * @returen	string
	 */
	public static function gradient($string, $start, $end) {
		$string = str_split($string);
		$r = (int) ((($start >> 16 & 255) - ($end >> 16 & 255)) / (count($string) - 1));
		$g = (int) ((($start >> 8 & 255) - ($end >> 8 & 255)) / (count($string) - 1));
		$b = (int) ((($start & 255) - ($end & 255)) / (count($string) - 1));
		
		$result = '';
		for ($i = 0, $max = count($string); $i < $max; $i++) {
			$result .= '<span style="color:rgb('.(($start >> 16 & 255) - $i * $r).','.(($start >> 8 & 255) - $i * $g).','.(($start & 255) - $i * $b).')">'.$string[$i].'</span>'; 
		}
		
		return $result;
	}
}
