<?php

declare(strict_types=1);

namespace App\Views;

use Slim\Views\Twig;
use Psr\Http\Message\ResponseInterface as Response;

/**
 * Представление (MVC шаблон)
 */
class View
{
    private Twig $twig;
    private string $template;

    public function __construct(Twig $twig, string $template)
    {
        $this->twig = $twig;
        $this->template = $template;
    }

    /**
     * Вывести шаблон
     *
     * @param Response $response
     * @param array<string, mixed> $context
     * @return Response
     */
    public function render(Response $response, array $context): Response
    {
        return $this->twig->render($response, $this->template, $context);
    }
}
