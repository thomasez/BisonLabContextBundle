<?php

namespace BisonLab\ContextBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class BisonLabContextBundle extends Bundle
{
    public function __toString()
    {
        return 'BisonLabContextBundle';
    }

    public function getPath(): string
    {
        return \dirname(__DIR__);
    }
}
