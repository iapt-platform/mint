import { useEffect, useState } from "react";
import { NotificationIcon } from "../../assets/icon";
import { Badge, Popover } from "antd";
import { get } from "../../request";
import { INotificationListResponse } from "../api/notification";
import NotificationList from "./NotificationList";
import { useAppSelector } from "../../hooks";
import { currentUser } from "../../reducers/current-user";
import { IUser } from "../auth/User";

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

    const url = `/v2/notification?view=to&limit=1`;
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
            //发送通知
            console.debug("notification isMute", isMute, mute);
            if (!isMute || isMute !== "true") {
              if (window.Notification && Notification.permission !== "denied") {
                Notification.requestPermission(function (status) {
                  const notification = new Notification(title, {
                    body: content,
                    icon:
                      process.env.REACT_APP_API_HOST +
                      "/assets/images/wikipali_logo.png",
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
    let timer = setInterval(queryNotification, 1000 * 60, currUser);
    return () => {
      clearInterval(timer);
    };
  }, [currUser]);

  return (
    <>
      {currUser ? (
        <Popover
          placement="bottomLeft"
          arrowPointAtCenter
          destroyTooltipOnHide
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
            <span style={{ color: "white", cursor: "pointer" }}>
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
