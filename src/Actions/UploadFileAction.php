<?php

declare(strict_types=1);

namespace App\Actions;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Factories\FileFactories\UploadedFileFactory;
use App\Factories\FormFactories\FileUploadFormFactory;
use App\Services\LinkGenerator;
use App\Database\UploadedFileGateway;
use App\Validators\FileUploadFormValidator;
use Slim\Flash\Messages;
use App\Actions\Action;
use App\Database\ThumbnailGateway;
use App\Database\UserGateway;
use App\Database\VideoPreviewGateway;
use App\Entities\Files\UploadedFiles\UploadedFile;
use App\Entities\Files\UploadedFiles\UploadedImage;
use App\Entities\Files\UploadedFiles\UploadedVideo;
use App\Entities\User;
use App\Factories\FileFactories\ThumbnailFactory;
use App\Factories\FileFactories\VideoPreviewFactory;
use App\Factories\UserFactory;
use App\Forms\FileUploadForm;
use App\Services\FileServices\ThumbnailService;
use App\Services\FileServices\UploadedFileService;
use App\Services\FileServices\VideoPreviewService;
use App\Storages\FileUnitOfWork;
use App\Storages\ThumbnailStorage;
use Exception;
use App\Storages\UploadedFileStorage;
use App\Storages\VideoPreviewStorage;
use App\Queue\Producers\VideoPreviewProducer;
use App\Queue\Producers\ThumbnailProducer;
use App\Services\UserAuthService;

/**
 * Action для загрузки файла
 */
class UploadFileAction extends Action
{
    private FileUploadFormFactory $fileUploadFormFactory;
    private FileUploadFormValidator $fileUploadFormValidator;
    private UploadedFileFactory $uploadedFileFactory;
    private UploadedFileGateway $uploadedFileGateway;
    private Messages $messages;
    private ThumbnailGateway $thumbnailGateway;
    private ThumbnailService $thumbnailService;
    private UploadedFileStorage $uploadedFileStorage;
    private ThumbnailFactory $thumbnailFactory;
    private ThumbnailStorage $thumbnailStorage;
    private VideoPreviewProducer $videoPreviewProducer;
    private ThumbnailProducer $thumbnailProducer;
    private VideoPreviewStorage $videoPreviewStorage;
    private VideoPreviewService $videoPreviewService;
    private VideoPreviewFactory $videoPreviewFactory;
    private UploadedFileService $uploadedFileService;
    private VideoPreviewGateway $videoPreviewGateway;
    private FileUnitOfWork $fileUnitOfWork;
    private UserAuthService $userAuthService;

