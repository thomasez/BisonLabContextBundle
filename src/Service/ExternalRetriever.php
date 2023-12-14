<?php

namespace BisonLab\ContextBundle\Service;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\DependencyInjection\ServiceLocator;
use BisonLab\ContextBundle\Lib\Retriever\RetrieverInterface;

/*
 * Absurdly simple. This is the single point for retrieving external data from 
 * a context. Aka, the main point with the context system.
 *
 * You will need a retriever service per external system. (Which of course can
 * point at the same retriever file.)
 * 
 * I guess it should implement an interface, but with only one single 
 * function? I'm lazy.
 *
 * There is also another annoying point here. And that is when there are more
 * than one object related to the context.
 *
 * I'll chicken out and let the two ends of this decide how they like it.
 * (They should know what they are dealing with and therefor know how to handle
 * one or more returned objects or arrays.)
 *
 * The retriever end also has to remember security. It should ponder a bit
 * about who is asking.
 *
 * And I should ponder about how they can find out.
 *
 * To use this, add the tag "bisonlab.context_owner_retriever" to the directory
 * you have yours.
 */

class ExternalRetriever
{
    private $locator;

    private $retrievers;

    public function __construct(ServiceLocator $locator)
    {
        $this->locator = $locator;
        foreach ($this->locator->getProvidedServices() as $sclass) {
            $retriever = $this->locator->get($sclass);
            // Is the correct thing to keep the instansiated object here or
            // just the class name for later retrieving via the locator?
            if ($retriever instanceOf RetrieverInterface) {
                foreach ($retriever->getSupports() as $system => $objects) {
                    $this->retrievers[$system] ??= [];
                    foreach ($objects as $o) {
                        $this->retrievers[$system][$o] = $retriever;
                    }
                }
            } else {
                // BC, and it will grab a whole system.
                foreach ($retriever->getSystems() as $name) {
                    $this->retrievers[$name] = $retriever;
                }
            }
        }
    }

    public function getExternalDataFromContext($context): mixed
    {
        if (!$system = $context->getSystem())
            return null;
        if (!isset($this->retrievers[$system]))
            return null;
        if (!$object_name = $context->getObjectName())
            return null;
        if (!$external_id = $context->getExternalId())
            return null;
        if (is_array($this->retrievers[$system])) {
            if ($retriever = $this->retrievers[$system][$object_name] ?? null)
                return $retriever->getExternalDataFromContext($context);
        } elseif ($retriever = $this->retrievers[$system] ?? null) {
            return $retriever->getExternalDataFromContext($context);
        }
        return null;
    }
}
