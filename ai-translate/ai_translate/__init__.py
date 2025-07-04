import logging
import tomllib
import json

import pika
from redis.cluster import RedisCluster
from types import SimpleNamespace

from .worker import handle_message


logger = logging.getLogger(__name__)


def open_redis_cluster(config):
    logger.debug("open redis cluster tcp://%s:%s/%s",
                 config['host'], config['port'], config['namespace'])
    cli = RedisCluster(host=config['host'], port=config['port'])
    logger.debug("%s", cli.get_nodes())
    return (cli, config['namespace'])


def start_consumer(context, name, config, queue, callback,proxy):
    logger.debug("open rabbitmq %s@%s:%d/%s with timeout %ds",
                 config['user'], config['host'], config['port'], config['virtual-host'], config['customer-timeout'])
    connection = pika.BlockingConnection(
        pika.ConnectionParameters(
            host=config['host'], port=config['port'],
            credentials=pika.PlainCredentials(
                config['user'], config['password']),
            virtual_host=config['virtual-host']))
    channel = connection.channel()

    def _callback(ch, method, properties, body):
        logger.info("received message(%s,%s)",
                    properties.message_id, properties.content_type)
        handle_message(context, ch, method, properties.message_id,
                       properties.content_type, json.loads(
                           body, object_hook=SimpleNamespace),
                       callback,proxy, config['customer-timeout'])

    channel.basic_consume(
        queue=queue, on_message_callback=_callback, auto_ack=False)

    logger.info('start a consumer(%s) for queue(%s)', name, queue)
    channel.start_consuming()


def launch(name, queue, config_file):
    logger.debug('load configuration from %s', config_file)
    with open(config_file, "rb") as config_fd:
        config = tomllib.load(config_fd)
        logger.debug('api-url:(%s)', config['app']['api-url'])
        redis_cli = open_redis_cluster(config['redis'])
        openai_proxy = config['app'].get('openai-proxy', None)
        start_consumer(redis_cli, name,
                       config['rabbitmq'], 
                       queue, config['app']['api-url'], 
                       openai_proxy)
