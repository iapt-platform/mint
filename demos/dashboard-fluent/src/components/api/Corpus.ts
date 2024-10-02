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
}

export interface IApiResponcePaliChapterList {
	ok: boolean;
	message: string;
	data: { rows: IApiPaliChapterList[]; count: number };
}
export interface IApiResponcePaliChapter {
	ok: boolean;
	message: string;
	data: IApiPaliChapterList;
}

export interface IApiResponcePaliPara {
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
}

/**
 * progress?view=chapter_channels&book=98&par=22
 */
export interface IApiChapterChannels {
	book: number;
	para: number;
	uid: string;
	channel_id: string;
	progress: number;
	updated_at: string;
	views: number;
	likes: number[];
	channel: {
		type: string;
		owner_uid: string;
		editor_id: number;
		name: string;
		summary: string;
		lang: string;
		status: number;
		created_at: string;
		updated_at: string;
		uid: string;
	};
}

export interface IApiResponseChapterChannelList {
	ok: boolean;
	message: string;
	data: { rows: IApiChapterChannels[]; count: number };
}
