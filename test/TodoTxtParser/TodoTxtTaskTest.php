<?php
namespace TodoTxtParser;

use PHPUnit_Framework_TestCase;

class TodoTxtTaskTest extends PHPUnit_Framework_TestCase {

    /**
     * @dataProvider getTasks
     *
     * @param $expectedString
     * @param $expectedJson
     * @param TodoTxtTask $task
     *
     */
    public function test_buildString($expectedString, $expectedJson, $task) {
        $this->assertEquals($expectedString, $task->buildString());
        $this->assertEquals($expectedJson, json_encode($task));
    }

    public function getTasks() {
        $t1 = new TodoTxtTask();
        $t1->setCleanText('Test string 1');

        $t2 = new TodoTxtTask();
        $t2->setCleanText('Test string 2')->setCreatedAt(new \DateTime('2015-11-22'));

        $t3 = new TodoTxtTask();
        $t3->setCleanText('Test string 3')->setCreatedAt(new \DateTime('2015-11-22'))->setCompleted()->setCompletedAt(new \DateTime('2015-12-23'));

        $t4 = new TodoTxtTask();
        $t4->setCleanText('Test string 4')->setCreatedAt(new \DateTime('2015-11-22'))->setPriority('A')->setCompleted()->setCompletedAt(new \DateTime('2015-12-23'));

        $t5 = new TodoTxtTask();
        $t5->setCleanText('Test string 5 @has +filter')->setProjects(['filter'])->setContexts(['has'])->setCreatedAt(new \DateTime('2015-11-22'))->setPriority('A')->setCompleted()->setCompletedAt(new \DateTime('2015-12-23'));

        $t6 = new TodoTxtTask();
        $t6->setCleanText('Test string 5 @has +filter')->setAddOns(['foo' => 'bar'])->setProjects(['filter'])->setContexts(['has'])->setCreatedAt(new \DateTime('2015-11-22'))->setPriority('A')->setCompleted()->setCompletedAt(new \DateTime('2015-12-23'));

        return [
           ['Test string 1', $this->getJson('t1'), $t1],
           ['2015-11-22 Test string 2', $this->getJson('t2'), $t2],
           ['x 2015-12-23 2015-11-22 Test string 3', $this->getJson('t3'), $t3],
           ['x 2015-12-23 (A) 2015-11-22 Test string 4', $this->getJson('t4'), $t4],
           ['x 2015-12-23 (A) 2015-11-22 Test string 5 @has +filter', $this->getJson('t5'), $t5],
           ['x 2015-12-23 (A) 2015-11-22 Test string 5 @has +filter foo:bar', $this->getJson('t6'), $t6],
        ];
    }

    private function getJson($name) {
        $json = file_get_contents(__DIR__ . '/fixtures/' . $name . '.json');

        $json = str_replace("\n", '', $json);
        return preg_replace('/([\[{:,"])\s+/', '$1', $json);
    }
}
