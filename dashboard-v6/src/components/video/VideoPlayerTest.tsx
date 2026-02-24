import { useState, useCallback, useRef, useMemo } from "react";
import VideoPlayer from "./VideoPlayer";
import type Player from "video.js/dist/types/player";

const VideoPlayerTest = () => {
  const [isPlaying, setIsPlaying] = useState(false);
  const [currentTime, setCurrentTime] = useState(0);
  const [duration, setDuration] = useState(0);
  const [volume, setVolume] = useState(1);
  const [log, setLog] = useState<string[]>([]);
  const playerRef = useRef<Player | null>(null);

  const addLog = useCallback((msg: string) => {
    const time = new Date().toLocaleTimeString();
    setLog((prev) => [`[${time}] ${msg}`, ...prev].slice(0, 20));
  }, []);

  const handleReady = useCallback(
    (player: Player) => {
      playerRef.current = player;
      addLog("播放器已就绪");

      player.on("play", () => {
        setIsPlaying(true);
        addLog("▶ 开始播放");
      });

      player.on("pause", () => {
        setIsPlaying(false);
        addLog("⏸ 暂停");
      });

      player.on("ended", () => {
        setIsPlaying(false);
        addLog("⏹ 播放结束");
      });

      player.on("timeupdate", () => {
        setCurrentTime(player.currentTime() ?? 0);
      });

      player.on("loadedmetadata", () => {
        setDuration(player.duration() ?? 0);
        addLog(`视频时长: ${Math.round(player.duration() ?? 0)}s`);
      });

      player.on("volumechange", () => {
        setVolume(player.volume() ?? 1);
      });

      player.on("error", () => {
        addLog("❌ 播放出错");
      });
    },
    [addLog]
  );

  const options = useMemo(
    () => ({
      autoplay: false,
      controls: true,
      responsive: true,
      fluid: true,
      sources: [
        {
          src: "https://vjs.zencdn.net/v/oceans.mp4",
          type: "video/mp4",
        },
      ],
    }),
    []
  );

  const formatTime = (seconds: number) => {
    const m = Math.floor(seconds / 60)
      .toString()
      .padStart(2, "0");
    const s = Math.floor(seconds % 60)
      .toString()
      .padStart(2, "0");
    return `${m}:${s}`;
  };

  const handleSeek = (e: React.ChangeEvent<HTMLInputElement>) => {
    const time = Number(e.target.value);
    playerRef.current?.currentTime(time);
    addLog(`跳转到 ${formatTime(time)}`);
  };

  const handleVolumeChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    const vol = Number(e.target.value);
    playerRef.current?.volume(vol);
  };

  const handlePlayPause = () => {
    const player = playerRef.current;
    if (!player) return;
    isPlaying ? player.pause() : player.play();
  };

  const handleMute = () => {
    const player = playerRef.current;
    if (!player) return;
    player.muted(!player.muted());
    addLog(player.muted() ? "🔇 已静音" : "🔊 取消静音");
  };

  const handleRestart = () => {
    const player = playerRef.current;
    if (!player) return;
    player.currentTime(0);
    player.play();
    addLog("🔁 重新播放");
  };

  return (
    <div
      style={{
        maxWidth: 800,
        margin: "40px auto",
        fontFamily: "sans-serif",
        padding: "0 16px",
      }}
    >
      <h2 style={{ marginBottom: 16 }}>🎬 VideoPlayer 测试</h2>

      <VideoPlayer options={options} onReady={handleReady} />

      <div
        style={{
          marginTop: 16,
          padding: 16,
          background: "#f5f5f5",
          borderRadius: 8,
          display: "flex",
          flexDirection: "column",
          gap: 12,
        }}
      >
        <div style={{ display: "flex", gap: 8, flexWrap: "wrap" }}>
          <button onClick={handlePlayPause} style={btnStyle}>
            {isPlaying ? "⏸ 暂停" : "▶ 播放"}
          </button>
          <button onClick={handleMute} style={btnStyle}>
            🔇 静音切换
          </button>
          <button onClick={handleRestart} style={btnStyle}>
            🔁 重播
          </button>
        </div>

        <div>
          <label style={{ fontSize: 13, color: "#555" }}>
            进度: {formatTime(currentTime)} / {formatTime(duration)}
          </label>
          <input
            type="range"
            min={0}
            max={duration || 100}
            value={currentTime}
            onChange={handleSeek}
            style={{ width: "100%", marginTop: 4 }}
          />
        </div>

        <div>
          <label style={{ fontSize: 13, color: "#555" }}>
            音量: {Math.round(volume * 100)}%
          </label>
          <input
            type="range"
            min={0}
            max={1}
            step={0.01}
            value={volume}
            onChange={handleVolumeChange}
            style={{ width: "100%", marginTop: 4 }}
          />
        </div>
      </div>

      <div style={{ marginTop: 16 }}>
        <h4 style={{ marginBottom: 8 }}>📋 事件日志</h4>
        <div
          style={{
            background: "#1e1e1e",
            color: "#d4d4d4",
            borderRadius: 6,
            padding: 12,
            height: 160,
            overflowY: "auto",
            fontSize: 13,
            fontFamily: "monospace",
          }}
        >
          {log.length === 0 ? (
            <span style={{ color: "#666" }}>等待事件...</span>
          ) : (
            log.map((entry, i) => <div key={i}>{entry}</div>)
          )}
        </div>
      </div>
    </div>
  );
};

const btnStyle: React.CSSProperties = {
  padding: "6px 14px",
  borderRadius: 6,
  border: "1px solid #ccc",
  background: "#fff",
  cursor: "pointer",
  fontSize: 14,
};

export default VideoPlayerTest;
