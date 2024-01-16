export interface IAttachmentRequest {
  id: string;
  name: string;
  filename: string;
  title: string;
  size: number;
  content_type: string;
  url: string;
}
export interface IAttachmentResponse {
  ok: boolean;
  message: string;
  data: IAttachmentRequest;
}
