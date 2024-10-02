import { useIntl } from "react-intl";
import { Button, Popover } from "antd";
import { ActionType, ProTable } from "@ant-design/pro-components";
import { UserAddOutlined } from "@ant-design/icons";

import { get } from "../../request";
import { RoleValueEnum } from "../../components/studio/table";

import { useRef, useState } from "react";
import InviteCreate from "../../components/invite/InviteCreate";
import { getSorterUrl } from "../../utils";
import { IInviteListResponse } from "../../components/api/Auth";

interface DataItem {
  sn: number;
  id: string;
  email: string;
  status: string;
  created_at: string;
}

interface IWidget {
  studioName?: string;
}

const InviteListWidget = ({ studioName }: IWidget) => {
  const intl = useIntl(); //i18n
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
            key: "created_at",
            width: 100,
            search: false,
            dataIndex: "created_at",
            valueType: "date",
          },
        ]}
        request={async (params = {}, sorter, filter) => {
          console.log(params, sorter, filter);
          let url = `/v2/invite?`;
          if (studioName) {
            url += `view=studio&studio=${studioName}`;
          } else {
            url += `view=all`;
          }
          const offset =
            ((params.current ? params.current : 1) - 1) *
            (params.pageSize ? params.pageSize : 20);
          url += `&limit=${params.pageSize}&offset=${offset}`;
          url += params.keyword ? "&search=" + params.keyword : "";

          url += getSorterUrl(sorter);

          console.log(url);
          const res = await get<IInviteListResponse>(url);
          const items: DataItem[] = res.data.rows.map((item, id) => {
            return {
              sn: id + offset + 1,
              id: item.id,
              email: item.email,
              status: item.status,
              created_at: item.created_at,
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
        toolBarRender={
          studioName
            ? () => [
                <Popover
                  content={
                    <InviteCreate
                      studio={studioName}
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
                  <Button
                    key="button"
                    icon={<UserAddOutlined />}
                    type="primary"
                  >
                    {intl.formatMessage({ id: "buttons.invite" })}
                  </Button>
                </Popover>,
              ]
            : undefined
        }
      />
    </>
  );
};

export default InviteListWidget;
