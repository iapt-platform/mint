import * as jspb from 'google-protobuf'

import * as google_protobuf_duration_pb from 'google-protobuf/google/protobuf/duration_pb';


export class ExcelModel extends jspb.Message {
  getSheetsList(): Array<ExcelModel.Sheet>;
  setSheetsList(value: Array<ExcelModel.Sheet>): ExcelModel;
  clearSheetsList(): ExcelModel;
  addSheets(value?: ExcelModel.Sheet, index?: number): ExcelModel.Sheet;

  serializeBinary(): Uint8Array;
  toObject(includeInstance?: boolean): ExcelModel.AsObject;
  static toObject(includeInstance: boolean, msg: ExcelModel): ExcelModel.AsObject;
  static serializeBinaryToWriter(message: ExcelModel, writer: jspb.BinaryWriter): void;
  static deserializeBinary(bytes: Uint8Array): ExcelModel;
  static deserializeBinaryFromReader(message: ExcelModel, reader: jspb.BinaryReader): ExcelModel;
}

export namespace ExcelModel {
  export type AsObject = {
    sheetsList: Array<ExcelModel.Sheet.AsObject>,
  }

  export class Sheet extends jspb.Message {
    getName(): string;
    setName(value: string): Sheet;

    getCellsList(): Array<ExcelModel.Sheet.Cell>;
    setCellsList(value: Array<ExcelModel.Sheet.Cell>): Sheet;
    clearCellsList(): Sheet;
    addCells(value?: ExcelModel.Sheet.Cell, index?: number): ExcelModel.Sheet.Cell;

    serializeBinary(): Uint8Array;
    toObject(includeInstance?: boolean): Sheet.AsObject;
    static toObject(includeInstance: boolean, msg: Sheet): Sheet.AsObject;
    static serializeBinaryToWriter(message: Sheet, writer: jspb.BinaryWriter): void;
    static deserializeBinary(bytes: Uint8Array): Sheet;
    static deserializeBinaryFromReader(message: Sheet, reader: jspb.BinaryReader): Sheet;
  }

  export namespace Sheet {
    export type AsObject = {
      name: string,
      cellsList: Array<ExcelModel.Sheet.Cell.AsObject>,
    }

    export class Cell extends jspb.Message {
      getRow(): number;
      setRow(value: number): Cell;

      getCol(): number;
      setCol(value: number): Cell;

      getVal(): string;
      setVal(value: string): Cell;

      serializeBinary(): Uint8Array;
      toObject(includeInstance?: boolean): Cell.AsObject;
      static toObject(includeInstance: boolean, msg: Cell): Cell.AsObject;
      static serializeBinaryToWriter(message: Cell, writer: jspb.BinaryWriter): void;
      static deserializeBinary(bytes: Uint8Array): Cell;
      static deserializeBinaryFromReader(message: Cell, reader: jspb.BinaryReader): Cell;
    }

    export namespace Cell {
      export type AsObject = {
        row: number,
        col: number,
        val: string,
      }
    }

  }

}

export class S3File extends jspb.Message {
  getBucket(): string;
  setBucket(value: string): S3File;

  getName(): string;
  setName(value: string): S3File;

  getContentType(): string;
  setContentType(value: string): S3File;

  serializeBinary(): Uint8Array;
  toObject(includeInstance?: boolean): S3File.AsObject;
  static toObject(includeInstance: boolean, msg: S3File): S3File.AsObject;
  static serializeBinaryToWriter(message: S3File, writer: jspb.BinaryWriter): void;
  static deserializeBinary(bytes: Uint8Array): S3File;
  static deserializeBinaryFromReader(message: S3File, reader: jspb.BinaryReader): S3File;
}

export namespace S3File {
  export type AsObject = {
    bucket: string,
    name: string,
    contentType: string,
  }
}

export class S3GetFileRequest extends jspb.Message {
  getBucket(): string;
  setBucket(value: string): S3GetFileRequest;

  getName(): string;
  setName(value: string): S3GetFileRequest;

