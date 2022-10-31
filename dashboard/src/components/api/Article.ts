import type { IStudioApiResponse } from "./Auth";

export interface IArticleListApiResponse {
	article: string;
	title: string;
	level: string;
	children: number;
}
//'uid','title','subtitle','article_list','owner','lang','updated_at','created_at'
export interface IAnthologyListApiResponse {
	uid: string;
	title: string;
	subtitle: string;
	summary: string;
	article_list: IArticleListApiResponse[];
	studio: IStudioApiResponse;
	lang: string;
	created_at: string;
	updated_at: string;
}
export interface IAnthologyListApiResponse2 {
	ok: boolean;
	message: string;
	data: IAnthologyListApiResponse;
}
export interface IAnthologyListApiResponse3 {
	ok: boolean;
	message: string;
	data: {
		rows: IAnthologyListApiResponse[];
		count: number;
	};
}

export interface IAnthologyStudioListApiResponse {
	ok: boolean;
	message: string;
	data: {
		count: number;
		rows: IAnthologyStudioListDataApiResponse[];
	};
}
export interface IAnthologyStudioListDataApiResponse {
	count: number;
	studio: IStudioApiResponse;
}
