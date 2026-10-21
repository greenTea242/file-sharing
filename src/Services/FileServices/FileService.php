<?php

namespace App\Services\FileServices;

use Exception;
use App\Utilities\FileUtils;
use App\Interfaces\DirectoryFinderInterface as DirectoryFinder;
use App\Traits\FilePathAwareTrait;
use App\Utilities\PathUtils;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Routing\RouteContext;

/**
 * Сервис для файлов
 */
abstract class FileService
{
    use FilePathAwareTrait;
    
    private const int MAX_FILES_IN_DIR = 500;
    private DirectoryFinder $directoryFinder;
    private const string PUBLIC_URL = '/';

    public function __construct(DirectoryFinder $directoryFinder)
    {
        $this->directoryFinder = $directoryFinder;
    }

    /**
     * Получить дату создания файла через путь
     *
     * @param string $path
     * @return integer
     */
    public function getCreatedAtByPath(string $path): int
    {
        $createdAt = filectime($path);
        if ($createdAt === false) {
            throw new Exception('Не удалось получить дату создания файла');
        }
        return $createdAt;
    }

    /**
     * Получить дату создания файла через необработанные данные
     *
     * @param string $createdAtRaw
     * @return integer
     */
    public function getCreatedAtByRaw(string $createdAtRaw): int
    {
        return strtotime($createdAtRaw);
    }

    /**
     * Получить область видимости
     *
     * @return string
     */
    abstract public function getScope(): string;

    /**
     * Получить папку
     *
     * @return string
     */
    public function getDir(): string
    {
        $parentDir = $this->getParentDir();
        $dirMask = "{$parentDir}/%";
        $dir = $this->directoryFinder->findMaxDir($dirMask);
        if (is_null($dir)) {
            return "{$parentDir}/1";
        }
        if (!$this->checkDirForFileLimit($dir)) {
            $dir = $this->incrementDir($dir);
        }
        return $dir;
    }
    
    /**
     * Получить путь
     *
     * @param string $filename
     * @param string $dir
     * @param string $scope
     * @return string
     */
    public function getPath(string $filename, string $dir, string $scope): string {  
        return $this->buildPath(
            filename: $filename, 
            dir: $dir, 
            scope: $scope
        );
    }    

    /**
     * Получить путь к папке
     *
     * @param string $dir
     * @param string $scope
     * @return string
     */
    public function getPathToDir(string $dir, string $scope): string {
        return $this->buildPath($dir, $scope);        
    }

    /**
     * Получить публичный URL
     *
     * @param string $filename
     * @param string $dir
     * @return string
     */
    protected function getPublicUrl(string $filename, string $dir): string
    {           
        $path = PathUtils::getPath($filename, $dir);
        return self::PUBLIC_URL . $path;
    }

    /**
     * Проверить папку на лимит файлов
     *
     * @param string $dir
     * @return boolean
     */
    protected function checkDirForFileLimit(string $dir): bool
    {
        $filesCount = $this->getFilesCountInDir($dir);
        if ($filesCount + 1 <= self::MAX_FILES_IN_DIR) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Инкрементировать папку
     *
     * @param string $dir
     * @return string
     */
    protected function incrementDir(string $dir): string
    {
        $basename = basename($dir);
        if (!is_numeric($dir)) {
            throw new Exception('Папка не числовая');
        }
        $basename = (string)((int)$basename + 1);
        $parentDir = dirname($dir);
        return "{$parentDir}/{$basename}";
    }

    /**
     * Получить родительскую папку
     *
     * @return string
     */
    abstract protected function getParentDir(): string;

    /**
     * Получить URL для маршрута
     *
     * @param Request $request
     * @param string $route
     * @param array<string, string> $data
     * @param array<string, string> $queryParams
     * @return string
     */
    protected function getUrlForRoute(
        Request $request,
        string $route,
        array $data = [],
        array $queryParams = []
    ): string {
        $routeContext = RouteContext::fromRequest($request);
        $routeParser = $routeContext->getRouteParser();
        return $routeParser->urlFor($route, $data, $queryParams);
    }

    /**
     * Получить количество файлов в папке
     *
     * @param string $dir
     * @return integer
     */
    private function getFilesCountInDir(string $dir): int
    {
        $pattern = "$dir/*";
        $files = glob($pattern);
        return $files ? count($files) : 0;
    }
}
