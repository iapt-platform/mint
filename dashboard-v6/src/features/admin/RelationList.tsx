import { useIntl } from "react-intl";
import { App, Button } from "antd";
import { ProTable } from "@ant-design/pro-components";
import { ExclamationCircleOutlined, ExportOutlined, ImportOutlined, PlusOutlined } from "@ant-design/icons";
import { deleteRelation, fetchRelationList, RELATION_EXPORT_URL, RELATION_IMPORT_URL } from "../../api/admin";
import type { IRelation } from "../../api/relation";
import DataImport from "../../components/general/DataImport";
import { get as getUiLang } from "../../locales";
import { getSorterUrl } from "../../utils";
import RelationEdit from "./RelationEdit";
import { relationColumns } from "./relationColumns";

interface IParams {
  name?: string;
  from?: string;
  to?: string;
  match?: string;
  category?: string;
  keyword?: string;
}

/** 语法关系表：增删改、导入导出、按名称 / 匹配 / 分类筛选。 */
const RelationList = () => {
  const intl = useIntl();
  const { message, modal } = App.useApp();

  const confirmDelete = (row: IRelation, reload: () => void) => {
    if (!row.id) return;
    const id = row.id;
    modal.confirm({
      icon: <ExclamationCircleOutlined />,
      title: intl.formatMessage({ id: "message.delete.confirm" }) + intl.formatMessage({ id: "message.irrevocable" }),
      content: row.name,
      okText: intl.formatMessage({ id: "buttons.delete" }),
      okType: "danger",
      cancelText: intl.formatMessage({ id: "buttons.no" }),
      onOk: () =>
        deleteRelation(id)
          .then((json) => {
            if (json.ok) {
              message.success(intl.formatMessage({ id: "flashes.success" }));
              reload();
            } else {
              message.error(json.message);
            }
          })
          .catch((e) => message.error(String(e?.message ?? e))),
    });
  };

  return (
    <ProTable<IRelation, IParams>
      columns={relationColumns({ intl, onDelete: confirmDelete })}
      request={async (params = {}, sorter, filter) => {
        const pageSize = params.pageSize ?? 20;
        const offset = ((params.current ?? 1) - 1) * pageSize;
        let query = `&ui-lang=${getUiLang()}&limit=${pageSize}&offset=${offset}`;
        if (typeof params.keyword !== "undefined") query += `&search=${params.keyword ?? ""}`;
        if (filter.case) query += `&case=${filter.case.join()}`;
        if (params.name) query += `&name=${params.name}`;
        if (params.match) query += `&match=${params.match}`;
        if (params.from) query += `&from=${params.from}`;
        if (params.to) query += `&to=${params.to}`;
        if (params.category) query += `&category=${params.category}`;
        query += getSorterUrl(sorter);

        const res = await fetchRelationList(query);
        return {
          total: res.data.count,
          success: res.ok,
          data: res.data.rows.map((item, index) => ({ ...item, sn: offset + index + 1 })),
        };
      }}
      rowKey="id"
      bordered
      pagination={{ showQuickJumper: true, showSizeChanger: true }}
      options={{ search: true }}
      search={{ filterType: "light" }}
      toolBarRender={(action) => [
        <DataImport
          key="import"
          url={RELATION_IMPORT_URL}
          trigger={<Button icon={<ImportOutlined />}>{intl.formatMessage({ id: "admin.import" })}</Button>}
          onSuccess={() => action?.reload()}
        />,
        <Button key="export" icon={<ExportOutlined />} href={RELATION_EXPORT_URL} target="_blank" rel="noreferrer">
          {intl.formatMessage({ id: "admin.export" })}
        </Button>,
        <RelationEdit
          key="create"
          trigger={
            <Button icon={<PlusOutlined />} type="primary">
              {intl.formatMessage({ id: "buttons.create" })}
            </Button>
          }
          onSuccess={() => action?.reload()}
        />,
      ]}
    />
  );
};

export default RelationList;
