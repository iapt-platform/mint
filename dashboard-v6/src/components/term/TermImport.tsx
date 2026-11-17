import { useState } from "react";
import { useIntl } from "react-intl";
import { Alert, Button, Modal, Space, Table, Typography, Upload } from "antd";
import { ImportOutlined } from "@ant-design/icons";

import {
  importTerms,
  type TermExchangeTarget,
  type TermImportResult,
} from "../../api/Term";

interface IWidget {
  target?: TermExchangeTarget;
  onSuccess?: () => void;
}

/**
 * 导入术语（xlsx，v3）。可以直接用导出的表格改完传回。部分成功：出错的行跳过，弹窗里逐行列出原因。
 */
const TermImportWidget = ({ target, onSuccess }: IWidget) => {
  const intl = useIntl();
  const [loading, setLoading] = useState(false);
  const [result, setResult] = useState<TermImportResult>();
  const label = (id: string) => intl.formatMessage({ id });

  return (
    <>
      <Upload
        accept=".xlsx"
        showUploadList={false}
        disabled={!target || loading}
        customRequest={async ({ file }) => {
          if (!target) {
            return;
          }
          setLoading(true);
          try {
            const data = await importTerms(target, file as File);
            setResult(data);
            onSuccess?.();
          } finally {
            setLoading(false);
          }
        }}
      >
        <Button icon={<ImportOutlined />} loading={loading} disabled={!target}>
          {label("buttons.import")}
        </Button>
      </Upload>
      <Modal
        open={!!result}
        title={label("term.import.result")}
        footer={null}
        width={640}
        onCancel={() => setResult(undefined)}
      >
        {result && (
          <Space orientation="vertical" style={{ width: "100%" }}>
            <Typography.Text>
              {intl.formatMessage(
                { id: "term.import.summary" },
                {
                  created: result.created,
                  updated: result.updated,
                  unchanged: result.unchanged,
                  failed: result.errors?.length ?? 0,
                },
              )}
            </Typography.Text>
            {!result.reindexed && (
              <Alert
                type="info"
                showIcon
                title={label("term.import.reindex_later")}
              />
            )}
            {(result.errors?.length ?? 0) > 0 && (
              <Table
                size="small"
                rowKey="row"
                pagination={{ pageSize: 10 }}
                dataSource={result.errors}
                columns={[
                  {
                    title: label("term.import.row"),
                    dataIndex: "row",
                    width: 80,
                  },
                  { title: label("term.import.reason"), dataIndex: "message" },
                ]}
              />
            )}
          </Space>
        )}
      </Modal>
    </>
  );
};

export default TermImportWidget;
