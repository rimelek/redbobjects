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
 * IncompatibleTable
 *
 * Akkor váltódik ki, ha egy megvalósítandó táblának nincs egyedi elsődleges kulcs mezője.<br />
 * Egyedi azonosító mező használata kötelező minden táblánál
 *
 * @author Takács Ákos (Rimelek), programmer [at] rimelek [dot] hu
 * 
 * @package Rimelek\REDBObjects
 */
class IncompatibleTable extends \Exception {
	function __toString() {
		return __CLASS__ . ": [".$this->code."]: ".$this->message."\n".
				$this->getTraceAsString();
	}
}