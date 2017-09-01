<?php

use Behat\Gherkin\Node\PyStringNode;
use Behat\Gherkin\Node\TableNode;
use PHPUnit_Framework_Assert as Assert;
use Coduo\PHPMatcher\PHPUnit\PHPMatcherAssertions;

trait ApiContext
{
    use PHPMatcherAssertions;

    private $client;

    private $token;

    /**
     * @When I send a POST request to :url with body:
     */
    public function iSendAPostRequestTo($url, PyStringNode $body)
    {
        $this->client = $this
            ->getMink()
            ->getSession()
            ->getDriver()
            ->getClient();

        $server = array('CONTENT_TYPE' => 'application/json');
        if ($this->token) {
            $server['HTTP_Authorization'] = "Bearer {$this->token}";
        }

        $this->response = $this->client->request(
            'POST',
            $url,
            array(),
            array(),
            $server,
            $body->getRaw()
        );
    }

    /**
     * @When I send a GET request to :url
     */
    public function iSendAGetRequestTo($url)
    {
        $this->client = $this
            ->getMink()
            ->getSession()
            ->getDriver()
            ->getClient();

        $server = array('CONTENT_TYPE' => 'application/json');
        if ($this->token) {
            $server['HTTP_Authorization'] = "Bearer {$this->token}";
        }

        $this->response = $this->client->request(
            'GET',
            $url,
            array(),
            array(),
            $server
        );
    }

    /**
     * @When I send a GET request to :url with parameters:
     */
    public function iSendAGetRequestToWithParameters($url, TableNode $table)
    {
        $this->client = $this
            ->getMink()
            ->getSession()
            ->getDriver()
            ->getClient();

        $server = [];
        if ($this->token) {
            $server['HTTP_Authorization'] = "Bearer {$this->token}";
        }

        $parameters = [];
        foreach ($table->getHash() as $row) {
            if (!preg_match('/([\w_-]+)\[([\w_-]+)\]/', $row['parameter'], $matches)) {
                $parameters[$row['parameter']] = $row['value'];
            } else {
                if (!isset($parameters[$matches[1]])) {
                    $parameters[$matches[1]] = [];
                }
                $parameters[$matches[1]][$matches[2]] = $row['value'];
            }
        }

        $this->response = $this->client->request(
            'GET',
            $url,
            $parameters,
            array(),
            $server
        );
    }

    /**
     * @When I send a GET request to :url with headers
     */
    public function iSendAGetRequestToWithHeaders($url, PyStringNode $headers)
    {
        $this->client = $this
            ->getMink()
            ->getSession()
            ->getDriver()
            ->getClient();

        $server = ['CONTENT_TYPE' => 'application/json'];
        foreach ($headers->getStrings() as $line) {
            list($key, $value) = explode(':', trim($line));

            if ($key == 'Authorization' && strpos($value, '$') !== false) {
                $value = "Bearer {$this->token}";
            }

            //convert to server format -> eg. X-Auth-Token -> HTTP_X_AUTH_TOKEN
            $key = 'HTTP_'.strtoupper(str_replace('-', '_', $key));
            $server[$key] = trim($value);
        }

        $this->response = $this->client->request(
            'GET',
            $url,
            array(),
            array(),
            $server
        );
    }

    /**
     * @When I send a DELETE request to :url
     */
    public function iSendADeleteRequestTo($url)
    {
        $this->client = $this
            ->getMink()
            ->getSession()
            ->getDriver()
            ->getClient();

        $server = array('CONTENT_TYPE' => 'application/json');
        if ($this->token) {
            $server['HTTP_Authorization'] = "Bearer {$this->token}";
        }

        $this->response = $this->client->request(
            'DELETE',
            $url,
            array(),
            array(),
            $server
        );
    }

    /**
     * @Then the response status code should be :http_code
     */
    public function theResponseStatusCodeShouldBe($http_code)
    {
        $actual_code = $this->client
            ->getResponse()
            ->getStatusCode();

        Assert::assertEquals($http_code, $actual_code);
    }

    /**
     * @Then the response should contain a :header_name header like :header_value
     */
    public function theResponseShouldContainAHeaderLike($header_name, $header_value)
    {
        Assert::assertTrue($this->client
            ->getResponse()
            ->headers
            ->has($header_name)
        );

        Assert::assertRegexp("!$header_value!i", $this->client
            ->getResponse()
            ->headers
            ->get($header_name)
        );
    }

    /**
     * @Then the response should contain the body:
     */
    public function theResponseShouldContainTheBody(PyStringNode $body)
    {
        $actual = json_decode($this->client->getResponse()->getContent(), true);

        $expected = json_decode($body->getRaw(), true);

        $this->assertMatchesPattern($expected, $actual);
    }

    /**
     * @Then an email is sent to :email with body like:
     */
    public function anEmailIsSentTo($email, PyStringNode $body)
    {
        $profiler = $this->getMink()
            ->getSession()
            ->getDriver()
            ->getClient()
            ->getProfile();

        $collector = $profiler->getCollector('swiftmailer');

        $mail = $collector->getMessages()[0];
        $actual_email = array_keys($mail->getTo())[0];

        Assert::assertEquals($email, $actual_email);
        Assert::assertContains($body->getRaw(), $mail->getBody());
    }

    /**
     * @Then echo the response
     */
    public function echoTheResponse()
    {
        $response = $this->client
            ->getResponse()
            ->getContent();

        echo $response;
    }

    /**
     * @Then the response should contain the :attribute attribute like :pattern
     */
    public function theResponseShouldContainTheAttributeLike($attribute, $pattern)
    {
        $body = json_decode($this->client->getResponse()->getContent());

        if ($attribute == 'token') {
            $this->token = $body->token;
        }

        Assert::assertRegExp($pattern, $body->{$attribute});
    }
}