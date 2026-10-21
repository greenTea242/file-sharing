<?php

declare(strict_types=1);

namespace App\Entities\Comments;

use Exception;

/**
 * Дерево комментариев
 */
class CommentTree
{
    /**
     * Корневые узлы
     *
     * @var array<CommentNode>
     */
    public readonly array $rootNodes;

    /**
     * Конструктор
     *
     * @param array<CommentNode> $rootNodes
     */
    public function __construct(array $rootNodes)
    {
        $this->rootNodes = $rootNodes;        
    }

    /**
     * Подсчитать число узлов
     *
     * @return integer
     */
    public function countNodes(): int {
        $nodesCount = count($this->rootNodes);

        foreach ($this->rootNodes as $rootNode) {
            $nodesCount += $rootNode->countDescendantNodes();            
        }

        return $nodesCount;
    }
}
