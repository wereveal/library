<?php

namespace Wer\GuideBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class MainControllerTest extends WebTestCase
{
    /**
     *  Tests to see if the main page displays the correct data
    **/
    public function testIndex()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/');

        $this->assertTrue($crawler->filter('html:contains("This is a test for now")')->count() > 0);
    }
}
