<?php

namespace App\Tests\Behat;

use Imbo\BehatApiExtension\Context\ApiContext;
use PHPUnit\Framework\Assert;

class ApiContextAuth extends ApiContext
{
    protected $token;
    private $savedValue = [];

    public function getTokenFromLogin()
    {
        $this->token = '';
        $this->requireResponse();
        $body = $this->getResponseBody();
        if (isset($body->token))
            $this->token = "Bearer $body->token";
    }

    public function iAmLoginWithExpiredToken()
    {
        $this->token = "Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJpYXQiOjE1OTI4NTgyMDYsImV4cCI6MTU5Mjg2MTgwNiwicm9sZXMiOlsiUk9MRV9TVVBFUl9BRE1JTiIsIlJPTEVfVVNFUiJdLCJ1c2VybmFtZSI6InN1cGVyYWRtaW4ifQ.CQ-xSMMUFtiYzeb8-3ftAKEHqysg9XM09tgIlqILMdBcVH0THfjJJYDq_ibYJCH6wIHp2MHW4FdqswyqTJQAYp83foc4hd9lvB8txgiNIrERuMXlYAl4PH4Mpl8_cfEOx3Q_Orlp2YxCCmhvj5JuGJ88ohgMdHItVkKDP3lj0TgJkWWj2SMJSSMT0YIe0UlrDunCQ_6exag8YYuZ1HCLPWP_vfuz11y_TOORpjIkl5dpovZXyD56uaAqX3D9xqPpvZUZMe3B7dpv3A7cNSXBqKKJC1Ame_wlMHk8P-ZN2YRHEhurQVDkodxMzYh_tW41yV8ApzV_bF6yHTzkOKvqEME7ZvbxKNTH52iR9XSOVdEOGYS03ICPR4evxTGues5xSyOavBm-VxS4HZpbJgULje_x5exuBX5PO7Nykky-qGkhaxV6ZjApR3YDfk3xifClaZ9q0-2oMjwf83u_tILeVUUFOxR9EyFMs85OVgNbfcf-QdzvJoIHOjRIAEZXJrIpAcYvVRj6yMS8qnDcA3XWhTXCcVmD5SlYrdENmuZ55OCIBLLLTx8XID1bX3BwcTRWpDLMIkW4JU5ZHLFavdyQyFIVm8iHUVc6cM-quqqwk2pkDkpWWCzxMadgWDNJehzjKYgqYxV4-TTTqgg4qB1xL5B4sAVc-1WF4xB06R9DDjQ";
    }

    public function requestPath($path, $method = null)
    {
        $this->setRequestHeader("Authorization", "{$this->token}");

        return parent::requestPath($path, $method);
    }

    public function logout()
    {
        $this->setRequestHeader('Authorization', '');
        $this->token = "Bearer ";
    }

    public function theResponseBodyHasFields($nbField)
    {
        $this->requireResponse();

        $body = $this->getResponseBody();
        Assert::assertEquals($nbField, count((array) $body));
    }

    public function thenISaveThe($value)
    {
        $this->requireResponse();
        $body = $this->getResponseBody();
        $this->savedValue[$value] = ((array) $body)[$value];
    }

    public function thenISaveTheAs($value, $name)
    {
        $this->requireResponse();
        $body = $this->getResponseBody();
        $this->savedValue[$name] = ((array) $body)[$value];
    }

    public function getSavedValue($field)
    {
        return $this->savedValue[$field];
    }
}
