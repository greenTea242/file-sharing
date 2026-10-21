<?php

declare(strict_types=1);

namespace App\Views;

use Slim\Views\Twig;

/**
 * Представление формы загрузки файла
 */
class FileUploadFormView extends View {     
    /**
     * Конструктор
     *
     * @param Twig $twig
     */         
    public function __construct(Twig $twig)
    {           
        parent::__construct($twig, 'file-upload-form.html.twig');  
    }
}