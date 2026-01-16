#!/usr/bin/env python3

import logging

logger = logging.getLogger(__name__)

def launch(keeps):
    # TODO
    root = "change-me"
    logger.warning("try to clean %s and keep %d records", root, keeps)
    logger.info("done.")
    
if __name__ == "__main__":
    logging.basicConfig(level=logging.DEBUG, format='%(asctime)s %(levelname).1s %(name)s: %(message)s')
    # TODO
    launch(7)
    
