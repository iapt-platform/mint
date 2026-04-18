import { useRef, useEffect } from "react";
import videojs from "video.js";
import type Player from "video.js/dist/types/player";
import "video.js/dist/video-js.css";

type PlayerOptions = typeof videojs.options;

interface IProps {
  options: PlayerOptions;
  onReady?: (player: Player) => void;
}

const VideoPlayerWidget = ({ options, onReady }: IProps) => {
  const videoRef = useRef<HTMLDivElement>(null);
  const playerRef = useRef<Player | null>(null);
  const onReadyRef = useRef(onReady);

  useEffect(() => {
    onReadyRef.current = onReady;
  }, [onReady]);

  useEffect(() => {
    if (!playerRef.current) {
      const videoElement = document.createElement("video-js");
      videoElement.classList.add("vjs-big-play-centered");

      videoRef.current?.appendChild(videoElement);

      const player = (playerRef.current = videojs(videoElement, options, () => {
        onReadyRef.current?.(player);
      }));
    } else {
      const player = playerRef.current;

      if (options.autoplay !== undefined) {
        player.autoplay(options.autoplay);
      }
      if (options.sources !== undefined) {
        player.src(options.sources);
      }
    }
  }, [options]); // 移除 onReady 依赖，改用 ref

  useEffect(() => {
    return () => {
      const player = playerRef.current;
      if (player && !player.isDisposed()) {
        player.dispose();
        playerRef.current = null;
      }
    };
  }, []);

  return (
    <div data-vjs-player>
      <div ref={videoRef} />
    </div>
  );
};

export default VideoPlayerWidget;
