<?php

namespace App\Tests\Behat;

use PHPUnit\Framework\Assert;
use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\TableNode;
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
        $this->createUser("admin", "admin_admin", "admin@en_equilibre", "FEMALE");
        $this->logout();
        $this->iAmLoginAs("superadmin");
        $this->apiContext->setRequestBody(
            "{\"roles\": [\"ROLE_AMBASSADOR\"]}"
        );
         $this->apiContext->requestPath("/api/en/user/2", 'PATCH');
        $this->logout();
        $this->createUser("user", "user_user", "user@en_equilibre.com", "FEMALE");
        $this->logout();
    }

    private function createUser($name, $password, $email, $gender) {
        $this->apiContext->setRequestBody('{
            "username": "'.$name.'",
            "password": "'.$password.'",
            "email": "'.$email.'"
        }');
        $this->apiContext->requestPath("/api/en/auth/register", 'POST');
        $this->apiContext->getTokenFromLogin();
    }

    /**
     * @Given I am login as :login
     * 
     * @param string $login can be user, admin or superadmin.
     */
     public function iAmLoginAs(string $login) {
        $this->apiContext->setRequestBody(
            '{"username": "'.$login.'", "password": "'.($login == 'superadmin' ? 'a' : $login.'_'.$login).'"}');
        $this->apiContext->requestPath('/api/en/auth/login_check', 'POST');
        $this->apiContext->getTokenFromLogin();
     }

    /**
     * @logout
     */
     public function logout() {
        $this->apiContext->logout();
     }

     /**
      * @Then I save the :value
      */
      public function thenISaveThe($value) 
      {
          $this->apiContext->thenISaveThe($value);
      }

      /**
       * @Then the previous filename should not exists
       *
       * @return void
       */
      public function thePreviousValueShouldNotExists()
      {
        Assert::assertEquals(file_exists($this->apiContext->savedValue), false);
      }

    /**
     * @Then the response body has :nbField fields
     */
    public function theResponseBodyHasFields($nbField)
    {
        $this->apiContext->theResponseBodyHasFields($nbField);
    }

    /**
     * @Given there are objects to post to :address with the following details:
     * @param TableNode $objects
     * @throws \Imbo\BehatApiExtension\Exception\AssertionFailedException
     */
    public function thereAreObjectsToPostToWithTheFollowingDetails($address, TableNode $objects)
    {
        $this->iAmLoginAs("admin");
        foreach ($objects->getColumnsHash() as $object) {
            $this->apiContext->setRequestBody(
                json_encode($object)
            );
            $this->apiContext->requestPath(
                $address,
                'POST'
            );
            $this->apiContext->assertResponseCodeIs(201);
        }
        $this->logout();
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
        $this->unlinkFiles();
    }

    /**
     * @Given unlink files
     *
     * @return void
     */
    public function unlinkFiles() {
        $files = glob("public/media/*"); // get all file names
        foreach($files as $file){ // iterate files
        if(is_file($file))
            unlink($file); // delete file
        }
    }
}