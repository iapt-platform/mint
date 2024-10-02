import logging
import json
import uuid
import os.path


from datetime import timedelta
from datetime import datetime


import psycopg
import pika
from redis import Redis
from redis.cluster import RedisCluster
from minio import Minio
from minio.versioningconfig import VersioningConfig as MinioVersioningConfig
from minio.commonconfig import ENABLED as MinioEnabled, Tags as MinioTags


VERSION = '2023.11.10'


def is_stopped():
    return os.path.isfile('.stop')


class RedisClient:
    def __init__(self, config):
        self.namespace = config['namespace']
        if config["db"]:
            self.connection = self.connection = Redis(
                host=config['host'], port=config['port'], db=config['db'])
            logging.info('connect redis single node tcp://%s:%d/%d with namespace(%s)',
                         config['host'], config['port'], config['db'], self.namespace)
        else:
            self.connection = RedisCluster(
                host=config['host'], port=config['port'])
            logging.info('connect redis cluster nodes with namespace(%s) in %s', self.namespace, list(
                map(lambda x: '%s:%d(%s)' % (x.host, x.port, x.server_type), self.connection.get_nodes())))
        self.connection.ping()

    def set(self, key, val, ttl=0):
        self.connection.setex(self._key(key), timedelta(seconds=ttl), val)

    def get(self, key):
        return self.connection.get(self._key(key))

    def _key(self, k):
        return '%s://%s' % (self.namespace, k)


class MinioClient:
    def __init__(self, config):
        logging.debug("connect to minio %s", config['endpoint'])
        self.namespace = config['namespace']
        self.connection = Minio(
            config['endpoint'],
            access_key=config['access-key'],
            secret_key=config['secret-key'],
            secure=config['secure'])
        logging.debug('found buckets: %s', self.list_buckets())

    def put_object(self, bucket, name, data, length, content_type):
        logging.debug("try to upload(%s, %s, %s) with %d bytes",
                      bucket, name, content_type, length)
        result = self.connection.put_object(
            bucket, name, data, length, content_type=content_type)
        logging.info("uploaded %s, etag: %s, version-id: %s",
                     result.object_name, result.etag, result.version_id)

    def get_object_url(self, bucket, name, ttl=60*60*24*7):
        return self.connection.presigned_get_object(bucket, name, expires=timedelta(seconds=ttl))

    def set_object_tags(self, bucket, name, tags):
        tmp = MinioTags.new_object_tags()
        for k, v in tags:
            tmp[k] = v
        self.connection.set_object_tags(bucket, name, tmp)

    def bucket_exists(self, bucket, published=False):
        ok = self.connection.bucket_exists(bucket)
        if not ok:
            logging.warning("bucket %s isn't existed, try to create it")
            self.connection.make_bucket(bucket)
            self.connection.set_bucket_versioning(
                bucket, MinioVersioningConfig(MinioEnabled))

        if published:
            policy = {
                "Version": "2023-10-06",
                "Statement": [
                    {
                        "Effect": "Allow",
                        "Principal": {"AWS": "*"},
                        "Action": [
                            "s3:GetObject"
                        ],
                        "Resource": "arn:aws:s3:::%s/*" % bucket,
                    },
                ],
            }
            self.connection.set_bucket_policy(bucket, json.dumps(policy))

    def list_buckets(self):
        return list(map(lambda x: x.name, self.connection.list_buckets()))

    def current_bucket(self, published):
        return '-' .join([self.namespace, datetime.now().strftime("%Y"), ('o' if published else 'p')])

    def random_filename(ext=''):
        return str(uuid.uuid4())+ext


# https://pika.readthedocs.io/en/stable/modules/parameters.html
class RabbitMqClient:
    def __init__(self, config):
        credentials = pika.PlainCredentials(config['user'], config['password'])
        self.parameters = pika.ConnectionParameters(
            config['host'],
            config['port'],
            config['virtual-host'],
            credentials)

    def produce(self, queue, id, message):
        logging.info("publish message(%s) to (%s) with %d bytes",
                     id, queue, len(message))
        with pika.BlockingConnection(self.parameters) as con:
            ch = con.channel()
            ch.queue_declare(queue=queue, durable=True)
            ch.basic_publish(exchange='', routing_key=queue,
                             body=message, properties=pika.BasicProperties(message_id=id, delivery_mode=pika.spec.PERSISTENT_DELIVERY_MODE))

    def start_consuming(self, queue, callback):
        logging.info("start consumer for %s", queue)

        def handler(ch, method, properties, body):
            callback(ch, method, properties, body)
            if is_stopped():
                logging.warning("stop consumer")
                ch.stop_consuming()

        with pika.BlockingConnection(self.parameters) as con:
            ch = con.channel()
            ch.queue_declare(queue=queue, durable=True)
            ch.basic_qos(prefetch_count=1)
            ch.basic_consume(queue=queue, on_message_callback=handler)
            try:
                ch.start_consuming()
            except KeyboardInterrupt:
                logging.warning("quit consumer...")
                ch.stop_consuming()


# -----------------------------------------------------------------------------


# https://www.postgresql.org/docs/current/libpq-connect.html


def postgresql_url(config):
    logging.debug('open postgresql://%s@%s:%d/%s',
                  config['user'], config['host'], config['port'], config['name'])
    url = 'host=%s port=%d user=%s password=%s dbname=%s sslmode=disable' % (
        config['host'], config['port'], config['user'], config['password'], config['name'])
    with psycopg.connect(url) as db:
        cur = db.cursor()
        cur.execute('SELECT VERSION(), CURRENT_TIMESTAMP')
        row = cur.fetchone()
        if row:
            logging.debug("%s %s", row[0], row[1])
    return url
