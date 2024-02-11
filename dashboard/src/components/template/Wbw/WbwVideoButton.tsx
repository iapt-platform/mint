import { VideoCameraOutlined } from "@ant-design/icons";
import { useEffect, useState } from "react";
import { get } from "../../../request";
import { IAttachmentResponse } from "../../api/Attachments";
import VideoModal from "../../general/VideoModal";
import { VideoIcon } from "../../../assets/icon";

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
    if (!video || video.length === 0) {
      return;
    }
    const url = `/v2/attachment/${video[curr].videoId}`;
    console.info("url", url);
    get<IAttachmentResponse>(url).then((json) => {
      console.log(json);
      if (json.ok) {
        setUrl(json.data.url);
      }
    });
  }, [curr, video]);

  return video && video.length > 0 ? (
    <VideoModal src={url} type={video[0].type} trigger={<VideoIcon />} />
  ) : (
    <></>
  );
};

export default WbwVideoButtonWidget;
