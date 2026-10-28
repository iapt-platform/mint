import { useEffect, useState } from "react";
import { Badge, Popover } from "antd";

import { get } from "../../request";
import type { INotificationListResponse } from "../../api/notification";
import { NotificationIcon } from "../../assets/icon";
import NotificationList from "./NotificationList";
import { useAppSelector } from "../../hooks";
import { currentUser, type IUser } from "../../reducers/current-user";
import { nextPollDelay } from "../../utils/poll-backoff";

/**
 * 其他标签页（或本页上一轮）这么久之内刚查过，就直接用 localStorage 里的结果。
 * 要短于最短的轮询间隔（5 分钟 -10% 抖动 = 4.5 分钟），否则本页会一直读到自己上一轮的缓存。
 */
const CACHE_MS = 4 * 60_000;

const NotificationIconWidget = () => {
  const [count, setCount] = useState<number>();
  const currUser = useAppSelector(currentUser);
  const [mute, setMute] = useState(false);

  /** 返回本轮是否成功，供轮询决定下一次的间隔 */
  const queryNotification = async (user?: IUser): Promise<boolean> => {
    if (!user) {
      console.debug("未登录 不查询 notification");
      return true;
    }
    const isMute = localStorage.getItem("notification/mute");
    if (isMute && isMute === "true") {
      setMute(true);
    } else {
      setMute(false);
    }
    const now = new Date();
    const notificationUpdatedAt = localStorage.getItem(
      "notification/updatedAt"
    );
    if (notificationUpdatedAt) {
      if (now.getTime() - parseInt(notificationUpdatedAt) < CACHE_MS) {
        const notificationCount = localStorage.getItem("notification/count");
        if (notificationCount !== null) {
          setCount(parseInt(notificationCount));
          console.debug("has notification count");
          return true;
        }
      }
    }

    const url = `/api/v2/notification?view=to&limit=1`;
    console.info("notification api request", url);
    return get<INotificationListResponse>(url)
      .then((json) => {
        if (json.ok) {
          console.debug("notification fetch ok ", json.data.unread);
          localStorage.setItem(
            "notification/updatedAt",
            now.getTime().toString()
          );
          localStorage.setItem(
            "notification/count",
            json.data.unread.toString()
          );
          setCount(json.data.unread);
          if (json.data.count > 0) {
            const newMessageTime = json.data.rows[0].created_at;
            const lastTime = localStorage.getItem("notification/new");
            if (lastTime === null || lastTime !== newMessageTime) {
              localStorage.setItem("notification/new", newMessageTime);

              const title = json.data.rows[0].res_type;
              const content = json.data.rows[0].content;
              localStorage.setItem(
                "notification/message",
                JSON.stringify({ title: title, content: content })
              );
              // 发送通知
              if (!isMute || isMute !== "true") {
                if (
                  window.Notification &&
                  Notification.permission !== "denied"
                ) {
                  Notification.requestPermission(function () {
                    const notification = new Notification(title, {
                      body: content,
                      icon: import.meta.env.BASE_URL + "logo192.png",
                      tag: json.data.rows[0].id,
                    });
                    notification.onclick = (event) => {
                      event.preventDefault(); // 阻止浏览器聚焦于 Notification 的标签页
                      window.open(json.data.rows[0].url, "_blank");
                    };
                  });
                }
              }
            }
          }
          return true;
        } else {
          console.error(json.message);
          return false;
        }
      })
      .catch((e: unknown) => {
        // 502 等非 2xx 时 get() 会抛 HttpError，原来没接住，成了未捕获的 promise
        console.error(e);
        return false;
      });
  };

  useEffect(() => {
    if (!currUser) return;
    let disposed = false;
    let failures = 0;
    let timer: ReturnType<typeof setTimeout> | undefined;

    // setTimeout 链：成功 5 分钟一次，连续失败逐步退避（见 poll-backoff.ts）
    const tick = async () => {
      const ok = await queryNotification(currUser);
      if (disposed) return;
      failures = ok ? 0 : failures + 1;
      timer = setTimeout(tick, nextPollDelay(failures));
    };

    // 间隔拉长到 5 分钟后，不能再等第一轮才出角标；
    // 有 CACHE_MS 的缓存挡着，多开标签页或重新挂载不会重复请求
    tick();

    return () => {
      disposed = true;
      clearTimeout(timer);
    };
  }, [currUser]);

  return (
    <>
      {currUser ? (
        <Popover
          placement="bottomLeft"
          arrow={{ pointAtCenter: true }}
          destroyOnHidden
          content={
            <div style={{ width: 600 }}>
              <NotificationList
                onChange={(unread: number) => setCount(unread)}
              />
            </div>
          }
          trigger="click"
        >
          <Badge count={count} size="small" dot={mute}>
            <span
              style={{
                color: "inherit",
                cursor: "pointer",
                fontSize: 18,
                display: "inline-flex",
              }}
            >
              <NotificationIcon />
            </span>
          </Badge>
        </Popover>
      ) : (
        <></>
      )}
    </>
  );
};

export default NotificationIconWidget;
