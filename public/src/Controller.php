<?php

use MongoDB\Client;

class Controller
{
    public function __construct(public Client $client)
    {

    }

    public function dropDbAction()
    {
        $this->client->dropDatabase(get('dropDb'));
    }

}