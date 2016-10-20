<?php

namespace TodoTxtParser;

use PHPUnit_Framework_TestCase;

class TodoTxtParserTest extends PHPUnit_Framework_TestCase {

    /**
     * @var TodoTxtParser
     */
    private $parser;

    public function setUp() {
        parent::setUp();

        $this->parser = new TodoTxtParser();
    }

    public function testString_hasTrimmableWhitespace_returnTask() {
        $expectedTask = (new TodoTxtTask())
            ->setOriginalText('Test has trimmable spaces')
            ->setCleanText('Test has trimmable spaces');

        $this->assertEquals($expectedTask, $this->parser->buildTaskFromString(' Test has  trimmable   spaces  '));
    }

    public function testString_hasCreatedAt_returnTask() {
        $expectedTask = (new TodoTxtTask())
            ->setOriginalText('2015-11-22 Test has created at')
            ->setCreatedAt(new \DateTime('2015-11-22'))
            ->setCleanText('Test has created at');

        $this->assertEquals($expectedTask, $this->parser->buildTaskFromString('2015-11-22 Test has created at'));
    }

    public function testString_hasMultipleCreatedAt_returnTaskUseFirst() {
        $string = '2015-11-22 2015-11-29 Test has multiple created at';

        $expectedTask = (new TodoTxtTask())
            ->setOriginalText($string)
            ->setCreatedAt(new \DateTime('2015-11-22'))
            ->setCleanText('2015-11-29 Test has multiple created at');

        $this->assertEquals($expectedTask, $this->parser->buildTaskFromString($string));
    }

    public function testString_hasCreatedAtInDescription_returnTaskDoNotSetCreatedAt() {
        $string = 'Test has 2015-11-22 created at';

        $expectedTask = (new TodoTxtTask())
            ->setOriginalText($string)
            ->setCleanText('Test has 2015-11-22 created at');

        $this->assertEquals($expectedTask, $this->parser->buildTaskFromString($string));
    }

    public function testString_hasNoCreatedAtButDate_returnTaskNoCreatedAt() {
        $string = 'Test 2015-11-22 no created at';

        $expectedTask = (new TodoTxtTask())
            ->setOriginalText($string)
            ->setCleanText('Test 2015-11-22 no created at');

        $this->assertEquals($expectedTask, $this->parser->buildTaskFromString($string));
    }

    public function testString_createdAtInTheFuture_throwException() {

        try {
            $this->parser->buildTaskFromString('2017-11-22 Test has created at');
        } catch (TodoTxtValidationException $e) {
            $this->assertError(TodoTxtParser::ERROR_CREATED_AT_IN_THE_FUTURE, $e->getErrors());
            return;
        }

        $this->fail('No exception was thrown');
    }

    public function testString_isCompletedNoCompletedAt_throwException() {

        try {
            $this->parser->buildTaskFromString('x 2017-11-22 Test has completed at');
        } catch (TodoTxtValidationException $e) {
            $this->assertError(TodoTxtParser::ERROR_COMPLETED_AT_IN_THE_FUTURE, $e->getErrors());
            return;
        }

        $this->fail('No exception was thrown');
    }

    public function testString_completedAtInTheFuture_throwException() {

        try {
            $this->parser->buildTaskFromString('x 2017-11-22 Test has completed at');
        } catch (TodoTxtValidationException $e) {
            $this->assertError(TodoTxtParser::ERROR_COMPLETED_AT_IN_THE_FUTURE, $e->getErrors());
            return;
        }

        $this->fail('No exception was thrown');
    }

    public function testString_hasAddOns_returnTask() {
        $rawText = 'due:2016-12-24 Test has type:several add-ons';

        $expectedTask = (new TodoTxtTask())
            ->setOriginalText($rawText)
            ->setAddOns([
                'due' => '2016-12-24',
                'type' => 'several'
            ])
            ->setCleanText('Test has add-ons');

        $this->assertEquals($expectedTask, $this->parser->buildTaskFromString($rawText));
    }

