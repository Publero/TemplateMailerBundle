<?php

/*
 * This file is part of the PubleroTemplateMailerBundle package.
 *
 * (c) Tomas Pecserke <tomas@pecserke.eu>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Publero\TemplateMailerBundle\Loader;

use Publero\TemplateMailerBundle\TemplateStorage\TemplateStorage;

class TwigTemplateLoader implements \Twig_LoaderInterface
{
    /**
     * @var TemplateStorage
     */
    private $storage;

    public function setTemplateStorage(TemplateStorage $storage)
    {
        $this->storage = $storage;
    }

    public function getSource($name)
    {
        list($code, $part) = $this->parseName($name);

        try {
            switch ($part) {
                case 'sender':
                    return $this->storage->getSender($code);
                case 'subject':
                    return $this->storage->getSubject($code);
                case 'body':
                    return $this->storage->getBody($code);
                default:
                    throw new \Twig_Error_Loader("invalid template name part '$part'");
            }
        } catch (\OutOfBoundsException $e) {
            throw new Twig_Error_Loader(sprintf('Template "%s" is not defined.', $name));
        }
    }

    public function getCacheKey($name)
    {
        return $name;
    }

    public function isFresh($name, $time)
    {
        list($code, $part) = $this->parseName($name);

        try {
            return $this->storage->isStored($code);
        } catch (\OutOfBoundsException $e) {
            throw new Twig_Error_Loader(sprintf('Template "%s" is not defined.', $name));
        }
    }

    /**
     * @param string $name
     * @return array(code, part)
     *
     * @throws \Twig_Error_Loader
     */
    private function parseName($name)
    {
        if (!preg_match('/^([^:]+):([^:]+)$/', $name, $parts, $matches)) {
            throw new \Twig_Error_Loader("invalid template name format");
        }

        return $matches;
    }
}
