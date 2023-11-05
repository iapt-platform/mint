import * as jspb from 'google-protobuf'



export class SearchRequest extends jspb.Message {
  getKeywordsList(): Array<string>;
  setKeywordsList(value: Array<string>): SearchRequest;
  clearKeywordsList(): SearchRequest;
  addKeywords(value: string, index?: number): SearchRequest;

  getBook(): number;
  setBook(value: number): SearchRequest;

  getPage(): SearchRequest.Page | undefined;
  setPage(value?: SearchRequest.Page): SearchRequest;
  hasPage(): boolean;
  clearPage(): SearchRequest;

  serializeBinary(): Uint8Array;
  toObject(includeInstance?: boolean): SearchRequest.AsObject;
  static toObject(includeInstance: boolean, msg: SearchRequest): SearchRequest.AsObject;
  static serializeBinaryToWriter(message: SearchRequest, writer: jspb.BinaryWriter): void;
  static deserializeBinary(bytes: Uint8Array): SearchRequest;
  static deserializeBinaryFromReader(message: SearchRequest, reader: jspb.BinaryReader): SearchRequest;
}

export namespace SearchRequest {
  export type AsObject = {
    keywordsList: Array<string>,
    book: number,
    page?: SearchRequest.Page.AsObject,
  }

  export class Page extends jspb.Message {
    getIndex(): number;
    setIndex(value: number): Page;

    getSize(): number;
    setSize(value: number): Page;

    serializeBinary(): Uint8Array;
    toObject(includeInstance?: boolean): Page.AsObject;
    static toObject(includeInstance: boolean, msg: Page): Page.AsObject;
    static serializeBinaryToWriter(message: Page, writer: jspb.BinaryWriter): void;
    static deserializeBinary(bytes: Uint8Array): Page;
    static deserializeBinaryFromReader(message: Page, reader: jspb.BinaryReader): Page;
  }

  export namespace Page {
    export type AsObject = {
      index: number,
      size: number,
    }
  }


  export enum PageCase { 
    _PAGE_NOT_SET = 0,
    PAGE = 99,
  }
}

export class SearchResponse extends jspb.Message {
  getItemsList(): Array<SearchResponse.Item>;
  setItemsList(value: Array<SearchResponse.Item>): SearchResponse;
  clearItemsList(): SearchResponse;
  addItems(value?: SearchResponse.Item, index?: number): SearchResponse.Item;

  getPage(): SearchRequest.Page | undefined;
  setPage(value?: SearchRequest.Page): SearchResponse;
  hasPage(): boolean;
  clearPage(): SearchResponse;

  getTotal(): number;
  setTotal(value: number): SearchResponse;

  serializeBinary(): Uint8Array;
  toObject(includeInstance?: boolean): SearchResponse.AsObject;
  static toObject(includeInstance: boolean, msg: SearchResponse): SearchResponse.AsObject;
  static serializeBinaryToWriter(message: SearchResponse, writer: jspb.BinaryWriter): void;
  static deserializeBinary(bytes: Uint8Array): SearchResponse;
  static deserializeBinaryFromReader(message: SearchResponse, reader: jspb.BinaryReader): SearchResponse;
}

export namespace SearchResponse {
  export type AsObject = {
    itemsList: Array<SearchResponse.Item.AsObject>,
    page?: SearchRequest.Page.AsObject,
    total: number,
  }

  export class Item extends jspb.Message {
    getRank(): number;
    setRank(value: number): Item;

    getHighlight(): string;
    setHighlight(value: string): Item;

    getBook(): number;
    setBook(value: number): Item;

    getParagraph(): number;
    setParagraph(value: number): Item;

    getContent(): string;
    setContent(value: string): Item;

    serializeBinary(): Uint8Array;
    toObject(includeInstance?: boolean): Item.AsObject;
    static toObject(includeInstance: boolean, msg: Item): Item.AsObject;
    static serializeBinaryToWriter(message: Item, writer: jspb.BinaryWriter): void;
    static deserializeBinary(bytes: Uint8Array): Item;
    static deserializeBinaryFromReader(message: Item, reader: jspb.BinaryReader): Item;
  }

  export namespace Item {
    export type AsObject = {
      rank: number,
      highlight: string,
      book: number,
      paragraph: number,
      content: string,
    }
  }

}

