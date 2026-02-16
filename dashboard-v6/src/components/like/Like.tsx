import { useEffect, useState } from "react";
import { Button, Space, Tooltip } from "antd";
import {
  LikeOutlined,
  LikeFilled,
  StarOutlined,
  StarFilled,
  EyeOutlined,
  EyeFilled,
} from "@ant-design/icons";

import { delete_, get, post } from "../../request";
import {
  ILikeCount,
  ILikeCountListResponse,
  ILikeCountResponse,
  ILikeRequest,
  TLikeType,
} from "../api/like";

interface IWidget {
  resId?: string;
  resType?: string;
}
const Like = ({ resId, resType }: IWidget) => {
  const [like, setLike] = useState<ILikeCount>();
  const [favorite, setFavorite] = useState<ILikeCount>();
  const [watch, setWatch] = useState<ILikeCount>();

  useEffect(() => {
    if (!resId) {
      return;
    }
    const url = `/v2/like?view=count&target_id=${resId}`;
    console.info("api request", url);
    get<ILikeCountListResponse>(url).then((json) => {
      console.info("api response", json);
      if (json.ok) {
        setLike(json.data.find((value) => value.type === "like"));
        setFavorite(json.data.find((value) => value.type === "favorite"));
        setWatch(json.data.find((value) => value.type === "watch"));
      }
    });
  }, [resId]);

  const setStatus = (data: ILikeCount) => {
    switch (data.type) {
      case "like":
        setLike(data);
        break;
      case "favorite":
        setFavorite(data);
        break;
      case "watch":
        setWatch(data);
        break;
    }
  };
  const add = (type: TLikeType) => {
    if (!resId || !resType) {
      return;
    }
    const url = `/v2/like`;
    post<ILikeRequest, ILikeCountResponse>(url, {
      type: type,
      target_id: resId,
      target_type: resType,
    }).then((json) => {
      if (json.ok) {
        setStatus(json.data);
      }
    });
  };

  const remove = (id?: string) => {
    if (!resId || !resType || !id) {
      return;
    }
    const url = `/v2/like/${id}`;
    console.info("api request", url);
    delete_<ILikeCountResponse>(url).then((json) => {
      console.info("api response", json);
      if (json.ok) {
        setStatus(json.data);
      }
    });
  };

  return (
    <Space>
      <Button
        type="text"
        icon={like?.selected ? <LikeFilled /> : <LikeOutlined />}
        onClick={() => {
          if (like?.selected) {
            remove(like.my_id);
          } else {
            add("like");
          }
        }}
      >
        {like?.count === 0 ? <></> : like?.count}
      </Button>
      <Button
        type="text"
        icon={favorite?.selected ? <StarFilled /> : <StarOutlined />}
        onClick={() => {
          if (favorite?.selected) {
            remove(favorite.my_id);
          } else {
            add("favorite");
          }
        }}
      >
        {favorite?.count === 0 ? <></> : favorite?.count}
      </Button>
      <Tooltip title="关注">
        <Button
          type="text"
          icon={watch?.selected ? <EyeFilled /> : <EyeOutlined />}
          onClick={() => {
            if (watch?.selected) {
              remove(watch.my_id);
            } else {
              add("watch");
            }
          }}
        >
          {watch?.count === 0 ? <></> : watch?.count}
        </Button>
      </Tooltip>
    </Space>
  );
};

export default Like;
