import { useIntl } from "react-intl";
import { useState } from "react";
import { ProList } from "@ant-design/pro-components";
import { Space, Tag, Button, Layout, Popconfirm } from "antd";
import { get } from "../../request";
import { IShareListResponse } from "../api/Share";

const { Content } = Layout;

interface IRoleTag {
  title: string;
  color: string;
}
interface DataItem {
  id: string;
  name?: string;
  tag: IRoleTag[];
  image: string;
  description?: string;
}
interface IWidget {
  groupId?: string;
}
const Widget = ({ groupId }: IWidget) => {
  const intl = useIntl(); //i18n
  const [canDelete, setCanDelete] = useState(false);
  const [resCount, setResCount] = useState(0);
  return (
    <Content>
      <ProList<DataItem>
        rowKey="id"
        headerTitle={
          intl.formatMessage({ id: "group.files" }) + "-" + resCount.toString()
        }
        showActions="hover"
        metas={{
          title: {
            dataIndex: "name",
          },
          avatar: {
            dataIndex: "image",
            editable: false,
          },
          description: {
            dataIndex: "description",
          },
          content: {
            dataIndex: "content",
            editable: false,
          },
          subTitle: {
            render: (text, row, index, action) => {
              const showtag = row.tag.map((item, id) => {
                return (
                  <Tag color={item.color} key={id}>
                    {item.title}
                  </Tag>
                );
              });
              return <Space size={0}>{showtag}</Space>;
            },
          },
          actions: {
            render: (text, row, index, action) => [
              canDelete ? (
                <Popconfirm
                  title={intl.formatMessage({
                    id: "forms.message.member.delete",
                  })}
                  onConfirm={(
                    e?: React.MouseEvent<HTMLElement, MouseEvent>
                  ) => {
                    console.log("delete", row.id);
                  }}
                  okText={intl.formatMessage({ id: "buttons.ok" })}
                  cancelText={intl.formatMessage({ id: "buttons.cancel" })}
                >
                  <Button size="small" type="link" danger key="link">
                    {intl.formatMessage({ id: "buttons.remove" })}
                  </Button>
                </Popconfirm>
              ) : (
                <></>
              ),
            ],
          },
        }}
        request={async (params = {}, sorter, filter) => {
          // TODO
          console.log(params, sorter, filter);

          let url = `/v2/share?view=group&id=${groupId}`;
          const offset =
            ((params.current ? params.current : 1) - 1) *
            (params.pageSize ? params.pageSize : 20);
          url += `&limit=${params.pageSize}&offset=${offset}`;
          if (typeof params.keyword !== "undefined") {
            url += "&search=" + (params.keyword ? params.keyword : "");
          }
          const res = await get<IShareListResponse>(url);
          if (res.ok) {
            console.log(res.data.rows);
            setResCount(res.data.count);
            switch (res.data.role) {
              case "owner":
                setCanDelete(true);
                break;
              case "manager":
                setCanDelete(true);
                break;
            }
            const items: DataItem[] = res.data.rows.map((item, id) => {
              let member: DataItem = {
                id: item.res_id,
                name: item.res_name,
                tag: [],
                image: "",
                description: item.owner?.nickName,
              };
              switch (item.power) {
                case 0:
                  member.tag.push({ title: "拥有者", color: "success" });
                  break;
                case 1:
                  member.tag.push({ title: "管理员", color: "default" });
                  break;
              }

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
      />
    </Content>
  );
};

export default Widget;
