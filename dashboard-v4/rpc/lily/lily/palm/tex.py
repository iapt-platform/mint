import logging
import tempfile
import os.path
import subprocess


from io import BytesIO


import msgpack

from . import lily_pb2, lily_pb2_grpc

from . import MinioClient

TEX2PDF_QUEUE = 'palm.lily.tex-to-pdf'


class Service(lily_pb2_grpc.TexServicer):
    def __init__(self, s3, cache, queue):
        super().__init__()
        self.s3 = s3
        self.cache = cache
        self.queue = queue

    def ToPdf(self, request, context):
        response = lily_pb2.S3File()
        response.content_type = 'application/pdf'
        response.name = MinioClient.random_filename('.pdf')
        response.bucket = self.s3.current_bucket(request.published)

        task = msgpack.packb(
            [request.SerializeToString(), response.SerializeToString()], use_bin_type=True)
        self.queue.produce(TEX2PDF_QUEUE, response.name, task)
        return response

    def ToWord(self, request, context):
        logging.info("convert tex to word %s" % request.content_type)
        response = lily_pb2.S3File()
        # TODO
        return response


def create_tex2pdf_queue_callback(s3):
    def it(ch, method, properties, body):
        logging.info("receive message %s", properties.message_id)
        _handle_tex2pdf_message(body, s3)
        ch.basic_ack(delivery_tag=method.delivery_tag)
    return it


def _handle_tex2pdf_message(message, s3):
    (request_b, response_b) = msgpack.unpackb(
        message, use_list=False, raw=False)
    request = lily_pb2.TexToRequest()
    request.ParseFromString(request_b)
    response = lily_pb2.S3File()
    response.ParseFromString(response_b)
    logging.info("convert tex to pdf(%d) ", len(request.files))
    with tempfile.TemporaryDirectory(prefix='tex-') as root:
        for name in request.files:
            with open(os.path.join(root, name), mode='wb') as fd:
                logging.debug("generate file %s/%s", root, name)
                fd.write(request.files[name])
        for _ in range(2):
            try:
                subprocess.run(
                    ['xelatex', '-halt-on-error', 'main.tex'],  check=True, cwd=root)
            except subprocess.CalledProcessError as e:
                logging.error("%s", e)
                return

        pdf_file = os.path.join(root, 'main.pdf')
        pdf_size = os.path.getsize(pdf_file)
        with open(pdf_file, mode="rb") as fd:
            pdf_data = BytesIO(fd.read())
            s3.bucket_exists(response.bucket, request.published)
            s3.put_object(response.bucket, response.name,
                          pdf_data, pdf_size, response.content_type)
            tags = {'title': request.title}
            if request.has_owner:
                tags['owner'] = request.owner
            if request.has_ttl:
                tags['ttl'] = request.ttl.seconds
            s3.set_object_tags(response.bucket, response.name, tags)
