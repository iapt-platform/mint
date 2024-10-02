"use strict";

import {Server, ServerCredentials} from '@grpc/grpc-js';

import {Config} from './env';
import logger from './logger';
import {MarkdownService} from './protocols/morus_grpc_pb';
import { to_html } from './services/markdown';

function main() {
    const args = process.argv;
    if(args.length !== 3){
        logger.error(`USAGE: node ${args[1]} CONFIG_FILE`);
        return;
    }    
    const config = new Config("config.json");
    logger.info(`start gRPC server on http://0.0.0.0:${config.port}`);
    var server = new Server();
    server.addService(MarkdownService, {toHtml: to_html});
    server.bindAsync(`0.0.0.0:${config.port}`, ServerCredentials.createInsecure(), ()=>{
        server.start();
    });
}

main();
