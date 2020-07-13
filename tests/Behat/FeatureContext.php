<?php

namespace App\Tests\Behat;

use PHPUnit\Framework\Assert;
use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\TableNode;
use Symfony\Component\HttpKernel\KernelInterface;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Behat\Gherkin\Node\PyStringNode;
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
    public static function server()
    {
        $tab_output = [];
        $hasServer = false;
        exec('symfony server:status', $tab_output, $ret);
        foreach ($tab_output as $output) {
            if (strpos($output, "Listening on") !== false) {
                $hasServer = true;
                break;
            }
        }
        if ($hasServer === false) {
            exec('APP_ENV=test symfony server:start -d', $tab_output, $ret);
            if ($ret === 0) {
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
    public static function stopServer()
    {
        if (FeatureContext::$hasOwnServer) {
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
        if ($ret != 0) {
            throw new Exception("Unable to delete the database.");
        }
        exec('php bin/console doctrine:database:create --if-not-exists -n -e test', $tab_output, $ret);
        if ($ret != 0) {
            throw new Exception("Unable to create the database.");
        }
        exec('php bin/console doctrine:migrations:migrate -n -e test', $tab_output, $ret);
        if ($ret != 0) {
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
        $this->iLogout();
        $this->createUser("admin", "admin_admin", "admin@en_equilibre.com");
        $this->iLogout();
        $this->iAmLoginAs("superadmin");
        $this->apiContext->setRequestBody(
            "{\"roles\": [\"ROLE_ADMIN\"]}"
        );
        $this->apiContext->requestPath("/api/en/user/admin", 'PATCH');
        $this->iLogout();
        $this->createUser("merchant", "merchant_merchant", "merchant@en_equilibre.com");
        $this->iLogout();
        $this->iAmLoginAs("superadmin");
        $this->apiContext->setRequestBody(
            "{\"roles\": [\"ROLE_MERCHANT\"]}"
        );
        $this->apiContext->requestPath("/api/en/user/merchant", 'PATCH');
        $this->iLogout();
        $this->createUser("user", "user_user", "user@way_b.com");
        $this->iLogout();
    }

    private function createUser($name, $password, $email)
    {
        $this->apiContext->setRequestBody('{
            "username": "' . $name . '",
            "password": "' . $password . '",
            "email": "' . $email . '"
        }');
        $this->apiContext->requestPath("/api/en/auth/register", 'POST');
        $this->apiContext->getTokenFromLogin();
    }

    /**
     * @Given I am login as :login
     * 
     * @param string $login can be user, admin or superadmin.
     */
    public function iAmLoginAs(string $login)
    {
        $this->apiContext->setRequestBody(
            '{"username": "' . $login . '", "password": "' . ($login == 'superadmin' ? 'a' : $login . '_' . $login) . '"}'
        );
        $this->apiContext->requestPath('/api/en/auth/login_check', 'POST');
        $this->apiContext->getTokenFromLogin();
    }

    /**
     * @Given I am login with expired token
     */
    public function iAmLoginWithExpiredToken()
    {
        $this->apiContext->iAmLoginWithExpiredToken();
    }
    /**
     * @When I logout
     */
    public function iLogout()
    {
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
     * @Then I save the :value as :name
     */
    public function thenISaveTheAs($value, $name)
    {
        $this->apiContext->thenISaveTheAs($value, $name);
    }

    /**
     * @Then the previous filename should not exists
     *
     * @return void
     */
    public function thePreviousValueShouldNotExists()
    {
        Assert::assertEquals(file_exists($this->apiContext->getSavedValue('filePath')), false);
    }

    /**
     * @Then the response body has :nbField fields
     */
    public function theResponseBodyHasFields($nbField)
    {
        $this->apiContext->theResponseBodyHasFields($nbField);
    }

    /**
     * @Then I request :url with :field using HTTP :type
     */
    public function iRequestWithUsingHTTP($url, $field, $type)
    {
        $this->apiContext->requestPath($url . $this->apiContext->getSavedValue($field), $type);
    }

    /**
     * @Then the request body is with :varName for :varField:
     */
    public function theRequestBodyIsWithFor($varName, $varField, PyStringNode $string)
    {
        $val = $this->apiContext->getSavedValue($varName);
        $data = str_replace($varField, $val, $string);
        $this->apiContext->setRequestBody($data);
    }

    /**
     * @Then the request body is with :varName for :varField and with :varName2 for :varField2:
     */
    public function theRequestBodyIsWithForAndWithFor($varName, $varField, $varName2, $varField2, PyStringNode $string)
    {
        $val = $this->apiContext->getSavedValue($varName);
        $data = str_replace($varField, $val, $string);
        $val = $this->apiContext->getSavedValue($varName2);
        $data = str_replace($varField2, $val, $data);
        $this->apiContext->setRequestBody($data);
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
            foreach (array_keys($object) as $key) {
                if (strpos($key, "#") === 0) {
                    $datas = explode(",", $object[$key]);
                    $object[substr($key, 1)] = [];
                    foreach ($datas as $lang) {
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
        $this->iLogout();
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
    public function unlinkFiles()
    {
        $files = glob("public/media/*"); // get all file names
        foreach ($files as $file) { // iterate files
            if (is_file($file))
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
