import * as jspb from 'google-protobuf'



export class File extends jspb.Message {
  getContentType(): string;
  setContentType(value: string): File;
  hasContentType(): boolean;
  clearContentType(): File;

  getPayload(): Uint8Array | string;
  getPayload_asU8(): Uint8Array;
  getPayload_asB64(): string;
  setPayload(value: Uint8Array | string): File;

  serializeBinary(): Uint8Array;
  toObject(includeInstance?: boolean): File.AsObject;
  static toObject(includeInstance: boolean, msg: File): File.AsObject;
  static serializeBinaryToWriter(message: File, writer: jspb.BinaryWriter): void;
  static deserializeBinary(bytes: Uint8Array): File;
  static deserializeBinaryFromReader(message: File, reader: jspb.BinaryReader): File;
}

export namespace File {
  export type AsObject = {
    contentType?: string,
    payload: Uint8Array | string,
  }

  export enum ContentTypeCase { 
    _CONTENT_TYPE_NOT_SET = 0,
    CONTENT_TYPE = 1,
  }
}

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

export class TexToRequest extends jspb.Message {
  getFilesMap(): jspb.Map<string, Uint8Array | string>;
  clearFilesMap(): TexToRequest;

  serializeBinary(): Uint8Array;
  toObject(includeInstance?: boolean): TexToRequest.AsObject;
  static toObject(includeInstance: boolean, msg: TexToRequest): TexToRequest.AsObject;
  static serializeBinaryToWriter(message: TexToRequest, writer: jspb.BinaryWriter): void;
  static deserializeBinary(bytes: Uint8Array): TexToRequest;
  static deserializeBinaryFromReader(message: TexToRequest, reader: jspb.BinaryReader): TexToRequest;
}

export namespace TexToRequest {
  export type AsObject = {
    filesMap: Array<[string, Uint8Array | string]>,
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