    public function testString_hasDuplicateAddOns_throwException() {
        try {
            $this->parser->buildTaskFromString('test:hasOne test:hasTwo text add-ons');
        } catch (TodoTxtValidationException $e) {
            $this->assertError(TodoTxtParser::ERROR_DUPLICATE_ADD_ON_KEY, $e->getErrors());
            return;
        }

        $this->fail('No exception was thrown');
    }

    public function testString_noString_throwException() {
        try {
            $this->parser->buildTaskFromString(null);
        } catch (TodoTxtValidationException $e) {
            $this->assertError(TodoTxtParser::ERROR_NO_TEXT_GIVEN, $e->getErrors());
            return;
        }
        $this->fail('No exception was thrown');
    }

    public function testString_emptyString_throwException() {
        try {
            $this->parser->buildTaskFromString('  ');
        } catch (TodoTxtValidationException $e) {
            $this->assertError(TodoTxtParser::ERROR_NO_TEXT_GIVEN, $e->getErrors());
            return;
        }
        $this->fail('No exception was thrown');
    }

    public function testString_onlyWhitespaceForText_throwException() {
        try {
            $this->parser->buildTaskFromString(null);
        } catch (TodoTxtValidationException $e) {
            $this->assertError(TodoTxtParser::ERROR_NO_TEXT_GIVEN, $e->getErrors());
            return;
        }
        $this->fail('No exception was thrown');
    }

    public function testDates_invalidCreatedAt_throwException() {
        try {
            $this->parser->buildTaskFromString('2016-01-33 test');
        } catch (TodoTxtValidationException $e) {
            $this->assertError(TodoTxtParser::ERROR_INVALID_DATE_CREATED_AT, $e->getErrors());
            return;
        }
        $this->fail('No exception was thrown');
    }

    public function testDates_invalidCompletedAt_throwException() {
        try {
            $this->parser->buildTaskFromString('x 2016-01-33 test');
        } catch (TodoTxtValidationException $e) {
            $this->assertError(TodoTxtParser::ERROR_INVALID_DATE_COMPLETED_AT, $e->getErrors());
            return;
        }
        $this->fail('No exception was thrown');
    }

    public function testString_onlyMeta_throwException() {

        try {
            $this->parser->buildTaskFromString('x 2016-22-31 2016-11-22 (A)');
        } catch (TodoTxtValidationException $e) {
            $this->assertError(TodoTxtParser::ERROR_NO_TASK_DESCRIPTION, $e->getErrors());
            return;
        }
        $this->fail('No exception was thrown');
    }

    public function test_hasFilter_returnTask() {

        $expectedTask = (new TodoTxtTask())
            ->setOriginalText('Test has @some @contexts for +project')
            ->setContexts(['some', 'contexts'])
            ->setProjects(['project'])
            ->setCleanText('Test has @some @contexts for +project');

        $this->assertEquals($expectedTask, $this->parser->buildTaskFromString('Test has @some @contexts for +project'));
    }

    public function test_hasFilterWithPunctuationMarks_returnTaskStripMarkFromFilterKeepInDescription() {

        $rawText = 'Test has @some @contexts: I love my +project! Yay!';
        $expectedTask = (new TodoTxtTask())
            ->setOriginalText($rawText)
            ->setContexts(['some', 'contexts'])
            ->setProjects(['project'])
            ->setCleanText($rawText);

        $this->assertEquals($expectedTask, $this->parser->buildTaskFromString($rawText));
    }

    public function test_hasSameFilter_mergeDuplicates() {
        $expectedTask = (new TodoTxtTask())
            ->setOriginalText('Test has @same @same @context +dub +dub +project')
            ->setProjects(['dub', 'project'])
            ->setContexts(['same', 'context'])
            ->setCleanText('Test has @same @same @context +dub +dub +project');

        $this->assertEquals($expectedTask, $this->parser->buildTaskFromString('Test has @same @same @context +dub +dub +project'));
    }

    public function test_hasPriority_returnTask() {
        $expectedTask = (new TodoTxtTask())
            ->setOriginalText('(A) test with priority')
            ->setPriority('A')
            ->setCleanText('test with priority');

        $this->assertEquals($expectedTask, $this->parser->buildTaskFromString('(A) test with priority'));
    }

