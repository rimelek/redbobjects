<?php
/**
 * R.E. DB Objects
 *
 * @author Takács Ákos (Rimelek), programmer [at] rimelek [dot] hu
 * @copyright Copyright (C) 2010, Takács Ákos
 * @version 2.1
 * @license http://www.gnu.org/licenses/lgpl.html
 * @package REDBObjects
 */

/**
 * Project kezelő osztály
 *
 * @author Takács Ákos (Rimelek), programmer [at] rimelek [dot] hu
 * @copyright Copyright (C) 2010, Takács Ákos
 * @license http://www.gnu.org/licenses/lgpl.html
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
			if ($file == '.' or $file == '..')
			{
				continue;
			}

			require_once $dir.'/'.$file;
		}
		return true;
	}
}

?>
