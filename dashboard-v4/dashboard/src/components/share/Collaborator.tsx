import { useIntl } from "react-intl";
import { useEffect, useRef, useState } from "react";
import { ActionType, ProList } from "@ant-design/pro-components";
import { Tag, Button, Popconfirm, Space, Badge, message, Dropdown } from "antd";
import { UserOutlined, TeamOutlined } from "@ant-design/icons";

import { delete_, get, put } from "../../request";

import {
  IShareDeleteResponse,
  IShareListResponse,
  IShareResponse,
  IShareUpdateRequest,
} from "../api/Share";
import User, { IUser } from "../auth/User";
import { TRole } from "../api/Auth";

import Group, { IGroup } from "../group/Group";

interface ICollaborator {
  sn?: number;
  id?: string;
  resId: string;
  resType: string;
  power?: number;
  user?: IUser;
  group?: IGroup;
  role?: TRole;
}
interface IWidget {
  resId?: string;
  load?: boolean;
  onReload?: Function;
}
const CollaboratorWidget = ({ resId, load = false, onReload }: IWidget) => {
  const intl = useIntl(); //i18n
  const [canDelete, setCanDelete] = useState(false);
  const [memberCount, setMemberCount] = useState<number>();

  useEffect(() => {
    if (load) {
      ref.current?.reload();
      if (typeof onReload !== "undefined") {
        onReload();
      }
    }
  }, [load, onReload]);
  const ref = useRef<ActionType>();
  const roleList: TRole[] = ["editor", "reader"];

  return (
    <ProList<ICollaborator>
      rowKey="id"
      actionRef={ref}
      headerTitle={
        <Space>
          {intl.formatMessage({ id: "labels.collaborators" })}
          <Badge color="geekblue" count={memberCount} />
        </Space>
      }
      showActions="hover"
      request={async (params = {}, sorter, filter) => {
        console.log(params, sorter, filter);

        let url = `/v2/share?view=res&id=${resId}`;
        const offset =
          ((params.current ? params.current : 1) - 1) *
          (params.pageSize ? params.pageSize : 20);
        url += `&limit=${params.pageSize}&offset=${offset}`;
        if (typeof params.keyword !== "undefined") {
          url += "&search=" + (params.keyword ? params.keyword : "");
        }
        const res = await get<IShareListResponse>(url);
        if (res.ok) {
          console.log(res.data);
          setMemberCount(res.data.count);
          switch (res.data.role) {
            case "owner":
              setCanDelete(true);
              break;
            case "manager":
              setCanDelete(true);
              break;
          }
          const items: ICollaborator[] = res.data.rows.map((item, id) => {
            let member: ICollaborator = {
              sn: id + 1,
              id: item.id,
              resId: item.res_id,
              resType: item.res_type,
              power: item.power,
              user: item.user,
              group: item.group,
              role: item.role,
            };

            return member;
          });
          console.log(items);
          return {
            total: res.data.count,
            succcess: true,
            data: items,
          };
        } else {
          console.error(res.message);
          return {
            total: 0,
            succcess: false,
            data: [],
          };
        }
      }}
      pagination={{
        showQuickJumper: true,
        showSizeChanger: true,
      }}
      metas={{
        title: {
          render: (text, row, index, action) => {
            return row.user ? (
              <User {...row.user} showAvatar={false} key={index} />
            ) : (
              <Group group={row.group} key={index} />
            );
          },
        },
        avatar: {
          render: (text, row, index, action) => {
            return row.user ? (
              <UserOutlined key={index} />
            ) : (
              <TeamOutlined key={index} />
            );
          },
        },
        subTitle: {
          render: (text, row, index, action) => {
            let right = "";
            switch (row.power) {
              case 10:
                right = intl.formatMessage({ id: "auth.role.reader" });
                break;
              case 20:
                right = intl.formatMessage({ id: "auth.role.editor" });
                break;
              case 30:
                right = intl.formatMessage({ id: "auth.role.manager" });
                break;
              default:
                break;
            }
            return (
              <Dropdown
                key={index}
                trigger={["click"]}
                menu={{
                  items: roleList.map((item) => {
                    return {
                      key: item,
                      label: intl.formatMessage({ id: "auth.role." + item }),
                    };
                  }),
                  onClick: (e) => {
                    put<IShareUpdateRequest, IShareResponse>(
                      `/v2/share/${row.id}`,
                      {
                        role: e.key as TRole,
                      }
                    ).then((json) => {
                      console.log(json);
                      if (json.ok) {
                        ref.current?.reload();
                      }
                    });
                  },
                }}
              >
                <Tag key={index}>{right}</Tag>
              </Dropdown>
            );
          },
        },
        actions: {
          render: (text, row, index, action) => [
            canDelete ? (
              <Popconfirm
                key={index}
                placement="bottomLeft"
                title={intl.formatMessage({
                  id: "forms.message.member.remove",
                })}
                onConfirm={(e?: React.MouseEvent<HTMLElement, MouseEvent>) => {
                  console.log("delete", row.id);
                  delete_<IShareDeleteResponse>("/v2/share/" + row.id)
                    .then((json) => {
                      if (json.ok) {
                        message.success("delete ok");
                        ref.current?.reload();
                      } else {
                        message.error(json.message);
                      }
                    })
                    .catch((e) => {
                      message.error(e);
                    });
                }}
                okText={intl.formatMessage({ id: "buttons.ok" })}
                cancelText={intl.formatMessage({ id: "buttons.cancel" })}
              >
                <Button size="small" type="link" danger key="link">
                  {intl.formatMessage({ id: "buttons.remove" })}
                </Button>
              </Popconfirm>
            ) : undefined,
          ],
        },
      }}
    />
  );
};

export default CollaboratorWidget;
