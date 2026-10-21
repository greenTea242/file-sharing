import * as bootstrap from 'bootstrap';
import '../scss/styles.scss';
import 'bootstrap-icons/font/bootstrap-icons.css';

import Plyr from 'plyr';
import 'plyr/dist/plyr.css';

import '../css/styles.css';
import { CommentReplyService } from './CommentReplyService.js';

const player = new Plyr('#player');
let commentReplyService = new CommentReplyService();

document.addEventListener('DOMContentLoaded', () => {
    let commentReplyElem = commentReplyService.getCommentReplyElem();    
    document.querySelectorAll('.btn-reply').forEach(button => {
        button.addEventListener('click', function () {
            let commentContentElem = commentReplyService.getCommentContentElem(button);        
            commentReplyService.toggleCommentReply(commentContentElem, commentReplyElem);

            if (commentReplyService.isCommentReplyVisible(commentReplyElem)) {
                commentReplyService.setParentId(commentContentElem, commentReplyElem);
                commentReplyService.scrollToCommentReply(commentReplyElem);
                commentReplyService.resetTextHeight();
            }
        });
    });
});