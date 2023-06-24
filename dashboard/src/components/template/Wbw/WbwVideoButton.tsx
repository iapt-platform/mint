import { VideoCameraOutlined } from "@ant-design/icons";
import VideoModal from "../../general/VideoModal";

export interface IVideo {
  url?: string;
  type?: string;
  title?: string;
}
interface IWidget {
  video: IVideo[];
}
const WbwVideoButtonWidget = ({ video }: IWidget) => {
  const url = video ? video[0].url : "";
  const src: string = process.env.REACT_APP_WEB_HOST
    ? process.env.REACT_APP_WEB_HOST
    : "";
  return video ? (
    <VideoModal
      src={src + "/" + url}
      type={video[0].type}
      trigger={<VideoCameraOutlined />}
    />
  ) : (
    <></>
  );
};

export default WbwVideoButtonWidget;
