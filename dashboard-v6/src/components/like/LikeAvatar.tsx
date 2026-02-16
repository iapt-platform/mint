import { useEffect, useState } from "react";

import {
  ILikeListResponse,
  ILikeRequest,
  ILikeResponse,
  TLikeType,
} from "../api/like";
import { get, post } from "../../request";
import { IUser } from "../auth/User";

import { IDataType } from "./WatchAdd";
import EditableAvatarGroup from "./EditableAvatarGroup";

interface IWidget {
  resId?: string;
  resType?: string;
  type?: TLikeType;
}
const LikeAvatar = ({ resId, resType, type }: IWidget) => {
  const [data, setData] = useState<IUser[]>();
  useEffect(() => {
    if (!resId) {
      return;
    }
    const url = `/v2/like?view=target&target_id=${resId}&type=${type}`;
    console.info("api request", url);
    get<ILikeListResponse>(url).then((json) => {
      console.info("api response", json);
      if (json.ok) {
        setData(json.data.rows.map((item) => item.user));
      }
    });
  }, [resId, type]);
  return (
    <>
      <EditableAvatarGroup
        users={data}
        onFinish={async (values: IDataType) => {
          if (!resId || !resType) {
            console.error("no resId or resType", resId, resType);
            return;
          }
          const update: ILikeRequest = {
            type: "watch",
            target_id: resId,
            target_type: resType,
            user_id: values.user_id,
          };
          const url = `/v2/like`;
          console.info("watch add api request", url, values);
          const add = await post<ILikeRequest, ILikeResponse>(url, update);
          console.debug("watch add api response", add);
          setData((origin) => {
            if (origin) {
              return [...origin, add.data.user];
            } else {
              return [add.data.user];
            }
          });
        }}
      />
    </>
  );
};

export default LikeAvatar;
