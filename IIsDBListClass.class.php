<?php
/**
 * R.E. DBObjects
 *
 * @author Takács Ákos (Rimelek), programmer [at] rimelek [dot] hu
 * @copyright Copyright (C) 2010, Takács Ákos
 * @version 2.1
 * @license http://www.gnu.org/licenses/lgpl.html
 * @package REDBObjects
 */

/**
 * IIsDBListClass interfész
 *
 * Ezt az interfészt kell megvalósítania azoknak az osztályoknak, amik
 * a rekordlistát szeretnék megvalósítani.
 *
 * @author Takács Ákos (Rimelek), programmer [at] rimelek [dot] hu
 * @copyright Copyright (C) 2010, Takács Ákos
 * @license http://www.gnu.org/licenses/lgpl.html
 * @package REDBObjects
 */
interface IIsDBListClass extends Countable
{
	/**
	 * A listához új elem hozzáadása
	 *
	 * @param IIsDBClass $object
	 */
	function add(IIsDBClass $object);
	
	/**
	 * Lista inicializálása
	 *
	 * @param string $sql sql lekérdezés FROM utáni része ( limit záradék nélkül )
	 * @param int $offset Eltolás. Az eredményhalmaz elejéből ennyi sort kihagy.
	 * @param int $limit Egyszerre ennyi sort fog lekérdezni. Elhagyása esetén mindet.
	 */
	function init($sql,&$offset=0,$limit=0);

	/**
	 * Egy tábla rekordjainak törlése
	 *
	 * @param string $keyName Melyik elsődleges kulcs mező szerint töröljön.
	 * 							Amelyik táblába tartozik a mező, annak egy rekordját fogja törölni
	 * @param string $keyValue Az elsődleges kulcs mező értéke.
	 * @return int Törölt rekordok száma
	 */
	function delete($keyName,$keyValue);

	/**
	 * Egyszerre minimum hány oldal szükséges a lista összes elemének megjelenítésére
	 * adott limit mellett
	 *
	 * @return int
	 */
	function countPages();

	/**
	 * Összesen hány rekordra illeszkedik a lista ( lekérdezés )
	 *
	 * @return int
	 */
	function countRecords();
}
?>
