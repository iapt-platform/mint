import { ProForm, ProFormSelect } from "@ant-design/pro-components";
import { Divider, List, message, Select } from "antd";
import { useState } from "react";
import { useIntl } from "react-intl";
import { get, post } from "../../request";
import { IUserApiData, IUserListResponse, TRole } from "../api/Auth";
import { IShareData, IShareRequest, IShareResponse } from "../api/Share";

interface IShareUserList {
  user: IUserApiData;
  role: TRole;
}
interface IWidget {
  resId: string;
  resType: string;
}
const Widget = ({ resId, resType }: IWidget) => {
  const [tableData, setTableData] = useState<IShareUserList[]>();
  const roleList = ["owner", "manager", "editor", "member", "delete"];
  const intl = useIntl();
  interface IFormData {
    userId: string;
    userType: string;
    role: TRole;
  }
  return (
    <div>
      <ProForm<IFormData>
        onFinish={async (values: IFormData) => {
          // TODO
          console.log(values);
          if (typeof resId !== "undefined") {
            post<IShareRequest, IShareResponse>("/v2/share", {
              user_id: values.userId,
              user_type: values.userType,
              role: values.role,
              res_id: resId,
              res_type: resType,
            }).then((json) => {
              console.log("add member", json);
              if (json.ok) {
                message.success(intl.formatMessage({ id: "flashes.success" }));
              }
            });
          }
        }}
      >
        <ProForm.Group>
          <ProFormSelect
            name="resType"
            label={intl.formatMessage({ id: "forms.fields.role.label" })}
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
          <ProFormSelect
            name="userId"
            label={intl.formatMessage({ id: "forms.fields.user.label" })}
            width="md"
            showSearch
            debounceTime={300}
            fieldProps={{
              mode: "tags",
            }}
            request={async ({ keyWords }) => {
              console.log("keyWord", keyWords);
              const json = await get<IUserListResponse>(
                `/v2/user?view=key&key=${keyWords}`
              );
              const userList = json.data.rows.map((item) => {
                return {
                  value: item.id,
                  label: `${item.userName}-${item.nickName}`,
                };
              });
              console.log("json", userList);
              return userList;
            }}
            placeholder={intl.formatMessage({
              id: "forms.message.user.required",
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
          <ProFormSelect
            name="role"
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
      <Divider></Divider>
      <List
        itemLayout="vertical"
        size="large"
        dataSource={tableData}
        renderItem={(item) => (
          <List.Item>
            <div style={{ display: "flex" }}>
              <span>{item.user.nickName}</span>
              <Select
                defaultValue={item.role}
                style={{ width: "100%" }}
                onChange={(value: string) => {
                  console.log(`selected ${value}`);
                }}
                options={roleList.map((item) => {
                  return {
                    value: item,
                    label: intl.formatMessage({ id: "auth.role." + item }),
                  };
                })}
              />
            </div>
          </List.Item>
        )}
      />
    </div>
  );
};

export default Widget;
