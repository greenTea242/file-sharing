<?php

declare(strict_types=1);

use App\Actions\AddCommentAction;
use Pimple\Container;
use App\Actions\DownloadFileAction;
use App\Actions\DownloadVideoPreviewAction;
use App\Actions\ShowRecentFilesAction;
use App\Actions\SearchFilesAction;
use App\Actions\ShowFileAction;
use App\Actions\ShowFileUploadFormAction;
use App\Actions\ShowUserFilesAction;
use App\Actions\UploadFileAction;
use App\Database\CommentGateway;
use Slim\Views\Twig;
use App\Views\FileUploadFormView;
use App\Views\FileView;
use App\Services\MetadataServices\MetadataService;
use JamesHeinrich\GetID3\GetID3;
use Slim\Csrf\Guard;
use Slim\Psr7\Factory\ResponseFactory;
use App\Services\CsrfProtection;
use App\Validators\FileUploadFormValidator;
use App\Factories\FileFactories\UploadedFileFactory;
use App\Database\UploadedFileGateway;
use App\Factories\FormFactories\FileUploadFormFactory;
use App\Services\FileServices\UploadedFileService;
use App\Services\UuidService;
use App\Views\TwigExtensions\FileTwigExtension;
use App\Middleware\FileSizeMiddleware;
use App\Views\HttpErrorHandlerView;
use App\Validators\FileUploadFormFileValidator;
use App\Services\FormServices\FileUploadFormService;
use Slim\Middleware\ContentLengthMiddleware;
use App\Middleware\PostSizeMiddleware;
use App\Views\FileSearchView;
use Slim\Flash\Messages;
use App\Views\RecentFilesView;
use App\Middleware\SessionStartMiddleware;
use App\Services\FileDownloaders\PhpNativeDownloader;
use App\Services\FileDownloaders\XSendFileDownloader;
use App\Factories\FileDownloaderFactory;
use App\Factories\MediaInfoFactory;
use App\Database\ThumbnailGateway;
use App\Database\VideoPreviewGateway;
use App\Factories\CommentFactories\CommentFactory;
use App\Factories\FileFactories\ThumbnailFactory;
use App\Factories\FileFactories\VideoPreviewFactory;
use App\Factories\FormFactories\CommentFormFactory;
use App\Factories\PredisClientFactory;
use App\Services\FileServices\ThumbnailService;
use App\Services\FileServices\VideoPreviewService;
use App\Storages\ThumbnailStorage;
use App\Storages\UploadedFileStorage;
use FFMpeg\FFMpeg;
use App\Services\VideoConverter;
use App\Services\GifService;
use App\Storages\VideoPreviewStorage;
use App\Queue\Consumers\ThumbnailConsumer;
use App\Queue\Consumers\VideoPreviewConsumer;
use App\Storages\FileUnitOfWork;
use App\Factories\RedisQueueClientFactory;
use App\Loggers\QueueLoggers\ConsumerLoggers\ThumbnailConsumerLogger;
use App\Loggers\QueueLoggers\ConsumerLoggers\VideoPreviewConsumerLogger;
use Slim\Routing\RouteParser;
use App\Queue\Producers\ThumbnailProducer;
use App\Queue\Producers\VideoPreviewProducer;
use App\Services\CommentService;
use App\Factories\CommentFactories\CommentTreeFactory;
use App\Services\FormServices\CommentFormService;
use App\Services\MetadataServices\GifMetadataService;
use App\Services\MimeTypeService;
use App\Validators\CommentFormValidator;
use App\Factories\UserFactory;
use App\Services\UserAuthService;
use App\Database\UserGateway;
use App\Middleware\AuthMiddleware;
use App\Views\UserFilesView;

