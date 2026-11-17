import { useState } from "react";
import { useIntl } from "react-intl";
import { Button } from "antd";
import { ExportOutlined } from "@ant-design/icons";

import { exportTerms, type TermExchangeTarget } from "../../api/Term";

interface IWidget {
  target?: TermExchangeTarget;
}

/** 导出术语为 xlsx（v3：列表接口带 Accept: xlsx，直接下载，取代 v2 terms-export 的两步下载） */
const TermExportWidget = ({ target }: IWidget) => {
  const intl = useIntl();
  const [loading, setLoading] = useState(false);
  return (
    <Button
      loading={loading}
      disabled={!target}
      icon={<ExportOutlined />}
      onClick={async () => {
        if (!target) {
          return;
        }
        setLoading(true);
        try {
          const blob = await exportTerms(target);
          const url = URL.createObjectURL(blob);
          const link = document.createElement("a");
          link.href = url;
          link.download = "terms.xlsx";
          link.click();
          URL.revokeObjectURL(url);
        } finally {
          setLoading(false);
        }
      }}
    >
      {intl.formatMessage({ id: "buttons.export" })}
    </Button>
  );
};

export default TermExportWidget;
