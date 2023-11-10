from . import lily_pb2, lily_pb2_grpc


class Service(lily_pb2_grpc.S3Servicer):
    def __init__(self, s3):
        super().__init__()
        self.s3 = s3

    def GetFile(self, request, context):
        response = lily_pb2.S3GetFileResponse()
        response.url = self.s3.get_object_url(
            request.bucket, request.name, request.ttl.seconds)
        return response
