<?php

namespace CSDT\PHPUnit\DockerBridgeBundle\TestCase;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * Kernel test case
 *
 * This class is used to abstract the container building
 * and removing with dependency support and no container
 * tests
 *
 * @author Matthieu Vallance <matthieu.vallance@cscfa.fr>
 */
class KernelTestCase extends KernelTestCase
{
    use TestCaseTrait;
}