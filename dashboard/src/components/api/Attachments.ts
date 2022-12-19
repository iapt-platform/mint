/*
            'name' => $filename,
            'size' => $file->getSize(),
            'type' => $file->getMimeType(),
            'url' => $filename,
*/
export interface IAttachmentRequest {
  uid: string;
  name?: string;
  size?: number;
  type?: string;
  url?: string;
}
export interface IAttachmentResponse {
  ok: boolean;
  message: string;
  data: IAttachmentRequest;
}
