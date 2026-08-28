import { useEffect, useState } from "react";
import { Badge, Popover } from "antd";

import { get } from "../../request";
import type { INotificationListResponse } from "../../api/notification";
import { NotificationIcon } from "../../assets/icon";
import NotificationList from "./NotificationList";
import { useAppSelector } from "../../hooks";
import { currentUser, type IUser } from "../../reducers/current-user";

const NotificationIconWidget = () => {
  const [count, setCount] = useState<number>();
  const currUser = useAppSelector(currentUser);
  const [mute, setMute] = useState(false);

  const queryNotification = (user?: IUser) => {
    if (!user) {
      console.debug("未登录 不查询 notification");
      return;
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
      if (now.getTime() - parseInt(notificationUpdatedAt) < 59000) {
        const notificationCount = localStorage.getItem("notification/count");
        if (notificationCount !== null) {
          setCount(parseInt(notificationCount));
          console.debug("has notification count");
          return;
        }
      }
    }

    const url = `/api/v2/notification?view=to&limit=1`;
    console.info("notification api request", url);
    get<INotificationListResponse>(url).then((json) => {
      if (json.ok) {
        console.debug("notification fetch ok ", json.data.unread);
        localStorage.setItem(
          "notification/updatedAt",
          now.getTime().toString()
        );
        localStorage.setItem("notification/count", json.data.unread.toString());
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
              if (window.Notification && Notification.permission !== "denied") {
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
      } else {
        console.error(json.message);
      }
    });
  };

  useEffect(() => {
    const timer = setInterval(() => queryNotification(currUser), 1000 * 60);
    return () => {
      clearInterval(timer);
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
