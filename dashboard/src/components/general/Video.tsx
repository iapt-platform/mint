import { useEffect, useRef, useState } from "react";
import videojs from "video.js";
import VideoPlayer from "./VideoPlayer";
import { IAttachmentResponse } from "../api/Attachments";
import { get } from "../../request";

interface IWidget {
  fileName?: string;
  fileId?: string;
  src?: string;
  type?: string;
}
const VideoWidget = ({ fileName, fileId, src, type }: IWidget) => {
  const playerRef = useRef<videojs.Player>();
  const [url, setUrl] = useState<string>();

  useEffect(() => {
    if (fileId) {
      const url = `/v2/attachment/${fileId}`;
      console.info("VideoWidget api request", url);
      get<IAttachmentResponse>(url).then((json) => {
        console.debug("VideoWidget api response", json);
        if (json.ok) {
          setUrl(json.data.url);
        }
      });
    }
  }, [fileId]);

  useEffect(() => {
    if (src) {
      setUrl(src);
    }
  }, [src]);

  const handlePlayerReady = (player: videojs.Player) => {
    if (playerRef.current) {
      playerRef.current = player;
      player.on("waiting", () => {
        console.log("player is waiting");
      });

      player.on("dispose", () => {
        console.log("player will dispose");
      });
    }
  };

  return (
    <VideoPlayer
      options={{
        autoplay: false,
        controls: true,
        responsive: true,
        fluid: true,
        poster: "",
        sources: [
          {
            src: url ? url : "",
            type: type ? type : "video/mp4",
          },
        ],
      }}
      onReady={handlePlayerReady}
    />
  );
};

export default VideoWidget;
