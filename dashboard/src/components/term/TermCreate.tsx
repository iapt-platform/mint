import { useIntl } from "react-intl";
import { useRef } from "react";
import { Button, Form, message } from "antd";
import {
  ModalForm,
  ProForm,
  ProFormInstance,
} from "@ant-design/pro-components";
import { PlusOutlined } from "@ant-design/icons";

import { ITermResponse, ITermCreateResponse } from "../api/Term";
import { get } from "../../request";

import TermEditInner from "./TermEditInner";

interface IFormData {
  word: string;
  tag: string;
  meaning: string;
  meaning2: string;
  note: string;
  channel: string;
  lang: string;
}

export interface IWidgetDictCreate {
  studio?: string;
  isCreate?: boolean;
  wordId?: string;
  word?: string;
  channel?: string;
  type?: "inline" | "modal";
}
const Widget = ({
  studio,
  isCreate,
  wordId,
  word,
  channel,
  type = "modal",
}: IWidgetDictCreate) => {
  const intl = useIntl();
  const [form] = Form.useForm<IFormData>();
  const formRef = useRef<ProFormInstance>();
  console.log("term render");
  const waitTime = (time: number = 100) => {
    return new Promise((resolve) => {
      setTimeout(() => {
        resolve(true);
      }, time);
    });
  };
  const editTrigger = (
    <span>
      {intl.formatMessage({
        id: "buttons.edit",
      })}
    </span>
  );
  const createTrigger = (
    <Button type="primary" icon={<PlusOutlined />}>
      {intl.formatMessage({
        id: "buttons.create",
      })}
    </Button>
  );

  const onFinish = async (values: IFormData) => {
    await waitTime(2000);
    console.log(values.word);
    message.success("提交成功");
    return true;
  };
  const request = async () => {
    console.log("request");
    let url: string;
    let data: IFormData = {
      word: "",
      tag: "",
      meaning: "",
      meaning2: "",
      note: "",
      lang: "",
      channel: "",
    };
    if (typeof isCreate !== "undefined" && isCreate === false) {
      // 如果是编辑，就从服务器拉取数据。
      url = "/v2/terms/" + (isCreate ? "" : wordId);
      console.log(url);
      const res = await get<ITermResponse>(url);
      console.log(res);
      return {
        word: res.data.word,
        tag: res.data.tag,
        meaning: res.data.meaning,
        meaning2: res.data.other_meaning,
        note: res.data.note,
        lang: res.data.language,
        channel: res.data.channal,
      };
    } else if (typeof channel !== "undefined") {
      //在channel新建
      url = `/v2/terms?view=create-by-channel&channel=${channel}&word=${word}`;
      const res = await get<ITermCreateResponse>(url);
      console.log(res);
      data = {
        word: res.data.word,
        tag: "",
        meaning: "",
        meaning2: "",
        note: "",
        lang: res.data.language,
        channel: "",
      };
      return data;
    } else if (typeof studio !== "undefined") {
      //在studio新建
      url = `/v2/terms?view=create-by-studio&studio=${studio}&word=${word}`;
    } else {
      return {
        word: "",
        tag: "",
        meaning: "",
        meaning2: "",
        note: "",
        lang: "",
        channel: "",
      };
    }
    return data;
  };

  const formProps = {
    form: form,
    autoFocusFirstInput: true,
    onFinish: onFinish,
    request: request,
  };

  let formTerm: JSX.Element;
  switch (type) {
    case "inline":
      formTerm = (
        <>
          <Button
            onClick={() => {
              formRef?.current?.resetFields();
            }}
          >
            {word}
          </Button>
          <ProForm<IFormData> {...formProps}>
            <TermEditInner />
          </ProForm>
        </>
      );
      break;
    case "modal":
      formTerm = (
        <>
          <ModalForm<IFormData>
            title={intl.formatMessage({
              id: isCreate ? "buttons.create" : "buttons.edit",
            })}
            trigger={isCreate ? createTrigger : editTrigger}
            modalProps={{
              destroyOnClose: true,
              onCancel: () => console.log("run"),
            }}
            submitTimeout={2000}
            {...formProps}
          >
            <TermEditInner />
          </ModalForm>
        </>
      );
      break;
    default:
      formTerm = <></>;
      break;
  }
  return formTerm;
};

export default Widget;
