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
 * @ignore
 */
require_once dirname(__FILE__).'/../ADBListClass.class.php';

/**
 * @ignore
 */
require_once dirname(__FILE__).'/../IIsDBListClass.class.php';

/**
 * @ignore
 */
require_once dirname(__FILE__).'/../IIsDBClass.class.php';

/**
 * @ignore
 */
require_once dirname(__FILE__).'/IsMySQLClass.class.php';

/**
 * Lista osztály
 *
 * Jelen verzió már tartalmazza a listakezeléshez szükséges legalapvetőbb
 * eljárásokat, tulajdonságokat. <br />
 * Ebbe beleértendők a következők:<br />
 * <ul>
 *	<li>Lista inicializálása: {@link init()}</li>
 *	<li>Egy oldal inicializálása: {@link page()}</li>
 *	<li>Új elem felvétele: {@link add()}</li>
 *	<li>Rekord törlése: {@link delete()}</li>
 *	<li>Listán való iteráció (foreach ciklus használata az objektumon)</li>
 * </ul>
 *
 * <code>
 * require_once 'REDBObjects/REDBObjects.class.php';
 * REDBObjects::uses('mysql');
 *
 * mysql_connect('localhost', 'root', 'password');
 * mysql_select_db('teszt');
 *
 * $list->tableName_signal = 'T_'; //Ez az alapértelmezett is
 * $list->table_field_sep = '__'; //az alapértelmezett az egy darab _ jel
 *
 * $list->page('teszt', 10);
 * foreach ($list as $key => $object)
 * {
 *	print $object->T_teszt__id.', '.$object->field.'<br />'.PHP_EOL;
 *	if ($object->id == 2)
 *	{
 *		$object->field = 'P';
 *		$object->update();
 *	}
 * }
 * </code>
 *
 * @property string $tableName_signal Táblanevet jelző prefix
 * @property string $table_field_sep Táblanevet és mezőnevet elválasztó jel
 *
 * @author Takács Ákos (Rimelek), programmer [at] rimelek [dot] hu
 * @copyright Copyright (C) 2010, Takács Ákos
 * @license http://www.gnu.org/licenses/lgpl.html
 * @package REDBObjects
 */
class IsMySQLListClass extends ADBListClass implements IIsDBListClass, Iterator, ArrayAccess
{
	/**
	 * Lista elemeit tároló tömb
	 *
	 * @var IIsDBClass[]
	 */
	public $records=array();

	/**
	 * A listában tárolandó objektum típus
	 *
	 * @var string
	 */
	protected $className = '';

	/**
	 * A lekérdezésre illeszkedő rekordok száma
	 *
	 * @var int
	 */
	protected $count=null;

	/**
	 * Adott limit mellett a megjelenítéshez szükséges oldalak minimális száma
	 *
	 * @var int
	 */
	protected $pages=1;

	/**
	 * Aktuális oldal száma, ha már lekértünk egy oldalt.
	 * @var int
	 */
	protected $page=null;

	/**
	 * Aktuális tábla index az iterációnál
	 *
	 * @var int
	 * @ignore
	 */
	protected $itTableIndex=0;
	
	/**
	 * Aktuális mezőindex az iterációnál
	 *
	 * @var int
	 * @ignore
	 */
	protected $itFieldIndex=0;

	/**
	*	A megvalósítandó táblák listája.
	*	Még a megváltoztatás előtt. (Aliasok)
	*
	*	@var array
	*/
	public $defaultTablelist=array();

	/**
	 * Védett táblák listája. Ezekből a táblákból nem törölhető
	 * rekord a {@link delete()} metódussal.
	 * @var array
	 */
	protected $protectedTables = array();

	/**
	 * Az sql kód from utáni része, amivel inicializálva lett a lista
	 * @var string
	 */
	protected $sql = "";

	/**
	 * Limit, ami az inicializáláskor lett megadva
	 * @var int
	 */
	protected $limit = 0;

