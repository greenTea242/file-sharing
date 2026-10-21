// Сервис для ответа на комментарий
export class CommentReplyService {
    // Открыть или закрыть ответ на комментарий
    toggleCommentReply(commentContentElem, commentReplyElem) {
        let commentReplyInCommentContent = commentContentElem.contains(commentReplyElem);

        if (this.isCommentReplyVisible(commentReplyElem) &&
            commentReplyInCommentContent) {
            this.#hideCommentReply(commentReplyElem);
        } else {
            this.#showCommentReply(commentReplyElem);
        }

        if (!commentReplyInCommentContent) {
            commentContentElem.append(commentReplyElem);
        }
    }

    // Получить элемент контента комментария
    getCommentContentElem(button) {
        let commentContentElem = button.closest('.comment-content');
        if (!commentContentElem) {
            throw new Error('Элемент .comment-content не найден');
        }
        return commentContentElem;
    }

    // Получить элемент ответа на комментарий
    getCommentReplyElem() {
        let commentReplyElem = document.querySelector('#comment-reply');
        if (!commentReplyElem) {
            throw new Error('Элемент #comment-reply не найден');
        }
        return commentReplyElem;
    }

    // Установить родительский идентификатор
    setParentId(commentContentElem, commentReplyElem) {
        let commentId = this.#getCommentId(commentContentElem);
        let parentIdElem = this.#getParentIdElem(commentReplyElem);
        parentIdElem.value = commentId;
    }

    // Ответ на ответ к комментарию виден?
    isCommentReplyVisible(commentReplyElem) {
        if (commentReplyElem.classList.contains('d-none')) {
            return false;
        } else {
            return true;
        }
    }

    // Просролить на ответ к коментарию
    scrollToCommentReply(commentReplyElem) {
        commentReplyElem.scrollIntoView();
    }

    // Сбросить высоту текста ответа к комментарию
    resetTextHeight() {
        let textElem = this.#getText();
        textElem.style.height = 'auto'; 
    }

    // Получить идентификатор комментария
    #getCommentId(commentContentElem) {        
        let commentIdElem = commentContentElem.querySelector('.comment-id');
        if (!commentIdElem) {
            throw new Error('Элемент .comment-id не найден');
        }
        return commentIdElem.innerHTML;
    }

    #getParentIdElem(commentReplyElem) {
        let parentIdElem = commentReplyElem.querySelector('input[name="parentId"]');
        if (!parentIdElem) {
            throw new Error('Элемент input[name="parentId" не найден');
        }
        return parentIdElem;
    }

    #showCommentReply(commentReplyElem) {
        commentReplyElem.classList.remove('d-none');
    }

    #hideCommentReply(commentReplyElem) {
        commentReplyElem.classList.add('d-none');
    }

    #getText() {
        let textElem = document.querySelector('#comment-reply-text');
        if (!textElem) {
            throw new Error('Элемент #comment-reply-text не найден');
        }
        return textElem;
    }
}