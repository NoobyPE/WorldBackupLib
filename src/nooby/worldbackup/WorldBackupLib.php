<?php

declare(strict_types=1);

namespace nooby\worldbackup;

use pocketmine\Server;

use exodus\worldbackup\async\DeleteBackup;

use nooby\worldbackup\async\CreateBackup;
use nooby\worldbackup\async\CreateCustomBackup;
use nooby\worldbackup\async\RemoveBackup;

use pocketmine\utils\AssumptionFailedError;

/**
 * Class WorldBackupLib
 * @package nooby\worldbackup
 */
final class WorldBackupLib
{
    /**
     * @param string $source
     * @param string $target
     */
    static function copyDirectory($source, $target): void
    {
      if (is_dir($target)) {
        @mkdir($source); //close the sv, so i ignore the errors
        $d = dir($target);

        while(FALSE !== ($entry = $d->read())) {
          if ($entry === "." or $entry === "..") {
            continue;
          }
          $newEntry = $target . DIRECTORY_SEPARATOR . $entry;
          if (is_dir($newEntry)) {
            self::copyDirectory($source . DIRECTORY_SEPARATOR . $entry, $newEntry);
            continue;
          }
          copy($newEntry, $source . DIRECTORY_SEPARATOR . $entry);
        }
        $d->close();
      } else {
        copy($source, $target);
      }
    }

    /**
     * @param string $directory
     */
    static function deleteDirectory(string $directory): void
    {
      if (substr($directory, strlen($directory) - 1, 1) !== "/") {
        $directory .= DIRECTORY_SEPARATOR;
        $files = glob($directory . "*", GLOB_MARK);
        foreach($files as $file) {
          if (is_dir($file)) {
            self::deleteDirectory($directory . DIRECTORY_SEPARATOR . $file);
          } else {
            unlink($file);
          }
        }
      }
      rmdir($directory);
    }

    /**
     * @param string $newName
     * @param string $worldName
     * @throws AssumptionFailedError
     */
    public static function createBackup(string $newName, string $worldName): void
    {
        $worldManager = Server::getInstance()->getWorldManager();
        
        if (!$worldManager->isWorldGenerated($worldName)) {
            throw new AssumptionFailedError('The world \"$worldName\" is not generated');
        }
        
        $worldOld = $worldManager->getWorldByName($worldName);
        if (!$worldManager->isWorldLoaded($worldName)) {
            $worldOld->save();
        }
        if ($worldOld !== null) {
            $worldManager->unloadWorld($worldOld);
        }

        $to = Server::getInstance()->getDataPath() . "worlds" . DIRECTORY_SEPARATOR . $newName;
        $from = Server::getInstance()->getDataPath() . "worlds" . DIRECTORY_SEPARATOR . $worldName;

        if (!is_dir($from)) {
            throw new AssumptionFailedError("the World is not a directory (\"$newName\")");
        }

        Server::getInstance()->getAsyncPool()->submitTask(new CreateBackup($newName, $to, $from));
    }
    
    /**
     * @param string $source
     */
    public static function removeBackup(string $source): bool
    {
        if (!is_dir($source)) {
            return false;
        }
        Server::getInstance()->getAsyncPool()->submitTask(new DeleteBackup($source));
        return true;
    }
}