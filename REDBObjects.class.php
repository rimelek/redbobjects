<?php
/**
 * R.E. DB Objects
 *
 * @author Takács Ákos (Rimelek), programmer [at] rimelek [dot] hu
 * 
 * @package REDBObjects
 */

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
	 * Adott adatbázis típushoz megfelelő osztályok importálása
	 *
	 * @param string $type Adatbázis típus
	 * @return bool True, ha sikerült az import. False, ha nem
	 */
	public static function uses($type='mysql')
	{
		$dir = dirname(__FILE__).'/'.$type;

		if (!is_dir($dir))
		{
			return false;
		}

		$files = scandir($dir);
		foreach ($files as $file)
		{
			if (is_dir($dir.'/'.$file))
			{
				continue;
			}

			require_once $dir.'/'.$file;
		}
		return true;
	}

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