<?php

$items = [1, 2, 3, 4];

foreach ($items as $item) {

    continue;
}

foreach ($items as $item) {
    \strval($item);
    continue;
}

foreach ($items as $item) {

    break;
}

foreach ($items as $item) {
    \strval($item);
    break;
}

foreach ($items as $item) {

    exit();
}

foreach ($items as $item) {
    \strval($item);
    exit();
}

foreach ($items as $item) {

    throw new Exception();
}

foreach ($items as $item) {
    \strval($item);
    throw new Exception();
}

foreach ($items as $item) {

    return;
}

foreach ($items as $item) {
    \strval($item);
    return;
}

switch (true) {
    case 1 === 1:

        return 'test';
    default:
        \strval($item);
        return 'default';
}

match ('hello') {
    'hello' => 'bye',
    'bye' => 'hello',
    default => throw new \Exception('hello'),
};
