<?php
/**
 * R.E. DBObjects
 *
 * @author Takács Ákos (Rimelek), programmer [at] rimelek [dot] hu
 * 
 * @package REDBObjects
 */

/**
 * ADBClass absztrakt ős
 *
 * Absztrakt őse Az IIsDBListClass és az IIsDBCLass interfészeket implementáló osztályoknak.<br />
 * Ezen tulajdonságokat és metódusokat automatikusan tartalmazzák a listát,
 * illetve rekordot megvalósító osztályok
 *
 * @author Takács Ákos (Rimelek), programmer [at] rimelek [dot] hu
 * 
 * @package REDBObjects
 */
abstract class ADBClass
{
	/**
	* A lekérdezhető mezők asszociatív tömbje
	* @var array $properties
	*/
	protected $properties = array();

	/**
	 * Virtuális mezők asszociatív tömbje
	 *
	 * @var array
	 */
	protected $virtualFields = array();


	/**
	* A megvalósítandó táblák listája
	*
	* @var array $tablelist
	*/
	public $tablelist=array();

	/**
	 * Táblák alias-ai a kulcsok, és az eredeti tábla az érték
	 *
	 * @var array $tableAliases
	 */
	public $tableAliases = array();

	/**
	 * Táblanevet jelző string
	 *
	 * Amennyiben egyező nevű mezők vannak több táblában, jelölni kell a táblanevet is.<br />
	 * Annak jelölése, hogy táblanév is szerepel a tulajdonságban, ezzel a string-el történik.
	 *
	 * Például: 
	 * <code>
	 * $object->T__tablename__fieldname = $value;
	 * </code>
	 *
	 * @var string $tableName_signal
	 */
	protected $tableName_signal = 'T__';

	/**
	 * Táblanevet és mezőnevet elválasztó string
	 *
	 * Ha megadjuk a táblanevet is a tulajdonságokban, akkor ezzel a stringgel
	 * kell jelölni a táblanév végét (Mező kezdetét)
	 * Például: 
	 * <code>
	 * $object->T__tablename__fieldname = $value;
	 * </code>
	 *
	 * @var string $table_field_sep
	 */
	protected $table_field_sep = '__';

	/**
	 * A táblák elsődleges kulcs mezőinek nevét tároló tömb.
	 *
	 * Asszociatív kétdimenziós tömb.
	 * 1. dimenzió kulcsa a tábla neve.
	 * 2. dimenzió kulcsa a kulcsmező neve, értéke a kulcsmező tartalma.
	 *
	 * @var array $priKeys
	 */
	protected $priKeys=array();

	/**
	 * Elsődleges kulcsok lekérdezése
	 *
	 * @param string $tableName Tábla neve
	 * @return array
	 */
	public function getPriKeys($tableName=null)
	{
		return is_null($tableName) ? $this->priKeys : 
				( isset($this->priKeys[$tableName]) ? $this->priKeys[$tableName] : array() );
	}

	/**
	 * Virtuális mezők lekérdezése
	 *
	 * @param string $name Mező táblával együtt (T__tabla__mezo) ha $field null
	 * @param string Mező neve. Tábla nélkül. Ekkor a $name csak a tábla
	 * @return string Mező értéke. null, ha nincs ilyen virtális mező. 
	 */
	public function getVirtualField($name, $field = null)
	{
		if (($field and $table = $name) or $this->sep_table_field($name, $table, $field)) {
			return isset($this->virtualFields[$table][$field]) ? $this->virtualFields[$table][$field] : null;
		} else {
			return isset($this->virtualFields[0][$name]) ? $this->virtualFields[0][$name] : null;
		}
	}

	/**
	 * Tábla és mezőnevet szétválasztó metódus
	 *
	 * A tábla és mezőnevek a nekik megfelelő referencia változókba kerülnek
	 * a művelet elvégzése után
	 *
	 * @param string $input A tábla és mezőnév egy tulajdonságnévként
	 * @param string $table Táblanév (referencia változó)
	 * @param string $field Mezőnév (referencia változó)
	 * @return bool true, ha táblanévvel volt hívva a tulajdonság, és false ha nem
	 */
	protected function sep_table_field($input,&$table,&$field)
	{
		if(preg_match('/^('.$this->tableName_signal.')(.*)('.$this->table_field_sep.')(.*)$/',$input,$out)) {
			$table = $out[2];
			$field = $out[4];
			return true;
		}
		return false;
	}
}