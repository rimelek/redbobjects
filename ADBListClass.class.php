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
require_once dirname(__FILE__).'/ADBClass.class.php';

/**
 * Lista osztályok tulajdonságai.
 * 
 * Minden lista lapozható
 *
 * @author Takács Ákos (Rimelek), programmer [at] rimelek [dot] hu
 * @copyright Copyright (C) 2010, Takács Ákos
 * @license http://www.gnu.org/licenses/lgpl.html
 * @package REDBObjects
 */
abstract class ADBListClass extends ADBClass
{
	/**
	 * Első oldalra ugró link szövege
	 * @var string
	 */
	protected $startLinkText = "&laquo;&laquo;";

	/**
	 * Az utolsó oldalra ugró link szövege
	 * @var string
	 */
	protected $endLinkText = "&raquo;&raquo;";

	/**
	 * Az előző oldalra ugró link szövege
	 * @var string
	 */
	protected $prevLinkText = 'Előző';

	/**
	 * A következő oldalra ugró link szövege
	 * @var string
	 */
	protected $nextLinkText = 'Következő';

	/**
	 * Az oldal linkek megjelenítésének sablonja
	 * {page} helyére kerül az oldalszám
	 * @var string
	 */
	protected $pageLinkPattern = '[{page}. oldal]';

	/**
	 * A változó neve, amiben az aktuális oldalszám lesz a $_GET tömbben.
	 * @var string
	 */
	protected $pagevar	= 'page';

	/**
	 * A lekérdezésre illeszkedő rekordok száma
	 *
	 * @var int
	 */
	protected $count=null;

	/**
	 * @return int A lapozandó adatokhoz szükséges maximális oldalszám
	 */
	abstract public function maxpage();

	/**
	 * Aktuális oldalszám szerint lista inicializálása
	 *
	 * @see $pagevar
	 * @param $sql
	 * @param int $page Elhagyása esetén az url-ből veszi az oldalszámot.
	 */
	abstract public function page($sql, $limit, $page=null);

	/**
	 *
	 * @param string $value A {@link $pagevar} új értéke
	 * @return string A {@link $pagevar} aktuális vagy új értéke
	 */
	public function pagevar($value = null)
	{
		if ($value !== null)
		{
			$this->pagevar = (string)$value;
		}
		return $this->pagevar;
	}

	/**
	 *
	 * @param string $value {@link $startLinkText} új értéke
	 * @return string {@link $startLinkText}  aktuális vagy új értéke
	 */
	public function startLinkText($value=null)
	{
		if ($value !== null)
		{
			$this->startLinkText = (string)$value;
		}
		return $this->startLinkText;
	}

	/**
	 *
	 * @param string $value {@link $prevLinkText} új értéke
	 * @return string {@link $prevLinkText} aktuális vagy új értéke
	 */
	public function prevLinkText($value=null)
	{
		if ($value !== null)
		{
			$this->prevLinkText = (string)$value;
		}
		return $this->prevLinkText;
	}

	/**
	 *
	 * @param string $value {@link $nextLinkText} új értéke
	 * @return string {@link $nextLinkText} aktuális vagy új értéke
	 */
	public function nextLinkText($value=null)
	{
		if ($value !== null)
		{
			$this->nextLinkText = (string)$value;
		}
		return $this->nextLinkText;
	}

	/**
	 *
	 * @param string $value {@link $endLinkText} új értéke
	 * @return string {@link $endLinkText} aktuális vagy új értéke
	 */
	public function endLinkText($value=null)
	{
		if ($value !== null)
		{
			$this->endLinkText = (string)$value;
		}
		return $this->endLinkText;
	}

	/**
	 *
	 * @param string $value {@link $pageLinkPattern} új értéke
	 * @return string {@link $pageLinkPattern} aktuális vagy új értéke
	 */
	public function pageLinkPattern($value=null)
	{
		if ($value !== null)
		{
			$this->pageLinkPattern = (string)$value;
		}
		return $this->pageLinkPattern;
	}

