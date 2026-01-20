import logging
import argparse
import sys
import os
import datetime

from . import launch
from .utils import is_stopped

logger = logging.getLogger(__name__)


def main():
    parser = argparse.ArgumentParser(
        prog='ai-translate',
        description='An OpenAI consumer process',
        epilog='https://github.com/iapt-platform/mint/tree/master')
    parser.add_argument('-c', '--config', type=str,
                        default='config.toml', help='configuration file')
    parser.add_argument('-q', '--queue', type=str, required=True,
                        help='queue name')
    parser.add_argument('-n', '--name', type=str, required=True,
                        help='consumer name')
    parser.add_argument('-d', '--debug',
                        action='store_true', help='run on debug mode')
    parser.add_argument('-v', '--version',
                        action='version', version='%(prog)s v2026.1.16')
    args = parser.parse_args()

    if args.debug:
        logging.basicConfig(
            level=args.debug, format='%(levelname)-5s %(asctime)s(%(pathname)s %(lineno)d): %(message)s')
    else:
        now = datetime.datetime.now()
        logging.basicConfig(filename=now.strftime("%Y%m%d%H%M%S.log"), level=logging.INFO,
                            format='%(levelname)-5s %(asctime)s(%(module)s): %(message)s')
    is_stopped()
    try:
        launch(args.name, args.queue, args.config)
    except KeyboardInterrupt:
        logger.warning('receive interrupted signal, exit...')
        try:
            sys.exit(0)
        except SystemExit:
            os._exit(0)


if __name__ == '__main__':
    main()
