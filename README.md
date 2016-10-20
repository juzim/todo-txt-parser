# Todo.Txt Parser

### A [todo.txt](http://todotxt.com) parsing and validation library for PHP

**Installation**

`$ composer require "juzim/todo-txt-parser:~1.0"`


**Usage**

```
$parser = new TodoTxtParser();
$task = $parser->buildTaskFromString('x 2016-11-22 2016-11-20 (A) due:2016-09-05 Tell @me to write the @README for +todoTxtParser');
```

This will return a TodoTxtTask object that includes all values ([see todo.txt rules](https://github.com/ginatrapani/todo.txt-cli/wiki/The-Todo.txt-Format)):

```
Get raw string:
$task->getRawString()   // 'x 2016-11-22 (A) 2016-01-02 Tell @me to write the @README for +todoTxtParser due:2016-09-05'

Get clean string (only readable text): 
$task->getCleanText()   // 'Tell @me to write the @README for +todoTxtParser'

Get metadata:
$task->isCompleted()    // starts with 'x '     ->  true
$task->getCompletedAt() // YYYY-MM-DD after 'x' ->  DateTime/2016-11-22
$task->getPriority()    // (A-Z)                ->  'A'
$task->getCreatedAt()   // YYYY-MM-DD           ->  DateTime/2016-01-02
$task->getProjects()    // +                    ->  ['todoTxtParser']
$task->getContexts()    // @                    ->  ['me', 'README']
$task->getAddOns()      // {key}:{text}         ->  ['due' => '2016-09-05']
```

The following errors get collected:

```
ERROR_NO_TEXT_GIVEN                     -> No text given
ERROR_NO_TASK_DESCRIPTION               -> Task is missing a description/only has metadata
ERROR_INVALID_DATE_CREATED_AT           -> Invalid created at date
ERROR_INVALID_DATE_COMPLETED_AT         -> Invalid completed at date
ERROR_COMPLETED_AT_MISSING              -> Task is completed but has no completed at date
ERROR_CREATED_AT_IN_THE_FUTURE          -> Created at date is in the future
ERROR_COMPLETED_AT_IN_THE_FUTURE        -> Completed at date is in the future
ERROR_DUPLICATE_ADD_ON_KEY              -> Duplicate add-on key
ERROR_COMPLETED_AT_BEFORE_CREATED_AT    -> Task was completed before it was created
```

If at least one of those errors occurred, a `TodoTxtValidationException` is thrown at the end which includes all collected errors in readable format.
Specific errors can be ignored with $parser->ignoreError(TodoTxtParser::ERROR_NAME).

**Notes**

* Metadata that doesn't match one of the rules becomes part of the description
* Leading/trailing whitespace and duplicate spaces get removed
* Add-Ons are not part of the clean text (not sure if that's correct thou)
* Anything goes for the description text, so remember to filter/escape accordingly 
* For now there are no rules for projects, contexts and add-ons other then the regex, but some restrictions like valid characters or length might be added later on
