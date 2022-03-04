<?php



declare(strict_types=1);

namespace nooby\worldbackup;

use nooby\worldbackup\async\CreateBackup;
use nooby\worldbackup\async\CreateCustomBackup;
use nooby\worldbackup\async\RemoveBackup;
use pocketmine\Server;
use RuntimeException;

/**
 * Class WorldBackupLib
 * @package nooby\worldbackup
 */
final class WorldBackupLib
{
    
    /**
     * @param string $worldName
     * @param string $destination
     * @throws RuntimeException
     */
    public static function createBackup(string $worldName, string $destination): void
    {
        $worldManager = Server::getInstance()->getWorldManager();
        
        if (!$worldManager->isWorldGenerated($worldName)) {
            throw new RuntimeException('The world `$worldName` is not generated');
        }
        
        if (!$worldManager->isWorldLoaded($worldName)) {
            throw new RuntimeException('The world `$worldName` is not loaded');
        }
        
        if (!is_dir($destination)) {
            throw new RuntimeException('Destination is not a directory (`$destination`)');
        }
        $source = Server::getInstance()->getDataPath() . 'worlds' . DIRECTORY_SEPARATOR . $worldName;
        Server::getInstance()->getAsyncPool()->submitTask(new CreateBackup($source, $destination));
    }
    
    
    /**
     * @param string $worldName
     * @param string $destination
     * @param string $newName
     * @throws RuntimeException
     */
    public static function createCustomBackup(string $worldName, string $destination, string $newName): void
    {
        $worldManager = Server::getInstance()->getWorldManager();
        
        if (!$worldManager->isWorldGenerated($worldName)) {
            throw new RuntimeException('The world `$worldName` is not generated');
        }
        
        if (!$worldManager->isWorldLoaded($worldName)) {
            throw new RuntimeException('The world `$worldName` is not loaded');
        }
        
        if (!is_dir($destination)) {
            throw new RuntimeException('Destination is not a directory (`$destination`)');
        }
        
        if ($worldManager->isWorldGenerated($newName)) {
            throw new RuntimeException('The new name already exists in another world');
        }
        $source = Server::getInstance()->getDataPath() . 'worlds' . DIRECTORY_SEPARATOR . $worldName;
        Server::getInstance()->getAsyncPool()->submitTask(new CreateCustomBackup($source, $destination, $newName));
    }
    
    /**
     * @param string $source
     */
    public static function removeBackup(string $source): void
    {
        if (!is_dir($source)) {
            throw new RuntimeException('Source is not a directory (`$source`)');
        }
        Server::getInstance()->getAsyncPool()->submitTask(new RemoveBackup($source));
    }
}

