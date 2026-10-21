<?php

declare(strict_types=1);

namespace App\Factories\CommentFactories;

use App\Entities\Comments\CommentTree;
use App\Entities\Comments\Comment;
use App\Entities\Comments\CommentNode;

/**
 * Фабрика деревьев с комментариями
 */
class CommentTreeFactory {
    /**
     * Создать дерево комментариев
     *
     * @param array<Comment> $comments
     * @return CommentTree
     */
    public function create(array $comments): CommentTree {
        $rootNodes = [];

        foreach ($comments as $comment) {
            $depth = $comment->getDepth();

            if ($depth != 1) {
                continue;
            }
            
            $rootNode = $this->createNode($comment, $comments);
            $rootNodes[] = $rootNode;
        }

        return new CommentTree($rootNodes);
    }

    /**
     * Получить список узлов-потомков
     *
     * @param Comment $comment
     * @param array<Comment> $comments
     * @return array<CommentNode>
     */
    private function getChildNodes(Comment $comment, array $comments): array {
        $childNodes = [];
        $childComments = $comments;
        $childComments = array_filter($childComments, function(Comment $childComment) use ($comment) {
            $childCommentId = $childComment->getId();
            $commentId = $comment->getId();
            if ($childCommentId != $commentId &&
                $childComment->parentId == $comment->getId()) {
                return true;
            } else {
                return false;
            }            
        });

        if (empty($childComments)) {
            return $childNodes;
        }

        foreach ($childComments as $childComment) {
            $childNode = $this->createNode($childComment, $comments);
            $childNodes[] = $childNode;
        }

        return $childNodes;
    }

    /**
     * Создать узел
     *
     * @param Comment $comment
     * @param array<Comment> $comments
     * @return CommentNode
     */
    private function createNode(Comment $comment, array $comments): CommentNode {
        $childNodes = $this->getChildNodes($comment, $comments);
        return new CommentNode($comment, $childNodes);
    }
}