	/**
	 * Konstruktor
	 *
	 * @param array $tablelist
	 * @param string $className
	 */
	function __construct($tablelist,$className='IsMySQLClass')
	{
		$this->tablelist = $this->defaultTableList = $tablelist;
		$this->className = $className;
		//lekérdezi a lehetséges mezőneveket.
		$this->getFields();

	}

	/**
	 *
	 * @ignore
	 * @return int
	 */
	public function count()
	{
		return count($this->records);
	}

	/**
	 *
	 * @param string $sql FROM utáni sql kód (Limit nélkül)
	 * @param int $limit limit
	 */
	protected function setPagesAndCount($sql, $limit)
	{
		$this->sql = $sql;
		$this->limit = $limit;
		//Lekérdezésre illeszkedő sorok száma limit nélkül
		$count = mysql_fetch_row( mysql_query( "select count(*) from $sql" ) );
		$this->count=$count[0];
		//Ha megadtuk a limitet
		if ($limit) {
			//Az oldalszámot ki kell számolni
			$this->pages=ceil($this->count / $limit);
		}
	}

	/**
	 * Aktuális oldalszám szerint lista inicializálása
	 *
	 * @see $pagevar
	 * @param $sql FROM utáni sql kód
	 * @param $limit Hány rekord legyen egy oldalon
	 * @param int $page Oldalszém. Elhagyása esetén az url-ből veszi az oldalszámot.
	 */
	public function page($sql, $limit, $page=null)
	{
		$this->setPagesAndCount($sql, $limit);
		$page = $page !== null ? (int)$page : $this->pageNumber();
		$maxpage = $this->countPages();
		if ($page <= 0)
		{
			$page = 1;
		}
		else if ($page >= $maxpage)
		{
			$page = $maxpage;
		}

		$offset = ($page-1) * $limit;
		$this->page = $page;
		$this->init($sql, $offset, $limit);
	}

	/**
	 * Lista inicializálása
	 *
	 *
	 * @param $psql FROM utáni sql kód (limit nélkül)
	 * @param $offset Ennyi rekordot ugrik át a listában
	 * @param $limit Ennyi rekordot kérdez le az offset értéktől kezdve
	 */
	public function init($psql,&$offset=0,$limit=0)
	{
		$afields = array();
		$this->records = array();
		//végig kell menni a táblalistánés összeállítani a lekérdezendő mezőlistát
		foreach($this->tablelist as $tableName=>$fieldList)
		{
			//egy táblán belül az összes mező felvétele táblanevet tartalmazó aliassal
			foreach ($fieldList as $field)
			{
				$afields[] = "`$tableName`.`$field` as `$tableName.$field`";
			}
			//ha az elsődleges kulcs mezőt nem kértük le, akkor automatikusan lekérdezéshez adódik
			if (!isset($fieldList[key($this->priKeys[$tableName])]))
			{
				$afields[] = "`$tableName`.`".
					key($this->priKeys[$tableName]).
					"` as `$tableName.".key($this->priKeys[$tableName])."`";

			}
		}

		if ($this->page === null)
		{
			$this->setPagesAndCount($psql, $limit);
		}
		//lekérdezendő mezőnevek megadása
		$fields = implode(",\n",$afields);
		$i=0;
		//sql lekérdezés limit nélkül
		$sql = "select $fields from $psql";

		//Ha ez után az eltolás mértéke nagyobb, mint amennyi rekord van
		if ($offset >= $this->count) {
			//Az eltolást az utolsó oldal első elemére állítja
			$offset = ($this->pages-1)*$limit;
		}
		if ($offset  < 0) {
			$offset = 0;
		}
		if ($this->count==0) {
			$this->pages=1;
		}
		//Ha megadták a limitet
		if ($limit) {
			//Akkor már hozzá lehet fűzni az sql lekérdezéshez a korrigált offsettel
			$sql .= " limit $offset, $limit";
		}

		$query = mysql_query($sql);
		$record=null;

		//Az ADBClass absztrakt osztály tulajdonságainak lekérdezése
		$ref = new ReflectionClass('ADBClass');
		$props = $ref->getDefaultProperties();
		//A listában tárolandó objektumtípus lekérdezése a későbbi példányosítás miatt.
		$IIsDBClass = new ReflectionClass($this->className);
		while($fetch = mysql_fetch_assoc($query)) {
			//objektumok létrehozása
			//Egy példány létrehozása a listában tárolandó objektumtípusból
			$record = $IIsDBClass->newInstance($this->defaultTablelist,true);

			$afields = array();
			//itt az eredményt be kell tölteni a properties tulajdonságba
			foreach($this->tablelist as $tableName=>$fieldList)
			{
				foreach ($fieldList as $field)
				{
					$this->properties[$tableName][$field] = $fetch[$tableName.'.'.$field];
				}
				//az elsődleges kulcsok értékeit külön is tárolni kell egy tömbben
				$this->priKeys[$tableName][key($this->priKeys[$tableName])] =
					$fetch[$tableName.'.'.key($this->priKeys[$tableName])];
			}

			//Az összes olyan tulajdonság beállítása az új objektumnak, ami a lista objektummal közös
			foreach ($props as $prop=>$value) {
				$record->$prop = $this->$prop;
			}
			$record->isSqlQuery = true;
			//Új elem felvétele a listába
			$this->records[] = $record;
		}
	}

