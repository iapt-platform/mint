import { useEffect, useState } from "react";
import { Alert, Spin } from "antd";
import { useIntl } from "react-intl";

import { previewMarkup } from "../../api/markup";
import { ApiError } from "../../api/error";
import MarkupView from "../markup/MarkupView";
import type { MarkupNode } from "../markup/types";

/** 停止输入多久后才请求预览 */
const DEBOUNCE_MS = 500;

const UUID = /^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i;

interface IWidget {
  code: string;
  /** 预览时按哪些 channel 查术语、读译文（非 uuid 的忽略） */
  channels?: string[];
}

/**
 * 模板代码的预览：交给后端渲染（`POST /v3/markup-previews`），与保存后在阅读页看到的一致。
 */
const TplPreview = ({ code, channels = [] }: IWidget) => {
  const intl = useIntl();
  const [nodes, setNodes] = useState<MarkupNode[]>([]);
  const [error, setError] = useState<string>();
  const [loading, setLoading] = useState(false);

  const channelKey = channels.filter((c) => UUID.test(c)).join(",");

  useEffect(() => {
    let active = true;
    const timer = setTimeout(() => {
      setLoading(true);
      previewMarkup({
        content: code,
        channels: channelKey === "" ? undefined : channelKey.split(","),
      })
        .then((result) => {
          if (!active) return;
          setNodes(result);
          setError(undefined);
        })
        .catch((e: unknown) => {
          if (!active) return;
          setNodes([]);
          setError(
            e instanceof ApiError
              ? e.message
              : intl.formatMessage({ id: "tpl-builder.preview.failed" }),
          );
        })
        .finally(() => active && setLoading(false));
    }, DEBOUNCE_MS);
    return () => {
      active = false;
      clearTimeout(timer);
    };
  }, [code, channelKey, intl]);

  if (error) {
    return <Alert type="warning" showIcon message={error} />;
  }
  return (
    <Spin spinning={loading}>
      <div style={{ minHeight: 40 }}>
        <MarkupView
          nodes={nodes}
          placeholder={intl.formatMessage({ id: "tpl-builder.preview.empty" })}
        />
      </div>
    </Spin>
  );
};

export default TplPreview;
