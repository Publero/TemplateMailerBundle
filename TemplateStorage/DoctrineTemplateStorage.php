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
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\DocumentRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Publero\TemplateMailerBundle\Client\RemoteStorageClient;
use Publero\TemplateMailerBundle\Model\Template;
use Publero\TemplateMailerBundle\TemplateProcessor\TemplateProcessor;

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

    public function getCode($hash)
    {
        /* @var Template $template */
        $template = $this->getTemplateRepository()->findOneByHash($hash);
        if ($template === null) {
            throw new \OutOfBoundsException("template with hash '$hash' is not stored");
        }

        return $template->getCode();
    }

    public function getSender($code)
    {
        return $this->getTemplateByCode($code)->getSender();
    }

    public function getSubject($code)
    {
        return $this->getTemplateByCode($code)->getSubject();
    }

    public function getBody($code)
    {
        return $this->getTemplateByCode($code)->getBody();
    }

    public function getDefaultParams($code)
    {
        return $this->getTemplateByCode($code)->getDefaultParams();
    }

    public function isStored($code)
    {
        return null !== $this->getTemplateRepository()->findOneByCode($code);
    }

    public function getTemplates()
    {
        if (class_exists('Doctrine\ORM\EntityManager') && $this->manager instanceof EntityManager) {
            /* @var EntityRepository $repo */
            $repo = $this->getTemplateRepository();
            $qb = $repo->createQueryBuilder('t');
            $qb->select('t.code');

            return array_map(
                function(array $item) {
                    return $item['code'];
                },
                $qb->getQuery()->getScalarResult()
            );
        } else if (class_exists('Doctrine\ODM\MongoDB\DocumentManager') && $this->manager instanceof DocumentManager) {
            /* @var DocumentRepository $repo */
            $repo = $this->getTemplateRepository();
            $qb = $repo->createQueryBuilder();
            $qb->select('code');

            return array_map(
                function(array $item) {
                    return $item['code'];
                },
                $qb->getQuery()->execute()
            );
        } else {
            return array_map(
                function(Template $template) {
                    return $template->getCode();
                },
                $this->getTemplateRepository()->findAll()
            );
        }
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
        return null !== $template->getHash() && $template->getChecksum() === $this->computeChecksum($template);
    }

    /**
     * @param Template $template
     * @return string
     */
    public function computeChecksum(Template $template)
    {
        return sha1(
            $template->getSender() .
            $template->getSubject() .
            $template->getBody() .
            json_encode($template->getDefaultParams()) .
            $template->getCode() .
            $template->getHash()
        );
    }

    public function update($code = null)
    {
        if (null !== $code) {
            $this->updateTemplate($this->getTemplateByCode($code));
        } else {
            foreach ($this->getTemplateRepository()->findAll() as $template) {
                $this->updateTemplate($template);
            }
        }

        $this->manager->flush();
    }

    protected function updateTemplate(Template $template)
    {
        $hasProcessor = null !== $this->templateProcessor;
        if (!$hasProcessor && $this->isTemplateFresh($template)) {
            return;
        }

        $params = $template->getDefaultParams();
        $code = $template->getCode();

        $sender = $hasProcessor ?
            $this->templateProcessor->processTemplate($code . ':sender', $params) :
            $template->getSender()
        ;
        $subject = $hasProcessor ?
            $this->templateProcessor->processTemplate($code . ':subject', $params) :
            $template->getSubject()
        ;
        $body = $hasProcessor ?
            $this->templateProcessor->processTemplate($code . ':body', $params) :
            $template->getBody()
        ;

        $hash = $this->persistRemote($sender, $subject, $body, $template->getDefaultParams(), $template->getHash());

        $template->setHash($hash);
        $template->setChecksum($this->computeChecksum($template));
    }

    public function assignHash($code, $hash)
    {
        $this->getTemplateByCode($code)->setHash($hash);
        $this->manager->flush();
    }

    public function persist($code, $sender, $subject, $body, array $defaultParams = array())
    {
        $template = $this->getTemplateRepository()->findOneByCode($code);
        if (null === $template) {
            $template = new Template();
            $template->setCode($code);

            $this->manager->persist($template);
        }
        $template->setSender($sender);
        $template->setSubject($subject);
        $template->setBody($body);
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
