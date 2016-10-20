<?php
namespace TodoTxtParser;

class TodoTxtParser {

    const ERROR_NO_TEXT_GIVEN = 'No text given';
    const ERROR_NO_TASK_DESCRIPTION = 'Task is missing a description/only has metadata';
    const ERROR_INVALID_DATE_CREATED_AT = 'Invalid created at date';
    const ERROR_INVALID_DATE_COMPLETED_AT = 'Invalid completed at date';
    const ERROR_COMPLETED_AT_MISSING = 'Task is completed but has no completed at date';
    const ERROR_CREATED_AT_IN_THE_FUTURE = 'Created at date is in the future';
    const ERROR_COMPLETED_AT_IN_THE_FUTURE = 'Completed at date is in the future';
    const ERROR_DUPLICATE_ADD_ON_KEY = 'Duplicate add-on key';
    const ERROR_COMPLETED_AT_BEFORE_CREATED_AT = 'Task was completed before it was created';


    private $ignored = [];

    private $errors = [];

    public function ignoreError($error) {
        $this->ignored[] = $error;
    }

    /**
     * @param string $string
     *
     * @return TodoTxtTask()
     * @throws TodoTxtValidationException
     */
    public function buildTaskFromString($string) {
        $task = new TodoTxtTask();
        $this->errors = [];

        if (!$string || empty($string)|| trim($string) == '') {
            $this->addError(self::ERROR_NO_TEXT_GIVEN);
        }

        $projects = [];
        $contexts = [];
        $cleanStringParts = [];
        $addOns = [];

        $string = trim(preg_replace('/\s+/', ' ', $string));

        foreach (explode(' ', $string) as $index => $part) {
            switch ($part) {
                case $index === 0 && $part == 'x':

                    $task->setCompleted();
                    break;
                case $index == 1 && $task->isCompleted() && $this->isDate($part):
                    try {
                        $date = new \DateTime($part);
                    } catch (\Exception $e) {
                        $this->addError(self::ERROR_INVALID_DATE_COMPLETED_AT);
                        break;
                    }

                    if ($this->isDateInTheFuture($part)) {
                        $this->addError(self::ERROR_COMPLETED_AT_IN_THE_FUTURE);
                        break;
                    }

                    $task->setCompletedAt($date);

                    break;
                case true == preg_match('/^\(([A-Z])\)$/', $part, $match) && !$task->getPriority() && !$task->getCreatedAt()
                    && (count($cleanStringParts) === 0):
                    $task->setPriority($match[1]);
                    break;
                case (count($cleanStringParts) === 0)
                        && !$task->getCreatedAt()
                        && $this->isDate($part):
                    try {
                        $date = new \DateTime($part);
                    } catch (\Exception $e) {
                        $this->addError(self::ERROR_INVALID_DATE_CREATED_AT);
                        break;
                    }

                    if ($this->isDateInTheFuture($part)) {
                        $this->addError(self::ERROR_CREATED_AT_IN_THE_FUTURE);
                        break;
                    }

                    if ($task->getCompletedAt() && $task->getCompletedAt() < $date) {
                        $this->addError(self::ERROR_COMPLETED_AT_BEFORE_CREATED_AT);
                        break;
                    }

                    $task->setCreatedAt($date);
                    break;
                case true == preg_match('/([@+])([\w-]+)([!?.:;-_&+]+)?$/', $part, $match):
                    if ($match[1] == '@') {
                        $contexts[] = $match[2];
                    } else {
                        $projects[] = $match[2];
                    }

                    $cleanStringParts[] = $part;
                    break;
                case (true == preg_match('/([a-zA-Z]+):((\w|-)+)/', $part, $match)):

                    if (isset($addOns[$match[1]])) {
                        $this->addError(self::ERROR_DUPLICATE_ADD_ON_KEY);
                    }
                    $addOns[$match[1]] = $match[2];
                    break;
                default:
                    $cleanStringParts[] = $part;
            }
        }

        $string = trim(preg_replace('/\s{2,}/', ' ', $string));
        $task->setOriginalText($string);

        if (count($cleanStringParts) == 0) {
            $this->addError(self::ERROR_NO_TASK_DESCRIPTION);
        }

        if ($task->isCompleted() && !$task->getCompletedAt()) {
            $this->addError(self::ERROR_COMPLETED_AT_MISSING);
        }

        if (count($this->errors) > 0) {
            throw new TodoTxtValidationException($this->errors);
        }

        $task->setProjects(array_values(array_unique($projects)));
        $task->setContexts(array_values(array_unique($contexts)));
        $task->setCleanText(implode(' ', $cleanStringParts));
        $task->setAddOns($addOns);

        return $task;
    }

    private function isDate($part) {
        return preg_match('/^\d{4}-\d{2}-\d{2}$/', $part);
    }

    /**
     * @param $part
     *
     * @return bool
     */
    private function isDateInTheFuture($part) {
        return $part > date('Y-m-d');
    }

    /**
     * @param string $error
     */
    private function addError($error) {
        if (!in_array($error, $this->ignored)) {
            $this->errors[] = $error;
        }
    }
}
