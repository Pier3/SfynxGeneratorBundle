<?php

namespace Sfynx\DddGeneratorBundle\Generator\Generalisation;

use Sfynx\DddGeneratorBundle\Util\PiFileManager;

trait ExecuteTrait
{
    public function execute()
    {
        $this->setSkeletonDirs($this->getRootSkeletonDir() . '/' . self::SKELETON_DIR);
        $targetDir = PiFileManager::getFileDirname($this->target);
        if (!file_exists(PiFileManager::getFileDirname($this->target))) {
            mkdir($targetDir, 0777, true);
        }
        $this->renderFile($this->getTemplateName(), $this->target, $this->getParameters());
        $this->setPermissions($this->target);
    }
}
