<?php
namespace TodoTxtParser;

class TodoTxtTask implements \JsonSerializable {

    /**
     * @var string
     */
    private $originalText;

    /**
     * @var string
     */
    private $cleanText;

    /**
     * @var \DateTime
     */
    private $createdAt;

    /**
     * @var string[] indexed by key
     *
     * @Link https://github.com/ginatrapani/todo.txt-cli/wiki/The-Todo.txt-Format#add-on-file-format-definitions
     */
    private $addOns = [];

    /**
     * @var string
     */
    private $priority;

    /**
     * @var string[]
     */
    private $projects = [];

    /**
     * @var string[]
     */
    private $contexts = [];

    /**
     * @var bool
     */
    private $completed = false;

    /**
     * @var \DateTime
     */
    private $completedAt;

    /**
     * @return mixed
     */
    public function getOriginalText() {
        return $this->originalText;
    }

    /**
     * @return \DateTime
     */
    public function getCreatedAt() {
        return $this->createdAt;
    }

    /**
     * @param \DateTime $createdAt
     *
     * @return $this
     */
    public function setCreatedAt($createdAt) {
        $this->createdAt = $createdAt;
        return $this;
    }

    /**
     * @param string $originalText
     *
     * @return $this
     */
    public function setOriginalText($originalText) {
        $this->originalText = $originalText;
        return $this;
    }

    /**
     * @param string $priority
     *
     * @return TodoTxtTask
     */
    public function setPriority($priority) {
        $this->priority = $priority;

        return $this;
    }

    /**
     * @return string
     */
    public function getPriority() {
        return $this->priority;
    }

    /**
     * @param null $date
     *
     * @return TodoTxtTask
     */
    public function setCompleted($date = null) {
        $this->completed = true;
        $this->setCompletedAt(new \DateTime($date));

        return $this;
    }

    /**
     * @return TodoTxtTask
     */
    public function setNotCompleted() {
        $this->completed = false;
        $this->setCompletedAt(null);

        return $this;
    }

    /**
     * @return boolean
     */
    public function isCompleted() {
        return $this->completed;
    }

    /**
     * @param string $cleanText
     *
     * @return TodoTxtTask
     */
    public function setCleanText($cleanText) {
        $this->cleanText = $cleanText;

        return $this;
    }

    /**
     * @return string
     */
    public function getCleanText() {
        return $this->cleanText;
    }

    /**
     * @param \string[] $contexts
     *
     * @return TodoTxtTask
     */
    public function setContexts($contexts) {
        $this->contexts = $contexts;

        return $this;
    }

    /**
     * @param \string[] $projects
     *
     * @return TodoTxtTask
     */
    public function setProjects($projects) {
        $this->projects = $projects;

        return $this;
    }

    /**
     * @return \string[]
     */
    public function getProjects() {
        return $this->projects;
    }

    /**
     * @return \string[]
     */
    public function getContexts() {
        return $this->contexts;
    }

    /**
     * Specify data which should be serialized to JSON
     *
     * @link http://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return mixed data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     * @since 5.4.0
     */
    public function jsonSerialize() {
        $vars = get_object_vars($this);

        $vars['string'] = $this->buildString();
        return $vars;
    }

    /**
     * @return \string[]
     */
    public function getAddOns() {
        return $this->addOns;
    }

    /**
     * @param $key
     *
     * @return string
     */
    public function getAddOn($key) {
        return isset($this->addOns[$key]) ? $this->addOns[$key] : null;
    }

    /**
     * @param \string[] $addOns
     *
     * @return $this
     */
    public function setAddOns($addOns) {
        $this->addOns = $addOns;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getCompletedAt() {
        return $this->completedAt;
    }

    /**
     * @param \DateTime $completedAt
     *
     * @return $this
     */
    public function setCompletedAt($completedAt) {
        $this->completed = $completedAt != null;
        $this->completedAt = $completedAt;

        return $this;
    }

    public function buildString() {
        $parts = [];

        if ($this->isCompleted()) {
            $parts[] = 'x';
            $parts[] = $this->getCompletedAt()->format('Y-m-d');
        }

        if ($this->getPriority()) {
            $parts[] = '(' . $this->getPriority() . ')';
        }

        if ($this->getCreatedAt()) {
            $parts[] = $this->getCreatedAt()->format('Y-m-d');
        }

        $parts[] = $this->getCleanText();

        foreach ($this->getAddOns() as $name => $value) {
            $parts[] = $name . ':' . $value;
        }

        return implode(' ', $parts);
    }
}
