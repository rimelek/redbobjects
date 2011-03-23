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
require_once dirname(__FILE__).'/../ADBClass.class.php';

/**
 * @ignore
 */
require_once dirname(__FILE__).'/../IIsDBClass.class.php';

/**
 * Adatbázis táblákat megvalósító osztály
 *
 * Az osztály, ami ezt az osztályt örökli, a konstruktorban megadott tábla, és mezőlista alapján elkészíti
 * saját tulajdonságait a mezőkkel azonos néven. Lehetőség van külön jelölni azt is, hogy pontosan melyik
 * tábla mezőjéről van szó, ha azonos mezőnevek is szerepelnek. Amennyiben ez nem történik meg, értékadáskor
 * az összes táblában megkapja az új értéket az adott nevű mező. Érték lekérdezésekor pedig kivételt dob. <br />
 * Alapesetben ilyenkor a tulajdonságot a 'T_' karakterlánccal kell kezdeni, majd a táblanevet és a
 * mezőnevet egy '_' karakter választja el egymástól. Ez azonban testre szabható. A 'T_' előtag
 * a {@link $tableName_signal} tulajdonságban, míg a táblát és mezőt elválasztó karakterlánc a
 * {@link $table_field_sep} tulajdonságban.<br />
 *
 * <b>Az osztály helyes használata:</b><br />
 * <code>
 * require_once 'REDBObjects/REDBObjects.class.php';
 * REDBObjects::uses('mysql');
 *
 * mysql_connect('localhost', 'root', 'password');
 * mysql_select_db('teszt');
 * 
 * class MyClass extends IsMySQLClass {}
 * $user = new MyClass(array(
 * 	'users'=>array('useremail','username'),
 * 	'profile'=>array('firstname','lastname','useremail')));
 * $user->keyName = 'userid';
 * $user->init(12);  //Valamilyen kapcsoló mező értéke alapján
 * //Vagy sql lekérdezés alapján. Ekkor a második paraméter true kell legyen.
 * //Első paraméter pedig az sql lekérdezés FROM kulcsszó utáni része
 * //$user->init("users left join profile where users.useremail = 'valami@ize.hu'",true);
 * print "A nevem: ".$user->lastname." ".$user->firstname."<br />";
 * $user->lastname = 'Új vezetéknév';
 * $user->firstname 'Új keresztnév';
 * //Vagy akár így is:
 * //$user['lastname'] = 'Új vezetéknév';
 * $user->T_profile_useremail = 'Új publikus emailcím';
 * //Ez a metódus végzi el a frissítést. Enélkül nem kerül adatbázisba az új adat
 * $user->update();
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
class IsMySQLClass extends ADBClass implements IIsDBClass, Iterator, ArrayAccess
{
	/**
	*	Az értékadás feltöltődő asszociatív tömb
	*
	*	@var array $new_properties
	*/
	public $new_properties = array();

	/**
	*	A táblákat összekapcsoló mező értéke
	*
	*	Ezt a mezőt csak az {@link IsDBList} osztály használja, hogy visszaadja az utoljára hozzáadott rekord
	*	összekapcsoló mezőjének értékét.
	*
	*	@var string $keyValue;
	*/
	public $keyValue="";

	/**
	*	Sql kérés volt-e megadva az init() paramétereként.
	*
	*	@var string $isSqlQuery
	*/
	protected $isSqlQuery = false;

	/**
	*	IsMySQLClass osztály konstruktora
	*
	*	@param array $tablelist Két dimenziós tömb. Formátuma:
	* 	array('table1'=>array('field1'[,field2]...)[,'table2'=>array('field1'[,'field2']...)]... )
	*	@param bool $list Ha false, akkor nem kérdezi le a mezőneveket előre.
	*/
	public function __construct($tablelist,$list=false)
	{
		$this->tablelist = $tablelist;
		if (!$list) {
			$this->getFields();
		}
	}

	/**
	 * @return int Objektum tulajdonságainak száma
	 */
	public function count()
	{
		return count($this->properties);
	}

	/**
	 * Objektum inicializálása
	 *
	 * Ez a metódus választja ki a táblákból azt az egy rekordot, amiből létrehozza az objektumtulajdonságokat.
	 *
	 * @param mixed $rowid Azonosító, ami minden táblában azonos értékű
	 * @param bool $bool Ha true, akkor a $rowid lehet sql utasítás ( csak a from kulcsszó utáni rész )
	 */
	public function init($rowid,$bool=false)
	{
		//ha sql lekérdezéssel inicializáljuk az objektumot
		if ($bool) {
			$this->isSqlQuery=true;
			$afields = array();
			//végig kell menni a táblalistánés összeállítani a lekérdezendő mezőlistát
			foreach($this->tablelist as $tableName=>$fieldList)
			{
				//egy táblán belül az összes mező felvétele táblanevet tartalmazó aliassal
				foreach ($fieldList as $field)
				{
					$afields[] = "`$tableName`.`$field` as `$tableName.$field`";
				}
				//ha az elsődleges kulcs mezőt nem kértük le, akkor automatikusan lekérdezéshez adódik
				if (!isset($fieldList[key($this->priKeys[$tableName])])) {
					$afields[] = "`$tableName`.`".
						key($this->priKeys[$tableName]).
						"` as `$tableName.".key($this->priKeys[$tableName])."`";

				}
			}
			$fields = implode(",\n",$afields);
			$i=0;
			$sql = "select $fields from $rowid";
			if($fetch = mysql_fetch_assoc(mysql_query($sql))) {
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
			}
			return;
		}
		//Ez a rész már csak akkor fut le, ha nem sql lekérdezéssel, hanem mező értékkel  inicializáltuk az objektumot

		//A kapcsoló mező nevére szükség van. Ezért ha nincs megadva, kivételt kell dobni
		if (trim($this->keyName) == "") throw new NotIssetPropertyException(__CLASS__."::keyName nincs beállítva!");

		$this->keyValue = $rowid;
		//végig kell menni ciklusban az összes táblán, és lekérdezni a mezők értékeit
		foreach ($this->tablelist as $tableName=>$fieldList) {
			$fields = '`'.implode('`, `',$fieldList).'`';
			$t = (isset($this->tableAliases[$tableName])) ? $this->tableAliases[$tableName] : $tableName;
			$sql = "select $fields from `$t` where `".$this->keyName."` = '$rowid' limit 1";
			if($fetch = mysql_fetch_assoc(mysql_query($sql)))
			{
				$this->properties[$tableName] = $fetch;
			}
		}
	}

	/**
	 * A táblalista szerint az összes lehetséges mező nevének lekérdezése,
	 * és beállítása tulajdonságnak
	 */
	public function getFields()
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
				//ha az összes mezőt * karakterrel jelöltük
				if (($_in = $in) !== false or ($in = array_search('*',$fieldList)) !== false)
				{
					//akkor törölhető a lista és felvehetők az mezőnevek egyenként
					if ($_in === false) $this->tablelist[$tableName] = array();
					$this->tablelist[$tableName][] = $field['Field'];

				}
				if (array_search($field['Field'],$this->tablelist[$tableName]) !== false)
				{
					$this->properties[$tableName][$field['Field']] = '';
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
			}
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
	 * Hozzáférés a mező értékeihez
	 *
	 * Ez a mágikus metódus szabályozza az értékek lekérdezését.
	 *
	 * @param string $var Mezőnév
	 * @ignore
	 */
	public function __get($var)
	{
		$table=''; $field=''; $hit=false;
		//Meg kell állapítani a mező és táblanevet, ha a tulajdonság lekérdezésekor a táblanevet is megadtuk
		$issep = $this->sep_table_field($var,$table,$field);
		//amennyiben tényleg megadták a táblanevet, a meghatározott mezőnevet be kell állítani
		if ($issep)
		{
			$var = $field;
		}
		//Jöhet a táblák pásztázása egyenként
		foreach ($this->tablelist as $tableName=>$fieldList)
		{
			$tbl = $tableName;
			//ha a táblanevet is megadták, de épp nem ez az a tábla,
			//akkor tovább lehet menni a következő táblára
			if ($issep and $tableName != $table) { continue; }
			//ha a lekérdezendő mezők közt van a $var változó értéke
			if (array_key_exists($var,$this->properties[$tableName])) {
				//Akkor ha már volt ilyen mező más táblában is, kivételt dobunk
				//mert nem tudni melyiket kell lekérdezni
				if ($hit)
				{
					require_once dirname(__FILE__).'/../exceptions/AmbiguousException.class.php';
					throw new AmbiguousException(get_class($this)."::$var nem egyértelmű!");
				}
				//a visszatérési értéket be lehet állítani előre
				$return = $this->properties[$tableName][$var]; $hit=true;
			}
		}
		//Ha volt találat, vissza lehet adni
		if ($hit) return $return;
		//Ha nem volt találat, és van ilyen tulajdonsága az objektumnak, akkor azt kell visszaadni
		if (isset($this->$var)) {
			return $this->$var;
		//de ha még az objektumnak sincs ilyen tulajdonsága, akkor kivételt dobunk
		} else {
			require_once dirname(__FILE__).'/../exceptions/NotIssetPropertyException.class.php';
			throw new NotIssetPropertyException(get_class($this)."::$var nem létezik!");
		}
	}

	/**
	 * Mezők értékeinek beállítása
	 *
	 * Ez a mágikus metódus szabályozza értékadáskor, hogy az értékek
	 * a {@link $new_properties} tömbben megfelelő helyre kerüljenek
	 *
	 * @param string $var Mezőnév
	 * @param mixed $value Mező új értéke
	 * @ignore
	 */
	public function __set($var,$value)
	{ 	$table=''; $field=''; $hit=false;
		//Meg kell állapítani a mező és táblanevet, ha értékadáskor a táblanevet is megadtuk
		$issep = $this->sep_table_field($var,$table,$field);
		//amennyiben tényleg megadták a táblanevet, a meghatározott mezőnevet be kell állítani
		if ($issep) {
			$var = $field;
		}
		//Jöhet a táblák pásztázása egyenként
		foreach ($this->tablelist as $tableName=>$fieldList)
		{
			//ha a táblanevet is megadták, de épp nem ez az a tábla,
			//akkor tovább lehet menni a következő táblára
			if($issep and $tableName != $table)
			{
				continue;
			}
			//függetlenül attól, más táblának is volt-e ilyen mezője
			//átadható az érték az új értékeknek
			if (array_key_exists($var,$this->properties[$tableName])) {
				$this->new_properties[$tableName][$var] = $value;
				$hit=true;
				//De ha meg volt adva a táblanév is, akkor kész vagyunk. A program leáll
				if ($issep) {
					return;
				}
			}
		}
		//Ha volt találat, kiléphetünk a programból
		if ($hit) return;
		//ha nem votl találat, de vagy a tableName_signal vagy a table_field_sep változó üres
		if (($var == 'tableName_signal' or $var == 'table_field_sep') and trim($value) == '') {
			//kivételt kell dobni, mert azok nem lehetnek sosem üresek
			require_once dirname(__FILE__).'/../exceptions/NotIssetPropertyException.class.php';
			throw new NotIssetPropertyException(get_class($this)."::$var nem maradhat üresen!");
		}
		//végül az objektum tulajdonságnak kell átadni az értéket, ha másnak nem lehetett
		$this->$var = $value;
	}

	/**
	 * Visszaadja, hogy létezik-e egy objektum tulajdonság, vagy sem
	 *
	 * @param mixed $var Vizsgálandó tulajdonság neve
	 * @return boolean
	 * @ignore
	 */
	public function __isset($var)
	{
		if (isset($this->$var)) {
			return true;
		}

		$table=''; $field=''; $hit=false;
		//Meg kell állapítani a mező és táblanevet, ha a tulajdonság lekérdezésekor a táblanevet is megadtuk
		$issep = $this->sep_table_field($var,$table,$field);
		//amennyiben tényleg megadták a táblanevet, a meghatározott mezőnevet be kell állítani
		if ($issep)
		{
			$var = $field;
		}
		//Jöhet a táblák pásztázása egyenként
		foreach ($this->tablelist as $tableName=>$fieldList)
		{
			//ha a táblanevet is megadták, de épp nem ez az a tábla,
			//akkor tovább lehet menni a következő táblára
			if ($issep and $tableName != $table) { continue; }
			//ha a lekérdezendő mezők közt van a $var változó értéke
			if (isset($this->properties[$tableName]) and
				array_key_exists($var,$this->properties[$tableName])) {
				//Akkor ha már volt ilyen mező más táblában is, kivételt dobunk
				//mert nem tudni melyiket kell lekérdezni
				if ($hit)
				{
					require_once dirname(__FILE__).'/../exceptions/AmbiguousException.class.php';
					throw new AmbiguousException(get_class($this)."::$var nem egyértelmű!");
				}
				//a visszatérési értéket be lehet állítani előre
				$return = array_key_exists($var,$this->properties[$tableName]); $hit=true;
			}
		}
		//Ha volt találat, vissza lehet adni
		if ($hit) return $return;
	}

	/**
	 * Adatok frissítése
	 *
	 * Ha a metódust a false paraméterrel hívjuk meg, Nem kerül be adatbázisba a módosítás,<br />
	 * csak lekérdezhetővé teszi az új értékeket
	 *
	 * @param bool $refreshDB
	 */
	function update($refreshDB=true)
	{
		//Az összes táblán végig kell menni
		foreach ($this->tablelist as $tableName => $fieldList)
		{
			$query=false;
			//ha az új értékek változó nem tömb, ki kell lépni, mert a ciklus tömböt vár
			if (!isset($this->new_properties[$tableName]) or !is_array($this->new_properties[$tableName])) continue;
			//most az új értékeken kell végigmenni
			foreach ($this->new_properties[$tableName] as $fieldName => $value)
			{
				//ha nem volt ilyen mező a properties tulajdonságban, akkor átugorjuk
				if (!array_key_exists($fieldName,$this->properties[$tableName])) { continue; }
				//egyébként escapeljük az értéket
				$this->new_properties[$tableName][$fieldName] = $value;
			}
			//csak azokat az értékeket hagyjuk meg amik különböznek az eredetitől
			$this->new_properties[$tableName] = array_diff_assoc($this->new_properties[$tableName],$this->properties[$tableName]);
			//ha frissíteni is szeretnénk az adatbázist, nem csak az objektumot
			if ($refreshDB) {
				//Akkor ha van mit frissíteni
				if (count($this->new_properties[$tableName])) {
					//el kell dönteni mi az updatelés feltétele. hhez kell a keyValue, és a keyName
					$keyName = ($this->isSqlQuery) ? key($this->priKeys[$tableName]) : $this->keyName;
					$keyValue = ($this->isSqlQuery) ? current($this->priKeys[$tableName]) : $this->keyValue;
					$update = array();
					//most már lehet összeállítani az sql utasítást
					foreach ($this->new_properties[$tableName] as $fieldName=>$value )
					{
						$update[] = "`$fieldName` = '".mysql_real_escape_string($value)."' ";
					}
					$update = implode(', ',$update);
					$t = (isset($this->tableAliases[$tableName])) ? $this->tableAliases[$tableName] : $tableName;
					$sql = "update `$t` set $update where `".$keyName."` = '".$keyValue."'";
					$query = mysql_query($sql);
				}
			}
			//ha frissíteni akartuk az adatbázist és a frissítés sikeres is lett, vagy nem akartuk frissíteni
			if (!$refreshDB or $query)
			{
				//akkor beállítható a lekérdezhető tulajdonságokhoz is az érték
				foreach ($this->new_properties[$tableName] as $fieldName => $value)
				{
					$this->properties[$tableName][$fieldName] = $value;
                }
			}
			//ha frissíteni akartuk az adatbázist, akkor törölni kell az uj értékek listáját
			if ($refreshDB)	{
				$this->new_properties[$tableName] = array();
			}
		}
	}

	/**
	 * Iteráció alaphelyzetbe állítása
	 *
	 * @ignore
	 */
	public function rewind()
	{
		//Az első tábla
		reset($this->properties);
		$this->itTableIndex=1;
		//Első mezője
		reset($this->properties[key($this->properties)]);
		$this->itFieldIndex=1;
	}

	/**
	 * Az aktuális elem visszaadása
	 *
	 * @return mixed
	 * @ignore
	 */
	public function current()
	{
		$k1 = key($this->properties);
		$k2 = key($this->properties[key($this->properties)]);
		if ($k1 === null or $k2 === null)
		{
			require_once dirname(__FILE__).'/../exceptions/IteratorEndException.class.php';
			throw new IteratorEndException();
		}
		return current($this->properties[$k1]);
	}

	/**
	 * Következő elem visszaadása
	 *
	 * @return mixed
	 * @ignore
	 */
	public function next()
	{
		//Ha már nincs több mező az aktuális táblában
		if ($this->itFieldIndex >= count($this->properties[key($this->properties)])) {
			//akkor ha még van több tábla
			if ($this->itTableIndex < count($this->properties)) {
				//Ugrás a következő tábla első mezőjére
				$this->itTableIndex++;
				$this->itFieldIndex=1;
				next($this->properties);
				return $this->properties[key($this->properties)];
			}
		}
		//Egyébként az aktuális táblában a következő mezőre ugrás
		$fields = &$this->properties[ key( $this->properties) ];

		$ret = next($fields);
		$this->itFieldIndex++;
		return $ret;
	}

	/**
	 * Aktuális elem kulcsa
	 *
	 * @return string
	 * @ignore
	 */
	public function key()
	{
		return key($this->properties[key($this->properties)]);
	}

	/**
	 * Iteráció kilépési feltétele
	 *
	 * @return bool
	 * @ignore
	 */
	public function valid()
	{
		require_once dirname(__FILE__).'/../exceptions/IteratorEndException.class.php';
		try
		{
			$this->current();
			return true;
		}
		catch (IteratorEndException $e){}
		return false;
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
		$exists = false;
		foreach ($this->properties as &$table) {
			if($exists = array_key_exists($index,$table)) break;
		}
		return $exists;
	}
	
	/**
	 * Adott indexű elem lekérdezése
	 *
	 * @param string $index
	 * @return mixed
	 * @ignore
	 */
	public function offsetGet($index)
	{
		return $this->$index;
	}

	/**
	 * Adott indexű elem értékének beállítása
	 *
	 * @param string $index
	 * @param mixed $value
	 */
	public function offsetSet($index,$value)
	{
		$this->$index = $value;
	}

	/**
	 * Adott indexű elem érvénytelenítése
	 *
	 * @param string $index
	 */
	public function offsetUnset($index)
	{
		foreach ($this->properties as &$table) {
			if($exists = array_key_exists($index,$table)) {
				unset($table[$index]);
			}
		}
	}
}
?>
