<?php
/**
 * R.E. DBObjects
 *
 * @author Takács Ákos (Rimelek), programmer [at] rimelek [dot] hu
 * 
 * @package REDBObjects
 */
namespace REDBObjects\Exceptions;

/**
 * NotIssetPropertyException
 *
 * Kiváltódik, ha egy szükséges tulajdonság értéke nem definiált.
 *
 * @author Takács Ákos (Rimelek), programmer [at] rimelek [dot] hu
 * 
 * @package REDBObjects
 */
class NotIssetPropertyException extends \Exception {
	function __toString() {
		return __CLASS__ . ": [".$this->code."]: ".$this->message."\n".
				$this->getTraceAsString();
	}
}
