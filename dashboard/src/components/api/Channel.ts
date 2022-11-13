import { IStudioApiResponse } from "./Auth";

export type ChannelInfoProps = {
  channelName: string;
  channelId: string;
  channelType: string;
  studioName: string;
  studioId: string;
  studioType: string;
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
