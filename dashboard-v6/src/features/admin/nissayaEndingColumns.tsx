import { Button, Dropdown, Space } from "antd";
import type { ProColumns } from "@ant-design/pro-components";
import { DeleteOutlined, MoreOutlined } from "@ant-design/icons";
import type { IntlShape } from "react-intl";
import type { INissayaEnding } from "../../api/admin";
import GrammarTags from "../../components/admin/GrammarTags";
import { LangValueEnum } from "../../components/general/LangSelect";
import TimeShow from "../../components/general/TimeShow";
import { NissayaCardModal } from "../../components/nissaya/NissayaCard";
import TermModal from "../../components/term/TermModal";
import NissayaEndingEdit from "./NissayaEndingEdit";

interface IOptions {
  intl: IntlShape;
  /** 删除（确认后由调用方删，删完调 reload） */
  onDelete: (row: INissayaEnding, reload: () => void) => void;
}

/** 列定义。刷新用 ProTable 传给 render 的 action，不经 ref。 */
export const nissayaEndingColumns = ({ intl, onDelete }: IOptions): ProColumns<INissayaEnding>[] => {
  const t = (id: string) => intl.formatMessage({ id });
  return [
    { title: t("dict.fields.sn.label"), dataIndex: "sn", key: "sn", width: 50, search: false },
    {
      title: t("forms.fields.ending.label"),
      dataIndex: "ending",
      key: "ending",
      search: false,
      sorter: true,
      render: (_, row, _i, action) => (
        <Space>
          <NissayaEndingEdit
            id={row.id}
            trigger={
              <Button type="link" size="small">
                {row.ending}
              </Button>
            }
            onSuccess={() => action?.reload()}
          />
          {row.termChannel ? (
            <TermModal
              trigger={<Button size="small">{t("admin.nissaya.term")}</Button>}
              id={row.termId}
              word={row.ending}
              channelId={row.termChannel}
            />
          ) : null}
        </Space>
      ),
    },
    {
      title: t("forms.fields.lang.label"),
      dataIndex: "lang",
      key: "lang",
      search: false,
      filters: true,
      valueEnum: LangValueEnum(),
    },
    {
      title: t("forms.fields.from.label"),
      dataIndex: "from",
      key: "from",
      search: false,
      render: (_, row) => <GrammarTags value={row.from} />,
    },
    { title: t("forms.fields.relation.label"), dataIndex: "relation", key: "relation", sorter: true },
    { title: t("forms.fields.count.label"), key: "count", width: 100, search: false, dataIndex: "count", sorter: true },
    {
      title: t("forms.fields.updated-at.label"),
      key: "updated_at",
      width: 100,
      search: false,
      dataIndex: "updated_at",
      valueType: "date",
      sorter: true,
      render: (_, row) => <TimeShow updatedAt={row.updated_at} showLabel showIcon={false} />,
    },
    {
      title: t("buttons.option"),
      key: "option",
      width: 130,
      valueType: "option",
      render: (_, row, _i, action) => [
        <NissayaCardModal key="preview" text={row.ending} trigger={<Button type="link" size="small">{t("buttons.preview")}</Button>} />,
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
