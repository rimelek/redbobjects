<?php
/**
 * R.E. DB Objects
 *
 * @author Takács Ákos (Rimelek), programmer [at] rimelek [dot] hu
 * 
 * @package REDBObjects
 */
namespace REDBObjects;

/**
 * Project kezelő osztály
 *
 * @author Takács Ákos (Rimelek), programmer [at] rimelek [dot] hu
 * 
 * @package REDBObjects
 */
class REDBObjects
{
	/**
	 *
	 * @param array $array
	 * @param bool $or
	 */
	public static function createWhere($array, $or=false)
	{
		$sep = $or ? ' or ' : ' and ';
		$where = array();
		foreach ($array as $k => $v) {
			$k = '`'.str_replace('.', '`.`', $k).'`';
			$where[] = " ".$k." = '".mysql_real_escape_string($v)."' ";
		}
		return implode($sep, $where);
	}
}