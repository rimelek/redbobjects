<?php
/**
 * R.E. DBObjects
 *
 * @author Takács Ákos (Rimelek), programmer [at] rimelek [dot] hu
 * 
 * @package REDBObjects
 */

/**
 * AmbiguousException
 *
 * Akkor váltódik ki, ha értékadásnál egy tulajdonság több táblához is tartozhat.<br />
 * Olyankor egy jelzősztringnek, és a táblanévnek meg kell előznie a mezőnevet. <br />
 * <code>
 * $this->mail = 'valami@ize.hu'; //hibás
 * $this->T_users_mail = 'valami@ize.hu'; //helyes
 * </code>
 *
 * @author Takács Ákos (Rimelek), programmer [at] rimelek [dot] hu
 * @package REDBObjects
 */
class AmbiguousException extends Exception {
	function __toString() {
		return __CLASS__ . ": [".$this->code."]: ".$this->message."\n".
				$this->getTraceAsString();
	}
} 