<?php

declare(strict_types=1);

namespace App\Views;

use Slim\Views\Twig;

/**
 * Представление пользовательских файлов
 */
class UserFilesView extends View {          
    /**
     * Конструктор
     *
     * @param Twig $twig
     */
    public function __construct(Twig $twig)
    {           
        parent::__construct($twig, 'files/user-files.html.twig');  
    }
}