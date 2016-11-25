<?php
namespace CSDT\PHPUnit\DockerBridgeBundle\TestThread;

use CSDT\CollectionsBundle\Collections\MapCollection;
use CSDT\CollectionsBundle\Collections\ValueCollection;

/**
 * Test info
 *
 * This class is used to store a test with it informations
 *
 * @author Matthieu Vallance <matthieu.vallance@cscfa.fr>
 */
class TestInfo
{
    /**
     * In queue
     *
     * Define the test as queued test, before
     * it execution
     *
     * @var binary
     */
    const IN_QUEUE = 0b00001;

    /**
     * In progress
     *
     * Define the test as running
     *
     * @var binary
     */
    const IN_PROGRESS = 0b000010;

    /**
     * Passed
     *
     * Define the test as already executed
     *
     * @var binary
     */
    const PASSED = 0b000100;

    /**
     * Failed
     *
     * Define the test as failed
     *
     * @var binary
     */
    const FAILED = 0b001000;

    /**
     * Success
     *
     * Define the test as success
     *
     * @var binary
     */
    const SUCCESS = 0b010000;

    /**
     * Skipped
     *
     * Define the test as skipped
     *
     * @var binary
     */
    const SKIPPED = 0b100000;

    /**
     * Method name
     *
     * The test method name
     *
     * @var string
     */
    private $methodName;

    /**
     * Annotation
     *
     * The test method annotations
     *
     * @var MapCollection <ValueCollection>
     */
    private $annotations;

    /**
     * Depend of
     *
     * The test dependence
     *
     * @var ValueCollection
     */
    private $dependOf;

    /**
     * Dependence
     *
     * The test which depends of the current
     *
     * @var ValueCollection
     */
    private $dependence;

    /**
     * Test status
     *
     * The test status as defined by constants
     *
     * @var binary
     */
    private $testStatus;

    /**
     * Construct
     *
     * The default class constructor
     *
     * @param string $methodName  The test method name
     * @param array  $annotations The test anntations
     *
     * @return void
     */
    public function __construct($methodName, array $annotations = array())
    {
        $this->methodName = $methodName;
        $this->parseAnnotationArray($annotations);

        $this->dependence = new ValueCollection();
        $this->dependOf = new ValueCollection();

        $this->testStatus = self::IN_QUEUE;
    }

    /**
     * Parse annotation array
     *
     * Parse the test method annotations
     *
     * @param array $annotations The method annotations
     *
     * @return void
     */
    private function parseAnnotationArray(array $annotations)
    {
        $map = new MapCollection();

        foreach ($annotations as $name => $items) {
            $set = new ValueCollection();
            $set->addAll($items);
            $map->set($name, $set);
        }

        $this->annotations = $map;
    }

    /**
     * Get name
     *
     * Return the current test method name
     *
     * @return string
     */
    public function getName()
    {
        return $this->methodName;
    }

    /**
     * Get annotation
     *
     * Return a given annotation name value
     *
     * @param string $name The annotation name
     *
     * @return ValueCollection
     */
    public function getAnnotation($name)
    {
        if ($this->annotations->has($name)) {
            return $this->annotations->get($name);
        }

        return new ValueCollection();
    }

    /**
     * Add dependency
     *
     * Add a test that depend of the current test
     *
     * @param TestInfo $dependency The test that depend of the current one
     *
     * @return void
     */
    public function addDependency(TestInfo $dependency)
    {
        $this->dependence->add($dependency);
    }

    /**
     * Get dependency
     *
     * Return the test set that depend of the current one
     *
     * @return ValueCollection
     */
    public function getDependency()
    {
        return $this->dependence;
    }

    /**
     * Add depend
     *
     * Add a test that the current one depend
     *
     * @param TestInfo $parent The test that the current one depend
     *
     * @return void
     */
    public function addDepend(TestInfo $parent)
    {
        $this->dependOf->add($parent);
    }

    /**
     * Get dependence
     *
     * Return the test set that the current one depend
     *
     * @return ValueCollection
     */
    public function getDependence()
    {
        return $this->dependOf;
    }

    /**
     * Set status
     *
     * Set the current test status
     *
     * @param binary $status The test status
     *
     * @return void
     */
    public function setStatus($status)
    {
        $this->testStatus = $status;
    }

    /**
     * Get status
     *
     * Return the current test status
     *
     * @return binary
     */
    public function getStatus()
    {
        return $this->testStatus;
    }
}
