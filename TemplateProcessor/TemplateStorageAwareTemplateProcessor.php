<?php

/*
 * This file is part of the PubleroTemplateMailerBundle package.
 *
 * (c) Tomas Pecserke <tomas@pecserke.eu>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Publero\TemplateMailerBundle\TemplateProcessor;

use Publero\TemplateMailerBundle\TemplateStorage\TemplateStorage;

interface TemplateStorageAwareTemplateProcessor extends TemplateProcessor
{
    /**
     * Sets template storage to this processor.
     *
     * @param TemplateStorage $storage
     */
    public function setTemplateStorage(TemplateStorage $storage);
}
