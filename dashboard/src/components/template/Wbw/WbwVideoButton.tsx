import { VideoCameraOutlined } from "@ant-design/icons";
import { useEffect, useState } from "react";
import { get } from "../../../request";
import { IAttachmentResponse } from "../../api/Attachments";
import VideoModal from "../../general/VideoModal";

export interface IVideo {
  videoId: string;
  url?: string;
  type?: string;
  title?: string;
}
interface IWidget {
  video: IVideo[];
}
const WbwVideoButtonWidget = ({ video }: IWidget) => {
  const [url, setUrl] = useState<string>();
  const [curr, setCurr] = useState(0);

  useEffect(() => {
    get<IAttachmentResponse>(`/v2/attachment/${video[curr].videoId}`).then(
      (json) => {
        console.log(json);
        if (json.ok) {
          setUrl(json.data.url);
        }
      }
    );
  }, [curr, video]);

  return video ? (
    <VideoModal
      src={url}
      type={video[0].type}
      trigger={<VideoCameraOutlined />}
    />
  ) : (
    <></>
  );
};

export default WbwVideoButtonWidget;
