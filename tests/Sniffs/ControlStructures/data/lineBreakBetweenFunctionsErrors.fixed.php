<?php

function test()
{
    $test = 1;
    if ($test === 2) {
        if ($test === 3) {

        }
    }

    $array = [1, 2, 3];
    array_filter(
        $array,
        function ($value) {
            echo $value;
        },
        ''
    );
}

try {
    strval(1);
} catch (Throwable $exception) {
    echo $exception->getMessage();
}

if (true) {
    echo 'ok';
} else {
    echo 'ko';
}

match ('hello') {
    'hello' => 'bye',
    'bye' => 'hello',
    default => null,
};
