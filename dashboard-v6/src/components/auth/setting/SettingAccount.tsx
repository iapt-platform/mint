import {
  ProForm,
  ProFormText,
  ProFormUploadButton,
} from "@ant-design/pro-components";
import { message } from "antd";
import { useAppSelector } from "../../../hooks";
import { currentUser } from "../../../reducers/current-user";
import { API_HOST, delete_, get, put } from "../../../request";
import { IUserRequest, IUserResponse } from "../../api/Auth";
import { UploadChangeParam, UploadFile } from "antd/es/upload/interface";
import { get as getToken } from "../../../reducers/current-user";
import { IAttachmentResponse } from "../../api/Attachments";
import { useIntl } from "react-intl";
import { IDeleteResponse } from "../../api/Group";

interface IAccount {
  id: string;
  realName: string;
  nickName: string;
  email: string;
  avatar?: UploadFile<IAttachmentResponse>[];
}

const SettingAccountWidget = () => {
  const user = useAppSelector(currentUser);
  const intl = useIntl();

  return (
    <ProForm<IAccount>
      onFinish={async (values: IAccount) => {
        console.log(values);
        let _avatar: string = "";

        if (
          typeof values.avatar === "undefined" ||
          values.avatar.length === 0
        ) {
          _avatar = "";
        } else if (typeof values.avatar[0].response === "undefined") {
          _avatar = values.avatar[0].uid;
        } else {
          console.debug("upload ", values.avatar[0].response);
          _avatar = values.avatar[0].response.data.name;
        }
        const url = `/v2/user/${user?.id}`;
        const postData = {
          nickName: values.nickName,
          avatar: _avatar,
          email: values.email,
        };
        console.log("account put ", url, postData);
        const res = await put<IUserRequest, IUserResponse>(url, postData);

        if (res.ok) {
          message.success(intl.formatMessage({ id: "flashes.success" }));
        } else {
          message.error(res.message);
        }
      }}
      params={{}}
      request={async () => {
        const url = `/v2/user/${user?.id}`;
        console.log("url", url);
        const res = await get<IUserResponse>(url);
        if (res.ok) {
        }
        return {
          id: res.data.id,
          realName: res.data.userName,
          nickName: res.data.nickName,
          email: res.data.email,
          avatar:
            res.data.avatar && res.data.avatarName
              ? [
                  {
                    uid: res.data.avatarName,
                    name: "avatar",
                    thumbUrl: res.data.avatar,
                  },
                ]
              : [],
        };
      }}
    >
      <ProFormUploadButton
        name="avatar"
        label="头像"
        max={1}
        fieldProps={{
          name: "file",
          listType: "picture-card",
          className: "avatar-uploader",
          headers: {
            Authorization: `Bearer ${getToken()}`,
          },
          onRemove: (file: UploadFile<any>): boolean => {
            console.log("remove", file);
            const url = `/v2/attachment/1?name=${file.uid}`;
            console.info("avatar delete url", url);
            delete_<IDeleteResponse>(url)
              .then((json) => {
                if (json.ok) {
                  message.success("删除成功");
                } else {
                  message.error(json.message);
                }
              })
              .catch((e) => console.log("Oops errors!", e));
            return true;
          },
        }}
        action={`${API_HOST}/api/v2/attachment?type=avatar`}
        extra="必须为正方形。最大512*512"
        onChange={(info: UploadChangeParam<UploadFile<any>>) => {}}
      />
      <ProFormText
        width="md"
        readonly
        name="realName"
        label="realName"
        tooltip="最长为 24 位"
      />
      <ProFormText width="md" name="nickName" label="nickName" />
      <ProFormText readonly name="email" width="md" label="email" />
    </ProForm>
  );
};

export default SettingAccountWidget;
