import logging
import io

import pandas

from . import lily_pb2, lily_pb2_grpc


class Service(lily_pb2_grpc.ExcelServicer):
    def Parse(self, request, context):
        logging.info("parse excel %s" % request.content_type)
        response = lily_pb2.ExcelParseResponse()

        file = io.BytesIO(request.payload)
        doc = pandas.read_excel(file, sheet_name=None)
        for name, sheet in doc:
            logging.info("find sheet %s", name)
            sht = response.sheets.add()
            sht.name = name
            for row, item in sheet.to_dict().items():
                for col, val in item.items():
                    logging.debug('find (%s, %s, %s)', row, col, val)
                    cell = sht.cells.add()
                    cell.row = row
                    cell.col = col
                    cell.val = val
        return response

    def Generate(self, request, context):
        logging.info("parse excel %s" % request.content_type)
        bio = io.BytesIO()
        writer = pandas.ExcelWriter(bio, engine="xlsxwriter")
        for name, sheet in request.sheets:
            # https://pandas.pydata.org/docs/reference/api/pandas.DataFrame.html
            df = pandas.DataFrame(data={'col1': [1, 2], 'col2': [3, 4]})
            df.to_excel(writer, scheet_name=name)
        writer.save()

        bio.seek(0)
        response = lily_pb2.File()
        response.payload = bio.read()
        return response
