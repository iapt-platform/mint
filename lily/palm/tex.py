import logging
import tempfile
import os.path
import subprocess

from . import lily_pb2, lily_pb2_grpc


def _tmp_root():
    return os.path.join(tempfile.tempdir())


class Service(lily_pb2_grpc.TexServicer):
    def ToPdf(self, request, context):
        logging.info("convert tex to pdf(%d)" % len(request.files))
        with tempfile.TemporaryDirectory(prefix='tex-') as root:
            for name in request.files:
                with open(os.path.join(root, name), mode='wb') as fd:
                    logging.debug("generate file %s/%s", root, name)
                    fd.write(request.files[name])
            for x in range(2):
                subprocess.run(
                    ['xelatex', '-halt-on-error', 'main.tex'], cwd=root)
            with open(os.path.join(root, 'main.pdf'), mode="rb") as fd:
                response = lily_pb2.File()
                response.content_type = 'application/pdf'
                response.payload = fd.read()
                return response

    def ToWord(self, request, context):
        logging.info("convert tex to word %s" % request.content_type)
        response = lily_pb2.File()
        return response
