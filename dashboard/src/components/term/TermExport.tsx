import { useState } from "react";
import { useIntl } from "react-intl";
import { Button, message } from "antd";
import { ExportOutlined } from "@ant-design/icons";
import modal from "antd/lib/modal";

import { API_HOST, get } from "../../request";

interface IExportResponse {
  ok: boolean;
  message: string;
  data: {
    uuid: string;
    filename: string;
    type: string;
  };
}
interface IWidget {
  channelId?: string;
  studioName?: string;
}
const TermExportWidget = ({ channelId, studioName }: IWidget) => {
  const intl = useIntl();
  const [loading, setLoading] = useState(false);
  return (
    <Button
      loading={loading}
      icon={<ExportOutlined />}
      onClick={() => {
        let url = `/v2/terms-export?view=`;
        if (typeof channelId !== "undefined") {
          url += `channel&id=${channelId}`;
        } else if (typeof studioName !== "undefined") {
          url += `studio&name=${studioName}`;
        }
        setLoading(true);
        get<IExportResponse>(url)
          .then((json) => {
            if (json.ok) {
              const link = `${API_HOST}/api/v2/terms-export/${json.data.uuid}`;
              modal.info({
                title: intl.formatMessage({ id: "buttons.download" }),
                content: (
                  <>
                    <a
                      href={link}
                      target="_blank"
                      key="export"
                      rel="noreferrer"
                    >
                      {intl.formatMessage({ id: "buttons.download.link" })}
                    </a>
                  </>
                ),
              });
            } else {
              message.error(json.message);
            }
          })
          .finally(() => {
            setLoading(false);
          });
      }}
    >
      {intl.formatMessage({ id: "buttons.export" })}
    </Button>
  );
};

export default TermExportWidget;
