import {
  Modal,
  Progress,
  Select,
  Space,
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
  const [format, setFormat] = useState<string>("html");
  const [exportStatus, setExportStatus] = useState<IStatus>();
  const [exportStart, setExportStart] = useState(false);
  const filenameRef = useRef(filename);

  useEffect(() => {
    // 及时更新 count 值
    filenameRef.current = filename;
  });
  const queryStatus = () => {
    console.log("timer", filenameRef.current);
    if (typeof filenameRef.current === "undefined") {
      return;
    }
    const url = `/v2/export/${filenameRef.current}`;
    console.log("url", url);
    get<IExportStatusResponse>(url).then((json) => {
      if (json.ok) {
        console.log("filename", json);
        setExportStatus(json.data.status);
        if (json.data.status.progress === 1) {
          setFilename(undefined);
          setUrl(json.data.url);
        }
      } else {
      }
    });
  };

  useEffect(() => {
    const interval = setInterval(() => queryStatus(), 3000);
    return () => clearInterval(interval);
  }, []);

  const exportChapter = (
    book: number,
    para: number,
    channel: string,
    format: string
  ): void => {
    const url = `/v2/export?book=${book}&par=${para}&channel=${channel}&format=${format}`;
    console.log("url", url);
    setExportStart(true);
    get<IExportResponse>(url).then((json) => {
      if (json.ok) {
        const filename = json.data;
        console.log("filename", filename);
        setFilename(filename);
      } else {
      }
    });
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
        if (type === "chapter") {
          if (articleId && channelId) {
            const para = articleId.split("-").map((item) => parseInt(item));
            const channels = channelId.split("_");
            if (para.length === 2) {
              exportChapter(para[0], para[1], channels[0], "html");
            } else {
              console.error("段落编号错误", articleId);
            }
          }
        } else {
          message.error("目前只支持章节导出");
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
                value: "pdf",
                label: "PDF",
                disabled: true,
              },
              {
                value: "word",
                label: "Word",
                disabled: true,
              },
              {
                value: "html",
                label: "Html",
              },
            ]}
            onSelect={(value) => setFormat(value)}
          />
        }
      />
      <ExportSettingLayout
        label="原文"
        content={<Switch size="small" onChange={(checked) => {}} />}
      />
      <ExportSettingLayout
        label="译文"
        content={
          <Switch size="small" defaultChecked onChange={(checked) => {}} />
        }
      />
      <ExportSettingLayout
        label="对照方式"
        content={
          <Select
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
        <Text>{exportStatus ? exportStatus.message : "正在生成……"}</Text>
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
        {url ? (
          <a href={url} target="_blank" rel="noreferrer">
            {"下载"}
          </a>
        ) : (
          <></>
        )}
      </div>
    </Modal>
  );
};

export default ExportModalWidget;
