import { message } from "antd";
import { useIntl } from "react-intl";
import {
  ProForm,
  ProFormDependency,
  ProFormInstance,
  ProFormSelect,
} from "@ant-design/pro-components";

import { post } from "../../request";
import { TRole } from "../api/Auth";
import { IShareRequest, IShareResponse } from "../api/Share";
import { useRef } from "react";
import { EResType } from "./Share";
import UserSelect from "../template/UserSelect";
import GroupSelect from "../template/GroupSelect";

interface IWidget {
  resId: string;
  resType: EResType;
  onSuccess?: Function;
}
const CollaboratorAddWidget = ({ resId, resType, onSuccess }: IWidget) => {
  const roleList = ["editor", "reader"];
  const intl = useIntl();
  const formRef = useRef<ProFormInstance>();
  interface IFormData {
    userId: string[];
    groupId: string[];
    userType: string;
    role: TRole;
  }
  return (
    <ProForm<IFormData>
      formRef={formRef}
      onFinish={async (values: IFormData) => {
        if (typeof resId !== "undefined") {
          const postData: IShareRequest = {
            user_id:
              values.userType === "user" ? values.userId : values.groupId,
            user_type: values.userType,
            role: values.role,
            res_id: resId,
            res_type: resType,
          };
          console.log("create share", postData);
          post<IShareRequest, IShareResponse>("/v2/share", postData).then(
            (json) => {
              console.log("add member", json);
              if (json.ok) {
                if (typeof onSuccess !== "undefined") {
                  onSuccess();
                }
                formRef.current?.resetFields(["userId"]);
                message.success(intl.formatMessage({ id: "flashes.success" }));
              }
            }
          );
        }
      }}
    >
      <ProForm.Group>
        <ProFormSelect
          initialValue={"user"}
          name="userType"
          label={intl.formatMessage({ id: "forms.fields.type.label" })}
          allowClear={false}
          options={[
            {
              value: "user",
              label: intl.formatMessage({ id: "auth.type.user" }),
            },
            {
              value: "group",
              label: intl.formatMessage({ id: "auth.type.group" }),
            },
          ]}
          rules={[
            {
              required: true,
              message: intl.formatMessage({
                id: "forms.message.user.required",
              }),
            },
          ]}
        />
      </ProForm.Group>
      <ProForm.Group>
        <ProFormDependency name={["userType"]}>
          {({ userType }) => {
            if (userType === "user") {
              return <UserSelect name="userId" multiple={true} />;
            } else {
              return <GroupSelect name="groupId" multiple={true} />;
            }
          }}
        </ProFormDependency>

        <ProFormSelect
          name="role"
          initialValue={"reader"}
          label={intl.formatMessage({ id: "forms.fields.role.label" })}
          allowClear={false}
          options={roleList.map((item) => {
            return {
              value: item,
              label: intl.formatMessage({ id: "auth.role." + item }),
            };
          })}
          rules={[
            {
              required: true,
              message: intl.formatMessage({
                id: "forms.message.user.required",
              }),
            },
          ]}
        />
      </ProForm.Group>
    </ProForm>
  );
};

export default CollaboratorAddWidget;
