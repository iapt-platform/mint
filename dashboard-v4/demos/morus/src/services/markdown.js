"use strict";

import createDOMPurify from 'dompurify';
import {JSDOM} from 'jsdom';
import {parse as parse_markdown} from 'marked';

import {MarkdownToHtmlResponse} from '../protocols/morus_pb';

export const to_html = (call, callback) => {
    const request = call.request.getPayload();
    const html = parse_markdown(request);
    var reply = new MarkdownToHtmlResponse();
    if(call.request.sanitize){
        const window = new JSDOM('').window;
        const purify = createDOMPurify(window);    
        const clean = purify.sanitize(html); 
        reply.setPayload(clean);
    }else{
        reply.setPayload(html);
    }
    callback(null, reply);
}