    /**
     * Конструктор
     *
     * @param FileUploadFormFactory $fileUploadFormFactory
     * @param FileUploadFormValidator $fileUploadFormValidator
     * @param UploadedFileFactory $uploadedFileFactory
     * @param UploadedFileGateway $uploadedFileGateway
     * @param Messages $messages
     * @param ThumbnailGateway $thumbnailGateway
     * @param ThumbnailService $thumbnailService
     * @param UploadedFileStorage $uploadedFileStorage
     * @param ThumbnailStorage $thumbnailStorage
     * @param VideoPreviewProducer $videoPreviewProducer
     * @param ThumbnailFactory $thumbnailFactory
     * @param ThumbnailProducer $thumbnailProducer
     * @param VideoPreviewStorage $videoPreviewStorage
     * @param VideoPreviewService $videoPreviewService
     * @param VideoPreviewFactory $videoPreviewFactory
     * @param UploadedFileService $uploadedFileService
     * @param VideoPreviewGateway $videoPreviewGateway
     * @param FileUnitOfWork $fileUnitOfWork
     * @param UserAuthService $userAuthService
     */
    public function __construct(
        FileUploadFormFactory $fileUploadFormFactory,
        FileUploadFormValidator $fileUploadFormValidator,
        UploadedFileFactory $uploadedFileFactory,
        UploadedFileGateway $uploadedFileGateway,
        Messages $messages,
        ThumbnailGateway $thumbnailGateway,
        ThumbnailService $thumbnailService,
        UploadedFileStorage $uploadedFileStorage,
        ThumbnailStorage $thumbnailStorage,
        VideoPreviewProducer $videoPreviewProducer,
        ThumbnailFactory $thumbnailFactory,
        ThumbnailProducer $thumbnailProducer,
        VideoPreviewStorage $videoPreviewStorage,
        VideoPreviewService $videoPreviewService,
        VideoPreviewFactory $videoPreviewFactory,
        UploadedFileService $uploadedFileService,
        VideoPreviewGateway $videoPreviewGateway,
        FileUnitOfWork $fileUnitOfWork,
        UserAuthService $userAuthService
    ) {
        $this->fileUploadFormFactory = $fileUploadFormFactory;
        $this->fileUploadFormValidator = $fileUploadFormValidator;
        $this->uploadedFileFactory = $uploadedFileFactory;
        $this->uploadedFileGateway = $uploadedFileGateway;
        $this->messages = $messages;
        $this->thumbnailGateway = $thumbnailGateway;
        $this->thumbnailService = $thumbnailService;
        $this->videoPreviewProducer = $videoPreviewProducer;
        $this->thumbnailFactory = $thumbnailFactory;
        $this->thumbnailProducer = $thumbnailProducer;
        $this->videoPreviewService = $videoPreviewService;
        $this->videoPreviewFactory = $videoPreviewFactory;
        $this->uploadedFileService = $uploadedFileService;
        $this->videoPreviewGateway = $videoPreviewGateway;
        $fileUnitOfWork->registerStorages(
            uploadedFileStorage: $uploadedFileStorage,
            thumbnailStorage: $thumbnailStorage,
            videoPreviewStorage: $videoPreviewStorage
        );
        $this->fileUnitOfWork = $fileUnitOfWork;
        $this->videoPreviewStorage = $videoPreviewStorage;
        $this->thumbnailStorage = $thumbnailStorage;
        $this->uploadedFileStorage = $uploadedFileStorage;
        $this->userAuthService = $userAuthService;
    }

    /**
     * {@inheritdoc}
     */
    protected function action(Request $request, Response $response, array $args): Response
    {
        $formData = $request->getParsedBody();
        $formFile = $request->getUploadedFiles()['file'] ?? null;
        $form = $this->fileUploadFormFactory->create(
            formData: $formData,
            file: $formFile,
            createCsrfToken: false
        );
        $linkGenerator = new LinkGenerator($request);
        $errors = $this->fileUploadFormValidator->validate($form, false);

        if ($errors) {
            $this->messages->addMessage('fileUploadFormErrors', $errors);
            $this->messages->addMessage('fileUploadFormData', $formData);
            $redirectUrl = $linkGenerator->generate('files.upload.form');
            return $this->redirect(
                url: $redirectUrl,
                code: 303,
                response: $response
            );
        }

        $this->uploadedFileGateway->beginTransaction();

        try {
            $user = $this->createUser($request);
            $response = $this->userAuthService->authorizeUser($user, $response);
            $file = $this->createUploadedFile($form, $user);

            if (
                !$this->thumbnailProducer->isEnabled &&
                $file instanceof UploadedVideo
            ) {
                $this->createThumbnailByVideo($file);
            } elseif ($file instanceof UploadedImage) {
                $this->createThumbnailByImage($file);
            }

            if (
                !$this->videoPreviewProducer->isEnabled &&
                $file instanceof UploadedVideo
            ) {
                $this->createVideoPreview($file);
            }

            $url = $linkGenerator->generate(
                routeName: 'files.show',
                data: ['id' => (string)$file->getId()]
            );
            $this->uploadedFileGateway->commit();
        } catch (Exception $e) {
            $this->uploadedFileGateway->rollback();
            $this->fileUnitOfWork->rollback();
            throw $e;
        }

        if ($file instanceof UploadedVideo) {
            if (
                $this->thumbnailProducer->isEnabled
            ) {
                $this->dispatchThumbnailCreation($file);
            }

            if (
                $this->videoPreviewProducer->isEnabled
            ) {
                $this->dispatchVideoPreviewCreation($file);
            }
        }

        $this->fileUnitOfWork->refreshFiles();
        return $this->redirect(
            url: $url,
            code: 302,
            response: $response
        );
    }

