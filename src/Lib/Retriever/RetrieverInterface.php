<?php

namespace BisonLab\ContextBundle\Lib\Retriever;

interface RetrieverInterface
{
    /*
     * What do we support?
     * You'd better support all objects within the system you say you support.
     */
    public function getSupports(): array;

    /*
     * The meat of this.
     */
    public function getExternalDataFromContext($context): mixed;
}
