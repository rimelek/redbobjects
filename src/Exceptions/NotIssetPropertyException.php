<?php
/**
 * R.E. DBObjects
 *
 * @author Takács Ákos (Rimelek), programmer [at] rimelek [dot] hu
 * 
 * @package Rimelek\REDBObjects
 */
namespace Rimelek\REDBObjects\Exceptions;

/**
 * NotIssetPropertyException
 *
 * Kiváltódik, ha egy szükséges tulajdonság értéke nem definiált.
 *
 * @author Takács Ákos (Rimelek), programmer [at] rimelek [dot] hu
 * 
 * @package Rimelek\REDBObjects
 */
class NotIssetPropertyException extends \Exception {
	function __toString() {
		return __CLASS__ . ": [".$this->code."]: ".$this->message."\n".
				$this->getTraceAsString();
	}
}
