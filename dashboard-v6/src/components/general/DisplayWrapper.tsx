/**
 * DisplayWrapper
 *
 * 将任意内容包装为可切换显示模式的通用容器组件。
 *
 * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
 * Props
 * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
 *
 *  style       TDisplayStyle   显示模式，默认 "modal"
 *  title       ReactNode       标题，用于 Modal/Card/Toggle 标题栏，
 *                              以及 icon/tag/reference/link 的文字内容
 *  trigger     ReactNode       自定义触发区域内容（modal/popover/link 模式）
 *                              不传时降级为 title
 *  href        string          link 模式的跳转地址；
 *                              modal 模式下 Ctrl/Cmd+Click 时新窗口打开
 *  icon        ReactNode       icon / tag 模式的图标，不传时降级为 <FileOutlined />
 *  children    ReactNode       弹出层 / 展开区域内渲染的内容
 *
 * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
 * 显示模式一览
 * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
 *
 *  modal       点击 trigger 弹出 Modal；Ctrl/Cmd+Click 新窗口打开 href    （点击）
 *  popover     点击 trigger 弹出气泡框，含关闭按钮                        （点击）
 *  card        常驻卡片，直接渲染 children                               （常驻）
 *  window      常驻裸 div，无任何装饰，直接渲染 children                  （常驻）
 *  toggle      折叠面板，点击标题展开 / 收起                              （点击）
 *  link        纯文字链接，点击新窗口打开 href                            （点击跳转）
 *  icon        显示图标，hover 弹出 popover                              （hover）
 *  tag         Antd Tag 带边框，显示 icon + title，hover 弹出 popover     （hover）
 *  reference   行内文字加虚线下划线，hover 弹出 popover，适合论文引用场景   （hover）
 *
 * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
 * 调用范例
 * ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
 *
 * // modal — 点击弹出；Ctrl/Cmd+Click 新窗口打开
 * <DisplayWrapper style="modal" title="视频标题" href="https://example.com/video">
 *   <Video src="https://example.com/video.mp4" />
 * </DisplayWrapper>
 *
 * // modal — 自定义 trigger
 * <DisplayWrapper style="modal" title="视频标题" trigger={<span><PlayCircleOutlined /> 点击播放</span>}>
 *   <Video src="https://example.com/video.mp4" />
 * </DisplayWrapper>
 *
 * // popover — 点击触发，含关闭按钮
 * <DisplayWrapper style="popover" title="预览" trigger={<span>点击查看</span>}>
 *   <Image src="https://example.com/image.png" />
 * </DisplayWrapper>
 *
 * // card — 常驻卡片
 * <DisplayWrapper style="card" title="视频">
 *   <Video src="https://example.com/video.mp4" />
 * </DisplayWrapper>
 *
 * // window — 常驻裸容器
 * <DisplayWrapper style="window">
 *   <Video src="https://example.com/video.mp4" />
 * </DisplayWrapper>
 *
 * // toggle — 折叠面板
 * <DisplayWrapper style="toggle" title="点击展开">
 *   <Video src="https://example.com/video.mp4" />
 * </DisplayWrapper>
 *
 * // link — 点击新窗口跳转
 * <DisplayWrapper style="link" title="查看原始文件" href="https://example.com/file.pdf" />
 *
 * // icon — hover 显示 popover；不传 icon 降级为 <FileOutlined />
 * <DisplayWrapper style="icon" title="附件说明" icon={<PaperClipOutlined />}>
 *   <p>这是附件的详细说明内容。</p>
 * </DisplayWrapper>
 *
 * // tag — Antd Tag，hover 显示 popover；不传 icon 降级为 <FileOutlined />
 * <DisplayWrapper style="tag" title="参考资料" icon={<BookOutlined />}>
 *   <p>Smith et al., 2024 — Some Paper Title</p>
 * </DisplayWrapper>
 *
 * // reference — 行内虚线下划线，hover 显示 popover，适合论文内文引用
 * <DisplayWrapper style="reference" title="Smith et al., 2024">
 *   <p>Some Paper Title, Journal of Examples, Vol. 1, pp. 1–10.</p>
 * </DisplayWrapper>
 */

import { useState } from "react";
import { Button, Card, Collapse, Modal, Popover, Tag, Typography } from "antd";
import { CloseOutlined, EditOutlined, FileOutlined } from "@ant-design/icons";

// ---- Types ----

export type TDisplayStyle =
  | "modal"
  | "popover"
  | "card"
  | "window"
  | "toggle"
  | "link"
  | "icon"
  | "tag"
  | "reference";

export interface IDisplayWrapperProps {
  /** 显示模式，默认 modal */
  style?: TDisplayStyle;
  /** Modal / Card / Toggle 标题栏文字 */
  title?: React.ReactNode;
  /** modal / popover / link / icon / tag / reference 的点击/hover 触发区域内容 */
  trigger?: React.ReactNode;
  /** link 模式跳转地址；modal 模式 Ctrl+Click 跳转地址 */
  href?: string;
  /** icon / tag 模式使用的图标，不传时降级为 <FileOutlined /> */
  icon?: React.ReactNode;
  /** 弹出层 / 展开区域内渲染的内容 */
  children?: React.ReactNode;
}

