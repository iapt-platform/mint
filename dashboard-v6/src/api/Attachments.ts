import { message } from "antd";
import { delete_ } from "../request";
import type { IDeleteResponse } from "./group";

export interface IAttachmentRequest {
  id: string;
  name: string;
  filename: string;
  title: string;
  size: number;
  content_type: string;
  url: string;
  thumbnail?: { small: string; middle: string };
  created_at?: string;
  updated_at?: string;
}
export interface IAttachmentUpdate {
  title: string;
}
export interface IAttachmentResponse {
  ok: boolean;
  message: string;
  data: IAttachmentRequest;
}

export interface IAttachmentListResponse {
  ok: boolean;
  message: string;
  data: { rows: IAttachmentRequest[]; count: number };
}

export interface IResAttachmentData {
  uid: string;
  sentence_id: string;
  attachment_id: string;
  attachment: IAttachmentRequest;
}
export interface IResAttachmentListResponse {
  ok: boolean;
  message: string;
  data: { rows: IResAttachmentData[]; count: number };
}

export const deleteRes = (id: string) => {
  const url = `/v2/attachment/${id}`;
  console.info("attachment delete url", url);
  delete_<IDeleteResponse>(url)
    .then((json) => {
      if (json.ok) {
        message.success("删除成功");
      } else {
        message.error(json.message);
      }
    })
    .catch((e) => console.log("Oops errors!", e));
};