	/**
	 * Új rekordok hozzáadása több táblához
	 *
	 * Az add() metódus segítségével egyszerűen hozzáadhatunk az adatbázishoz egy új rekordot.<br />
	 * Csak egy értékekkel feltöltött, példányosított {@link IsMySQLClass} típusú objektumra van szükség.
	 *
	 * <code>
	 * require_once 'REDBObjects/REDBObjects.class.php';
	 * REDBObjects::uses('mysql');
	 *
	 * mysql_connect('localhost', 'root', 'password');
	 * mysql_select_db('teszt');
	 * 
	 * class MyClass extends IsMySQLClass {}
	 * class MyList extends IsMySQLListClass {}
	 * $tablelist = array(
	 *	'table1'=>array('field1','field2'),
	 *	'table2'=>array('field3','field4'));
	 * $object = new MyClass($tablelist);
	 * $object->keyName = 'id';
	 * $list = new MyList($tablelist,'MyClass');
	 * $object->field1 = 'value1';
	 * $object->field3 = 'value3';
	 * // A tulajdonságokat lekérdezhetővé teszi,de nem frissíti az adatbázist,
	 * // mivel nincs is mit
	 * $object->update(false);
	 * $list->add($object);
	 * </code>
	 *
	 * @param IIsDBClass $object Listához adandó objektum
	 * @param bool $append Ha true, nem csak adatbázisba veszi fel az elemet,
	 *				Hanem azonnal hozzáfűzi a recordlistához. Lekérdezhetővé téve.
	 * @return int Autoincrement azonosító
	 */
	function add(IIsDBClass $object, $append=false)
	{
		//ciklusban végigmegy a táblákon
		$props = $object->properties;
		$first = true;
		$last_id = 0;
		foreach ($props as $tableName=>&$fields)
		{
			//újabb ciklusban a mezőkön
			$fieldNames = array();
			$fieldValues = array();
			if (!$first)
			{
				$fields[$object->keyName] = $object->keyValue;
			}
			foreach ($fields as $fieldName => $fieldValue)
			{
				//a mezőneveket és az értékeiket külön tömbbe tölti.
				$fieldNames[] = "`$fieldName`";
				$fieldValues[] = "'$fieldValue'";
			}
			//vesszővel elválasztott formátumba konvertálja az értékek és nevek tömbjeit
			$fieldValues = implode(', ',$fieldValues);
			$fieldNames = implode(', ',$fieldNames);
			//felviszi a táblába az új sort a megadott mezőkkel
			$t = isset($this->tableAliases[$tableName]) ? $this->tableAliases[$tableName] : $tableName;
			mysql_query("insert into `$t` ($fieldNames) values($fieldValues)");
			if ($first)
			{
				$first = false;
				//szükség lehet az elsődleges kulcsra, ha a kapcsoló mező auto_increment
				$last_id = mysql_insert_id();
				//visszaadja a kapott objektumnak a kapcsoló mező értékét, hogy felhasználható legyen az init() metódusban
				$object->keyValue = ( $object->keyName == key($object->priKeys[$tableName])) ? $last_id :
						$fields[$object->keyName];
			}
		}
		$this->count++;
		if ($append)
		{
			$this->records[] = $object;
		}
		return $last_id;
	}