// ---- Hover Popover（icon / tag / reference 共用） ----

interface IHoverPopoverProps {
  triggerNode: React.ReactNode;
  title?: React.ReactNode;
  children?: React.ReactNode;
}

const HoverPopover = ({ triggerNode, title, children }: IHoverPopoverProps) => (
  <Popover
    title={title}
    content={children}
    styles={{ container: { width: 700, maxHeight: "90dvh" } }}
    trigger="hover"
    placement="bottom"
  >
    {triggerNode}
  </Popover>
);

// ---- Click Popover（原 popover 模式） ----

const ClickPopover = ({ trigger, title, children }: IDisplayWrapperProps) => {
  const [open, setOpen] = useState(false);
  return (
    <Popover
      title={
        <div
          style={{
            display: "flex",
            justifyContent: "space-between",
            alignItems: "center",
          }}
        >
          {title}
          <Button
            type="link"
            size="small"
            icon={<CloseOutlined />}
            onClick={() => setOpen(false)}
          />
        </div>
      }
      content={children}
      trigger="click"
      placement="bottom"
      open={open}
    >
      <span onClick={() => setOpen(true)} style={{ cursor: "pointer" }}>
        {trigger ?? title}
      </span>
    </Popover>
  );
};

// ---- Modal 模式 ----

const ModalDisplay = ({
  trigger,
  title,
  href,
  children,
}: IDisplayWrapperProps) => {
  const [open, setOpen] = useState(false);
  return (
    <>
      <Typography.Link
        onClick={(e: React.MouseEvent<HTMLElement>) => {
          if ((e.ctrlKey || e.metaKey) && href) {
            window.open(href, "_blank");
          } else {
            setOpen(true);
          }
        }}
      >
        {trigger ?? title}
      </Typography.Link>
      <Modal
        width={800}
        destroyOnHidden
        style={{ maxWidth: "90%", top: 20 }}
        title={title}
        open={open}
        onOk={() => setOpen(false)}
        onCancel={() => setOpen(false)}
        footer={[]}
      >
        {children}
      </Modal>
    </>
  );
};

// ---- Card 模式 ----

const CardDisplay = ({ title, children }: IDisplayWrapperProps) => (
  <Card title={title}>{children}</Card>
);

// ---- Window 模式 ----

const WindowDisplay = ({ children }: IDisplayWrapperProps) => (
  <div>{children}</div>
);

// ---- Toggle 模式 ----

const ToggleDisplay = ({ title, children }: IDisplayWrapperProps) => (
  <Collapse bordered={false}>
    <Collapse.Panel header={title} key="panel">
      {children}
    </Collapse.Panel>
  </Collapse>
);

// ---- Link 模式 ----

const LinkDisplay = ({ trigger, title, href }: IDisplayWrapperProps) => (
  <Typography.Link onClick={() => href && window.open(href, "_blank")}>
    {trigger ?? title}
  </Typography.Link>
);

// ---- Icon 模式（hover popover） ----

const IconDisplay = ({ icon, title, children }: IDisplayWrapperProps) => {
  const iconNode = icon ?? <FileOutlined />;
  return (
    <HoverPopover
      title={title}
      triggerNode={
        <span style={{ cursor: "pointer", fontSize: 16 }}>{iconNode}</span>
      }
    >
      {children}
    </HoverPopover>
  );
};

// ---- Tag 模式（hover popover） ----

const TagDisplay = ({ icon, title, children }: IDisplayWrapperProps) => {
  const iconNode = icon ?? <FileOutlined />;
  return (
    <HoverPopover
      title={title}
      triggerNode={
        <Tag icon={iconNode} style={{ cursor: "pointer" }}>
          {title}
        </Tag>
      }
    >
      {children}
    </HoverPopover>
  );
};

// ---- Reference 模式（hover popover） ----

const referenceStyle: React.CSSProperties = {
  borderBottom: "1px dashed currentColor",
  cursor: "help",
  textDecoration: "none",
  color: "inherit",
};

const ReferenceDisplay = ({ title, children }: IDisplayWrapperProps) => {
  const [open, setOpen] = useState(false);
  return (
    <>
      <HoverPopover
        title={title}
        triggerNode={<span style={referenceStyle}>{title}</span>}
      >
        {children}
      </HoverPopover>
      <Button
        type="link"
        icon={<EditOutlined />}
        onClick={() => setOpen((v) => !v)}
      />
      {open && children}
    </>
  );
};

// ---- DisplayWrapper 主组件 ----

export const DisplayWrapper = (props: IDisplayWrapperProps) => {
  const { style = "modal" } = props;

  switch (style) {
    case "modal":
      return <ModalDisplay {...props} />;
    case "popover":
      return <ClickPopover {...props} />;
    case "card":
      return <CardDisplay {...props} />;
    case "window":
      return <WindowDisplay {...props} />;
    case "toggle":
      return <ToggleDisplay {...props} />;
    case "link":
      return <LinkDisplay {...props} />;
    case "icon":
      return <IconDisplay {...props} />;
    case "tag":
      return <TagDisplay {...props} />;
    case "reference":
      return <ReferenceDisplay {...props} />;
    default:
      return null;
  }
};

export default DisplayWrapper;