return function (Container $container) {
    $container[UploadFileAction::class] = function (Container $c) {
        return new UploadFileAction(
            fileUploadFormFactory: $c[FileUploadFormFactory::class],
            fileUploadFormValidator: $c[FileUploadFormValidator::class],
            uploadedFileFactory: $c[UploadedFileFactory::class],
            uploadedFileGateway: $c[UploadedFileGateway::class],
            messages: $c[Messages::class],
            thumbnailGateway: $c[ThumbnailGateway::class],
            thumbnailService: $c[ThumbnailService::class],
            thumbnailStorage: $c[ThumbnailStorage::class],
            uploadedFileStorage: $c[UploadedFileStorage::class],
            thumbnailFactory: $c[ThumbnailFactory::class],
            videoPreviewProducer: $c[VideoPreviewProducer::class],
            thumbnailProducer: $c[ThumbnailProducer::class],
            videoPreviewStorage: $c[VideoPreviewStorage::class],
            videoPreviewService: $c[VideoPreviewService::class],
            videoPreviewFactory: $c[VideoPreviewFactory::class],
            uploadedFileService: $c[UploadedFileService::class],
            videoPreviewGateway: $c[VideoPreviewGateway::class],
            fileUnitOfWork: $c[FileUnitOfWork::class],
            userAuthService: $c[UserAuthService::class]
        );
    };

    $container[ShowFileUploadFormAction::class] = function (Container $c) {
        return new ShowFileUploadFormAction(
            fileUploadFormFactory: $c[FileUploadFormFactory::class],
            messages: $c[Messages::class],
            view: $c[FileUploadFormView::class],
            fileUploadFormValidator: $c[FileUploadFormValidator::class],
            userAuthService: $c[UserAuthService::class]
        );
    };

    $container[ShowFileAction::class] = function (Container $c) {
        return new ShowFileAction(
            view: $c[FileView::class],
            uploadedFileFactory: $c[UploadedFileFactory::class],
            commentFormFactory: $c[CommentFormFactory::class],
            messages: $c[Messages::class],
            commentFormValidator: $c[CommentFormValidator::class],
            userAuthService: $c[UserAuthService::class]
        );
    };

    $container[DownloadFileAction::class] = function (Container $c) {
        return new DownloadFileAction(
            fileDownloaderFactory: $c[FileDownloaderFactory::class],
            fileDownloaderType: $c['settings']['fileDownloaderType'],
            uploadedFileFactory: $c[UploadedFileFactory::class]
        );
    };

    $container[ShowRecentFilesAction::class] = function (Container $c) {
        return new ShowRecentFilesAction(
            view: $c[RecentFilesView::class],
            uploadedFileGateway: $c[UploadedFileGateway::class],
            uploadedFileFactory: $c[UploadedFileFactory::class],
            userAuthService: $c[UserAuthService::class]
        );
    };

    $container[SearchFilesAction::class] = function (Container $c) {
        return new SearchFilesAction($c[FileSearchView::class]);
    };

    $container[Twig::class] = function (Container $c) {
        $twig = Twig::create(
            __DIR__ . '/../resources/templates',
            [
                'cache' => $c['settings']['twig']['cache'],
                'debug' => true,
                'strict_variables' => true
            ]
        );
        $twig->addExtension($c[FileTwigExtension::class]);
        return $twig;
    };

    $container[FileUploadFormView::class] = function (Container $c) {
        return new FileUploadFormView($c[Twig::class]);
    };

    $container[RecentFilesView::class] = function (Container $c) {
        return new RecentFilesView($c[Twig::class]);
    };

    $container[FileView::class] = function (Container $c) {
        return new FileView($c[Twig::class]);
    };

    $container[MetadataService::class] = function (Container $c) {
        return new MetadataService(
            getID3: $c[GetID3::class],
            mimeTypeService: $c[MimeTypeService::class],
            gifMetadataService: $c[GifMetadataService::class]
        );
    };

    $container['csrf'] = function (Container $c) {
        return new Guard(new ResponseFactory());
    };

    $container[FileUploadFormFactory::class] = function (Container $c) {
        return new FileUploadFormFactory($c[FileUploadFormService::class]);
    };

    $container[CsrfProtection::class] = function (Container $c) {
        return new CsrfProtection($c['csrf']);
    };

    $container[FileUploadFormValidator::class] = function (Container $c) {
        return new FileUploadFormValidator($c[FileUploadFormFileValidator::class]);
    };

    $container[UploadedFileFactory::class] = function (Container $c) {
        return new UploadedFileFactory(
            uploadedFileGateway: $c[UploadedFileGateway::class],
            mediaInfoFactory: $c[MediaInfoFactory::class],
            uploadedFileService: $c[UploadedFileService::class],
            thumbnailFactory: $c[ThumbnailFactory::class],
            videoPreviewFactory: $c[VideoPreviewFactory::class],
            commentTreeFactory: $c[CommentTreeFactory::class],
            commentFactory: $c[CommentFactory::class]
        );
    };

    $container[PDO::class] = function (Container $c) {
        $dsn = sprintf(
            'pgsql:host=%s;port=%s;dbname=%s',
            $c['settings']['db']['host'],
            $c['settings']['db']['port'],
            $c['settings']['db']['name']
        );
        $pdo = new PDO(
            $dsn,
            $c['settings']['db']['username'],
            $c['settings']['db']['password']
        );
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $pdo;
    };

    $container[UploadedFileGateway::class] = function (Container $c) {
        return new UploadedFileGateway($c[PDO::class]);
    };

    $container[UploadedFileService::class] = function (Container $c) {
        return new UploadedFileService(
            uuidService: $c[UuidService::class],
            uploadedFileGateway: $c[UploadedFileGateway::class],
            routeParser: $c[RouteParser::class]
        );
    };

    $container[UuidService::class] = function (Container $c) {
        return new UuidService();
    };

    $container[FileSizeMiddleware::class] = function (Container $c) {
        return new FileSizeMiddleware(
            $c[FileUploadFormFactory::class],
            $c[FileUploadFormValidator::class]
        );
    };

    $container[HttpErrorHandlerView::class] = function (Container $c) {
        return new HttpErrorHandlerView($c[Twig::class]);
    };

    $container[FileUploadFormFileValidator::class] = function (Container $c) {
        return new FileUploadFormFileValidator($c[UploadedFileService::class]);
    };

    $container[FileUploadFormService::class] = function (Container $c) {
        return new FileUploadFormService($c[CsrfProtection::class]);
    };

    $container[PostSizeMiddleware::class] = function (Container $c) {
        return new PostSizeMiddleware();
    };

    $container[ContentLengthMiddleware::class] = function (Container $c) {
        return new ContentLengthMiddleware();
    };

    $container[FileSearchView::class] = function (Container $c) {
        return new FileSearchView($c[Twig::class]);
    };

    $container[Messages::class] = function (Container $c) {
        $storage = [];
        return new Messages($storage);
    };

    $container[SessionStartMiddleware::class] = function (Container $c) {
        return new SessionStartMiddleware($c[Messages::class]);
    };

    $container[PhpNativeDownloader::class] = function (Container $c) {
        return new PhpNativeDownloader();
    };

    $container[XSendFileDownloader::class] = function (Container $c) {
        return new XSendFileDownloader();
    };

    $container[FileDownloaderFactory::class] = function (Container $c) {
        return new FileDownloaderFactory();
    };

    $container[MediaInfoFactory::class] = function (Container $c) {
        return new MediaInfoFactory($c[MetadataService::class]);
    };

    $container[FileTwigExtension::class] = function (Container $c) {
        return new FileTwigExtension($c[UploadedFileService::class]);
    };

    $container[ThumbnailGateway::class] = function (Container $c) {
        return new ThumbnailGateway($c[PDO::class]);
    };

    $container[UploadedFileStorage::class] = function (Container $c) {
        return new UploadedFileStorage($c[UploadedFileService::class]);
    };

    $container[ThumbnailStorage::class] = function (Container $c) {
        return new ThumbnailStorage($c[GifService::class], $c[ThumbnailService::class]);
    };

    $container[FFMpeg::class] = function (Container $c) {
        return FFMpeg::create();
    };

    $container[VideoConverter::class] = function (Container $c) {
        return new VideoConverter($c[FFMpeg::class]);
    };

    $container[GifService::class] = function (Container $c) {
        return new GifService($c[FFMpeg::class]);
    };

    $container[VideoPreviewConsumer::class] = function (Container $c) {
        return new VideoPreviewConsumer(
            videoPreviewFactory: $c[VideoPreviewFactory::class],
            videoPreviewGateway: $c[VideoPreviewGateway::class],
            videoPreviewService: $c[VideoPreviewService::class],
            videoPreviewStorage: $c[VideoPreviewStorage::class],
            isEnabled: $c['settings']['workers']['videoPreviewWorker'],
            redisQueueClientFactory: $c[RedisQueueClientFactory::class],
            logger: $c[VideoPreviewConsumerLogger::class]
        );
    };

    $container[ThumbnailConsumer::class] = function (Container $c) {
        return new ThumbnailConsumer(
            thumbnailService: $c[ThumbnailService::class],
            thumbnailStorage: $c[ThumbnailStorage::class],
            thumbnailFactory: $c[ThumbnailFactory::class],
            thumbnailGateway: $c[ThumbnailGateway::class],
            isEnabled: $c['settings']['workers']['thumbnailWorker'],
            redisQueueClientFactory: $c[RedisQueueClientFactory::class],
            logger: $c[ThumbnailConsumerLogger::class]
        );
    };

    $container[VideoConverter::class] = function (Container $c) {
        return new VideoConverter($c[FFMpeg::class]);
    };

    $container[VideoPreviewService::class] = function (Container $c) {
        return new VideoPreviewService(
            videoPreviewGateway: $c[VideoPreviewGateway::class],
            mediaInfoFactory: $c[MediaInfoFactory::class],
            routeParser: $c[RouteParser::class]
        );
    };

    $container[VideoPreviewStorage::class] = function (Container $c) {
        return new VideoPreviewStorage($c[VideoConverter::class], $c[VideoPreviewService::class]);
    };

    $container[VideoPreviewFactory::class] = function (Container $c) {
        return new VideoPreviewFactory($c[VideoPreviewService::class], $c[VideoPreviewGateway::class]);
    };

    $container[FileUnitOfWork::class] = function (Container $c) {
        return new FileUnitOfWork();
    };

    $container[ThumbnailFactory::class] = function (Container $c) {
        return new ThumbnailFactory(
            thumbnailService: $c[ThumbnailService::class],
            mediaInfoFactory: $c[MediaInfoFactory::class],
            thumbnailGateway: $c[ThumbnailGateway::class]
        );
    };

    $container[ThumbnailService::class] = function (Container $c) {
        return new ThumbnailService($c[ThumbnailGateway::class]);
    };

    $container[VideoPreviewGateway::class] = function (Container $c) {
        return new VideoPreviewGateway($c[PDO::class]);
    };

    $container[RedisQueueClientFactory::class] = function (Container $c) {
        return new RedisQueueClientFactory($c['settings']['redis']);
    };

    $container[VideoPreviewProducer::class] = function (Container $c) {
        return new VideoPreviewProducer(
            isEnabled: $c['settings']['workers']['videoPreviewWorker'],
            consumer: $c[VideoPreviewConsumer::class],
            predisClientFactory: $c[PredisClientFactory::class],
            logger: $c[VideoPreviewConsumerLogger::class]
        );
    };

    $container[ThumbnailProducer::class] = function (Container $c) {
        return new ThumbnailProducer(
            isEnabled: $c['settings']['workers']['thumbnailWorker'],
            consumer: $c[ThumbnailConsumer::class],
            predisClientFactory: $c[PredisClientFactory::class],
            logger: $c[ThumbnailConsumerLogger::class]
        );
    };

    $container[PredisClientFactory::class] = function (Container $c) {
        return new PredisClientFactory($c['settings']['redis']);
    };

    $container[VideoPreviewConsumerLogger::class] = function (Container $c) {
        return new VideoPreviewConsumerLogger();
    };

    $container[ThumbnailConsumerLogger::class] = function (Container $c) {
        return new ThumbnailConsumerLogger();
    };

    $container[MimeTypeService::class] = function (Container $c) {
        return new MimeTypeService();
    };

    $container[GifMetadataService::class] = function (Container $c) {
        return new GifMetadataService($c[MimeTypeService::class]);
    };

    $container[GetID3::class] = function (Container $c) {
        return new GetID3();
    };

    $container[DownloadVideoPreviewAction::class] = function (Container $c) {
        return new DownloadVideoPreviewAction(
            fileDownloaderFactory: $c[FileDownloaderFactory::class],
            fileDownloaderType: $c['settings']['fileDownloaderType'],
            videoPreviewFactory: $c[VideoPreviewFactory::class]
        );
    };

    $container[CommentService::class] = function (Container $c) {
        return new CommentService($c[CommentGateway::class]);
    };

    $container[CommentFactory::class] = function (Container $c) {
        return new CommentFactory($c[CommentService::class], $c[CommentGateway::class]);
    };

    $container[CommentGateway::class] = function (Container $c) {
        return new CommentGateway($c[PDO::class]);
    };

    $container[AddCommentAction::class] = function (Container $c) {
        return new AddCommentAction(
            commentGateway: $c[CommentGateway::class],
            commentFormFactory: $c[CommentFormFactory::class],
            commentFormValidator: $c[CommentFormValidator::class],
            messages: $c[Messages::class],
            commentFactory: $c[CommentFactory::class],
            userAuthService: $c[UserAuthService::class]
        );
    };

    $container[CommentFormValidator::class] = function (Container $c) {
        return new CommentFormValidator(
            $c[UploadedFileGateway::class],
            $c[CommentGateway::class]
        );
    };

    $container[CommentTreeFactory::class] = function (Container $c) {
        return new CommentTreeFactory();
    };

    $container[CommentFormFactory::class] = function (Container $c) {
        return new CommentFormFactory($c[CommentFormService::class]);
    };

    $container[CommentFormService::class] = function (Container $c) {
        return new CommentFormService($c[CsrfProtection::class]);
    };

    $container[UserFactory::class] = function (Container $c) {
        return new UserFactory(
            $c[UserGateway::class],
            $c[UploadedFileFactory::class]
        );
    };

    $container[UserGateway::class] = function (Container $c) {
        return new UserGateway($c[PDO::class]);
    };

    $container[UserAuthService::class] = function (Container $c) {
        return new UserAuthService(
            uuidService: $c[UuidService::class],
            userFactory: $c[UserFactory::class],
            userGateway: $c[UserGateway::class]
        );
    };

    $container[ShowUserFilesAction::class] = function (Container $c) {
        return new ShowUserFilesAction(
            userAuthService: $c[UserAuthService::class], 
            view: $c[UserFilesView::class],
            uploadedFileGateway: $c[UploadedFileGateway::class]
        );
    };

    $container[AuthMiddleware::class] = function (Container $c) {
        return new AuthMiddleware($c[UserAuthService::class]);
    };
    
    $container[UserFilesView::class] = function (Container $c) {
        return new UserFilesView($c[Twig::class]);
    };
};
