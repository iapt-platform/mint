import { useEffect, useState } from "react";
import { Button, message, Space, Typography } from "antd";
import { EditOutlined, SaveOutlined } from "@ant-design/icons";

import type {
  ITaskData,
  ITaskResponse,
  ITaskUpdateRequest,
} from "../../api/task";
import MdView from "../template/MdView";
import MDEditor from "@uiw/react-md-editor";
import "../article/article.css";
import { patch } from "../../request";
import _DiscussionDrawer from "../discussion/DiscussionDrawer";
import { useIntl } from "react-intl";
const { Text } = Typography;

interface IWidget {
  task?: ITaskData;
  onChange?: (data: ITaskData[]) => void;
  onDiscussion?: () => void;
}
const Description = ({ task, onChange, onDiscussion }: IWidget) => {
  const intl = useIntl();

  const [mode, setMode] = useState<"read" | "edit">("read");
  const [content, setContent] = useState(task?.description);
  const [loading, setLoading] = useState(false);

  useEffect(() => setContent(task?.description), [task]);
  return (
    <div>
      <div
        style={{
          display: "flex",
          justifyContent: "space-between",
          padding: 8,
        }}
      >
        <span>
          <Text strong>任务描述</Text>
        </span>
        <span>
          {mode === "read" ? (
            <Space>
              <Button key={1} onClick={onDiscussion}>
                {intl.formatMessage({ id: "buttons.discussion" })}
              </Button>
              <Button
                key={2}
                ghost
                type="primary"
                icon={<EditOutlined />}
                onClick={() => setMode("edit")}
              >
                {intl.formatMessage({ id: "buttons.edit" })}
              </Button>
            </Space>
          ) : (
            <Button
              ghost
              type="primary"
              icon={<SaveOutlined />}
              loading={loading}
              onClick={() => {
                if (!task) {
                  return;
                }
                const setting: ITaskUpdateRequest = {
                  id: task.id,
                  studio_name: "",
                  description: content,
                };
                const url = `/v2/task/${setting.id}`;
                console.info("api request", url, setting);
                setLoading(true);
                patch<ITaskUpdateRequest, ITaskResponse>(url, setting)
                  .then((json) => {
                    console.info("api response", json);
                    if (json.ok) {
                      message.success("Success");
                      setMode("read");
                      onChange && onChange([json.data]);
                    } else {
                      message.error(json.message);
                    }
                  })
                  .finally(() => setLoading(false));
              }}
            >
              {intl.formatMessage({ id: "buttons.save" })}
            </Button>
          )}
        </span>
      </div>
      {mode === "read" ? (
        <MdView html={task?.html} />
      ) : (
        <MDEditor
          className="pcd_md_editor"
          value={content ?? undefined}
          onChange={(value: unknown) => setContent(value)}
          height={450}
          minHeight={200}
          style={{ width: "100%" }}
        />
      )}
    </div>
  );
};

export default Description;
