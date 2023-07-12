import { useParams } from "react-router-dom";
import { useIntl } from "react-intl";
import { Button, Popover } from "antd";
import { ActionType, ProTable } from "@ant-design/pro-components";
import { UserAddOutlined } from "@ant-design/icons";

import { get } from "../../../request";
import { RoleValueEnum } from "../../../components/studio/table";

import { useRef, useState } from "react";
import InviteCreate from "../../../components/invite/InviteCreate";

export interface IInviteData {
  id: string;
  user_uid: string;
  email: string;
  status: string;
  created_at: string;
  updated_at: string;
}
interface IInviteListResponse {
  ok: boolean;
  message: string;
  data: {
    rows: IInviteData[];
    count: number;
  };
}
export interface IInviteResponse {
  ok: boolean;
  message: string;
  data: IInviteData;
}

interface DataItem {
  sn: number;
  id: string;
  email: string;
  status: string;
  createdAt: number;
}

const Widget = () => {
  const intl = useIntl(); //i18n
  const { studioname } = useParams(); //url 参数
  const [openCreate, setOpenCreate] = useState(false);

  const ref = useRef<ActionType>();

  return (
    <>
      <ProTable<DataItem>
        actionRef={ref}
        columns={[
          {
            title: intl.formatMessage({
              id: "dict.fields.sn.label",
            }),
            dataIndex: "sn",
            key: "sn",
            width: 50,
            search: false,
          },
          {
            title: intl.formatMessage({
              id: "forms.fields.email.label",
            }),
            dataIndex: "email",
            key: "email",
          },
          {
            title: intl.formatMessage({
              id: "forms.fields.status.label",
            }),
            dataIndex: "status",
            key: "status",
            width: 100,
            search: false,
            filters: true,
            onFilter: true,
            valueEnum: RoleValueEnum(),
          },
          {
            title: intl.formatMessage({
              id: "forms.fields.created-at.label",
            }),
            key: "created-at",
            width: 100,
            search: false,
            dataIndex: "createdAt",
            valueType: "date",
            sorter: (a, b) => a.createdAt - b.createdAt,
          },
        ]}
        request={async (params = {}, sorter, filter) => {
          // TODO
          console.log(params, sorter, filter);
          let url = `/v2/invite?view=studio&studio=${studioname}`;
          const offset =
            ((params.current ? params.current : 1) - 1) *
            (params.pageSize ? params.pageSize : 20);
          url += `&limit=${params.pageSize}&offset=${offset}`;
          url += params.keyword ? "&search=" + params.keyword : "";
          console.log(url);
          const res = await get<IInviteListResponse>(url);
          const items: DataItem[] = res.data.rows.map((item, id) => {
            const date = new Date(item.created_at);
            return {
              sn: id + 1,
              id: item.id,
              email: item.email,
              status: item.status,
              createdAt: date.getTime(),
            };
          });
          console.log(items);
          return {
            total: res.data.count,
            succcess: true,
            data: items,
          };
        }}
        rowKey="id"
        bordered
        pagination={{
          showQuickJumper: true,
          showSizeChanger: true,
        }}
        search={false}
        options={{
          search: true,
        }}
        toolBarRender={() => [
          <Popover
            content={
              <InviteCreate
                studio={studioname}
                onCreate={() => {
                  setOpenCreate(false);
                  ref.current?.reload();
                }}
              />
            }
            placement="bottomRight"
            trigger="click"
            open={openCreate}
            onOpenChange={(open: boolean) => {
              setOpenCreate(open);
            }}
          >
            <Button key="button" icon={<UserAddOutlined />} type="primary">
              {intl.formatMessage({ id: "buttons.invite" })}
            </Button>
          </Popover>,
        ]}
      />
    </>
  );
};

export default Widget;