	/**
	 * @return int Lekérdezésre illeszkedő rekordok száma
	 */
	function countRecords()
	{
		return (int)$this->count;
	}

	/**
	 * @return int Lekérdezésre illeszekőd oldalak száma a limitnek megfelelően
	 */
	function countPages()
	{
		return (int)$this->pages;
	}

	/**
	 * {@link countPages()} aliasa
	 * @return int
	 */
	public function maxpage()
	{
		return $this->countPages();
	}

	/**
	 * A táblalista szerint az összes lehetséges mező nevének lekérdezése,
	 * és beállítása tulajdonságnak
	 */
	protected function getFields()
	{
		//a táblákat végigjárva az összes mezőjének nevét lekérdezi, így az indexek mindig léteznek,
		//és az update() metódus akor is kiszűri a nem létező neveket, ha nem volt inicializálva az objektum
		$fields = array();
		foreach($this->tablelist as $tableName => $fieldList)
		{
			$tn = $from = $tableName;
			$exp = explode(' ',$tableName);
			if (count($exp) == 3 and trim(strtolower($exp[1])) == 'as' )
			{
				$from = trim($exp[0]);
				$this->tableAliases[$exp[2]] = $from;
				$alias = trim($exp[2]);
				$this->tablelist[$alias] = $this->tablelist[$tableName];
				unset($this->tablelist[$tableName]);
				$tableName = $alias;
			}
			$query = mysql_query("show columns from `".$from."`");
			$in = false;
			while ($field = mysql_fetch_assoc($query))
			{

				$this->properties[$tableName][$field['Field']] = '';
				//ha az összes mezőt * karakterrel jelöltük
				if (($_in = $in) !== false or $in = (array_search('*',$fieldList) !== false) )
				{ //var_dump($tableName, $in , $_in,$fieldList);
					//akkor törölhető a lista és felvehetők az mezőnevek egyenként
					if ($_in === false)
					{
						$this->tablelist[$tableName] = array();
					} //var_dump($field['Field']);
					$this->tablelist[$tableName][] = $field['Field'];

				}
				//Ha ez az elsődleges kulcs mező, akkor visszaadja a primary_key
				//tulajdonságnak, hogy a kapcsoló mező elsődleges kulcs
				if ($this->keyName == $field['Field'])
				{
					$this->primary_key = $field['Field'];
				}
				//minden elsődleges kulcs mező értékét külön is tároljuk,
				//ezért előtte biztositjuk a helyet neki üres értékkel
				if ($field['Key'] == 'PRI')
				{
					$this->priKeys[$tableName] = array($field['Field']=>'');
				}

			}// var_dump($this->tablelist);
			//Mivel elsődleges kulcs mezőkre mindenképp szükség van,
			//ezért kivételt kell dobni, ha egy tábla nem tartalmaz olyan mezőt.
			if ( !isset($this->priKeys[$tableName]) )
			{
				require_once dirname(__FILE__).'/../exceptions/IncompatibleTable.class.php';
				throw new IncompatibleTable("Egyedi elsődleges kulcs mező használata kötelező! Tábla: ".$tableName);
			}
		}
	}

	/**
	 * Egy táblát le lehet vele védeni, hogy a megvalósított listában egy törlés
	 * esetén abból a táblából ne lehessen semmit sem kitörölni. Hasznos, ha például
	 * egy üzenetlistából törlünk felhasználói azonosító alapján a {@link delete()} -el,
	 * és nem szeretnénk, hogy a felhasználó is törlődjön. 
	 *
	 * @param string $table Levédendő tábla neve. Akár {@link $tableName_signal} -al együtt
	 */
	public function protectTable($table)
	{
		$this->protectedTables[$table] = $table;
	}

