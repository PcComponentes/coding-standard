<?php
declare(strict_types=1);

function test(): void
{
    try {
        \strval(1);
    } finally {
        \strval(2);
    }
}
