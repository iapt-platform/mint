import { useIntl } from "react-intl";
import { App, Button } from "antd";
import { ProTable } from "@ant-design/pro-components";
import { ExclamationCircleOutlined, ExportOutlined, ImportOutlined, PlusOutlined } from "@ant-design/icons";
import {
  deleteNissayaEnding,
  fetchNissayaEndingList,
  NISSAYA_ENDING_EXPORT_URL,
  NISSAYA_ENDING_IMPORT_URL,
  type INissayaEnding,
} from "../../api/admin";
import DataImport from "../../components/general/DataImport";
import { get as getUiLang } from "../../locales";
import { getSorterUrl } from "../../utils";
import NissayaEndingEdit from "./NissayaEndingEdit";
import { nissayaEndingColumns } from "./nissayaEndingColumns";

interface IParams {
  relation?: string;
  keyword?: string;
}

/**
 * Nissaya 语尾表：增删改、预览、术语维护、导入导出。
 * `relation` 来自路由 /admin/nissaya-ending/:relation，作为初始筛选。
 */
const NissayaEndingList = ({ relation }: { relation?: string }) => {
  const intl = useIntl();
  const { message, modal } = App.useApp();

  const confirmDelete = (row: INissayaEnding, reload: () => void) => {
    if (!row.id) return;
    const id = row.id;
    modal.confirm({
      icon: <ExclamationCircleOutlined />,
      title: intl.formatMessage({ id: "message.delete.confirm" }) + intl.formatMessage({ id: "message.irrevocable" }),
      content: row.ending,
      okText: intl.formatMessage({ id: "buttons.delete" }),
      okType: "danger",
      cancelText: intl.formatMessage({ id: "buttons.no" }),
      onOk: () =>
        deleteNissayaEnding(id)
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
    <ProTable<INissayaEnding, IParams>
      search={{ filterType: "light" }}
      options={{ search: true }}
      form={{ initialValues: relation ? { relation } : undefined }}
      columns={nissayaEndingColumns({ intl, onDelete: confirmDelete })}
      request={async (params = {}, sorter, filter) => {
        const pageSize = params.pageSize ?? 20;
        const offset = ((params.current ?? 1) - 1) * pageSize;
        let query = `&limit=${pageSize}&offset=${offset}&ui-lang=${getUiLang()}`;
        if (params.keyword) query += `&search=${params.keyword}`;
        if (params.relation) query += `&relation=${params.relation}`;
        if (filter.case) query += `&case=${filter.case.join()}`;
        if (filter.lang) query += `&lang=${filter.lang.join()}`;
        query += getSorterUrl(sorter);

        const res = await fetchNissayaEndingList(query);
        return {
          total: res.data.count,
          success: res.ok,
          data: res.data.rows.map((item, index) => ({
            sn: offset + index + 1,
            id: item.id,
            ending: item.ending,
            lang: item.lang,
            relation: item.relation,
            case: item.case,
            from: item.from,
            count: item.count,
            termId: item.term_id,
            termChannel: item.term_channel,
            updated_at: item.updated_at,
          })),
        };
      }}
      rowKey="id"
      bordered
      pagination={{ showQuickJumper: true, showSizeChanger: true }}
      toolBarRender={(action) => [
        <DataImport
          key="import"
          url={NISSAYA_ENDING_IMPORT_URL}
          trigger={<Button icon={<ImportOutlined />}>{intl.formatMessage({ id: "admin.import" })}</Button>}
          onSuccess={() => action?.reload()}
        />,
        <Button key="export" icon={<ExportOutlined />} href={NISSAYA_ENDING_EXPORT_URL} target="_blank" rel="noreferrer">
          {intl.formatMessage({ id: "admin.export" })}
        </Button>,
        <NissayaEndingEdit
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

export default NissayaEndingList;
