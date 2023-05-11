<?php

namespace BisonLab\ContextBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

trait ContextBaseTrait
{
    /*
     * This is not for storage into the DB. So no, it should not have been here.
     */
    private $config;

    /**
     * @var integer $id
     *
     * @ORM\Id
     * @ORM\Column(name="id", type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string $system
     *
     * @ORM\Column(name="system", type="string", length=255)
     */
    private $system;

    /**
     * @var string $object_name
     *
     * @ORM\Column(name="object_name", type="string", length=255)
     */
    private $object_name;

    /**
     * @var string $external_id
     *
     * @ORM\Column(name="external_id", type="string", length=80)
     */
    private $external_id;

    /**
     * @var string $url
     *
     * @ORM\Column(name="url", type="string", length=255, nullable=true)
     */
    private $url;

    public function __construct(
        $options = array()
    ) {
        if (isset($options['system'])) 
            $this->setSystem($options['system']);
        if (isset($options['object_name'])) 
            $this->setObjectName($options['object_name']);
        if (isset($options['external_id'])) 
            $this->setExternalId($options['external_id']);
        if (isset($options['url'])) 
            $this->setUrl($options['url']);
    }

    /**
     * Get id
     *
     * @return integer 
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * Set system
     *
     * @param string $system
     * @return $this
     */
    public function setSystem(string $system): self
    {
        $this->system = $system;
        return $this;
    }

    /**
     * Get system
     *
     * @return string 
     */
    public function getSystem(): string
    {
        return $this->system;
    }

    /**
     * Set object name
     *
     * @param string $object_name
     * @return a Context object
     */
    public function setObjectName(string $object_name): self
    {
        $this->object_name = $object_name;
        return $this;
    }

    /**
     * Get object_name
     *
     * @return string 
     */
    public function getObjectName(): string
    {
        return $this->object_name;
    }

    /**
     * Set external_id
     *
     * @param string $externalId
     * @return $this
     */
    public function setExternalId(string|int $externalId): self
    {
        $this->external_id = $externalId;
        return $this;
    }

    /**
     * Get external_id
     *
     * @return string 
     */
    public function getExternalId(): string|int
    {
        return $this->external_id;
    }

    /**
     * Reset url
     * Aka use the config and template to create a new URL.
     *
     * @param string $url
     * @return $this
     */
    public function resetUrl(): void
    {
        // Good old one. No externalid, no need to do this?
        // The URL may just be a url, alas, do this anyway.
        if (isset($this->config['url_base'])) {
            $this->url = $this->config['url_base'] . $this->getExternalId();
        }    
        // Or we have a twig template'ish. (Notice that it will override the
        // url_base one.
        if (isset($this->config['url_template'])) {
            $this->url = $this->config['url_template'];
            $context_arr = array(
                'external_id' => $this->getExternalId(),
                'system' => $this->getSystem(),
                'object_name' => $this->getObjectName(),
                'owner_id' => $this->getOwnerId(),
                );
            // Add owner object properties. (Not going full twig parser, yet)
            foreach ((array)$this->getOwner() as $k => $v) {
                if (!(is_numeric($v) || is_string($v))) continue;
                // Why did I have to do this? Got a reference error.
                $karr = explode("\x00", $k);
                $key = "owner." . array_pop($karr);
                $context_arr[$key] = $v;
            }
            foreach ($context_arr as $key => $val) {
                // What to do if the template has a key with no corresponding
                // val?  Jump it and hope it'll be better next time round
                // (maybe after a flush)
                if (empty($val) && strstr($this->url, $key)) {
                    $this->url = null;
                    break;
                }
                $this->url = preg_replace('/\{\{\s?'.$key.'\s?\}\}/i',
                                $val , $this->url);
            }
        }    
    }

    /**
     * Set url
     *
     * @param string $url
     * @return a Context object
     */
    public function setUrl(?string $url)
    {
        $this->url = $url;
        return $this;
    }

    /**
     * Get url
     *
     * @return string 
     */
    public function getUrl(): ?string
    {
        /*
         * May be overkill, but if the config has changed it may be useful to
         * try again. 
         */
        if (empty($this->url))
            $this->resetUrl();
        return $this->url;
    }

    /**
     * Generic main object setting.
     *
     * @return object
     */
    public function setOwner(object $object): self
    {
        $this->owner = $object;
        return $this;
    }

    /**
     * Generic main object.
     *
     * @return object
     */
    public function getOwner(): object
    {
        return $this->owner;
    }

    /*
     * Owner helpers.
     */
    public function getOwnerId(): mixed
    {
        return $this->getOwner()?->getId();
    }

    public function __toString(): string
    {
        return (string)$this->id;
    }

    public function setConfig($config = array()): self
    {
        $this->config = $config;
        return $this;
    }

    public function getConfig(): mixed
    {
        return $this->config;
    }

    /**
     * Get label
     *
     * @return string 
     */
    public function getLabel(): string
    {
        return $this->config['label'];
    }

    /**
     * Get Context type
     *
     * @return string 
     */
    public function getContextType(): string
    {
        return $this->config['type'];
    }

    /*
     * This is decided by the context type and the existance of an external id.
     * It should be no context object if there are no external ID but that's
     * another story.
     */
    public function isDeleteable(): bool
    {
        // It's not really working well as it is now. Not bad enough so we'll
        // keep it but if you end up having issues, just clone the function
        // into your context object and return true.
        return (
            ($this->getConfig()['type'] == 'external_master' || 
             $this->getConfig()['type'] == 'master') 
            && $this->getExternalId()) ? false : true;
    }

    /*
     * Default behaviour is NOT to accept the same context used more than
     * one. Not really sure I agree with myself here, but it makes
     * the most sense.
     */
    public function isUnique(): bool
    {
        return $this->config['unique'] ?? true;
    }

    /*
     * If there can be only one of these per Owner.
     * This is default true, it usually makes no sense with more.
     */
    public function getOnePerOwner(): bool
    {
        return $this->config['one_per_owner'] ?? true;
    }

    /**
     * Required. A flag for use if you want the form to have required set.
     * I can add a check for this in the EventListener, but the only way I can
     * "report" back to the application is through Exceptions, which I hate.
     * But using a custom exception would make it kinda easier.
     *
     * @return boolean 
     */
    public function getRequired(): bool
    {
        return $this->config['required'] ?? false;
    }

    /*
     * Alas, default behaviour is to log context changes.
     */
    public function doNotLog(): bool
    {
        return $this->config['no_logging'] ?? false;
    }
}
