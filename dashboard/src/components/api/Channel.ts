import { IStudio } from "../auth/StudioName";
import { IStudioApiResponse, Role } from "./Auth";
export type TChannelType =
  | "translation"
  | "nissaya"
  | "original"
  | "wbw"
  | "commentary";
export interface IChannelApiData {
  id: string;
  name: string;
  type?: TChannelType;
}

export interface ChannelInfoProps {
  channel: IChannelApiData;
  studio: IStudio;
  count?: number;
}

export type IFinal = [number, boolean];
export interface IApiResponseChannelData {
  uid: string;
  name: string;
  summary: string;
  type: TChannelType;
  studio: IStudioApiResponse;
  lang: string;
  status: number;
  created_at: string;
  updated_at: string;
  role?: Role;
  final?: IFinal[];
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
