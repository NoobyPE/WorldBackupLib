<?php

namespace nooby\worldbackup\async;

use Exception;
use nooby\worldbackup\WorldBackupLib;

use const GLOB_MARK;
use function substr;
use function strlen;
use function glob;
use function rmdir;

use pocketmine\Server;
use pocketmine\scheduler\AsyncTask;

class DeleteBackup extends AsyncTask
{
  private $directory;
  
  public function __construct(string $directory)
  {
    $this->directory = $directory;
  }
  
  public function onRun(): void
  {
    try {
      WorldBackupLib::deleteDirectory($this->directory);
    }catch(Exception $exception) {
      Server::getInstance()->getLogger()->info($exception->getMessage());
    }
  }
  
}
