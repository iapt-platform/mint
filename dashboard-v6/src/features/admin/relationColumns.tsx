import { Button, Dropdown, Space, Tag } from "antd";
import type { ProColumns } from "@ant-design/pro-components";
import { DeleteOutlined, MoreOutlined } from "@ant-design/icons";
import type { IntlShape } from "react-intl";
import type { IRelation } from "../../api/relation";
import GrammarTags from "../../components/admin/GrammarTags";
import TimeShow from "../../components/general/TimeShow";
import TermCell from "../../components/admin/TermCell";
import RelationEdit from "./RelationEdit";

interface IOptions {
  intl: IntlShape;
  /** 删除（确认后由调用方删，删完调 reload） */
  onDelete: (row: IRelation, reload: () => void) => void;
}

/** 列定义。刷新用 ProTable 传给 render 的 action，不经 ref。 */
export const relationColumns = ({ intl, onDelete }: IOptions): ProColumns<IRelation>[] => {
  const t = (id: string) => intl.formatMessage({ id });
  return [
    { title: t("dict.fields.sn.label"), dataIndex: "sn", key: "sn", width: 50, search: false },
    {
      title: t("forms.fields.relation.label"),
      dataIndex: "name",
      key: "name",
      sorter: true,
      render: (_, row, _i, action) => (
        <RelationEdit
          id={row.id}
          trigger={
            <Button type="link" size="small">
              {row.name}
            </Button>
          }
          onSuccess={() => action?.reload()}
        />
      ),
    },
    {
      title: t("forms.fields.name.label"),
      dataIndex: "name1",
      key: "name1",
      search: false,
      render: (_, row, _i, action) => (
        <TermCell
          term={row.name_term}
          word={row.name}
          channelId={row.name_term ? row.category_channel : row.name_channel}
          reload={() => action?.reload()}
          createLabel={t("buttons.create")}
        />
      ),
    },
    {
      title: t("forms.fields.from.label"),
      dataIndex: "from",
      key: "from",
      render: (_, row) => <GrammarTags value={row.from} />,
    },
    {
      title: t("forms.fields.to.label"),
      dataIndex: "to",
      key: "to",
      render: (_, row) => <GrammarTags value={row.to} />,
    },
    {
      title: t("forms.fields.match.label"),
      dataIndex: "match",
      key: "match",
      render: (_, row) => row.match?.map((item) => <Tag key={item}>{item}</Tag>),
    },
    {
      title: t("forms.fields.category.label"),
      dataIndex: "category",
      key: "category",
      sorter: true,
      render: (_, row, _i, action) => (
        <Space>
          {row.category}
          <TermCell
            term={row.category_term}
            word={row.category}
            channelId={row.category_channel}
            reload={() => action?.reload()}
            createLabel={t("buttons.create")}
          />
        </Space>
      ),
    },
    {
      title: t("forms.fields.editor.label"),
      dataIndex: "editor",
      key: "editor",
      search: false,
      render: (_, row) => row.editor?.nickName,
    },
    {
      title: t("forms.fields.updated-at.label"),
      key: "updated_at",
      width: 100,
      search: false,
      dataIndex: "updated_at",
      valueType: "date",
      sorter: true,
      render: (_, row) => <TimeShow updatedAt={row.updated_at} showLabel={false} showIcon={false} />,
    },
    {
      title: t("buttons.option"),
      key: "option",
      width: 80,
      valueType: "option",
      render: (_, row, _i, action) => [
        <Dropdown
          key="more"
          trigger={["click"]}
          menu={{
            items: [{ key: "remove", label: t("buttons.delete"), icon: <DeleteOutlined />, danger: true }],
            onClick: (e) => {
              if (e.key === "remove") {
                onDelete(row, () => action?.reload());
              }
            },
          }}
        >
          <Button type="link" size="small" icon={<MoreOutlined />} />
        </Dropdown>,
      ],
    },
  ];
};
