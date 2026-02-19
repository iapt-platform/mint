import { VideoCtl } from "../Video";

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
  return video && video.length > 0 ? (
    <VideoCtl id={video[0].videoId} type={video[0].type} _style="modal" />
  ) : (
    <></>
  );
};

export default WbwVideoButtonWidget;
