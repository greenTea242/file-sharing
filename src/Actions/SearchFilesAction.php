<?php

declare(strict_types=1);

namespace App\Actions;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Views\FileSearchView;
use App\Actions\Action;

/**
 * Действие для поиска файлов
 */
class SearchFilesAction extends Action
{
    private FileSearchView $view;

    /**
     * Конструктор
     *
     * @param FileSearchView $view
     */
    public function __construct(
        FileSearchView $view
    ) {
        $this->view = $view;
    }

    /**
     * {@inheritdoc}
     */
    protected function action(
        Request $request,
        Response $response,
        array $args
    ): Response {
        $context = [
            'activeTab' => null
        ];
        return $this->view->render($response, $context);
    }
}
