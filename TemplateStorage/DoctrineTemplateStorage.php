<?php

/*
 * This file is part of the PubleroTemplateMailerBundle package.
 *
 * (c) Tomas Pecserke <tomas@pecserke.eu>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Publero\TemplateMailerBundle\TemplateStorage;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\Persistence\ObjectRepository;
use Publero\TemplateMailerBundle\Client\RemoteStorageClient;
use Publero\TemplateMailerBundle\Model\Template;

class DoctrineTemplateStorage extends TemplateStorage
{
    /**
     * @var ObjectManager
     */
    protected $manager;

    public function __construct(RemoteStorageClient $remoteClient, ObjectManager $manager)
    {
        parent::__construct($remoteClient);

        $this->manager = $manager;
    }

    /**
     * @return ObjectRepository
     */
    protected function getTemplateRepository()
    {
        return $this->manager->getRepository('Publero\TemplateMailerBundle\Model\Template');
    }

    /**
     * Returns the Template persisted object by template code.
     *
     * @param string $code
     * @return Template
     *
     * @throws \OutOfBoundsException If no template with specified code is stored
     */
    protected function getTemplateByCode($code)
    {
        /* @var Template $template */
        $template = $this->getTemplateRepository()->findOneByCode($code);
        if ($template === null) {
            throw new \OutOfBoundsException("template '$code' is not stored");
        }

        return $template;
    }

    public function getHash($code)
    {
        return $this->getTemplateByCode($code)->getHash();
    }

    public function getSource($code)
    {
        return $this->getTemplateByCode($code)->getSource();
    }

    public function isStored($code)
    {
        return null !== $this->getTemplateRepository()->findOneByCode($code);
    }

    public function isFresh($code)
    {
        return $this->isTemplateFresh($this->getTemplateByCode($code));
    }

    /**
     * @param Template $template
     * @return bool
     */
    protected function isTemplateFresh(Template $template)
    {
        return $template->getChecksum() === $this->computeChecksum($template);
    }

    /**
     * @param Template $template
     * @return string
     */
    protected function computeChecksum(Template $template)
    {
        return sha1(
            $template->getSource() .
            json_encode($template->getDefaultParams()) .
            $template->getCode() .
            $template->getHash()
        );
    }

    public function update($code = null)
    {
        if (null !== $code) {
            $template = $this->getTemplateByCode($code);
            if ($this->isTemplateFresh($template)) {
                return;
            }
            $hash = $this->persistRemote($template->getSource(), $template->getHash(), $template->getDefaultParams());
            $template->setHash($hash);
            $template->setChecksum($this->computeChecksum($template));
        } else {
            foreach ($this->getTemplateRepository()->findAll() as $template) {
                if ($this->isTemplateFresh($template)) {
                    continue;
                }
                $hash = $this->persistRemote($template->getSource(), $template->getSource());
                $template->setHash($hash);
                $template->setChecksum($this->computeChecksum($template));
            }
        }

        $this->manager->flush();
    }

    public function assignHash($code, $hash)
    {
        $this->getTemplateByCode($code)->setHash($hash);
        $this->manager->flush();
    }

    public function persist($code, $source, array $defaultParams = array())
    {
        $template = $this->getTemplateRepository()->findOneByCode($code);
        if (null === $template) {
            $template = new Template();
            $template->setCode($code);

            $this->manager->persist($template);
        }
        $template->setSource($source);
        $template->setDefaultParams($defaultParams);

        $this->manager->flush();
    }

    public function delete($code)
    {
        $template = $this->getTemplateByCode($code);

        $this->manager->remove($template);
        $this->manager->flush();
    }
}
