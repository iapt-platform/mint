import {
  Collapse,
  Modal,
  Progress,
  Select,
  Switch,
  Typography,
  message,
} from "antd";
import { useEffect, useRef, useState } from "react";
import { get } from "../../request";
import { ArticleType } from "../article/Article";
import ExportSettingLayout from "./ExportSettingLayout";

const { Text } = Typography;

interface IExportResponse {
  ok: boolean;
  message?: string;
  data: string;
}

interface IStatus {
  progress: number;
  message: string;
  log?: string[];
}
interface IExportStatusResponse {
  ok: boolean;
  message?: string;
  data: {
    url?: string;
    status: IStatus;
  };
}
interface IWidget {
  type?: ArticleType;
  articleId?: string;
  book?: string | null;
  para?: string | null;
  channelId?: string | null;
  anthologyId?: string | null;
  open?: boolean;
  onClose?: Function;
}

const ExportModalWidget = ({
  type,
  book,
  para,
  channelId,
  articleId,
  anthologyId,
  open = false,
  onClose,
}: IWidget) => {
  const [isModalOpen, setIsModalOpen] = useState(open);
  const [filename, setFilename] = useState<string>();
  const [url, setUrl] = useState<string>();
  const [format, setFormat] = useState<string>("docx");
  const [exportStatus, setExportStatus] = useState<IStatus>();
  const [exportStart, setExportStart] = useState(false);
  const [hasOrigin, setHasOrigin] = useState(false);
  const [hasTranslation, setHasTranslation] = useState(true);

  const filenameRef = useRef(filename);

  useEffect(() => {
    // 及时更新 count 值
    filenameRef.current = filename;
  });
  const queryStatus = () => {
    if (typeof filenameRef.current === "undefined") {
      return;
    }
    const url = `/v2/export/${filenameRef.current}`;
    console.info("api request export", url);
    get<IExportStatusResponse>(url)
      .then((json) => {
        console.info("api response export", json);
        if (json.ok) {
          setExportStatus(json.data.status);
          if (json.data.status.progress === 1) {
            setFilename(undefined);
            setUrl(json.data.url);
          }
        } else {
          console.error(json.message);
        }
      })
      .catch((e) => console.error(e));
  };

  useEffect(() => {
    const interval = setInterval(() => queryStatus(), 3000);
    return () => clearInterval(interval);
  }, []);

  const getUrl = (): string => {
    if (!articleId) {
      throw new Error("id error");
    }
    let url = `/v2/export?type=${type}&id=${articleId}&format=${format}`;
    url += channelId ? `&channel=${channelId}` : "";
    url += "&origin=" + (hasOrigin ? "true" : "false");
    url += "&translation=" + (hasTranslation ? "true" : "false");
    switch (type) {
      case "chapter":
        const para = articleId?.split("-").map((item) => parseInt(item));
        if (para?.length === 2) {
          url += `&book=${para[0]}&par=${para[1]}`;
        } else {
          throw new Error("段落编号错误");
        }
        if (!channelId) {
          throw new Error("请选择版本");
        }
        break;
      case "article":
        url += `&id=${articleId}`;
        url += anthologyId ? `&anthology=${anthologyId}` : "";
        break;
      default:
        throw new Error("此类型暂时无法导出" + type);
        break;
    }
    return url;
  };
  const exportRun = (): void => {
    const url = getUrl();
    console.info("api request", url);
    setExportStart(true);
    get<IExportResponse>(url)
      .then((json) => {
        console.info("api response", json);
        if (json.ok) {
          const filename = json.data;
          console.log("filename", filename);
          setFilename(filename);
        } else {
        }
      })
      .catch((e) => {});
  };

  const closeModal = () => {
    if (typeof onClose !== "undefined") {
      onClose();
    }
  };
  useEffect(() => setIsModalOpen(open), [open]);
  return (
    <Modal
      destroyOnClose
      title="导出"
      width={400}
      open={isModalOpen}
      onOk={() => {
        console.log("type", type);
        try {
          exportRun();
        } catch (error) {
          message.error((error as Error).message);
          console.error(error);
        }
      }}
      onCancel={closeModal}
      okText={"导出"}
      okButtonProps={{ disabled: exportStart }}
    >
      <ExportSettingLayout
        label="格式"
        content={
          <Select
            defaultValue={format}
            bordered={false}
            options={[
              {
                value: "docx",
                label: "Word",
              },
              {
                value: "pdf",
                label: "PDF",
              },
              {
                value: "epub",
                label: "epub电子书",
              },
              {
                value: "markdown",
                label: "Markdown",
              },
              {
                value: "html",
                label: "Html",
              },
              {
                value: "pptx",
                label: "PPT幻灯片",
              },
              {
                value: "txt",
                label: "Text纯文本",
              },
              {
                value: "tex",
                label: "LaTex",
              },
            ]}
            onSelect={(value) => setFormat(value)}
          />
        }
      />
      <ExportSettingLayout
        label="原文"
        content={
          <Switch
            disabled={!hasTranslation}
            size="small"
            defaultChecked={hasOrigin}
            onChange={(checked) => setHasOrigin(checked)}
          />
        }
      />
      <ExportSettingLayout
        label="译文"
        content={
          <Switch
            disabled={!hasOrigin}
            size="small"
            defaultChecked={hasTranslation}
            onChange={(checked) => setHasTranslation(checked)}
          />
        }
      />
      <ExportSettingLayout
        label="对照方式"
        content={
          <Select
            disabled
            defaultValue={"auto"}
            bordered={false}
            options={[
              {
                value: "auto",
                label: "自动",
              },
              {
                value: "col",
                label: "分栏",
              },
              {
                value: "row",
                label: "纵列",
              },
            ]}
            onSelect={(value) => setFormat(value)}
          />
        }
      />

      <div style={{ display: exportStart ? "block" : "none" }}>
        <Progress
          percent={exportStatus ? Math.round(exportStatus?.progress * 100) : 0}
          status={
            exportStatus
              ? exportStatus.progress === 1
                ? "success"
                : "active"
              : "normal"
          }
        />
        <Collapse collapsible="icon" ghost>
          <Collapse.Panel
            header={
              <div style={{ display: "flex", justifyContent: "space-between" }}>
                <Text>
                  {exportStatus ? exportStatus.message : "正在生成……"}
                </Text>
                {url ? (
                  <a href={url} target="_blank" rel="noreferrer">
                    {"下载"}
                  </a>
                ) : (
                  <></>
                )}
              </div>
            }
            key="1"
          >
            <div style={{ height: 200, overflowY: "auto" }}>
              {exportStatus?.log?.map((item, id) => {
                return <div key={id}>{item}</div>;
              })}
            </div>
          </Collapse.Panel>
        </Collapse>
      </div>
    </Modal>
  );
};

export default ExportModalWidget;