    /**
     * Создать миниатюру через видео
     *
     * @param UploadedVideo $video
     * @return void
     */
    private function createThumbnailByVideo(UploadedVideo $video): void
    {
        $thumbnailInfoDTO = $this->thumbnailStorage->saveByVideo($video);

        try {
            $thumbnail = $this->thumbnailFactory->createByUploadedFile($video, $thumbnailInfoDTO);
            $id = $this->thumbnailGateway->addThumbnail($thumbnail);
            $thumbnail->setId($id);

            $url = $this->thumbnailService->getUrl($thumbnail->filename, $thumbnail->dir);
            $thumbnail->setUrl($url);

            $video->setThumbnail($thumbnail);
            $this->fileUnitOfWork->registerFile($thumbnail);
        } catch (Exception $e) {
            $this->thumbnailStorage->delete($thumbnailInfoDTO->getPath());
            throw $e;
        }
    }

    /**
     * Создать миниатюру через изображение
     *
     * @param UploadedImage $image
     * @return void
     */
    private function createThumbnailByImage(UploadedImage $image): void
    {
        $thumbnailInfoDTO = $this->thumbnailStorage->saveByImage($image);

        try {
            $thumbnail = $this->thumbnailFactory->createByUploadedFile($image, $thumbnailInfoDTO);
            $id = $this->thumbnailGateway->addThumbnail($thumbnail);
            $thumbnail->setId($id);

            $url = $this->thumbnailService->getUrl($thumbnail->filename, $thumbnail->dir);
            $thumbnail->setUrl($url);

            $image->setThumbnail($thumbnail);
            $this->fileUnitOfWork->registerFile($thumbnail);
        } catch (Exception $e) {
            $this->thumbnailStorage->delete($thumbnailInfoDTO->getPath());
            throw $e;
        }
    }

    /**
     * Создать загруженный файл
     *
     * @param FileUploadForm $form     
     * @param User $user
     * @return UploadedFile
     */
    private function createUploadedFile(FileUploadForm $form, User $user): UploadedFile {
        $fileInfoDTO = $this->uploadedFileStorage->save($form->file);

        try {
            $file = $this->uploadedFileFactory->createByFileUploadForm(
                form: $form,
                fileInfoDTO: $fileInfoDTO,
                user: $user
            );

            $id = $this->uploadedFileGateway->addFile($file);
            $file->setId($id);

            $url = $this->uploadedFileService->getUrl($id);
            $file->setUrl($url);

            $this->fileUnitOfWork->registerFile($file);
            return $file;
        } catch (Exception $e) {
            $this->uploadedFileStorage->delete($fileInfoDTO->getPath());
            throw $e;
        }
    }

    /**
     * Создать видео-превью
     *
     * @param UploadedVideo $video
     * @return void
     */
    private function createVideoPreview(UploadedVideo $video): void
    {
        $previewInfoDTO = $this->videoPreviewStorage->save($video);

        try {
            $preview = $this->videoPreviewFactory->createByUploadedFile($video, $previewInfoDTO);

            $id = $this->videoPreviewGateway->addPreview($preview);
            $preview->setId($id);

            $url = $this->videoPreviewService->getUrl($id);
            $preview->setUrl($url);

            $video->setVideoPreview($preview);
            $this->fileUnitOfWork->registerFile($preview);
        } catch (Exception $e) {
            $this->videoPreviewStorage->delete($previewInfoDTO->getPath());
            throw $e;
        }
    }

    /**
     * Отправить создание видео-превью в воркер
     *
     * @param UploadedVideo $video
     * @return void
     */
    private function dispatchVideoPreviewCreation(UploadedVideo $video): void
    {
        $this->videoPreviewProducer->send($video);
    }

    /**
     * Отправить создание миниатюры в воркер
     *
     * @param UploadedVideo $video
     * @return void
     */
    private function dispatchThumbnailCreation(UploadedVideo $video): void
    {
        $this->thumbnailProducer->send($video);
    }

    /**
     * Создать пользователя
     *
     * @param Request $request
     * @return User
     */
    private function createUser(Request $request): User
    {
        $user = $this->userAuthService->getUser($request);

        if (!$user->isRegistered()) {
            $this->userAuthService->registerUser($user);
        }
        
        return $user;
    }
}
