import logging
import argparse
import sys
import tomllib


from palm import VERSION,  RedisClient, MinioClient, RabbitMqClient, is_stopped
from palm.tex import TEX2PDF_QUEUE, create_tex2pdf_queue_callback
from palm.server import Rpc as RpcServer

NAME = 'lily'

if __name__ == '__main__':
    parser = argparse.ArgumentParser(
        prog=NAME,
        description='A tex to pdf/word converter',
        epilog='https://github.com/saturn-xiv/palm')
    parser.add_argument('-c', '--config',
                        type=argparse.FileType(mode='rb'),
                        default='config.toml',
                        help='load configuration(toml)')
    parser.add_argument('-d', '--debug',
                        action='store_true',
                        help='run on debug mode')
    parser.add_argument('-w', '--worker',
                        help='run queue worker %s' % (TEX2PDF_QUEUE))
    parser.add_argument('-v', '--version',
                        action='store_true',
                        help=('print %s version' % NAME))
    args = parser.parse_args()
    if args.version:
        print(VERSION)
        sys.exit()
    logging.basicConfig(level=(logging.DEBUG if args.debug else logging.INFO))
    if args.debug:
        logging.debug('run on debug mode with %s', args)

    if is_stopped():
        logging.error('.stop file existed, quit...')
        sys.exit(1)

    logging.info('load configuration from %s', args.config.name)

    config = tomllib.load(args.config)
    redis_client = RedisClient(config['redis'])
    minio_client = MinioClient(config['minio'])
    rabbitmq_client = RabbitMqClient(config['rabbitmq'])
    if args.worker:
        if args.worker == TEX2PDF_QUEUE:
            callback = create_tex2pdf_queue_callback(minio_client)
            rabbitmq_client.start_consuming(TEX2PDF_QUEUE, callback)
        else:
            logging.error('unimplemented queue %s', args.worker)
            sys.exit(1)
        sys.exit()
    rpc_server = RpcServer(
        config['rpc'], minio_client, redis_client, rabbitmq_client)
    rpc_server.start()
