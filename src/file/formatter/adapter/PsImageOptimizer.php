<?php

namespace tkanstantsin\fileupload\formatter\adapter;

use ImageOptimizer\Optimizer;
use ImageOptimizer\OptimizerFactory;
use tkanstantsin\fileupload\model\IFile;

/**
 * Class ImageOptimizer
 * Optimize images using `ps/image-optimizer`
 * @see https://github.com/psliwa/image-optimizer
 */
class PsImageOptimizer implements IFormatAdapter
{
    public const DEFAULT_CONFIG = [
        'ignore_errors'                     => true,
        'execute_only_first_jpeg_optimizer' => false,
        'execute_only_first_png_optimizer'  => false,
        'jpegoptim_options'                 => ['-m90', '--strip-all', '--all-progressive'],
        'jpegtran_options'                  => ['-optimize', '-progressive', '-perfect'],
    ];

    /**
     * @var string|null
     */
    public $tempDir;

    /**
     * @var array
     */
    public $optimizerConfig = self::DEFAULT_CONFIG;

    /**
     * ImageOptimizer constructor.
     *
     * @param array $config
     */
    public function __construct(array $config = [])
    {
        foreach ($config as $key => $value) {
            $this->$key = $value;
        }
    }

    /**
     * Applies filters or something to content and return it
     *
     * @param IFile $file
     * @param       $content
     *
     * @return mixed
     * @throws \ImageOptimizer\Exception\Exception
     */
    public function exec(IFile $file, $content)
    {
        $tmpPath = tempnam($this->getTempDir(), '');
        file_put_contents($tmpPath, $content);
        $this->getOptimizer()->optimize($tmpPath);
        $content = file_get_contents($tmpPath);

        @unlink($tmpPath);

        return $content;
    }

    /**
     * @return string
     */
    protected function getTempDir(): string
    {
        return (string) ($this->tempDir ?? sys_get_temp_dir());
    }

    /**
     * @param string $name
     *
     * @return Optimizer
     */
    protected function getOptimizer($name = OptimizerFactory::OPTIMIZER_SMART): Optimizer
    {
        return (new OptimizerFactory($this->optimizerConfig))->get($name);
    }
}