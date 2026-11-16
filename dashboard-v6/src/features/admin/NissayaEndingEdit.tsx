import { ModalForm, ProForm, ProFormText } from "@ant-design/pro-components";
import { App, Form } from "antd";
import { useState, type JSX } from "react";
import { useIntl } from "react-intl";
import { fetchNissayaEnding, saveNissayaEnding, type INissayaEnding } from "../../api/admin";
import GrammarSelect from "../../components/admin/GrammarSelect";
import LangSelect from "../../components/general/LangSelect";

interface IWidget {
  trigger?: JSX.Element;
  /** 有 id 是编辑，没有是新建 */
  id?: string;
  onSuccess?: () => void;
}

/** 新建 / 编辑一条 Nissaya 语尾。 */
const NissayaEndingEdit = ({ trigger = <>{"trigger"}</>, id, onSuccess }: IWidget) => {
  const intl = useIntl();
  const { message } = App.useApp();
  const [form] = Form.useForm<INissayaEnding>();
  const [title, setTitle] = useState<string | undefined>(id ? "" : intl.formatMessage({ id: "buttons.create" }));

  return (
    <ModalForm<INissayaEnding>
      title={title}
      trigger={trigger}
      form={form}
      autoFocusFirstInput
      modalProps={{ destroyOnHidden: true }}
      submitTimeout={2000}
      onFinish={async (values) => {
        const data = {
          ...values,
          from: { spell: values.fromSpell, case: values.fromCase ? values.fromCase : undefined },
        };
        const res = await saveNissayaEnding(data, id);
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
              const res = await fetchNissayaEnding(id);
              if (!res.ok) {
                return { id: undefined, ending: "", relation: "", lang: "" };
              }
              setTitle(res.data.ending);
              return {
                id,
                ending: res.data.ending,
                relation: res.data.relation,
                from: res.data.from,
                fromCase: res.data.from?.case,
                fromSpell: res.data.from?.spell,
                lang: res.data.lang,
              };
            }
          : undefined
      }
    >
      <ProForm.Group>
        <ProFormText
          width="md"
          name="ending"
          label={intl.formatMessage({ id: "forms.fields.ending.label" })}
          tooltip={intl.formatMessage({ id: "admin.nissaya.ending.tooltip" })}
        />
        <LangSelect width="md" />
      </ProForm.Group>
      <ProForm.Group>
        <GrammarSelect name="fromCase" />
        <ProFormText width="md" name="fromSpell" label={intl.formatMessage({ id: "buttons.spell" })} />
      </ProForm.Group>
      <ProForm.Group>
        <ProFormText width="md" name="relation" label={intl.formatMessage({ id: "forms.fields.relation.label" })} />
      </ProForm.Group>
    </ModalForm>
  );
};

export default NissayaEndingEdit;
