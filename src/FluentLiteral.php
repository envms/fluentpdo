<?php

namespace FluentPDO;

/** SQL literal value
 */
class FluentLiteral
{
	protected $value = '';

	/** Create literal value
	 * @param string $value
	 */
	public function __construct($value)
	{
		$this->value = $value;
	}

	/** Get literal value
	 * @return string
	 */
	public function __toString()
	{
		return $this->value;
	}
}
