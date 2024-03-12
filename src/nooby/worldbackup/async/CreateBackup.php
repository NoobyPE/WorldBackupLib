<?php

declare(strict_types=1);

namespace nooby\worldbackup\async;

use Exception;
use RuntimeException;
use nooby\worldbackup\WorldBackupLib;
use pocketmine\scheduler\AsyncTask;
use pocketmine\Server;
use pocketmine\world\format\io\data\BaseNbtWorldData;

/**
 * Class CreateBackupAsync
 * @package nooby\worldbackup\async
 */
class CreateBackup extends AsyncTask
{
    
    /** @var string */
    private string $name, $to, $from;

    /** @var float */
    private float $time;
    
    /**
     * CreateBackup construct.
     * @type Async
     * @param string $name
     * @param string $to
     * @param string $from
     */
    public function __construct(string $name, string $to, string $from)
    {
        $this->name = $name;
        $this->to = $to;
        $this->from = $from;
        
        $this->time = microtime(true);
    }
    
    public function onRun(): void
    {
        try {
            WorldBackupLib::copyDirectory($this->to, $this->from);
        }catch(Exception $e) {
            Server::getInstance()->getLogger()->info($e->getMessage());
        }
    }
    
    public function onCompletion(): void
    {
        $worldManager = Server::getInstance()->getWorldManager();
        if (!$worldManager->isWorldLoaded($this->name)) {
            $worldManager->loadWorld($this->name, true);
        }

        $world = $worldManager->getWorldByName($this->name);
        if (empty($world)) {
            throw new RuntimeException("the World does not exist of the New Name");
        }

        $worldData = $world->getProvider()->getWorldData();
        if (!$worldData instanceof BaseNbtWorldData) {
            return;
        }

        $worldData->getCompoundTag()->setString("LevelName", $this->name);
        Server::getInstance()->getLogger()->notice("Timeout: " . microtime(true) - $this->time);
    }

}