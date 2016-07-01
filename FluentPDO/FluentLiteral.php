<?php

/**
 * SQL literal value
 */
class FluentLiteral
{

    /** @var string */
    protected $value = '';

    /**
     * Create literal value
     *
     * @param string $value
     */
    function __construct($value) {
        $this->value = $value;
    }

    /**
     * Get literal value
     *
     * @return string
     */
    function __toString() {
        return $this->value;
    }
    
}
