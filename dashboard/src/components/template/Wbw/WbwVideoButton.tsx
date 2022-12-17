import { VideoCameraOutlined } from "@ant-design/icons";

export interface IVideo {
  url?: string;
  title?: string;
}
interface IWidget {
  video?: IVideo[];
}
const Widget = ({ video }: IWidget) => {
  return video ? <VideoCameraOutlined /> : <></>;
};

export default Widget;
