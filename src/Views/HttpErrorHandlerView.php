<?php

declare(strict_types=1);

namespace App\Views;

use Slim\Views\Twig;

/**
 * Представление обработчика HTTP ошибок
 */
class HttpErrorHandlerView extends View {              
    /**
     * Конструктор
     *
     * @param Twig $twig
     */
    public function __construct(Twig $twig)
    {           
        parent::__construct($twig, 'error.html.twig');  
    }
}