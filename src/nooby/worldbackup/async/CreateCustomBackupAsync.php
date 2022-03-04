<?php

declare(strict_types=1);

namespace nooby\worldbackup\async;

use pocketmine\nbt\{TreeRoot, BigEndianNbtSerializer,ReaderTracker};
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\scheduler\AsyncTask;

use pocketmine\Server;

/**
 * Classa CreateCustomBackupAsync
 * @package nooby\worldbackup\async
 */
class CreateCustomBackup extends AsyncTask
{
    
    /** @var string */
    private string $source, $destination, $newName;
    /** @var float */
    private float $time;
    
    /**
     * CreateCustomBackupAsync construct.
     * @param string $source
     * @param string $destination
     * @param string $newName
     */
    public function __construct(string $source, string $destination, string $newName)
    {
        $this->source = $source;
        $this->destination = $destination;
        $this->newName = $newName;
        
        $this->time = microtime(true);
    }
    
    public function onRun(): void
    {
        $this->copySource($this->source, $this->destination . DIRECTORY_SEPARATOR . $this->newName);
        # Edit nbt
        $levelPath = $this->destination . DIRECTORY_SEPARATOR . $this->newName . DIRECTORY_SEPARATOR . "level.dat";
        $newName = $this->newName;
        
        $nbt = new BigEndianNbtSerializer();
        $levelRead = $nbt->read(file_get_contents($levelPath));
        $levelTag = $levelRead->getTag();
        
        /** @var CompoundTag|null $levelData */
        $levelData = $levelTag->getCompoundTag('Data');

        if ($levelData instanceof CompoundTag) {
            $levelData->setString('LevelName', $newName);
            $nbt = new BigEndianNbtSerializer();
            file_put_contents($levelPath, $nbt->write(new TreeRoot(CompoundTag()->setTag("", $levelData))));
            $this->setResult($newName);
        }
    }
    
    public function onCompletion(): void
    {
        $result = $this->getResult();
        if (is_dir(Server::getInstance()->getDataPath() . $result . DIRECTORY_SEPARATOR)) {
          Server::getInstance()->getLogger()->info("[WorldBackupLib: Se instalo correctamente en: " . $this->time ."]");
        }
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
