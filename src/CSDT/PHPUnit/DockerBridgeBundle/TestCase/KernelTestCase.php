<?php

namespace CSDT\PHPUnit\DockerBridgeBundle\TestCase;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase as BaseTestCase;

/**
 * Kernel test case
 *
 * This class is used to abstract the container building
 * and removing with dependency support and no container
 * tests
 *
 * @author Matthieu Vallance <matthieu.vallance@cscfa.fr>
 */
abstract class KernelTestCase extends BaseTestCase
{
    use TestCaseTrait;
}
