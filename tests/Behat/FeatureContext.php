<?php

namespace App\Tests\Behat;

use PHPUnit\Framework\Assert;
use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\TableNode;
use Symfony\Component\HttpKernel\KernelInterface;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\SchemaTool;
use Exception;

final class FeatureContext implements Context
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

    private static $hasOwnServer = false;

    public function __construct(KernelInterface $kernel)
    {
        $this->kernel = $kernel;
        $this->em = $this->kernel
            ->getContainer()->get('doctrine.orm.entity_manager');
    }

    /**
     * Launch the symfony server for test.
     * @BeforeSuite
     *
     * @return void
     */
    public static function server() {
        $tab_output = [];
        $hasServer = false;
        exec('symfony server:status', $tab_output, $ret);
        foreach($tab_output as $output) {
            if (strpos($output, "Listening on") !== false) {
                $hasServer = true;
                break; 
            }
        }
        if($hasServer === false)
        {
            exec('APP_ENV=test symfony server:start -d', $tab_output, $ret);
            if($ret === 0) {
                FeatureContext::$hasOwnServer = true;
                print_r("Server start");
            }
        }
    }

    /**
     * @AfterSuite
     *
     * @return void
     */
    public static function stopServer() {
        if(FeatureContext::$hasOwnServer) {
            exec('symfony server:stop');
            print_r("server stop");
        }
    }
    /**
     * @BeforeFeature
     *
     * @return void
     */
    public static function prepare()
    {
        $tab_output = [];
        exec('php bin/console doctrine:database:drop --if-exists --force -n -e test', $tab_output, $ret);
        if($ret != 0) {
            throw new Exception("Unable to delete the database.");
        }
        exec('php bin/console doctrine:database:create --if-not-exists -n -e test', $tab_output, $ret);
        if($ret != 0) {
            throw new Exception("Unable to create the database.");
        }
        exec('php bin/console doctrine:migrations:migrate -n -e test', $tab_output, $ret);
        if($ret != 0) {
            throw new Exception("Unable to apply migrations");
        }
        print_r('Clearing database done.');
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
        /* $this->apiContext->setRequestBody(
            "{\"roles\": [\"ROLE_AMBASSADOR\"]}"
        );
         $this->apiContext->requestPath("/api/en/user/2", 'PATCH'); */
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
      * @Given I am login with expired token
      */
     public function iAmLoginWithExpiredToken() {
        $this->apiContext->iAmLoginWithExpiredToken();
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
            foreach(array_keys($object) as $key) {
                if(strpos($key, "#") === 0) {
                    $datas = explode(",", $object[$key]);
                    $object[substr($key, 1)] = [];
                    foreach($datas as $lang) {
                        $lang_trad = explode(":", $lang);
                        $object[substr($key, 1)][trim($lang_trad[0])] = trim($lang_trad[1]);
                    }
                    unset($object[$key]);
                }
            }
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

    /**
     * @Given the media folder is unwritable
     */
    public function theMediaFolderIsUnwritable()
    {
        chmod("public/media", 0444);
    }

    /**
     * @Given the media folder is writable
     */
    public function theMediaFolderIsWritable()
    {
        chmod("public/media", 0766);
        print_r("Warning: if it not works. Please run sudo public/media 0744 to set the writable works again.");
    }
}
