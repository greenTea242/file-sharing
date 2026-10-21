<?php

declare(strict_types=1);

namespace App\Views;

use Slim\Views\Twig;

/**
 * Представление последних файлов
 */
class RecentFilesView extends View {          
    /**
     * Конструктор
     *
     * @param Twig $twig
     */
    public function __construct(Twig $twig)
    {           
        parent::__construct($twig, 'files/recent-files.html.twig');  
    }
}