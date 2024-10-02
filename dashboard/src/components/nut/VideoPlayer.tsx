import { useRef, useEffect } from "react";
import videojs from "video.js";

interface IProps {
  options: videojs.PlayerOptions;
  onReady: (player: videojs.Player) => void;
}

const Widget = ({ options, onReady }: IProps) => {
  const videoRef = useRef<HTMLDivElement>(null);
  const playerRef = useRef<videojs.Player | null>();

  useEffect(() => {
    if (!playerRef.current) {
      const videoElement = document.createElement("video-js");

      videoElement.classList.add("vjs-big-play-centered");
      if (videoRef.current) {
        videoRef.current.appendChild(videoElement);
      }

      const player = (playerRef.current = videojs(videoElement, options, () => {
        onReady && onReady(player);
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
  }, [options, playerRef, videoRef, onReady]);

  useEffect(() => {
    const player = playerRef.current;

    return () => {
      if (player && !player.isDisposed()) {
        player.dispose();
        playerRef.current = null;
      }
    };
  }, [playerRef]);

  return (
    <div data-vjs-player>
      <div ref={videoRef} className="video-js" />
    </div>
  );
};

export default Widget;
