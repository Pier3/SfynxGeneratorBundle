<?php
declare(strict_types = 1);

namespace Sfynx\DddGeneratorBundle\Generator\Generalisation;

use Exception;
use Sfynx\DddGeneratorBundle\Util\PiFileManager;

/**
 * Trait ExecuteTrait.
 *
 * @category Generator
 * @package Generalisation
 */
trait ExecuteTrait
{
    /**
     * Execute the handler to render a template with parameters into a target file.
     */
    public function execute()
    {
        try {
            $this->setSkeletonDirs($this->getRootSkeletonDir() . '/' . self::SKELETON_DIR);
            $targetDir = PiFileManager::getFileDirectoryName($this->target);
            if (!file_exists(PiFileManager::getFileDirectoryName($this->target))) {
                mkdir($targetDir, 0777, true);
            }
            $this->renderFile($this->getTemplateName(), $this->target, $this->getParameters());
            $this->setPermissions($this->target);
        } catch (Exception $e) {
            $errorMessage = PHP_EOL . ' # /!\ Exception occurs during the execution of handler "%s":' . PHP_EOL
                . '    %s' . PHP_EOL . PHP_EOL;
            fwrite(STDERR, sprintf($errorMessage, get_class($this), $e->getMessage()));
        }
    }
}
