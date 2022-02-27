<?php
/**
 * R.E. DBObjects
 *
 * @author Takács Ákos (Rimelek), programmer [at] rimelek [dot] hu
 * 
 * 
 * @package Rimelek\REDBObjects
 */
namespace Rimelek\REDBObjects\Exceptions;

/**
 * IteratorEndException
 *
 * Ha a lista objektumon való iteráció túlment az utolsó elemen.
 *
 * @author Takács Ákos (Rimelek), programmer [at] rimelek [dot] hu
 * 
 * @package Rimelek\REDBObjects
 */
class IteratorEndException extends \Exception {
	function __toString() {
		return __CLASS__ . ": [".$this->code."]: ".$this->message."\n".
				$this->getTraceAsString();
	}
}