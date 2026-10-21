<?php

namespace App\Services\FileServices;

use App\Utilities\StringUtils;
use Psr\Http\Message\UploadedFileInterface as Source;
use App\Database\UploadedFileGateway;
use App\Entities\Files\File;
use App\Entities\Files\UploadedFiles\UploadedAudio;
use App\Entities\Files\UploadedFiles\UploadedImage;
use App\Entities\Files\UploadedFiles\UploadedVideo;
use App\Entities\Files\UploadedFiles\UploadedFile;
use App\Services\FileServices\FileService;
use App\Services\UuidService;
use App\Utilities\FileUtils;
use Slim\Routing\RouteParser;

/**
 * Сервис для загруженных файлов
 */
class UploadedFileService extends FileService
{
    protected const string EXTENSION_SEPARATOR = '.';
    protected const string TEXT_EXTENSION = 'txt';

    private UuidService $uuidService;  
    private RouteParser $routeParser;  

    /**
     * Конструктор
     *
     * @param UuidService $uuidService
     * @param UploadedFileGateway $uploadedFileGateway     
     */
    public function __construct(
        UuidService $uuidService, 
        UploadedFileGateway $uploadedFileGateway,
        RouteParser $routeParser)
    {
        parent::__construct($uploadedFileGateway);
        $this->uuidService = $uuidService;   
        $this->routeParser = $routeParser;     
    }

    /**
     * Получить комментарий пользователя
     *
     * @param string|null $userCommentRaw
     * @return string|null
     */
    public function getUserComment(?string $userCommentRaw = null): ?string
    {
        if (is_null($userCommentRaw)) {
            return $userCommentRaw;
        } else {
            return trim($userCommentRaw);
        }
    }

    /**
     * Файл является загруженным изображением?
     *
     * @param UploadedFile $file
     * @return boolean
     */
    public function isUploadedImage(UploadedFile $file): bool
    {
        return ($file instanceof UploadedImage);
    }

    /**
     * Файл является загруженным аудио?
     *
     * @param UploadedFile $file
     * @return boolean
     */
    public function isUploadedAudio(UploadedFile $file): bool
    {
        return ($file instanceof UploadedAudio);
    }

    /**
     * Файл является загруженным видео?
     *
     * @param UploadedFile $file
     * @return boolean
     */
    public function isUploadedVideo(UploadedFile $file): bool
    {
        return ($file instanceof UploadedVideo);
    }

    /**
     * Получить максимальный размер загружаемого файла
     *
     * @return integer
     */
    public function getMaxSize(): int
    {
        $fileSizeRaw = ini_get('upload_max_filesize');
        return FileUtils::getBytes($fileSizeRaw);
    }

    /**
     * Получить форматированный максимальный размер загружаемого файла
     *
     * @return string
     */
    public function getFormattedMaxSize(): string
    {
        $fileSizeRaw = $this->getMaxSize();
        return FileUtils::formatSize($fileSizeRaw);
    }

    /**
     * Получить имя файла пользователя
     *
     * @param Source $source
     * @return string
     */
    public function getUserFilename(Source $source): string
    {
        $userFilename = $source->getClientFilename();
        $userFilenameWithoutExt = $this->getUserFilenameWithoutExt($userFilename);        
        $userExt = $this->getUserExtension($userFilename);
        return FileUtils::getFilenameByParts($userFilenameWithoutExt, $userExt);
    }

    /**
     * Получить URL
     *
     * @param integer $id     
     * @return string
     */
    public function getUrl(int $id): string
    {
        $data = [
            'id' => (string)$id
        ];
        return $this->routeParser->urlFor('files.download', $data);        
    }    

    /**
     * Получить дату загрузки через необработанные данные
     *
     * @return integer
     */
    public function getUploadedAtByRaw(string $uploadedAtRaw): int {
        return strtotime($uploadedAtRaw);
    }

    /**
     * @inheritDoc          
     */
    public function getScope(): string
    {
        return File::SCOPE_PRIVATE;
    }

    /**
     * Получить имя  файла
     *     
     * @param string $userFilename
     * @return string
     */
    public function getFilename(string $userFilename): string
    {
        $filenameWithoutExt = $this->getFilenameWithoutExt($userFilename);
        $ext = $this->getExt();
        return FileUtils::getFilenameByParts($filenameWithoutExt, $ext);        
    }

    /**
     * @inheritDoc     
     */
    protected function getParentDir(): string
    {        
        $parentDir = 'uploads';
        $date = gmdate('y/m/d');
        $parentDir .= "/{$date}";
        return $parentDir;   
    }

    /**
     * Получить расширение
     *
     * @return string
     */
    private function getExt(): string {
        return FileUtils::EXTENSION_TXT;        
    }

    /**
     * Получить имя файла без расширения
     *
     * @param string $userFilename
     * @return string
     */
    private function getFilenameWithoutExt(string $userFilename): string {
        $userFilenameWithoutExt = FileUtils::getFilenameWithoutExt($userFilename);
        $latinUserFilenameWithoutExt = StringUtils::transliterateToLatin($userFilenameWithoutExt);
        $id = $this->uuidService->createUuid();
        return "{$latinUserFilenameWithoutExt}_{$id}";
    }

    /**
     * Получить имя файла клиента без расширения
     *
     * @param string $userFilename
     * @return string
     */
    private function getUserFilenameWithoutExt(string $userFilename): string {
        $userFilenameWithoutExt = FileUtils::getFilenameWithoutExt($userFilename);
        $userFilenameWithoutExt = mb_substr($userFilenameWithoutExt, 0, 50);
        return rtrim($userFilenameWithoutExt);
    } 

    /**
     * Получить расширение пользователя
     *
     * @param string $userFilename
     * @return string
     */
    private function getUserExtension(string $userFilename): string {
        $userExt = FileUtils::getExt($userFilename);
        return mb_substr($userExt, 0, 20);
    }   
}