	/**
	 *
	 * @see protectTable()
	 * @param string $table Feloldandó tábla neve
	 */
	public function unProtectTable($table)
	{
		unset($this->protectedTables[$table]);
	}

	/**
	 * Rekord törlés mezőn értéke
	 *
	 * <code>
	 * $list->delete('T_tablename_fieldname','value');
	 * </code>
	 *
	 * @see IIsDBListClass::delete()
	 * @return mixed true, ha sikeres a törlés, egyébként a MySQL hibaüzenet
	 */
	public function delete($keyName,$keyValue)
	{
		$table = "";
		$field = "";
		$i=0;
		$keyValue = mysql_real_escape_string($keyValue);
		$keyName = mysql_real_escape_string($keyName);
		//Ha a mezőnevet nem a táblanévvel együtt adtuk meg
		if (!$this->sep_table_field($keyName,$table,$field)) {
			//minden nem védett táblából a megfelelő rekordok törlése
			foreach ($this->tablelist as $t => &$f)
			{
				if (isset($this->protectedTables[$t])) continue;
				$table = (isset($this->tableAliases[$t])) ? $this->tableAliases[$t] : $t;
				mysql_query("delete from `".$table."` where `$keyName` = '$keyValue'");
				$i+=mysql_affected_rows();

			}
			$this->setPagesAndCount($this->sql, $this->limit);
			return $i;
		}
		if (isset($this->protectedTables[$table])) return 0;
		if(isset($this->tableAliases[$table]))
		{
			$table = $this->tableAliases[$table];
		}
		//ha konkrétan egy adott táblából kell törölni
		mysql_query("delete from `$table` where `".$field."`='".$keyValue."'");
		$this->setPagesAndCount($this->sql, $this->limit);
		return mysql_affected_rows();
	}

	/**
	 * Az iteráció alaphelyzetbe állítása
	 * @ignore
	 */
	public function rewind()
	{
		reset($this->records);
	}
	/**
	 * Az aktuális elem visszaadása
	 *
	 * @return IsMySQLClass
	 * @ignore
	 */
	public function current()
	{
		return current($this->records);
	}

	/**
	 * Következő elem visszaadása
	 *
	 * @return IsMySQLClass
	 * @ignore
	 */
	public function next()
	{
		return next($this->records);
	}

	/**
	 * Aktuális elem kulcsának visszaadása
	 *
	 * @return int
	 * @ignore
	 */
	public function key()
	{
		return key($this->records);
	}

	/**
	 * Iteráció kilépési feltétele
	 *
	 * @return bool
	 * @ignore
	 */
	public function valid()
	{
		return ($this->current() !== false);
	}

	/**
	 * Létezik-e egy megadott index a listában
	 *
	 * @param string $index
	 * @return bool
	 * @ignore
	 */
	public function offsetExists($index)
	{
		return isset($this->records[$index]);
	}

	/**
	 * Adott indexű elem lekérdezése
	 *
	 * @param string $index
	 * @return IsMySQLClass
	 * @ignore
	 */
	public function offsetGet($index)
	{
		return $this->records[$index];
	}
	
	/**
	 * Adott indexű elem értékének beállítása
	 *
	 * @param string $index
	 * @param mixed $value
	 * @ignore
	 */
	public function offsetSet($index,$value)
	{
		
	}

	/**
	 * Adott indexű elem érvénytelenítése
	 *
	 * @param string $index
	 * @ignore
	 */
	public function offsetUnset($index)
	{
		
	}

	/**
	 *
	 * @param string $var
	 * @return mixed
	 * @ignore
	 */
	public function  __get($var)
	{
		if ($var == 'tableName_signal' or $var == 'table_field_sep')
		{
			return $this->$var;
		}
		return null;
	}

	/**
	 *
	 * @param string $var
	 * @param mixed $value
	 * @ignore
	 */
	public function __set($var, $value)
	{
		if ($var == 'tableName_signal' or $var == 'table_field_sep')
		{
			$this->$var = $value;
			return;
		}
		$this->$var = $value;
	}
}
?>
