<?php
/**
 * R.E. DBObjects
 *
 * @author Takács Ákos (Rimelek), programmer [at] rimelek [dot] hu
 * @copyright Copyright (C) 2010, Takács Ákos
 * @version 2.1
 * @package REDBObjects
 */

/**
 * NotIssetPropertyException
 *
 * Kiváltódik, ha egy szükséges tulajdonság értéke nem definiált.
 *
 * @author Takács Ákos (Rimelek), programmer [at] rimelek [dot] hu
 * @copyright Copyright (C) 2010, Takács Ákos
 * @package REDBObjects
 */
class NotIssetPropertyException extends Exception {
	function __toString() {
		return __CLASS__ . ": [".$this->code."]: ".$this->message."\n".
				$this->getTraceAsString();
	}
}
?>