    public function test_hasPriorityHasCreatedAt_returnTask() {
        $rawText = '(A) 2016-01-01 test with priority and created at';
        $expectedTask = (new TodoTxtTask())
            ->setOriginalText($rawText)
            ->setPriority('A')
            ->setCreatedAt(new \DateTime('2016-01-01'))
            ->setCleanText('test with priority and created at');

        $this->assertEquals($expectedTask, $this->parser->buildTaskFromString($rawText));
    }

    public function test_hasPriorityIsCompleted_returnTaskRemovePriority() {
        $string = 'x 2016-01-03 (A) 2016-01-02 completed with priority and dates';
        $expectedTask = (new TodoTxtTask())
            ->setOriginalText($string)
            ->setCreatedAt(new \DateTime('2016-01-02'))
            ->setCompletedAt(new \DateTime('2016-01-03'))
            ->setCompleted()
            ->setPriority('A')
            ->setCleanText('completed with priority and dates');

        $this->assertEquals($expectedTask, $this->parser->buildTaskFromString($string));
    }

    public function test_hasPriorityAfterDescription_returnTaskDoNotSetPriority() {
        $string = 'this is (A) test with priority';
        $expectedTask = (new TodoTxtTask())
            ->setOriginalText($string)
            ->setCleanText('this is (A) test with priority');

        $this->assertEquals($expectedTask, $this->parser->buildTaskFromString($string));
    }

    public function test_hasMultiplePriority_returnTaskUseFirst() {
        $string = '(A) (B) this is test with multiple (C) priorities';
        $expectedTask = (new TodoTxtTask())
            ->setOriginalText($string)
            ->setPriority('A')
            ->setCleanText('(B) this is test with multiple (C) priorities');

        $this->assertEquals($expectedTask, $this->parser->buildTaskFromString($string));
    }

    public function test_isCompleted_returnTask() {
        $expectedTask = (new TodoTxtTask())
            ->setOriginalText('x 2016-01-01 test completed')
            ->setCompleted()
            ->setCompletedAt(new \DateTime('2016-01-01'))
            ->setCleanText('test completed');

        $this->assertEquals($expectedTask, $this->parser->buildTaskFromString('x 2016-01-01 test completed'));
    }

    public function test_isCompletedWithDate_returnTaskSetCompletedDoNotSetCreatedAt() {
        $rawText = 'x 2015-11-22 test completed';
        $expectedTask = (new TodoTxtTask())
            ->setOriginalText($rawText)
            ->setCompleted()
            ->setCompletedAt(new \DateTime('2015-11-22'))
            ->setCleanText('test completed');

        $this->assertEquals($expectedTask, $this->parser->buildTaskFromString($rawText));
    }

    public function test_isCompletedWithDateAndCreationDate_returnTask() {
        $rawText = 'x 2015-11-22 2015-01-02 test completed';
        $expectedTask = (new TodoTxtTask())
            ->setOriginalText($rawText)
            ->setCompleted()
            ->setCompletedAt(new \DateTime('2015-11-22'))
            ->setCreatedAt(new \DateTime('2015-01-02'))
            ->setCleanText('test completed');

        $this->assertEquals($expectedTask, $this->parser->buildTaskFromString($rawText));
    }

    public function test_isCompletedDateBeforeCreationDate_throwException() {
        try {
            $this->parser->buildTaskFromString('x 2015-11-22 2015-12-02 time travel is fun');
        } catch (TodoTxtValidationException $e) {
            $this->assertError(TodoTxtParser::ERROR_COMPLETED_AT_BEFORE_CREATED_AT, $e->getErrors());
            return;
        }
        $this->fail('No exception was thrown');
    }

    public function test_hasSingleXNotOnStart_returnTaskDoNotSetCompleted() {
        $string = 'test x not completed';
        $expectedTask = (new TodoTxtTask())
            ->setOriginalText($string)
            ->setCleanText($string);

        $this->assertEquals($expectedTask, $this->parser->buildTaskFromString($string));
    }

    private function assertError($expectedError, $errors) {
        $this->assertContains($expectedError, $errors, 'Error not in error list: ' . implode(';', $errors));
    }
}
