import { useMemo, useState } from "react";
import { Divider, Input, Menu, Modal, Typography } from "antd";
import { useIntl } from "react-intl";

import type { ArticleType } from "../../api/article";
import TplForm from "./TplForm";
import TplPreview from "./TplPreview";
import {
  buildTplCode,
  initialValues,
  TEMPLATES,
  tplKey,
  type TplValues,
} from "./templates";

const { Paragraph, Text } = Typography;

/** article 模板的 type 能取的值；入口传来的其它类型（series、term…）按 article 处理 */
const ARTICLE_TYPES: string[] = ["article", "chapter", "para", "anthology"];

/** 弹窗内容区高度：视口减去上边距 20、标题约 40、上下内边距 40、下边距 20 */
const BODY_HEIGHT = "calc(100vh - 140px)";

interface IWidget {
  trigger?: React.ReactNode;
  open?: boolean;
  /** 从文章 / 章节入口打开时：article 模板预填的类型、id、标题、channel */
  tpl?: ArticleType;
  articleId?: string;
  /** channel uid，多个用 `_` 连接（与阅读页地址的 channel 参数相同） */
  channelsId?: string | null;
  title?: string;
  onClose?: () => void;
}

/**
 * 模板生成器：选模板 → 填参数 → 得到模板代码（可复制）与后端渲染的预览。
 * 能生成的模板见 ./templates.ts（与后端 App\Services\Markup\Templates 对应）。
 */
const TplBuilderWidget = ({
  trigger,
  open = false,
  tpl,
  articleId,
  channelsId,
  title,
  onClose,
}: IWidget) => {
  const intl = useIntl();
  /** 点 trigger 打开的；受控打开（open）由调用方在 onClose 里关 */
  const [innerOpen, setInnerOpen] = useState(false);
  const [active, setActive] = useState("article");

  /** 每个模板各自的参数值，切换页签不丢 */
  const prefilled = useMemo<Record<string, TplValues>>(() => {
    const all = Object.fromEntries(
      TEMPLATES.map((spec) => [spec.name, initialValues(spec)]),
    );
    if (articleId) {
      all.article = {
        ...all.article,
        type: tpl && ARTICLE_TYPES.includes(tpl) ? tpl : "article",
        id: articleId,
        title,
        channel: channelsId ?? undefined,
      };
    }
    return all;
  }, [tpl, articleId, title, channelsId]);
  // 入口换了文章 / 章节就丢掉改过的值，回到新的预填值
  const [state, setState] = useState({ base: prefilled, values: prefilled });
  const values = state.base === prefilled ? state.values : prefilled;
  const setValues = (next: Record<string, TplValues>) =>
    setState({ base: prefilled, values: next });

  const spec = TEMPLATES.find((t) => t.name === active) ?? TEMPLATES[0];
  const current = values[spec.name] ?? {};
  const code = buildTplCode(spec, current);

  /** 预览按哪些 channel 渲染：表单里填的优先，其次是入口传来的 */
  const previewChannels = (current.channel ?? channelsId ?? "")
    .split(/[_,]/)
    .filter((c) => c !== "");

  const handleCancel = () => {
    setInnerOpen(false);
    onClose?.();
  };

  const tabLabel = (name: string) => {
    const id = `tpl-builder.tpl.${tplKey(name)}`;
    return intl.messages[id] ? `${intl.formatMessage({ id })} ${name}` : name;
  };

  const panel = (
    <div>
      {intl.messages[`tpl-builder.desc.${tplKey(spec.name)}`] ? (
        <Paragraph type="secondary">
          {intl.formatMessage({ id: `tpl-builder.desc.${tplKey(spec.name)}` })}
        </Paragraph>
      ) : null}
      <TplForm
        spec={spec}
        values={current}
        onChange={(v) => setValues({ ...values, [spec.name]: v })}
      />
      <Divider titlePlacement="start" plain>
        {intl.formatMessage({ id: "tpl-builder.code" })}
      </Divider>
      <Input.TextArea
        value={code}
        readOnly
        autoSize={{ minRows: 1, maxRows: 8 }}
        style={{ fontFamily: "monospace" }}
      />
      <Text copyable={{ text: code }}>
        {intl.formatMessage({ id: "buttons.copy" })}
      </Text>
      <Divider titlePlacement="start" plain>
        {intl.formatMessage({ id: "buttons.preview" })}
      </Divider>
      <TplPreview code={code} channels={previewChannels} />
    </div>
  );

  return (
    <>
      <span onClick={() => setInnerOpen(true)}>{trigger}</span>
      <Modal
        style={{ top: 20, paddingBottom: 0 }}
        width={1000}
        footer={false}
        title={intl.formatMessage({ id: "tpl-builder.title" })}
        open={open || innerOpen}
        onCancel={handleCancel}
      >
        {/*
          弹窗不超出浏览器：内容区高度 = 视口 − 弹窗上下边距、标题与内边距。
          模板列表与修改区同高、各自滚动，换模板时修改区高度不变。
          不用竖排 Tabs：限高后它把放不下的页签收进「更多」下拉，二十个模板大半看不见。
        */}
        <div style={{ display: "flex", height: BODY_HEIGHT }}>
          <Menu
            mode="inline"
            selectedKeys={[spec.name]}
            onClick={({ key }) => setActive(key)}
            style={{ width: 200, flexShrink: 0, overflowY: "auto" }}
            items={TEMPLATES.map((t) => ({
              key: t.name,
              label: tabLabel(t.name),
            }))}
          />
          {/* 只渲染当前模板：预览会请求后端 */}
          <div
            className="tpl-builder-panel"
            style={{
              flex: 1,
              minWidth: 0,
              overflowY: "auto",
              padding: "0 8px 0 24px",
            }}
          >
            {panel}
          </div>
        </div>
      </Modal>
    </>
  );
};

export default TplBuilderWidget;
