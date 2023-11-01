
import logging
from time import sleep
from concurrent import futures
import threading

import psycopg
import pika
import grpc
from grpc_health.v1 import health_pb2, health, health_pb2_grpc

from . import lily_pb2_grpc, excel, tex


VERSION = '2023.9.29'


def _health_checker(servicer, name):
    while True:
        servicer.set(name, health_pb2.HealthCheckResponse.SERVING)
        sleep(5)


def _setup_health_thread(server):
    servicer = health.HealthServicer(
        experimental_non_blocking=True,
        experimental_thread_pool=futures.ThreadPoolExecutor(max_workers=2)
    )
    health_pb2_grpc.add_HealthServicer_to_server(servicer, server)
    health_checker_thread = threading.Thread(
        target=_health_checker,
        args=(servicer, 'palm.lily'),
        daemon=True
    )
    health_checker_thread.start()


def start_server(addr, workers):
    server = grpc.server(futures.ThreadPoolExecutor(max_workers=workers))
    lily_pb2_grpc.add_ExcelServicer_to_server(excel.Service(), server)
    lily_pb2_grpc.add_TexServicer_to_server(tex.Service(), server)
    _setup_health_thread(server)
    server.add_insecure_port(addr)
    server.start()
    logging.info(
        "Lily gRPC server started, listening on %s with %d threads", addr, workers)
    try:
        server.wait_for_termination()
    except KeyboardInterrupt:
        logging.warn('exited...')
        server.stop(0)


# https://pika.readthedocs.io/en/stable/modules/parameters.html
def rabbitmq_parameters(config):
    credentials = pika.PlainCredentials(config['user'], config['password'])
    parameters = pika.ConnectionParameters(
        config['host'],
        config['port'],
        config['virtual-host'],
        credentials)
    return parameters


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
