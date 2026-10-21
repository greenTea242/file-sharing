<?php

declare(strict_types=1);

namespace App\Views;

use Slim\Views\Twig;

/**
 * Представление файла
 */
class FileView extends View {          
    /**
     * Конструктор
     *
     * @param Twig $twig
     */
    public function __construct(Twig $twig)
    {           
        $path = 'file.html.twig';
        parent::__construct($twig, $path);  
    }
}