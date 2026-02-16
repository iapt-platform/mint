import { useIntl } from "react-intl";
import {
  ProForm,
  ProFormInstance,
  ProFormSelect,
  ProFormText,
} from "@ant-design/pro-components";
import { Tag, message } from "antd";

import { get, post, put } from "../../request";
import { useRef } from "react";
import { ITagRequest, ITagResponse } from "../api/Tag";

interface IWidgetCourseCreate {
  studio?: string;
  tagId?: string;
  onCreate?: Function;
}
const TagCreateWidget = ({ studio, tagId, onCreate }: IWidgetCourseCreate) => {
  const intl = useIntl();
  const formRef = useRef<ProFormInstance>();

  const _color = [
    "b60205",
    "d93f0b",
    "fbca04",
    "0e8a16",
    "006b75",
    "1d76db",
    "0052cc",
    "5319e7",
    "e99695",
    "f9d0c4",
    "fef2c0",
    "c2e0c6",
    "bfdadc",
    "c5def5",
    "bfd4f2",
    "d4c5f9",
  ];
  const colorOptions = _color.map((item) => {
    return {
      value: parseInt(item, 16),
      label: <Tag color={`#${item}`}>{item}</Tag>,
    };
  });

  return (
    <ProForm<ITagRequest>
      formRef={formRef}
      onFinish={async (values: ITagRequest) => {
        console.log(values);
        if (studio) {
          values.studio = studio;
          let url = `/v2/tag`;
          if (tagId) {
            url += `/${tagId}`;
          }
          console.info("CourseCreateWidget api request", url, values);
          let res: any;
          if (tagId) {
            res = await put<ITagRequest, ITagResponse>(url, values);
          } else {
            res = await post<ITagRequest, ITagResponse>(url, values);
          }

          console.info("CourseCreateWidget api response", res);
          if (res.ok) {
            message.success(intl.formatMessage({ id: "flashes.success" }));
            formRef.current?.resetFields(["title"]);
            if (typeof onCreate !== "undefined") {
              onCreate();
            }
          } else {
            message.error(res.message);
          }
        } else {
          console.error("no studio");
        }
      }}
      request={
        tagId
          ? async () => {
              const url = `/v2/tag/${tagId}`;
              console.info("api request", url);
              const res = await get<ITagResponse>(url);
              console.info("api response", res);
              return res.data;
            }
          : undefined
      }
    >
      <ProForm.Group>
        <ProFormText
          width="md"
          name="name"
          required
          label={intl.formatMessage({ id: "forms.fields.name.label" })}
          rules={[
            {
              max: 32,
              min: 1,
            },
          ]}
        />
      </ProForm.Group>
      <ProForm.Group>
        <ProFormText
          width="md"
          name="description"
          label={intl.formatMessage({ id: "forms.fields.description.label" })}
          rules={[
            {
              max: 256,
            },
          ]}
        />
      </ProForm.Group>
      <ProForm.Group>
        <ProFormSelect
          width="md"
          name="color"
          label={intl.formatMessage({ id: "forms.fields.color.label" })}
          options={colorOptions}
        />
      </ProForm.Group>
    </ProForm>
  );
};

export default TagCreateWidget;
