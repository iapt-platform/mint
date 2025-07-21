import { useEffect, useState } from "react";
import { Button, Dropdown, Space } from "antd";
import type { MenuProps } from "antd";
import { IArticleListResponse } from "../api/Article";
import { get } from "../../request";
import { useAppSelector } from "../../hooks";
import { currentUser } from "../../reducers/current-user";

// 接口定义
export interface IPromptNode {
  text: string;
  prompt?: string;
  children?: IPromptNode[];
}

// Markdown 解析函数
export function parseMarkdownToPromptNodes(markdown: string): IPromptNode[] {
  const lines = markdown
    .split("\n")
    .map((l) => l.trim())
    .filter(Boolean);

  const result: IPromptNode[] = [];
  let currentButton: IPromptNode | null = null;
  let currentChild: { title: string; content: string[] } | null = null;

  for (let line of lines) {
    if (line.startsWith("# ")) {
      if (currentButton) {
        if (currentChild) {
          currentButton.children = currentButton.children || [];
          currentButton.children.push({
            text: currentChild.title,
            prompt: currentChild.content.join("\n"),
          });
        }
        result.push(currentButton);
      }
      currentButton = { text: line.replace("# ", ""), children: [] };
      currentChild = null;
    } else if (line.startsWith("## ")) {
      if (currentChild) {
        currentButton!.children!.push({
          text: currentChild.title,
          prompt: currentChild.content.join("\n"),
        });
      }
      currentChild = {
        title: line.replace("## ", ""),
        content: [],
      };
    } else {
      currentChild?.content.push(line);
    }
  }

  if (currentChild) {
    currentButton!.children!.push({
      text: currentChild.title,
      prompt: currentChild.content.join("\n"),
    });
  }
  if (currentButton) {
    // 若没有children但有currentChild.prompt，作为主按钮 prompt
    if (!currentButton.children?.length && currentChild?.content.length) {
      currentButton.prompt = currentChild.content.join("\n");
      delete currentButton.children;
    }
    result.push(currentButton);
  }

  return result;
}

interface IWidget {
  onText?: (prompt: string) => void;
}
// 按钮组组件
const PromptButtonGroup = ({ onText }: IWidget) => {
  const user = useAppSelector(currentUser);
  const [data, setData] = useState<IPromptNode[]>([]);

  useEffect(() => {
    if (!user) {
      return;
    }
    const getPrompt = async (studio: string) => {
      const urlTpl = `/v2/article?view=template&studio_name=${user?.realName}&subtitle=_template_prompt_&content=true`;
      const json = await get<IArticleListResponse>(urlTpl);
      if (json.ok) {
        if (json.data.rows.length > 0) {
          if (json.data.rows[0].content) {
            return json.data.rows[0].content;
          }
        }
      }
      return false;
    };

    getPrompt(user?.realName).then((value) => {
      if (value) {
        setData(parseMarkdownToPromptNodes(value));
      } else {
        getPrompt("admin").then((value) => {
          if (value) {
            setData(parseMarkdownToPromptNodes(value));
          }
        });
      }
    });
  }, [user, user?.realName]);

  return (
    <Space>
      {data.map((node) => {
        if (node.children && node.children.length > 0) {
          const items: MenuProps["items"] = node.children.map((child, idx) => ({
            key: `${node.text}-${idx}`,
            label: child.text,
            onClick: () => onText && onText(child.prompt || ""),
          }));

          return (
            <Dropdown key={node.text} menu={{ items }} trigger={["click"]}>
              <Button type="link" size="small">
                {node.text}
              </Button>
            </Dropdown>
          );
        } else {
          return (
            <Button
              key={node.text}
              onClick={() => onText && onText(node.prompt || "")}
            >
              {node.text}
            </Button>
          );
        }
      })}
    </Space>
  );
};

export default PromptButtonGroup;
