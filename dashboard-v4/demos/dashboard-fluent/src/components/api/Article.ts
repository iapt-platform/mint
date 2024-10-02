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
export interface IAnthologyStudioListApiResponse {
	count: number;
	studio: IStudioApiResponse;
}
