<?php

use Assert\Assertion;

$text = 'test';

strval($text);
strval($text);strval($text);

strval($text);

function current()
{
    echo 1;
}

class strval extends primary {
    public function execute()
    {
        $this->current();
        parent::current();
    }

    public static function test()
    {
    }
}

class primary {
    public function current()
    {
    }
}

strval::test();
