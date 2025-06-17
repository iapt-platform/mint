import logging
import tomllib
import os
import sys

import pika
from redis.cluster import RedisCluster

from .worker import handle_message

logger = logging.getLogger(__name__)


def is_stopped():
    f = ".stop"
    logger.warning("file %s exists, will be exit", f)
    if os.path.exists(f):
        sys.exit(0)


def open_redis_cluster(config):
    cli = RedisCluster(host=config['host'], port=config['port'])
    logger.debug("%s", cli.get_nodes())
    return (cli, config['namespace'])


def start_consumer(context, name, queue, config):
    connection = pika.BlockingConnection(
        pika.ConnectionParameters(
            host=config['host'], port=config['port'],
            credentials=pika.PlainCredentials(
                config['user'], config['password']),
            virtual_host=config['virtual-host']))
    channel = connection.channel()

    def callback(ch, method, properties, body):
        logger.info("received message(%s,%s)",
                    properties.message_id, properties.content_type)
        handle_message(context, properties.message_id,
                       properties.content_type, body)

    channel.basic_consume(
        queue=queue, on_message_callback=callback, auto_ack=True)

    logger.info('start a consumer(%s) for queue(%s)', name, queue)
    channel.start_consuming()


def launch(name, queue, config_file):
    logger.debug('load configuration from %s', config_file)
    with open(config_file, "rb") as config_fd:
        config = tomllib.load(config_fd)
        redis_cli = open_redis_cluster(config['redis'])
        start_consumer(redis_cli, name, queue, config['rabbitmq'])