	/**
	 * Aktuális oldalszám
	 * @return int
	 */
	public function pageNumber()
	{
		$pageNumber = isset($_GET[$this->pagevar]) ? abs((int)$_GET[$this->pagevar]) : 1;

		if ($pageNumber > ($maxPage = $this->maxpage()))
		{
			$pageNumber = $maxPage;
		}
		return $pageNumber;
	}

	/**
	 * Legenerálja és visszaadja a lapozó linkeket
	 *
	 * @param int $numberOfLinks Megjelenítendő linkek maximális száma
	 * @return string A lapozó linkek
	 */
	public function pageLinks($numberOfLinks)
	{
		$page = $this->pageNumber();
		$maxpage = $this->maxpage();

		//lapozó linkek
		$numberOfLinks = (int)$numberOfLinks;
		$numberOfLinks2 = floor($numberOfLinks / 2);
		$linkoffset = ($page > $numberOfLinks2) ? $page - $numberOfLinks2 : 0;
		$linkend = $linkoffset+$numberOfLinks;

		if ($maxpage - $numberOfLinks2 < $page)
		{
			$linkoffset = $maxpage - $numberOfLinks;
			if ($linkoffset < 0)
			{
				$linkoffset = 0;
			}
			$linkend = $maxpage;
		}
		$pageLinks = '';
		$pagevar = $this->pagevar();

		if (trim($this->startLinkText()) != '')
		{
			$url_start = self::setUrl(array($pagevar=>1));
			$pageLinks .= "<a href='".$url_start."'>".$this->startLinkText()."</a> &nbsp; ";
		}
		if (trim($this->prevLinkText()) != '')
		{
			$url_prev = self::setUrl(array($pagevar=>$page-1));
			$pageLinks .= ($page > 1)
				? "<a href='".$url_prev."'>".$this->prevLinkText()."</a>"
				: $this->prevLinkText();
			$pageLinks .= " &nbsp; ";
		}

		for ($i=1+$linkoffset; $i <= $linkend; $i++)
		{
			$url = self::setUrl(array($pagevar=>$i));
			$class = ($i == $page) ? "pagelink current" : "pagelink";
			$pageLinkText = str_replace('{page}',$i,$this->pageLinkPattern());
			$pageLinks .= "<a href='".$url."' class='$class'>".$pageLinkText."</a> &nbsp; ";
		}
		if (trim($this->nextLinkText()) != '')
		{
			$url_next = self::setUrl(array($pagevar=>$page+1));
			$pageLinks .= ($page < $maxpage)
				? "<a href='".$url_next."'>".$this->nextLinkText()."</a>"
				: $this->nextLinkText();
			$pageLinks .= " &nbsp; ";
		}
		if (trim($this->endLinkText()) != '')
		{
			$url_end = self::setUrl(array($pagevar=>$this->maxpage()));
			$pageLinks .= "<a href='".$url_end."'>".$this->endLinkText()."</a> &nbsp; ";
		}
		return $pageLinks;
	}

	/**
	 * A lapozáshoz az url beállítása
	 *
	 * @param array $vars Beállítandó $_GET változók asszociatív tömbje.
	 * @param string $url Az alap url, amihez hozzá kell adni a változókat.
	 *				Elhagyása esetén a REQUEST_URI lesz.
	 * @param string $sep query string-ben az eválasztó jel. Alapértelmezett &amp;amp;
	 * @return string
	 */
	public static function setUrl($vars, $url=null,$sep=null)
	{
		if ($sep === null)
		{
			$sep = '&amp;';
		}
		if ($url === null)
		{
			$url = $_SERVER['REQUEST_URI'];
		}
		$parse = parse_url($url);
		$file = $parse['path'];
		$get = array();
		if (isset($parse['query']))
		{
			parse_str($parse['query'],$get);
		}
		foreach ($vars as $key => &$value)
		{
			$get[$key] = $value;
		}
		$ret = $file;
		$query = http_build_query($get, '', $sep);
		if ($query)
		{
			$ret  .= '?'.$query;
		}
		return $ret;
	}

}
?>
