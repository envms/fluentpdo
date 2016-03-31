<?php

/** Base query exception; is triggered by an incorrect query.
 */
class FluentQueryException extends Exception
{
    private $query;

    public function __construct($query, $message, $code) {
        $this->query = $query;
        parent::__construct($message, $code);
    }

    public function __tostring() {
        return sprintf(
            "Fluent query failed with error %s and message '%s' in %s:%d\nQuery: %s",
            $this->getCode(), $this->getMessage(), $this->getFile(), $this->getLine(), $this->getQuery()
        );
    }

    public function getQuery() {
        return $this->query;
    }
}
