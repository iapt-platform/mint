import * as jspb from 'google-protobuf'



export class MarkdownToHtmlRequest extends jspb.Message {
  getPayload(): string;
  setPayload(value: string): MarkdownToHtmlRequest;

  getSanitize(): boolean;
  setSanitize(value: boolean): MarkdownToHtmlRequest;

  serializeBinary(): Uint8Array;
  toObject(includeInstance?: boolean): MarkdownToHtmlRequest.AsObject;
  static toObject(includeInstance: boolean, msg: MarkdownToHtmlRequest): MarkdownToHtmlRequest.AsObject;
  static serializeBinaryToWriter(message: MarkdownToHtmlRequest, writer: jspb.BinaryWriter): void;
  static deserializeBinary(bytes: Uint8Array): MarkdownToHtmlRequest;
  static deserializeBinaryFromReader(message: MarkdownToHtmlRequest, reader: jspb.BinaryReader): MarkdownToHtmlRequest;
}

export namespace MarkdownToHtmlRequest {
  export type AsObject = {
    payload: string,
    sanitize: boolean,
  }
}

export class MarkdownToHtmlResponse extends jspb.Message {
  getPayload(): string;
  setPayload(value: string): MarkdownToHtmlResponse;

  serializeBinary(): Uint8Array;
  toObject(includeInstance?: boolean): MarkdownToHtmlResponse.AsObject;
  static toObject(includeInstance: boolean, msg: MarkdownToHtmlResponse): MarkdownToHtmlResponse.AsObject;
  static serializeBinaryToWriter(message: MarkdownToHtmlResponse, writer: jspb.BinaryWriter): void;
  static deserializeBinary(bytes: Uint8Array): MarkdownToHtmlResponse;
  static deserializeBinaryFromReader(message: MarkdownToHtmlResponse, reader: jspb.BinaryReader): MarkdownToHtmlResponse;
}

export namespace MarkdownToHtmlResponse {
  export type AsObject = {
    payload: string,
  }
}

