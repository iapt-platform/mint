import logging
import tomllib
import json
import os
import sys

import pika
from redis.cluster import RedisCluster
from types import SimpleNamespace

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
    mq_config = config['rabbitmq']
    connection = pika.BlockingConnection(
        pika.ConnectionParameters(
            host=mq_config['host'], port=mq_config['port'],
            credentials=pika.PlainCredentials(
                mq_config['user'], mq_config['password']),
            virtual_host=mq_config['virtual-host']))
    channel = connection.channel()

    def callback(ch, method, properties, body):
        logger.info("received message(%s,%s)",
                    properties.message_id, properties.content_type)
        handle_message(context, ch, method, properties.message_id,
                       properties.content_type, json.loads(
                           body, object_hook=SimpleNamespace),
                       config['app']['api-url'], config['rabbitmq']['customer-timeout'])

    channel.basic_consume(
        queue=queue, on_message_callback=callback, auto_ack=False)

    logger.info('start a consumer(%s) for queue(%s)', name, queue)
    channel.start_consuming()


def launch(name, queue, config_file):
    logger.debug('load configuration from %s', config_file)
    with open(config_file, "rb") as config_fd:
        config = tomllib.load(config_fd)
        redis_cli = open_redis_cluster(config['redis'])
        logger.info('api-url:(%s)', config['app']['api-url'])
        logger.info('customer-timeout:(%s)',
                    config['rabbitmq']['customer-timeout'])
        start_consumer(redis_cli, name, queue, config)
