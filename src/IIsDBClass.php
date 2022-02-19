<?php
/**
 * R.E. DBObjects
 *
 * @author Takács Ákos (Rimelek), programmer [at] rimelek [dot] hu
 * 
 * @package REDBObjects
 */

/**
 * IIsDBClass interfész
 *
 * Ezt az interfészt kell megvalósítania minden osztálynak, amely valamilyen adatbázis
 * tábláinak mezőit szeretné tulajdonságokként kezelni
 *
 * @author Takács Ákos (Rimelek), programmer [at] rimelek [dot] hu
 * 
 * @package REDBObjects
 */
interface IIsDBClass extends Countable
{
	/**
	 * Rekord(ok) frissítése/módosítása
	 *
	 * @param bool $refreshDB false esetén nem ír adatbázisba,
	 * 							csak az objektum tulajdonságokat teszi lekérdezhetővé.
	 */
	function update($refreshDB=true);

	/**
	 * Mezőnevek objektum tulajdonságokként való megvalósítása.
	 * Dinamikus tulajdonság (mező) lekérdezés
	 *
	 * @param string $var
	 */
	function __get($var);

	/**
	 * Mezőnevek objektum tulajdonságokként való megvalósítása.
	 * Dinamikus tulajdonság (mező) beállítás.
	 *
	 * @param string $var
	 * @param mixed $value
	 */
	function __set($var,$value);
	
	/**
	 * Egy rekord kiválasztása
	 *
	 * @param mixed $rowid Az elsődleges kulcs, ha ez alapján vannak összekapcsolva a táblák.<br />
	 * 						Sql lekérdezés a FROM utántól, ha változó szempontok szerint történik az összekapcsolás.
	 */
	function init($rowid);
}
