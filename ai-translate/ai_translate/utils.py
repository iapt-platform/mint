import logging
import os
import sys

logger = logging.getLogger(__name__)


def is_stopped():
    f = ".stop"
    if os.path.exists(f):
        logger.warning("file %s exists, will be exit", f)
        sys.exit(0)
