import { Button, Form, Input, InputNumber, Select, Space } from "antd";
import { FolderOpenOutlined } from "@ant-design/icons";
import { useIntl } from "react-intl";

import { useAppSelector } from "../../hooks";
import { currentUser } from "../../reducers/current-user";
import ArticleListModal from "../article/ArticleListModal";
import ChannelTableModal from "../channel/ChannelTableModal";
import AttachmentDialog from "../attachment/AttachmentDialog";
import {
  isVisible,
  tplKey,
  type TplField,
  type TplSpec,
  type TplValues,
} from "./templates";

interface IWidget {
  spec: TplSpec;
  values: TplValues;
  onChange: (values: TplValues) => void;
}

/**
 * 一个模板的参数表单。受控：值由 TplBuilder 按模板分别保存，切换页签不丢。
 */
const TplForm = ({ spec, values, onChange }: IWidget) => {
  const intl = useIntl();
  const user = useAppSelector(currentUser);

  /** 参数的显示名：先找模板专用的（同名参数在不同模板里意思不同，如 text），再找通用的 */
  const label = (field: TplField) => {
    const own = `tpl-builder.field.${tplKey(spec.name)}.${field.name}`;
    const id = intl.messages[own] ? own : `tpl-builder.field.${field.name}`;
    return intl.messages[id]
      ? `${intl.formatMessage({ id })} (${field.name})`
      : field.name;
  };

  const set = (name: string, value: string | undefined) =>
    onChange({ ...values, [name]: value });

  const control = (field: TplField) => {
    const value = values[field.name];
    const text = (
      <Input
        value={value}
        placeholder={field.placeholder}
        allowClear
        onChange={(e) => set(field.name, e.target.value)}
      />
    );
    switch (field.kind) {
      case "select":
        return (
          <Select
            value={value}
            allowClear={!field.required}
            style={{ width: 200 }}
            options={field.options?.map((o) => ({ value: o, label: o }))}
            onChange={(v: string | undefined) => set(field.name, v)}
          />
        );
      case "textarea":
        return (
          <Input.TextArea
            value={value}
            placeholder={field.placeholder}
            autoSize={{ minRows: 3, maxRows: 10 }}
            onChange={(e) => set(field.name, e.target.value)}
          />
        );
      case "number":
        return (
          <InputNumber
            value={value === undefined || value === "" ? null : Number(value)}
            min={0}
            style={{ width: 200 }}
            onChange={(v) =>
              set(field.name, v === null ? undefined : String(v))
            }
          />
        );
      case "article":
        return (
          <Space.Compact style={{ width: "100%" }}>
            {text}
            {/* 只有 type=article 能从文章列表里挑；章节、段落的 id 是经文坐标 */}
            {(values.type ?? "article") === "article" ? (
              <ArticleListModal
                studioName={user?.realName}
                multiple={false}
                trigger={
                  <Button
                    icon={<FolderOpenOutlined />}
                    title={intl.formatMessage({
                      id: "tpl-builder.select.article",
                    })}
                  />
                }
                onSelect={(id: string, title: string) =>
                  onChange({
                    ...values,
                    id,
                    title: values.title ? values.title : title,
                  })
                }
              />
            ) : null}
          </Space.Compact>
        );
      case "channel":
        return (
          <Space.Compact style={{ width: "100%" }}>
            {text}
            <ChannelTableModal
              trigger={
                <Button
                  icon={<FolderOpenOutlined />}
                  title={intl.formatMessage({
                    id: "tpl-builder.select.channel",
                  })}
                />
              }
              onSelect={(channel) => set(field.name, channel.id)}
            />
          </Space.Compact>
        );
      case "attachment":
        return (
          <Space.Compact style={{ width: "100%" }}>
            {text}
            <AttachmentDialog
              trigger={
                <Button
                  icon={<FolderOpenOutlined />}
                  title={intl.formatMessage({
                    id: "tpl-builder.select.attachment",
                  })}
                />
              }
              onSelect={(file) =>
                onChange({
                  ...values,
                  [field.name]: file.url,
                  title: values.title ? values.title : file.title,
                })
              }
            />
          </Space.Compact>
        );
      default:
        return text;
    }
  };

  return (
    <Form layout="vertical" size="small">
      {spec.fields
        .filter((f) => isVisible(f, values))
        .map((field) => (
          <Form.Item
            key={field.name}
            label={label(field)}
            required={field.required}
            style={{ marginBottom: 8 }}
          >
            {control(field)}
          </Form.Item>
        ))}
    </Form>
  );
};

export default TplForm;
