#!/usr/bin/env python3

import logging
import argparse
import os
import sys
import shutil

logger = logging.getLogger(__name__)


def launch(root, keeps, debug):
    logger.warning("try to clean %s and keep recent %d items", root, keeps)
    items = os.listdir(root)
    logger.debug("found %d files", len(items))
    if len(items) <= keeps:
        return
    items.sort(key=lambda it: os.path.getmtime(
        os.path.join(root, it)), reverse=True)
    items = items[keeps:]
    for it in items:
        it = os.path.join(root, it)
        logger.warning("delete %s", it)
        if not debug:
            shutil.rmtree(it)


if __name__ == "__main__":
    parser = argparse.ArgumentParser(
        prog='mint',
        description='Clean legacy files',
        epilog='https://github.com/iapt-platform/mint')
    parser.add_argument('-k', '--keep', type=int, default=7)
    parser.add_argument('-d', '--debug', action='store_true')
    parser.add_argument('-v', '--version', action='version',
                        version='%(prog)s v2026.1.17')
    args = parser.parse_args()
    logging.basicConfig(
        level=logging.DEBUG if args.debug else logging.INFO, format='%(asctime)s %(levelname).1s %(name)s: %(message)s')
    logging.debug('run on debug mode')
    if args.keep < 2:
        logging.error("keeps must lager than 2")
        sys.exit(1)
    root = os.getcwd()
    launch(root, args.keep, args.debug)
    logger.info("done.")
