import logging
import argparse
import tomllib
import signal
import threading
from concurrent import futures
from time import sleep

from sqlalchemy import create_engine

from . import yuanhengsi, zhuangchunjiang


logger = logging.getLogger(__name__)


def main():
    parser = argparse.ArgumentParser(description="A rbac service(gRPC).")
    parser.add_argument('-c', '--config', default='config.toml')
    parser.add_argument('-y', '--yuanhengsi', help="元亨寺's data folder")
    parser.add_argument('-z', '--zhuangchunjiang', help="庄春江工作站's data folder")
    parser.add_argument('-d', '--debug',
                        action='store_true', help='run on debug mode')
    parser.add_argument('-v', '--verbose',
                        action='version', version='2026.5.8')
    args = parser.parse_args()
    logging.basicConfig(
        format='%(asctime)s %(levelname).1s %(message)s', level=logging.DEBUG if args.debug else logging.INFO)
    logger.debug("running on debug mode")

    logger.debug("load configuration from %s", args.config)
    with open(args.config, "rb") as file:
        config = tomllib.load(file)
        db = open_db(config['postgresql'], args.debug)
        if args.zhuangchunjiang is not None:
            zhuangchunjiang.launch(db, args.zhuangchunjiang)
        if args.yuanhengsi is not None:
            yuanhengsi.launch(db, args.yuanhengsi)
        logging.info('done.')


# https://docs.sqlalchemy.org/en/20/dialects/postgresql.html#dialect-postgresql-psycopg-connect
# https://docs.sqlalchemy.org/en/20/dialects/mysql.html#module-sqlalchemy.dialects.mysql.mariadbconnector
# https://docs.sqlalchemy.org/en/20/dialects/sqlite.html#module-sqlalchemy.dialects.sqlite.pysqlite
def open_db(config, debug):
    logger.debug("open postgresql://%s@%s:%d/%s",
                 config['user'], config['host'], config['port'], config['db-name'])
    return create_engine(
        f"postgresql+psycopg://{config['user']}:{config['password']}@{config['host']}:{config['port']}/{config['db-name']}?sslmode=disable", echo=debug)
