<?php

declare(strict_types=1);

namespace App\Entities\Comments;

/**
 * Узел с комментарием
 */
readonly class CommentNode {
    public readonly Comment $comment;
    /**
     * Узлы потомков
     *
     * @var array<CommentNode>
     */                
    public readonly array $childNodes;

    /**
     * Конструктор
     *
     * @param Comment $comment
     * @param array<CommentNode> $childNodes
     */
    public function __construct(
        Comment $comment, 
        array $childNodes = []
    ) {
        $this->comment = $comment;
        $this->childNodes = $childNodes;
    }

    /**
     * Подсчитать число потомков
     *
     * @return integer
     */
    public function countDescendantNodes(): int {
        $descendantNodes = count($this->childNodes);
        foreach ($this->childNodes as $childNode) {
            $descendantNodes += $childNode->countDescendantNodes();
        }
        return $descendantNodes;
    }
}