  getTtl(): google_protobuf_duration_pb.Duration | undefined;
  setTtl(value?: google_protobuf_duration_pb.Duration): S3GetFileRequest;
  hasTtl(): boolean;
  clearTtl(): S3GetFileRequest;

  serializeBinary(): Uint8Array;
  toObject(includeInstance?: boolean): S3GetFileRequest.AsObject;
  static toObject(includeInstance: boolean, msg: S3GetFileRequest): S3GetFileRequest.AsObject;
  static serializeBinaryToWriter(message: S3GetFileRequest, writer: jspb.BinaryWriter): void;
  static deserializeBinary(bytes: Uint8Array): S3GetFileRequest;
  static deserializeBinaryFromReader(message: S3GetFileRequest, reader: jspb.BinaryReader): S3GetFileRequest;
}

export namespace S3GetFileRequest {
  export type AsObject = {
    bucket: string,
    name: string,
    ttl?: google_protobuf_duration_pb.Duration.AsObject,
  }
}

export class S3GetFileResponse extends jspb.Message {
  getUrl(): string;
  setUrl(value: string): S3GetFileResponse;

  serializeBinary(): Uint8Array;
  toObject(includeInstance?: boolean): S3GetFileResponse.AsObject;
  static toObject(includeInstance: boolean, msg: S3GetFileResponse): S3GetFileResponse.AsObject;
  static serializeBinaryToWriter(message: S3GetFileResponse, writer: jspb.BinaryWriter): void;
  static deserializeBinary(bytes: Uint8Array): S3GetFileResponse;
  static deserializeBinaryFromReader(message: S3GetFileResponse, reader: jspb.BinaryReader): S3GetFileResponse;
}

export namespace S3GetFileResponse {
  export type AsObject = {
    url: string,
  }
}

export class TexToRequest extends jspb.Message {
  getTitle(): string;
  setTitle(value: string): TexToRequest;

  getFilesMap(): jspb.Map<string, Uint8Array | string>;
  clearFilesMap(): TexToRequest;

  getTtl(): google_protobuf_duration_pb.Duration | undefined;
  setTtl(value?: google_protobuf_duration_pb.Duration): TexToRequest;
  hasTtl(): boolean;
  clearTtl(): TexToRequest;

  getOwner(): string;
  setOwner(value: string): TexToRequest;
  hasOwner(): boolean;
  clearOwner(): TexToRequest;

  getPublished(): boolean;
  setPublished(value: boolean): TexToRequest;

  serializeBinary(): Uint8Array;
  toObject(includeInstance?: boolean): TexToRequest.AsObject;
  static toObject(includeInstance: boolean, msg: TexToRequest): TexToRequest.AsObject;
  static serializeBinaryToWriter(message: TexToRequest, writer: jspb.BinaryWriter): void;
  static deserializeBinary(bytes: Uint8Array): TexToRequest;
  static deserializeBinaryFromReader(message: TexToRequest, reader: jspb.BinaryReader): TexToRequest;
}

export namespace TexToRequest {
  export type AsObject = {
    title: string,
    filesMap: Array<[string, Uint8Array | string]>,
    ttl?: google_protobuf_duration_pb.Duration.AsObject,
    owner?: string,
    published: boolean,
  }

  export enum TtlCase { 
    _TTL_NOT_SET = 0,
    TTL = 7,
  }

  export enum OwnerCase { 
    _OWNER_NOT_SET = 0,
    OWNER = 8,
  }
}

export class EpubBuildRequest extends jspb.Message {
  serializeBinary(): Uint8Array;
  toObject(includeInstance?: boolean): EpubBuildRequest.AsObject;
  static toObject(includeInstance: boolean, msg: EpubBuildRequest): EpubBuildRequest.AsObject;
  static serializeBinaryToWriter(message: EpubBuildRequest, writer: jspb.BinaryWriter): void;
  static deserializeBinary(bytes: Uint8Array): EpubBuildRequest;
  static deserializeBinaryFromReader(message: EpubBuildRequest, reader: jspb.BinaryReader): EpubBuildRequest;
}

export namespace EpubBuildRequest {
  export type AsObject = {
  }
}

