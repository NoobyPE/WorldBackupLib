<?php

declare(strict_types=1);

namespace libs\worldbackup\async;

use pocketmine\scheduler\AsyncTask;

/**
 * Class CreateBackupAsync
 * @package nooby\worldbackup\async
 */
class CreateBackupAsync extends AsyncTask
{
    
    /** @var string */
    private string $source, $destination;
    /** @var float */
    private float $time;
    
    /**
     * CreateBackupAsync construct.
     * @param string $source
     * @param strkng $destination
     */
    public function __construct(string $source, string $destination)
    {
        $this->source = $source;
        $this->destination = $destination;
        
        $this->time = microtime(true);
    }
    
    public function onRun(): void
    {
        $levelName = basename($this->source);
        $this->copySource($this->source, $this->destination . DIRECTORY_SEPARATOR . $levelName);
    }
    
    public function onCompletion(): void
    {
        // thanks dylan :)
    }
    
    /**
     * @param string $source
     * @param string $target
     */
    private function copySource(string $source, string $target): void
    {
        if (is_dir($source)) {
            @mkdir($target);
            $d = dir($source);

            while (FALSE !== ($entry = $d->read())) {
                if ($entry === '.' || $entry === '..')
                    continue;
                $Entry = $source . DIRECTORY_SEPARATOR . $entry;

                if (is_dir($Entry)) {
                    $this->copySource($Entry, $target . DIRECTORY_SEPARATOR . $entry);
                    continue;
                }
                @copy($Entry, $target . DIRECTORY_SEPARATOR . $entry);
            }
            $d->close();
        } else
            @copy($source, $target);
    }
}
