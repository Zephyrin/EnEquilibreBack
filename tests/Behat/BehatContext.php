<?php

namespace App\Tests\Behat;

use Behat\Behat\Context\Context;
use Symfony\Component\HttpKernel\KernelInterface;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\SchemaTool;

final class BehatContext implements Context
{
    /**
     * An helper for authentication and body acces.
     * 
     * @var ApiContextAuth apiContext
     */
    private $apiContext;

    /** 
     * The kernel interface to get environment variable.
     * 
     * @var KernelInterface
     */
    private $kernel;

    /**
     * Acces to the database for cleaning process.
     *
     * @var EntityManager
     */
    private $em;


    public function __construct(KernelInterface $kernel)
    {
        $this->kernel = $kernel;
        $this->em = $this->kernel
            ->getContainer()->get('doctrine.orm.entity_manager');
    }

    /** @BeforeScenario
     * @param BeforeScenarioScope $scope
     */
    public function gatherContexts(BeforeScenarioScope $scope)
    {
        $this->apiContext = $scope->getEnvironment()
            ->getContext(ApiContextAuth::class);
    }

    /**
     * @Then the application's kernel should use :expected environment
     */
    public function kernelEnvironmentShouldBe(string $expected): void
    {
        if ($this->kernel->getEnvironment() !== $expected) {
            throw new \RuntimeException();
        }
    }

    /**
     * @Given there are default users
     */
    public function thereAreDefaultUsers()
    {
        /* Create default super admin user into the database then create other users. */
        $this->iAmLoginAs('superadmin');
        $this->logout();
        $this->createUser("admin", "admin", "admin@en_equilibre", "FEMALE");
        $this->logout();
        $this->iAmLoginAs("superadmin");
        $this->apiContext->setRequestBody(
            "{\"roles\": [\"ROLE_AMBASSADOR\"]}"
        );
         $this->apiContext->requestPath("/api/user/2", 'PATCH');
        $this->logout();
        $this->createUser("user", "user", "user@en_equilibre.com", "FEMALE");
        $this->logout();
    }

    private function createUser($name, $password, $email, $gender) {
        $this->apiContext->setRequestBody("{
            \"username\": \"".$name."\",
            \"password\": \"".$password."\",
            \"email\": \"".$email."\"
        }");
        $this->apiContext->requestPath("/api/auth/register", 'POST');
        $this->apiContext->getTokenFromLogin();
    }

    /**
     * @Given I am login as :login
     * @param string $login
     */
     public function iAmLoginAs(string $login) {
        $this->apiContext->setRequestBody(
            '{"username": "'.$login.'", "password": "'.$login.'"}');
        $this->apiContext->requestPath('/api/login_check', 'POST');
        $this->apiContext->getTokenFromLogin();
     }

    /**
     * @logout
     */
     public function logout() {
        $this->apiContext->logout();
     }

    /**
     * @Then the response body has :nbField fields
     */
    public function theResponseBodyHasFields($nbField)
    {
        $this->apiContext->theResponseBodyHasFields($nbField);
    }

    /**
     * @Given clean up database
     * @throws \Doctrine\ORM\Tools\ToolsException
     */
    public function cleanUpDatabase()
    {
        $metaData = $this->em->getMetadataFactory()->getAllMetadata();
        $schemaTool = new SchemaTool($this->em);
        $schemaTool->dropDatabase();
        if (!empty($metaData)) {
            $schemaTool->createSchema($metaData);
        }
    }
}