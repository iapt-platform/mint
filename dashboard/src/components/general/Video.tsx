import { useRef } from "react";
import videojs from "video.js";
import VideoPlayer from "./VideoPlayer";

interface IWidget {
  src?: string;
  type?: string;
}
export const VideoWidget = ({ src, type }: IWidget) => {
  const playerRef = useRef<videojs.Player>();

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
            src: src ? src : "",
            type: type ? type : "video/mp4",
          },
        ],
      }}
      onReady={handlePlayerReady}
    />
  );
};

export default VideoWidget;
