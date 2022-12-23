<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace SixtySeven\InventoryProcessed\Logger;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use Psr\Log\LogLevel;
/**
 * Logging to file
 */
class Logger extends \Psr\Log\AbstractLogger
{
    /**
     * @var WriteInterface
     */
    private $dir;

    /**
     * Path to SQL debug data log
     *
     * @var string
     */
    protected $debugFile;

    /**
     * @param Filesystem $filesystem
     * @param string $debugFile
     * @param bool $logAllQueries
     * @param float $logQueryTime
     * @param bool $logCallStack
     */
    public function __construct(
        Filesystem $filesystem,
        $debugSuccessFile = 'log/import_success.log',
        $debugErrorFile = 'log/import_error.log',
        $logAllQueries = false,
        $logQueryTime = 0.05,
        $logCallStack = false
    ) {
        //parent::__construct($logAllQueries, $logQueryTime, $logCallStack);
        $this->dir = $filesystem->getDirectoryWrite(DirectoryList::VAR_DIR);
        $this->debugSuccessFile = $debugSuccessFile;
        $this->debugErrorFile = $debugErrorFile;
    }

    /**
     * {@inheritdoc}
     */
    public function log($level, $str, array $context = array())
    {
        $str = '## ' . date('Y-m-d H:i:s') . "\r\n" . $str;
        $debugFile = $this->debugErrorFile;
        if(LogLevel::INFO==$level){
            $debugFile = $this->debugSuccessFile;
        }
        $stream = $this->dir->openFile($debugFile, 'a');
        $stream->lock();
        $stream->write($level."\r\n".$str,"\r\n".print_r($context,true));
        $stream->unlock();
        $stream->close();
    }

    /**
     * {@inheritdoc}
     */
    public function logStats($type, $sql, $bind = [], $result = null)
    {
        $stats = $this->getStats($type, $sql, $bind, $result);
        if ($stats) {
            $this->log($stats);
        }
    }

    public function critical($message, array $context = array())
    {
        $this->log(LogLevel::ERROR, $message, $context);
    }

    public function logSuccess($message, array $context = array())
    {
        $this->log(LogLevel::INFO, $message, $context);
    }
}
