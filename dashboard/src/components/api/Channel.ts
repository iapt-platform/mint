import { IStudioApiResponse } from "./Auth";

export type ChannelInfoProps = {
	ChannelName: string;
	ChannelId: string;
	ChannelType: string;
	StudioName: string;
	StudioId: string;
	StudioType: string;
};

export interface IApiResponseChannelData {
	uid: string;
	name: string;
	summary: string;
	type: string;
	studio: IStudioApiResponse;
	lang: string;
	status: number;
	created_at: string;
	updated_at: string;
}
export interface IApiResponseChannel {
	ok: boolean;
	message: string;
	data: IApiResponseChannelData;
}
export interface IApiResponseChannelList {
	ok: boolean;
	message: string;
	data: {
		rows: IApiResponseChannelData[];
		count: number;
	};
}
