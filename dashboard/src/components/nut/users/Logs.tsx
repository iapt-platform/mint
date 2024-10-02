import { ProTable } from "@ant-design/pro-components";
import { useIntl } from "react-intl";

interface IItem {
  id: number;
  message: string;
  createdAt: Date;
}

const Widget = () => {
  const intl = useIntl();
  return (
    <ProTable<IItem>
      columns={[
        {
          title: intl.formatMessage({ id: "forms.fields.id.label" }),
          dataIndex: "id",
          key: "id",
          width: 80,
          search: false,
        },

        {
          title: intl.formatMessage({ id: "forms.fields.message.label" }),
          dataIndex: "message",
          key: "message",
          search: false,
        },
        {
          title: intl.formatMessage({ id: "forms.fields.created-at.label" }),
          key: "created-at",
          width: 200,
          render: (_, it) => <>{it.createdAt.toISOString()}</>,
          search: false,
        },
      ]}
      request={async (params = {}, sorter, filter) => {
        // TODO
        console.log(params, sorter, filter);

        const size = params.pageSize || 20;
        return {
          total: 1 << 12,
          success: true,
          data: Array.from(Array(size).keys()).map((x) => {
            const id = ((params.current || 1) - 1) * size + x + 1;
            var it: IItem = {
              id,
              message: `message ${id}`,
              createdAt: new Date(),
            };
            return it;
          }),
        };
      }}
      rowKey="id"
      bordered
      pagination={{
        showQuickJumper: true,
        showSizeChanger: true,
      }}
      headerTitle={intl.formatMessage({ id: "nut.users.logs.title" })}
    />
  );
};

export default Widget;
