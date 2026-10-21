<?php

declare(strict_types=1);

use App\Actions\AddCommentAction;
use Slim\App;
use App\Middleware\FileSizeMiddleware;
use App\Middleware\PostSizeMiddleware;
Use App\Actions\ShowFileUploadFormAction;
Use App\Actions\UploadFileAction;
Use App\Actions\ShowRecentFilesAction;
Use App\Actions\SearchFilesAction;
Use App\Actions\ShowFileAction;
Use App\Actions\DownloadFileAction;
use App\Actions\DownloadVideoPreviewAction;
use App\Actions\ShowUserFilesAction;
use App\Middleware\AuthMiddleware;

return function (App $app) {                    
    $app->get('/', ShowFileUploadFormAction::class)        
        ->add('csrf')
        ->setName('files.upload.form');
    $app->post('/files/upload', UploadFileAction::class)
        ->add('csrf')
        ->add(FileSizeMiddleware::class)
        ->add(PostSizeMiddleware::class)
        ->setName('files.upload');
    $app->get('/files', ShowRecentFilesAction::class)->setName('files');
    $app->get('/files/search', SearchFilesAction::class)->setName('files.search');
    $app->get('/files/{id}', ShowFileAction::class)->setName('files.show')
        ->add('csrf');
    $app->get('/files/{id}/download', DownloadFileAction::class)->setName('files.download');    
    $app->get('/video-previews/{id}/download', DownloadVideoPreviewAction::class)
        ->setName('video-previews.download');    
    $app->post('/comments/add', AddCommentAction::class)
        ->add('csrf')
        ->add(PostSizeMiddleware::class)
        ->setName('comments.add');
    $app->get('/users/{id}/files', ShowUserFilesAction::class)
        ->add(AuthMiddleware::class)
        ->setName('users.files');
};