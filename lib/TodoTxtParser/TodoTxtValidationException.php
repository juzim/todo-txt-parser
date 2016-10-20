<?php
namespace TodoTxtParser;

use Exception;

class TodoTxtValidationException extends \Exception {

    /**
     * @var string[]
     */
    private $errors;

    public function __construct($errors) {
        $this->errors = $errors;
        parent::__construct('Invalid todo.txt string: ' . implode('; ', $errors));
    }

    /**
     * @return \string[]
     */
    public function getErrors() {
        return $this->errors;
    }
}
