import { ModalForm, ProForm, ProFormSelect, ProFormText } from "@ant-design/pro-components";
import { App, Form } from "antd";
import { useState, type JSX } from "react";
import { useIntl } from "react-intl";
import { fetchRelation, saveRelation } from "../../api/admin";
import type { IRelation } from "../../api/relation";
import GrammarSelect from "../../components/admin/GrammarSelect";

interface IWidget {
  trigger?: JSX.Element;
  /** 有 id 是编辑，没有是新建 */
  id?: string;
  onSuccess?: () => void;
}

/** 新建 / 编辑一条语法关系。 */
const RelationEdit = ({ trigger = <>{"trigger"}</>, id, onSuccess }: IWidget) => {
  const intl = useIntl();
  const { message } = App.useApp();
  const [form] = Form.useForm<IRelation>();
  const [title, setTitle] = useState<string>(id ? "" : intl.formatMessage({ id: "buttons.create" }));

  return (
    <ModalForm<IRelation>
      title={title}
      trigger={trigger}
      form={form}
      autoFocusFirstInput
      modalProps={{ destroyOnHidden: true }}
      submitTimeout={3000}
      onFinish={async (values) => {
        const data = {
          ...values,
          from: { spell: values.fromSpell, case: values.fromCase },
          to: { spell: values.toSpell, case: values.toCase },
        };
        const res = await saveRelation(data, id);
        if (res.ok) {
          message.success(intl.formatMessage({ id: "flashes.success" }));
          onSuccess?.();
        } else {
          message.error(res.message);
        }
        return true;
      }}
      request={
        id
          ? async () => {
              const res = await fetchRelation(id);
              if (!res.ok) {
                return { id: undefined, name: "" };
              }
              setTitle(res.data.name);
              return {
                id,
                name: res.data.name,
                case: res.data.case,
                from: res.data.from,
                fromCase: res.data.from?.case,
                fromSpell: res.data.from?.spell,
                to: res.data.to,
                toCase: res.data.to?.case,
                toSpell: res.data.to?.spell,
                match: res.data.match ?? undefined,
                category: res.data.category,
              };
            }
          : undefined
      }
    >
      <ProForm.Group>
        <ProFormText width="md" name="name" label={intl.formatMessage({ id: "forms.fields.name.label" })} />
      </ProForm.Group>
      <ProForm.Group title={intl.formatMessage({ id: "forms.fields.from.label" })}>
        <GrammarSelect name="fromCase" />
        <ProFormText width="md" name="fromSpell" label={intl.formatMessage({ id: "buttons.spell" })} />
      </ProForm.Group>
      <ProForm.Group title={intl.formatMessage({ id: "forms.fields.to.label" })}>
        <GrammarSelect name="toCase" />
        <ProFormText width="md" name="toSpell" label={intl.formatMessage({ id: "buttons.spell" })} />
      </ProForm.Group>
      <ProForm.Group>
        <ProFormSelect
          options={["gender", "number", "case"].map((item) => ({ value: item, label: item }))}
          fieldProps={{ mode: "tags" }}
          width="md"
          name="match"
          allowClear={false}
          label={intl.formatMessage({ id: "forms.fields.match.label" })}
        />
        <ProFormText width="md" name="category" label={intl.formatMessage({ id: "forms.fields.category.label" })} />
      </ProForm.Group>
    </ModalForm>
  );
};

export default RelationEdit;
