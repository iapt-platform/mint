import type { MenuProps } from "antd";
export interface ITocPathNode {
  key?: string;
  book?: number;
  paragraph?: number;
  title: string;
  paliTitle?: string;
  level: number;
  menu?: MenuProps["items"];
}

export interface IApiPaliChapterList {
  id: string;
  book: number;
  paragraph: number;
  level: number;
  toc: string;
  title: string;
  lenght: number;
  chapter_len: number;
  next_chapter: number;
  prev_chapter: number;
  parent: number;
  chapter_strlen: number;
  path: string;
  progress_line?: number[];
}
export interface IPaliChapterListResponse {
  ok: boolean;
  message: string;
  data: { rows: IApiPaliChapterList[]; count: number };
}
export interface IApiResponsePaliChapter {
  ok: boolean;
  message: string;
  data: IApiPaliChapterList;
}

//////////////////

export interface IPaliPara {
  book: number;
  paragraph: number;
  level: number;
  class: string;
  toc: string;
  text: string;
  html: string;
  lenght: number;
  chapter_len: number;
  next_chapter: number;
  prev_chapter: number;
  parent: number;
  chapter_strlen: number;
  path: string;
  uid: string;
}

export interface IPaliParagraphResponse {
  ok: boolean;
  message: string;
  data: IPaliPara;
}
export interface IPaliListResponse {
  ok: boolean;
  message: string;
  data: { rows: IPaliPara[]; count: number };
}

//

export interface IPaliToc {
  book: number;
  paragraph: number;
  level: string;
  toc: string;
  translation?: string;
}

export interface IPaliTocListResponse {
  ok: boolean;
  message: string;
  data: { rows: IPaliToc[]; count: number };
}

//

export interface IChapterToc {
  book: number;
  paragraph: number;
  level: number;
  text: string | null;
  chapter_len: number;
  chapter_strlen: number;
  parent: number;
}

export interface IChapterTocListResponse {
  ok: boolean;
  message: string;
  data: { rows: IChapterToc[]; count: number };
}
